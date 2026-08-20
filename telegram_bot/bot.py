import os
import subprocess
import json
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import Application, CommandHandler, CallbackQueryHandler, ContextTypes

# Mengambil variabel dari .env
TOKEN = os.getenv("TELEGRAM_BOT_TOKEN")
ALLOWED_CHAT_ID = int(os.getenv("TELEGRAM_CHAT_ID", "0"))

async def newacc_command(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.effective_chat.id
    
    # Filter Keamanan: Hanya Chat ID Anda yang bisa membuat akun
    if user_id != ALLOWED_CHAT_ID:
        await update.message.reply_text("⛔ Akses ditolak! Anda tidak memiliki izin.")
        return

    # Validasi input argumen (Membutuhkan 2 parameter: NIP dan Password)
    if len(context.args) < 2:
        await update.message.reply_text(
            "❌ **Format salah!**\n\n"
            "Gunakan format spasi:\n"
            "`/newacc <NAMA> <NIP> <PASSWORD>`\n\n"
            "Contoh:\n"
            "`/newacc JOHN DOE 199201012020011001 Rahasia123`",
            parse_mode="Markdown"
        )
        return

    name= context.args[0]
    nip = context.args[1]
    password = context.args[2]

    await update.message.reply_text(f"⏳ Sedang mendaftarkan User `{name}` ke database...", parse_mode="Markdown")

    tinker_code = (
        f"\\App\\Models\\User::create(["
        f"'name' => '{name}', "
        f"'nip' => '{nip}', "
        f"'password' => \\Illuminate\\Support\\Facades\\Hash::make('{password}')"
        f"]);"
    )
    
    cmd = [
        "docker", "exec", "slbb-app",
        "php", "artisan", "tinker",
        f"--execute={tinker_code}"
    ]

    try:
        res = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
        if "App\\Models\\User" in res.stdout or res.returncode == 0:
            await update.message.reply_text(
                f"✅ **Akun Berhasil Dibuat!**\n\n"
                f"👤 **Nama: `{name}`\n"
                f"🆔 **NIP:** `{nip}`\n"
                f"🔑 **Password:** `{password}`\n\n"
                f"User sekarang sudah bisa login di halaman web.",
                parse_mode="Markdown"
            )
        else:
            await update.message.reply_text(f"❌ **Gagal membuat akun:**\n```{res.stderr or res.stdout}```", parse_mode="Markdown")
    except Exception as e:
        await update.message.reply_text(f"❌ **Error:** {str(e)}")


async def listuser_command(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.effective_chat.id
    if user_id != ALLOWED_CHAT_ID:
        await update.message.reply_text("⛔ Akses ditolak!")
        return

    await update.message.reply_text("⏳ Mengambil daftar user dari database...", parse_mode="Markdown")

    # Ambil kolom id, name, dan nip dalam format JSON
    tinker_code = "echo json_encode(\\App\\Models\\User::select('id', 'name', 'nip')->get());"

    cmd = [
        "docker", "exec", "slbb-app",
        "php", "artisan", "tinker",
        f"--execute={tinker_code}"
    ]

    try:
        res = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
        output = res.stdout.strip()

        # Cari posisi JSON string di stdout
        start_idx = output.find("[")
        end_idx = output.rfind("]")

        if start_idx != -1 and end_idx != -1:
            json_str = output[start_idx:end_idx + 1]
            users = json.loads(json_str)

            if not users:
                await update.message.reply_text("ℹ️ Belum ada user terdaftar di database.")
                return

            # Format pesan output Telegram
            msg = f"📋 **Daftar User ({len(users)} Akun):**\n\n"
            for idx, user in enumerate(users, start=1):
                msg += f"{idx}. **Nama:** {user.get('name')}\n"
                msg += f"   🆔 **NIP:** `{user.get('nip')}`\n"
                msg += f"   🔑 **ID DB:** `{user.get('id')}`\n\n"

            await update.message.reply_text(msg, parse_mode="Markdown")
        else:
            await update.message.reply_text(
                f"❌ **Gagal membaca data:**\n```{res.stdout or res.stderr}```",
                parse_mode="Markdown"
            )

    except Exception as e:
        await update.message.reply_text(f"❌ **Error:** {str(e)}")

# Fungsi saat Anda mengetik /start atau /menu di Telegram
async def start_command(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.effective_chat.id
    
    # Keamanan: Tolak jika bukan ID Anda
    if user_id != ALLOWED_CHAT_ID:
        await update.message.reply_text("⛔ Akses ditolak! Anda tidak memiliki izin.")
        return

    # Membuat Tombol Pilihan
    keyboard = [
        [
            InlineKeyboardButton("🌐 Restart Webserver", callback_data="restart_webserver"),
            InlineKeyboardButton("⚙️ Restart App (Laravel)", callback_data="restart_app"),
        ],
        [
            InlineKeyboardButton("💥 Restart SEMUA Service", callback_data="restart_all"),
            InlineKeyboardButton("📊 Cek Status Service", callback_data="check_status"),
        ]
    ]
    reply_markup = InlineKeyboardMarkup(keyboard)
    await update.message.reply_text("🤖 **Panel Kontrol Docker Server**\nSilakan pilih aksi:", reply_markup=reply_markup, parse_mode="Markdown")

# Fungsi saat Tombol di-klik
async def button_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()

    user_id = query.message.chat.id
    if user_id != ALLOWED_CHAT_ID:
        await query.edit_message_text("⛔ Akses Ditolak!")
        return

    action = query.data

    if action == "restart_webserver":
        await query.edit_message_text("⏳ Sedang merestart Nginx Webserver...")
        res = subprocess.run(["docker", "restart", "slbb-webserver"], capture_output=True, text=True)
        if res.returncode == 0:
            await query.edit_message_text("✅ **Nginx Webserver berhasil direstart!**", parse_mode="Markdown")
        else:
            await query.edit_message_text(f"❌ Gagal restart: {res.stderr}")

    elif action == "restart_app":
        await query.edit_message_text("⏳ Sedang merestart Aplikasi Laravel...")
        res = subprocess.run(["docker", "restart", "slbb-app"], capture_output=True, text=True)
        if res.returncode == 0:
            await query.edit_message_text("✅ **Aplikasi Laravel berhasil direstart!**", parse_mode="Markdown")
        else:
            await query.edit_message_text(f"❌ Gagal restart: {res.stderr}")

    elif action == "restart_all":
        await query.edit_message_text("⏳ Sedang merestart SEMUA container...")
        res = subprocess.run(["docker", "restart", "slbb-webserver", "slbb-app"], capture_output=True, text=True)
        if res.returncode == 0:
            await query.edit_message_text("✅ **Seluruh Service Berhasil Direstart!**", parse_mode="Markdown")
        else:
            await query.edit_message_text(f"❌ Gagal restart: {res.stderr}")

    elif action == "check_status":
        res = subprocess.run(["docker", "ps", "--format", "table {{.Names}}\t{{.Status}}"], capture_output=True, text=True)
        await query.edit_message_text(f"📊 **Status Container Saat Ini:**\n```\n{res.stdout}\n```", parse_mode="Markdown")

def main():
    if not TOKEN or ALLOWED_CHAT_ID == 0:
        print("Error: TOKEN atau CHAT_ID belum diset di file .env")
        return

    app = Application.builder().token(TOKEN).build()
    app.add_handler(CommandHandler("start", start_command))
    app.add_handler(CommandHandler("menu", start_command))
    app.add_handler(CommandHandler("newacc", newacc_command))
    app.add_handler(CommandHandler("listuser", listuser_command))
    app.add_handler(CallbackQueryHandler(button_handler))

    print("Bot Telegram Control Server Berjalan...")
    app.run_polling()

if __name__ == "__main__":
    main()