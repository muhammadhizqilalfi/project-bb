<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Gavel,
    Package,
    FileCheck2,
    Save,
    ArrowRight,
    ArrowLeft,
    Check,
    RotateCcw,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import type { PropType } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';

const activeMenu = ref('FORM');

type BarangBukti = {
    jumlahSatuan: number | string;
    uraianBarangBukti: string;
    jenisSatuan: string;
    macamJenisKadar: string;
    amarPutusan: string;
    uraianPutusan: string;
    tempatPenyimpanan: string;
};

type SavedCase = {
    satuanKerja?: string;
    kategoriTindakPidana?: string;
    pasalDidakwakan?: string;
    noRegBendaSitaan?: string;
    tglPenerimaan?: string;
    noKepPengadilan?: string;
    tglKepPengadilan?: string;
    tglPelaksanaanPutusan?: string;
    barangBuktiList?: BarangBukti[];
};

type FormItem = {
    id: string;
    name: string;
    month: number;
    year: number;
    cases?: SavedCase[];
};

const props = defineProps({
    form: {
        type: Object as PropType<FormItem | null>,
        default: null,
    },
});

const isNewForm = computed(() => !props.form);
const currentStep = ref(props.form ? 2 : 1);

const formHeader = ref({
    name: props.form?.name || '',
    month: props.form?.month || new Date().getMonth() + 1,
    year: props.form?.year || new Date().getFullYear(),
});

const createEmptyBarangBukti = (): BarangBukti => ({
    jumlahSatuan: 1,
    uraianBarangBukti: '',
    jenisSatuan: '',
    macamJenisKadar: '',
    amarPutusan: '',
    uraianPutusan: '',
    tempatPenyimpanan: '',
});

const DEFAULT_SATKER = 'Kejari Banda Aceh';

const formCase = ref({
    satuanKerja: DEFAULT_SATKER,
    kategoriTindakPidana: '',
    pasalDidakwakan: '',
    noRegBendaSitaan: '',
    tglPenerimaan: '',
    noKepPengadilan: '',
    tglKepPengadilan: '',
    tglPelaksanaanPutusan: '',
    barangBuktiList: [createEmptyBarangBukti()],
});

const monthOptions = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 10 }, (_, index) => currentYear - index);
});

const kategoriPidanaOptions = [
    'KAMNEGTIBUM DAN TPUL',
    'NARKOTIKA DAN ZAT ADITIF LAINNYA',
    'OHARDA',
    'TERORIS',
    'KORUPSI',
];

const amarPutusanOptions = [
    'Digunakan dalam Perkara',
    'Dirampas untuk Negara',
    'Dirampas untuk Baitul Mal',
    'Dikembalikan',
    'Dimusnahkan',
    'Sda',
];

const satuanOptions = [
    'Gram',
    'Kilogram (Kg)',
    'Milliliter (ml)',
    'Liter (L)',
    'Unit',
    'Paket',
    'Pcs',
    'Buah',
    'Bungkus',
    'Batang',
    'Lembar',
];

const tempatPenyimpananOptions = [
    'Gudang Barang Bukti Kejaksaan Negeri Banda Aceh',
    'RUPBASAN',
];

const addBarangBukti = () => {
    formCase.value.barangBuktiList.push(createEmptyBarangBukti());
};

const removeBarangBukti = (index: number) => {
    if (formCase.value.barangBuktiList.length > 1) {
        formCase.value.barangBuktiList.splice(index, 1);
    }
};

const goToStep2 = () => {
    if (!formHeader.value.name) {
        alert('Harap isi Nama Form terlebih dahulu.');
        return;
    }
    currentStep.value = 2;
};

const goToStep1 = () => {
    if (isNewForm.value) {
        currentStep.value = 1;
    }
};

const submitForm = () => {
    const casePayload = {
        ...formCase.value,
        tglPelaksanaanPutusan: formCase.value.tglPelaksanaanPutusan || '-',
    };

    if (isNewForm.value) {
        router.post('/forms/3c/wizard', {
            header: formHeader.value,
            case: casePayload,
        });
    } else {
        router.post(`/form3c/${props.form?.id}/cases`, casePayload);
    }
};

