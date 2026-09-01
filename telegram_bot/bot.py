import os
import subprocess
import json
import logging
from datetime import datetime, time
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import (
    Application,
    CommandHandler,
    CallbackQueryHandler,
    MessageHandler,
    ContextTypes,
    ConversationHandler,
    filters,
)

# Setup Logging
logging.basicConfig(
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s", level=logging.INFO
)

# State untuk ConversationHandler (Input Text dari Tombol)
WAITING_NEWACC_INPUT, WAITING_DELACC_INPUT = range(2)

# Config Environment
TOKEN = os.getenv("TELEGRAM_BOT_TOKEN")
RAW_CHAT_IDS = os.getenv("TELEGRAM_CHAT_ID", "")

ALLOWED_CHAT_IDS = set(
    int(chat_id.strip())
    for chat_id in RAW_CHAT_IDS.split(",")
    if chat_id.strip().isdigit()
)

def is_authorized(user_id: int) -> bool:
    return user_id in ALLOWED_CHAT_IDS


# ==========================================
# 🎹 KEYBOARD LAYOUTS (TAMPILAN TOMBOL)
# ==========================================

def main_menu_keyboard():
    return InlineKeyboardMarkup([
        [
            InlineKeyboardButton("📊 Status Service", callback_data="check_status"),
            InlineKeyboardButton("💾 Backup Database", callback_data="menu_backup"),
        ],
        [
            InlineKeyboardButton("👥 Kelola User", callback_data="menu_user"),
            InlineKeyboardButton("🔄 Restart Service", callback_data="menu_restart"),
        ]
    ])

def user_menu_keyboard():
    return InlineKeyboardMarkup([
        [
            InlineKeyboardButton("📋 List User", callback_data="list_user"),
            InlineKeyboardButton("➕ Tambah User", callback_data="prompt_new_user"),
        ],
        [
            InlineKeyboardButton("🗑️ Hapus User", callback_data="prompt_del_user"),
        ],
        [
            InlineKeyboardButton("⬅️ Kembali ke Menu Utama", callback_data="main_menu"),
        ]
    ])

def restart_menu_keyboard():
    return InlineKeyboardMarkup([
        [
            InlineKeyboardButton("🌐 Restart Webserver", callback_data="restart_webserver"),
            InlineKeyboardButton("⚙️ Restart App (Laravel)", callback_data="restart_app"),
        ],
        [
            InlineKeyboardButton("💥 Restart SEMUA Service", callback_data="restart_all"),
        ],
        [
            InlineKeyboardButton("⬅️ Kembali ke Menu Utama", callback_data="main_menu"),
        ]
    ])

def backup_menu_keyboard():
    return InlineKeyboardMarkup([
        [
            InlineKeyboardButton("📦 Backup Sekarang", callback_data="do_backup_now"),
        ],
        [
            InlineKeyboardButton("⬅️ Kembali ke Menu Utama", callback_data="main_menu"),
        ]
    ])

def cancel_keyboard():
    return InlineKeyboardMarkup([
        [InlineKeyboardButton("❌ Batal / Kembali", callback_data="cancel_action")]
    ])


# ==========================================
# 📦 HELPER BACKUP & PENGIRIMAN FILE
# ==========================================

async def execute_and_send_backup(bot, chat_id: int = None):
    """
    Eksekusi backup SQLite, hapus file >30 hari, dan kirim dokumen ke Telegram.
    """
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    backup_filename = f"backup-{timestamp}.sqlite"
    tmp_local_path = f"/tmp/{backup_filename}"

    # 1. Jalankan backup & retention di container slbb-app
    cmd_backup = [
        "docker", "exec", "slbb-app",
        "sh", "-c",
        f"mkdir -p /var/www/database/backups && "
        f"cp /var/www/database/database.sqlite /var/www/database/backups/{backup_filename} && "
        f"find /var/www/database/backups -type f -name '*.sqlite' -mtime +30 -delete"
    ]
    subprocess.run(cmd_backup, capture_output=True, text=True)

    # 2. Copy file backup dari container slbb-app ke container bot /tmp
    cmd_copy = ["docker", "cp", f"slbb-app:/var/www/database/database.sqlite", tmp_local_path]
    res_cp = subprocess.run(cmd_copy, capture_output=True, text=True)

    if res_cp.returncode != 0:
        error_msg = f"❌ **Gagal mengambil file backup:**\n```{res_cp.stderr}```"
        if chat_id:
            await bot.send_message(chat_id=chat_id, text=error_msg, parse_mode="Markdown")
        return False

    # 3. Kirim file dokumen ke Telegram
    targets = [chat_id] if chat_id else list(ALLOWED_CHAT_IDS)
    caption_text = (
        f"📦 **Backup Database SQLite**\n"
        f"📅 **Tanggal:** `{datetime.now().strftime('%d-%m-%Y %H:%M:%S')}`\n"
        f"📁 **File:** `{backup_filename}`"
    )

    for target_id in targets:
        try:
            with open(tmp_local_path, "rb") as doc:
                await bot.send_document(
                    chat_id=target_id,
                    document=doc,
                    filename=backup_filename,
                    caption=caption_text,
                    parse_mode="Markdown"
                )
        except Exception as e:
            logging.error(f"Gagal mengirim file backup ke {target_id}: {e}")

    # 4. Bersihkan file temporary di container bot
    if os.path.exists(tmp_local_path):
        os.remove(tmp_local_path)

    return True