const resetCaseForm = () => {
    formCase.value = {
        satuanKerja: DEFAULT_SATKER,
        kategoriTindakPidana: '',
        pasalDidakwakan: '',
        noRegBendaSitaan: '',
        tglPenerimaan: '',
        noKepPengadilan: '',
        tglKepPengadilan: '',
        tglPelaksanaanPutusan: '',
        barangBuktiList: [createEmptyBarangBukti()],
    };
};
</script>

<template>
    <Head :title="isNewForm ? 'Buat Form 3C Baru' : 'Tambah Case Form 3C'" />

    <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
        <div class="mx-auto w-full space-y-8 p-8">
            <!-- PAGE HEADER -->
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-4"
            >
                <div>
                    <h1
                        class="text-2xl font-extrabold tracking-tight text-slate-900"
                    >
                        {{
                            isNewForm
                                ? 'Buat Form 3C & Input Case'
                                : 'Tambah Case Baru'
                        }}
                    </h1>
                    <p class="mt-1 text-xs text-slate-500">
                        Laporan Bulanan Barang Bukti Berdasarkan Putusan
                        Pengadilan (PN / PT / MA)
                    </p>
                </div>

                <button
                    type="button"
                    class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-300 px-3.5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100"
                    @click="router.get('/form3c')"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Daftar</span>
                </button>
            </div>

            <!-- PROGRESS STEPPER BAR -->
            <div
                class="rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-md"
            >
                <div class="mx-auto flex max-w-xl items-center justify-center">
                    <div
                        class="flex cursor-pointer items-center gap-3"
                        @click="goToStep1"
                    >
                        <div
                            :class="[
                                'flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition-all duration-300',
                                currentStep === 1
                                    ? 'border-2 border-[#FFD000] bg-slate-900 text-[#FFD000] shadow-[0_0_15px_rgba(255,208,0,0.4)] ring-4 ring-amber-500/20'
                                    : 'bg-emerald-500 text-white',
                            ]"
                        >
                            <Check
                                v-if="currentStep > 1"
                                class="h-5 w-5 stroke-[3]"
                            />
                            <span v-else>1</span>
                        </div>
                        <div class="hidden text-left sm:block">
                            <p
                                class="text-xs font-bold tracking-wider uppercase"
                                :class="
                                    currentStep === 1
                                        ? 'text-[#FFD000]'
                                        : 'text-slate-300'
                                "
                            >
                                Tahap 1
                            </p>
                            <p class="text-[11px] font-medium text-slate-400">
                                Informasi Form
                            </p>
                        </div>
                    </div>

                    <div
                        class="mx-4 h-0.5 max-w-[120px] flex-1 transition-all duration-300"
                        :class="
                            currentStep >= 2 ? 'bg-emerald-500' : 'bg-slate-700'
                        "
                    ></div>

                    <div class="flex items-center gap-3">
                        <div
                            :class="[
                                'flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition-all duration-300',
                                currentStep === 2
                                    ? 'border-2 border-[#FFD000] bg-slate-900 text-[#FFD000] shadow-[0_0_15px_rgba(255,208,0,0.4)] ring-4 ring-amber-500/20'
                                    : 'border border-slate-700 bg-slate-800 text-slate-500',
                            ]"
                        >
                            2
                        </div>
                        <div class="hidden text-left sm:block">
                            <p
                                class="text-xs font-bold tracking-wider uppercase"
                                :class="
                                    currentStep === 2
                                        ? 'text-[#FFD000]'
                                        : 'text-slate-500'
                                "
                            >
                                Tahap 2
                            </p>
                            <p class="text-[11px] font-medium text-slate-400">
                                Input Data Case
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BANNER FORM LAMA -->
            <div
                v-if="!isNewForm"
                class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-4"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-amber-800 uppercase"
                    >
                        Menambahkan Case Untuk Form Existing:
                    </p>
                    <p class="mt-0.5 text-sm font-extrabold text-slate-900">
                        {{ form?.name }} (Periode:
                        {{
                            monthOptions.find((m) => m.value === form?.month)
                                ?.label
                        }}
                        {{ form?.year }})
                    </p>
                </div>
                <span
                    class="rounded-full bg-amber-200 px-2.5 py-1 text-[10px] font-bold text-amber-900 uppercase"
                    >Form Terkunci</span
                >
            </div>

            <!-- TAHAP 1 -->
            <div
                v-if="currentStep === 1"
                class="w-full space-y-6 rounded-xl border border-slate-200 bg-white p-8 shadow-xs"
            >
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-base font-bold text-slate-900">
                        Tahap 1: Pengaturan Form Bulanan
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Masukkan judul dan periode laporan bulanan yang akan
                        dibuat.
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-700 uppercase"
                        >
                            NAMA FORM LAPORAN
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formHeader.name"
                            type="text"
                            required
                            placeholder="Contoh: FORM 3C Juli 2026"
                            class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 text-sm font-semibold text-slate-900 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-700 uppercase"
                                >BULAN
                                <span class="text-red-500">*</span></label
                            >
                            <select
                                v-model="formHeader.month"
                                required
                                class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 text-sm font-semibold text-slate-900 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                            >
                                <option
                                    v-for="m in monthOptions"
                                    :key="m.value"
                                    :value="m.value"
                                >
                                    {{ m.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-700 uppercase"
                                >TAHUN
                                <span class="text-red-500">*</span></label
                            >
                            <select
                                v-model="formHeader.year"
                                required
                                class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 text-sm font-semibold text-slate-900 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                            >
                                <option
                                    v-for="y in yearOptions"
                                    :key="y"
                                    :value="y"
                                >
                                    {{ y }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        class="flex cursor-pointer items-center gap-2 rounded-lg bg-[#0E1B2E] px-6 py-3 text-xs font-bold text-white shadow-sm transition-all hover:bg-slate-800"
                        @click="goToStep2"
                    >
                        <span>LANJUT KE INPUT CASE (TAHAP 2)</span>
                        <ArrowRight class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- TAHAP 2 -->
            <form
                v-if="currentStep === 2"
                @submit.prevent="submitForm"
                class="space-y-6"
            >
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    
                    <!-- FIELD 1 & FIELD 2 (SISI KIRI) -->
                    <div
                        class="h-fit space-y-5 rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs lg:col-span-5"
                    >
                        <!-- FIELD 1. PERKARA DAN REGISTER -->
                        <div
                            class="flex items-center gap-3 border-b border-slate-100 pb-3"
                        >
                            <div
                                class="rounded-lg bg-slate-100 p-2 text-slate-800"
                            >
                                <Gavel class="h-5 w-5" />
                            </div>
                            <h2
                                class="text-xs font-bold tracking-wider text-slate-900 uppercase"
                            >
                                1. PERKARA & REGISTER
                            </h2>
                        </div>

                        <!-- SATUAN KERJA (DEFAULT READ-ONLY) -->
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                            >
                                SATUAN KERJA
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formCase.satuanKerja"
                                type="text"
                                readonly
                                class="w-full cursor-not-allowed rounded-lg border border-transparent bg-slate-200/70 px-3.5 py-2.5 text-xs font-bold text-slate-700 outline-none"
                            />
                        </div>

                        <!-- OPTION KATEGORI TINDAK PIDANA -->
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-700 uppercase"
                                >KATEGORI TINDAK PIDANA
                                <span class="text-red-500">*</span></label
                            >
                            <select
                                v-model="formCase.kategoriTindakPidana"
                                required
                                class="w-full rounded-lg border border-amber-300 bg-[#FFFBEB] px-3.5 py-2.5 text-xs font-bold text-slate-900 transition-all outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                            >
                                <option value="" disabled>
                                    -- Pilih Jenis Tindak Pidana --
                                </option>
                                <option
                                    v-for="kat in kategoriPidanaOptions"
                                    :key="kat"
                                    :value="kat"
                                >
                                    {{ kat }}
                                </option>
                            </select>
                        </div>

                        <!-- TEXT AREA PASAL YANG DIDAKWAKAN -->
                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >PASAL YANG DIDAKWAKAN
                                <span class="text-red-500">*</span></label
                            >
                            <textarea
                                v-model="formCase.pasalDidakwakan"
                                rows="1"
                                required
                                placeholder="e.g. Pasal 112 ayat (1) jo Pasal 114 ayat (1)"
                                @input="
                                    (e) => {
                                        const el = e.target as HTMLTextAreaElement;
                                        el.style.height = 'auto';
                                        el.style.height = el.scrollHeight + 'px';
                                    }
                                "
                                class="w-full resize-none overflow-hidden rounded-lg border border-transparent bg-[#F4F6F8] p-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                            ></textarea>
                        </div>

                        <!-- TEXT AREA NO. REG SITAAN & TANGGAL PENERIMAAN BB -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >NO. REG SITAAN
                                    <span class="text-red-500">*</span></label
                                >
                                <textarea
                                    v-model="formCase.noRegBendaSitaan"
                                    rows="1"
                                    required
                                    placeholder="RB-000/O.1.10/..."
                                    @input="
                                        (e) => {
                                            const el = e.target as HTMLTextAreaElement;
                                            el.style.height = 'auto';
                                            el.style.height = el.scrollHeight + 'px';
                                        }
                                    "
                                    class="w-full resize-none overflow-hidden rounded-lg border border-transparent bg-[#F4F6F8] p-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                ></textarea>
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >TGL PENERIMAAN BB
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="formCase.tglPenerimaan"
                                    type="date"
                                    required
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                            </div>
                        </div>

                        <!-- FIELD 2. PUTUSAN DAN EKSEKUSI HAKIM -->
                        <div class="space-y-4 border-t border-slate-100 pt-3">
                            <div
                                class="flex items-center gap-2 text-xs font-bold text-slate-800"
                            >
                                <FileCheck2 class="h-4 w-4 text-slate-600" />
                                <span>2. PUTUSAN & EKSEKUSI HAKIM</span>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-slate-600 uppercase"
                                        >NO. KEP (PN/PT/MA)
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="formCase.noKepPengadilan"
                                        type="text"
                                        required
                                        placeholder="No. Putusan"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3 py-2 text-xs text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-slate-600 uppercase"
                                        >TGL. KEP (PN/PT/MA)
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="formCase.tglKepPengadilan"
                                        type="date"
                                        required
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3 py-2 text-xs text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-bold text-slate-600 uppercase"
                                    >TGL PELAKSANAAN PUTUSAN</label
                                >
                                <input
                                    v-model="formCase.tglPelaksanaanPutusan"
                                    type="date"
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3 py-2 text-xs text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- FIELD 3. DAFTAR BARANG BUKTI (REPEATER SISI KANAN) -->
                    <div class="space-y-5 lg:col-span-7">
                        <div
                            class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-slate-100 p-2 text-slate-800"
                                >
                                    <Package class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2
                                        class="text-xs font-bold tracking-wider text-slate-900 uppercase"
                                    >
                                        3. DAFTAR BARANG BUKTI
                                    </h2>
                                    <p class="text-[11px] text-slate-500">
                                        Total:
                                        {{ formCase.barangBuktiList.length }}
                                        Barang Bukti
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="flex cursor-pointer items-center gap-1.5 rounded-lg bg-[#0E1B2E] px-3.5 py-2 text-xs font-bold text-white shadow-xs transition-colors hover:bg-slate-800"
                                @click="addBarangBukti"
                            >
                                <Plus class="h-4 w-4" />
                                <span>Tambah Barang Bukti</span>
                            </button>
                        </div>

                        <!-- ITEM BARANG BUKTI REPEATER -->
                        <div
                            v-for="(bb, index) in formCase.barangBuktiList"
                            :key="index"
                            class="relative space-y-4 rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs transition-all"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-100 pb-3"
                            >
                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold tracking-wider text-slate-800 uppercase"
                                >
                                    Barang Bukti #{{ index + 1 }}
                                </span>

                                <button
                                    v-if="formCase.barangBuktiList.length > 1"
                                    type="button"
                                    class="flex cursor-pointer items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-500 transition-colors hover:bg-red-50 hover:text-red-700"
                                    @click="removeBarangBukti(index)"
                                    title="Hapus Barang Bukti Ini"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    <span>Hapus</span>
                                </button>
                            </div>

                            <!-- BARIS 1: FIELD OPTION JUMLAH SATUAN | FIELD TEXT AREA URAIAN/KETERANGAN BARANG BUKTI -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <div class="sm:col-span-4">
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        JUMLAH SATUAN
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="bb.jumlahSatuan"
                                        type="number"
                                        step="any"
                                        min="1"
                                        required
                                        placeholder="1"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs font-semibold text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                </div>
                                <div class="sm:col-span-8">
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        URAIAN / KETERANGAN BARANG BUKTI
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        v-model="bb.uraianBarangBukti"
                                        rows="1"
                                        required
                                        placeholder="Deskripsi / spesifikasi rinci barang bukti..."
                                        @input="
                                            (e) => {
                                                const el = e.target as HTMLTextAreaElement;
                                                el.style.height = 'auto';
                                                el.style.height = el.scrollHeight + 'px';
                                            }
                                        "
                                        class="w-full resize-none overflow-hidden rounded-lg border border-transparent bg-[#F4F6F8] p-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- BARIS 2: FIELD OPTION JENIS SATUAN | FIELD TEXT AREA MACAM JENIS KADAR -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <div class="sm:col-span-4">
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        JENIS SATUAN
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="bb.jenisSatuan"
                                        required
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    >
                                        <option value="" disabled>
                                            Pilih Satuan...
                                        </option>
                                        <option
                                            v-for="s in satuanOptions"
                                            :key="s"
                                            :value="s"
                                        >
                                            {{ s }}
                                        </option>
                                    </select>
                                </div>
                                <div class="sm:col-span-8">
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        MACAM JENIS KADAR BARANG BUKTI
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        v-model="bb.macamJenisKadar"
                                        rows="1"
                                        required
                                        placeholder="Spesifikasi rinci, kadar kemurnian, nomor mesin..."
                                        @input="
                                            (e) => {
                                                const el = e.target as HTMLTextAreaElement;
                                                el.style.height = 'auto';
                                                el.style.height = el.scrollHeight + 'px';
                                            }
                                        "
                                        class="w-full resize-none overflow-hidden rounded-lg border border-transparent bg-[#F4F6F8] p-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- BARIS 3: FIELD OPTION AMAR PUTUSAN | FIELD TEXT AREA URAIAN/KETERANGAN AMAR PUTUSAN -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <div class="sm:col-span-4">
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        AMAR PUTUSAN
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="bb.amarPutusan"
                                        required
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    >
                                        <option value="" disabled>
                                            Pilih Amar Putusan...
                                        </option>
                                        <option
                                            v-for="opt in amarPutusanOptions"
                                            :key="opt"
                                            :value="opt"
                                        >
                                            {{ opt }}
                                        </option>
                                    </select>
                                </div>
                                <div class="sm:col-span-8">
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        URAIAN / KETERANGAN AMAR PUTUSAN
                                    </label>
                                    <textarea
                                        v-model="bb.uraianPutusan"
                                        rows="1"
                                        placeholder="Rincian / uraian amar putusan hakim..."
                                        @input="
                                            (e) => {
                                                const el = e.target as HTMLTextAreaElement;
                                                el.style.height = 'auto';
                                                el.style.height = el.scrollHeight + 'px';
                                            }
                                        "
                                        class="w-full resize-none overflow-hidden rounded-lg border border-transparent bg-[#F4F6F8] p-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- BARIS 4: FIELD OPTION TEMPAT PENYIMPANAN -->
                            <div>
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >
                                    TEMPAT PENYIMPANAN
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="bb.tempatPenyimpanan"
                                    required
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                >
                                    <option value="" disabled>
                                        Pilih Tempat Penyimpanan...
                                    </option>
                                    <option
                                        v-for="opt in tempatPenyimpananOptions"
                                        :key="opt"
                                        :value="opt"
                                    >
                                        {{ opt }}
                                    </option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- BOTTOM ACTION BUTTONS -->
                <div
                    class="flex items-center justify-between border-t border-slate-200 pt-4"
                >
                    <div>
                        <button
                            v-if="isNewForm"
                            type="button"
                            class="flex cursor-pointer items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-300"
                            @click="goToStep1"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            <span>KEMBALI KE TAHAP 1</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-100"
                            @click="resetCaseForm"
                        >
                            <RotateCcw class="h-4 w-4" />
                            <span>RESET CASE</span>
                        </button>
                        <button
                            type="submit"
                            class="flex cursor-pointer items-center gap-2 rounded-lg bg-[#0E1B2E] px-6 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-slate-800"
                        >
                            <Save class="h-4 w-4" />
                            <span>{{
                                isNewForm ? 'SIMPAN FORM & CASE' : 'SIMPAN CASE'
                            }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>