async def scheduled_backup_job(context: ContextTypes.DEFAULT_TYPE):
    """Jadwal Otomatis Harian (Jam 02:00 AM)"""
    logging.info("Menjalankan jadwal backup otomatis harian...")
    await execute_and_send_backup(context.bot)


# ==========================================
# 🎮 COMMAND & BUTTON HANDLERS
# ==========================================

async def start_command(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.effective_chat.id
    if not is_authorized(user_id):
        await update.message.reply_text("⛔ Akses ditolak! Anda tidak memiliki izin.")
        return

    await update.message.reply_text(
        "🤖 **Panel Kontrol Docker Server SLBB**\n"
        "Silakan pilih menu di bawah ini:",
        reply_markup=main_menu_keyboard(),
        parse_mode="Markdown"
    )

async def help_command(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.effective_chat.id
    if not is_authorized(user_id):
        await update.message.reply_text("⛔ Akses ditolak!")
        return

    await update.message.reply_text(
        "📖 **PANDUAN PENGGUNAAN BOT CONTROL**\n\n"
        "Gunakan tombol menu interaktif (`/start`) untuk mengelola seluruh sistem:\n"
        "• **Status Service:** Cek status container Docker.\n"
        "• **Backup Database:** Backup manual & kirim otomatis file `.sqlite`.\n"
        "• **Kelola User:** Tambah, lihat, atau hapus user Laravel.\n"
        "• **Restart Service:** Restart Nginx, App, atau Semua container.",
        parse_mode="Markdown"
    )

async def button_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()

    user_id = query.message.chat.id
    if not is_authorized(user_id):
        await query.edit_message_text("⛔ Akses Ditolak!")
        return

    action = query.data

    # NAVIGASI MENU
    if action == "main_menu":
        await query.edit_message_text(
            "🤖 **Panel Kontrol Docker Server SLBB**\nSilakan pilih menu:",
            reply_markup=main_menu_keyboard(),
            parse_mode="Markdown"
        )
    elif action == "menu_user":
        await query.edit_message_text(
            "👥 **Menu Kelola User**\nPilih aksi yang ingin dilakukan:",
            reply_markup=user_menu_keyboard(),
            parse_mode="Markdown"
        )
    elif action == "menu_restart":
        await query.edit_message_text(
            "🔄 **Menu Restart Service**\nPilih service yang akan direstart:",
            reply_markup=restart_menu_keyboard(),
            parse_mode="Markdown"
        )
    elif action == "menu_backup":
        await query.edit_message_text(
            "💾 **Menu Backup Database**\nBackup dilakukan otomatis setiap jam 02:00 Pagi, atau jalankan manual sekarang:",
            reply_markup=backup_menu_keyboard(),
            parse_mode="Markdown"
        )

    # AKSI DOCKER RESTART & STATUS
    elif action == "check_status":
        res = subprocess.run(["docker", "ps", "--format", "table {{.Names}}\t{{.Status}}"], capture_output=True, text=True)
        await query.edit_message_text(
            f"📊 **Status Container Saat Ini:**\n```\n{res.stdout}\n```",
            reply_markup=main_menu_keyboard(),
            parse_mode="Markdown"
        )
    elif action == "restart_webserver":
        await query.edit_message_text("⏳ Sedang merestart Nginx Webserver...")
        res = subprocess.run(["docker", "restart", "slbb-webserver"], capture_output=True, text=True)
        msg = "✅ **Nginx Webserver berhasil direstart!**" if res.returncode == 0 else f"❌ Gagal: {res.stderr}"
        await query.edit_message_text(msg, reply_markup=restart_menu_keyboard(), parse_mode="Markdown")
    
    elif action == "restart_app":
        await query.edit_message_text("⏳ Sedang merestart Aplikasi Laravel...")
        res = subprocess.run(["docker", "restart", "slbb-app"], capture_output=True, text=True)
        msg = "✅ **Aplikasi Laravel berhasil direstart!**" if res.returncode == 0 else f"❌ Gagal: {res.stderr}"
        await query.edit_message_text(msg, reply_markup=restart_menu_keyboard(), parse_mode="Markdown")

    elif action == "restart_all":
        await query.edit_message_text("⏳ Sedang merestart SEMUA container...")
        res = subprocess.run(["docker", "restart", "slbb-webserver", "slbb-app"], capture_output=True, text=True)
        msg = "✅ **Seluruh Service Berhasil Direstart!**" if res.returncode == 0 else f"❌ Gagal: {res.stderr}"
        await query.edit_message_text(msg, reply_markup=restart_menu_keyboard(), parse_mode="Markdown")

    # AKSI KELOLA USER (LIST)
    elif action == "list_user":
        await query.edit_message_text("⏳ Mengambil daftar user dari database...")
        tinker_code = "echo json_encode(\\App\\Models\\User::select('id', 'name', 'nip')->get());"
        cmd = ["docker", "exec", "slbb-app", "php", "artisan", "tinker", f"--execute={tinker_code}"]
        try:
            res = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
            output = res.stdout.strip()
            start_idx, end_idx = output.find("["), output.rfind("]")

            if start_idx != -1 and end_idx != -1:
                users = json.loads(output[start_idx:end_idx + 1])
                if not users:
                    msg = "ℹ️ Belum ada user terdaftar di database."
                else:
                    msg = f"📋 **Daftar User ({len(users)} Akun):**\n\n"
                    for idx, user in enumerate(users, start=1):
                        msg += f"{idx}. **{user.get('name')}**\n   🆔 NIP: `{user.get('nip')}` | ID DB: `{user.get('id')}`\n"
            else:
                msg = f"❌ Gagal membaca data:\n```{res.stdout or res.stderr}```"
        except Exception as e:
            msg = f"❌ Error: {str(e)}"
        
        await query.edit_message_text(msg, reply_markup=user_menu_keyboard(), parse_mode="Markdown")

    # AKSI BACKUP SEKARANG
    elif action == "do_backup_now":
        await query.edit_message_text("⏳ Sedang memproses backup database & mengirim file...")
        success = await execute_and_send_backup(context.bot, chat_id=user_id)
        if success:
            await query.message.reply_text("✅ **Backup selesai & dikirim di atas!**", reply_markup=backup_menu_keyboard(), parse_mode="Markdown")


# ==========================================
# 💬 CONVERSATION HANDLERS (INPUT TEXT VIA TOMBOL)
# ==========================================

async def prompt_new_user(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    await query.edit_message_text(
        "📝 **Tambah User Baru**\n\n"
        "Silakan kirim pesan balasan dengan format:\n"
        "`<NIP> <PASSWORD> <NAMA LENGKAP>`\n\n"
        "Contoh:\n"
        "`199201012020011001 Rahasia123 JOHN DOE`",
        reply_markup=cancel_keyboard(),
        parse_mode="Markdown"
    )
    return WAITING_NEWACC_INPUT

async def process_new_user_input(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.effective_chat.id
    if not is_authorized(user_id):
        return ConversationHandler.END

    args = update.message.text.split()
    if len(args) < 3:
        await update.message.reply_text(
            "❌ **Format salah!** Harap masukkan NIP, Password, dan Nama.\n"
            "Contoh: `199201012020011001 Rahasia123 JOHN DOE`",
            reply_markup=cancel_keyboard(),
            parse_mode="Markdown"
        )
        return WAITING_NEWACC_INPUT

    nip, password, name = args[0], args[1], " ".join(args[2:])
    await update.message.reply_text(f"⏳ Sedang mendaftarkan User `{name}`...", parse_mode="Markdown")

    tinker_code = (
        f"\\App\\Models\\User::create(["
        f"'name' => '{name}', 'nip' => '{nip}', "
        f"'password' => \\Illuminate\\Support\\Facades\\Hash::make('{password}')"
        f"]);"
    )
    cmd = ["docker", "exec", "slbb-app", "php", "artisan", "tinker", f"--execute={tinker_code}"]

    try:
        res = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
        if "App\\Models\\User" in res.stdout or res.returncode == 0:
            msg = f"✅ **Akun Berhasil Dibuat!**\n\n👤 **Nama:** `{name}`\n🆔 **NIP:** `{nip}`\n🔑 **Password:** `{password}`"
        else:
            msg = f"❌ **Gagal:**\n```{res.stderr or res.stdout}```"
    except Exception as e:
        msg = f"❌ **Error:** {str(e)}"

    await update.message.reply_text(msg, reply_markup=user_menu_keyboard(), parse_mode="Markdown")
    return ConversationHandler.END


async def prompt_del_user(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    await query.edit_message_text(
        "🗑️ **Hapus User**\n\n"
        "Silakan kirim pesan balasan dengan NIP atau ID Database user yang ingin dihapus:\n"
        "Contoh: `199201012020011001` atau `5`",
        reply_markup=cancel_keyboard(),
        parse_mode="Markdown"
    )
    return WAITING_DELACC_INPUT

async def process_del_user_input(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.effective_chat.id
    if not is_authorized(user_id):
        return ConversationHandler.END

    target = update.message.text.strip()
    await update.message.reply_text(f"⏳ Sedang menghapus akun `{target}`...", parse_mode="Markdown")

    tinker_code = (
        "$u = \\App\\Models\\User::where('nip', '" + target + "')->orWhere('id', '" + target + "')->first(); "
        "if ($u) { $n = $u->name; $nip = $u->nip; $u->delete(); echo \"DELETED:{$n}:{$nip}\"; } "
        "else { echo \"NOT_FOUND\"; }"
    )
    cmd = ["docker", "exec", "slbb-app", "php", "artisan", "tinker", f"--execute={tinker_code}"]

    try:
        res = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
        output = res.stdout.strip()

        if "DELETED:" in output:
            parts = output.split("DELETED:")[1].split(":")
            msg = f"🗑️ **Akun Berhasil Dihapus!**\n\n👤 **Nama:** `{parts[0]}`\n🆔 **NIP:** `{parts[1] if len(parts)>1 else target}`"
        elif "NOT_FOUND" in output:
            msg = f"❌ **User `{target}` tidak ditemukan!**"
        else:
            msg = f"❌ Gagal:\n```{res.stderr or res.stdout}```"
    except Exception as e:
        msg = f"❌ Error: {str(e)}"

    await update.message.reply_text(msg, reply_markup=user_menu_keyboard(), parse_mode="Markdown")
    return ConversationHandler.END


async def cancel_action(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    if query:
        await query.answer()
        await query.edit_message_text(
            "🤖 **Panel Kontrol Docker Server SLBB**\nSilakan pilih menu:",
            reply_markup=main_menu_keyboard(),
            parse_mode="Markdown"
        )
    return ConversationHandler.END


# ==========================================
# 🚀 MAIN APPLICATION
# ==========================================

def main():
    if not TOKEN or not ALLOWED_CHAT_IDS:
        print("Error: TOKEN atau TELEGRAM_CHAT_ID belum diset di file .env")
        return

    app = Application.builder().token(TOKEN).build()

    # 1. Jadwalkan Backup Otomatis Harian Jam 02:00 Pagi
    if app.job_queue:
        app.job_queue.run_daily(
            scheduled_backup_job,
            time=time(hour=2, minute=0, second=0)
        )
        print("⏰ Scheduled Daily Backup aktif (Setiap Jam 02:00 AM)")

    # 2. Setup Conversation Handler untuk Tambah & Hapus User via Button
    conv_handler = ConversationHandler(
        entry_points=[
            CallbackQueryHandler(prompt_new_user, pattern="^prompt_new_user$"),
            CallbackQueryHandler(prompt_del_user, pattern="^prompt_del_user$"),
        ],
        states={
            WAITING_NEWACC_INPUT: [
                MessageHandler(filters.TEXT & ~filters.COMMAND, process_new_user_input)
            ],
            WAITING_DELACC_INPUT: [
                MessageHandler(filters.TEXT & ~filters.COMMAND, process_del_user_input)
            ],
        },
        fallbacks=[
            CallbackQueryHandler(cancel_action, pattern="^cancel_action$"),
            CommandHandler("start", start_command),
        ],
    )

    # 3. Add Handlers
    app.add_handler(CommandHandler("start", start_command))
    app.add_handler(CommandHandler("menu", start_command))
    app.add_handler(CommandHandler("help", help_command))
    app.add_handler(conv_handler)
    app.add_handler(CallbackQueryHandler(button_handler))

    print(f"🤖 Bot Telegram Control Server Berjalan... ({len(ALLOWED_CHAT_IDS)} chat ID terdaftar)")
    app.run_polling()

if __name__ == "__main__":
    main()