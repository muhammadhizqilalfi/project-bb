<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Gavel,
    Package,
    CheckSquare,
    Eye,
    Save,
    ArrowRight,
    ArrowLeft,
    Check,
    RotateCcw,
    X,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import type { PropType } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';

const activeMenu = ref('FORM');

type BarangBukti = {
    jenisBarangBukti: string;
    namaBarangBukti: string;
    jumlah: string | number;
    satuan: string;
    ukuranDetail: string;
    tempatPenyimpanan: string;
};

type SavedCase = {
    satuanKerja?: string;
    kategoriTindakPidana?: string;
    noRegBendaSitaan?: string;
    noRegPenyidikan?: string;
    identitasTersangka?: string;
    pasalDisangkakan?: string;
    statusDiselesaikan?: string;
    tglPelaksanaanPutusan?: string;
    keterangan?: string;
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
    jenisBarangBukti: '',
    namaBarangBukti: '',
    jumlah: '',
    satuan: '',
    ukuranDetail: '',
    tempatPenyimpanan: '',
});

const formCase = ref({
    satuanKerja: '',
    kategoriTindakPidana: '',
    noRegBendaSitaan: '',
    noRegPenyidikan: '',
    identitasTersangka: '',
    pasalDisangkakan: '',
    statusDiselesaikan: 'Belum Selesai',
    tglPelaksanaanPutusan: '',
    keterangan: '',
    barangBuktiList: [createEmptyBarangBukti()],
});

const showPreviewModal = ref(false);

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

const satuanKerjaOptions = [
  'Kejari Banda Aceh', 
  'Kejaksaan Negeri Banda Aceh'
];

const kategoriPidanaOptions = [
    'KAMNEGTIBUM DAN TPUL',
    'NARKOTIKA DAN ZAT ADITIF LAINNYA',
    'OHARDA',
    'TERORIS',
    'KORUPSI',
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

const jenisBbOptions = [
    'Narkotika',
    'Psikotropika / Zat Adiktif',
    'Kendaraan',
    'Senjata Api / Tajam',
    'Elektronik / HP',
    'Uang / Dokumen',
    'Lain-lain',
];

const tempatPenyimpananOptions = [
    'Gudang Barang Bukti Kejaksaan Negeri Banda Aceh',
    'Lainnya',
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
    if (isNewForm.value) {
        router.post('/forms/3a/wizard', {
            header: formHeader.value,
            case: formCase.value,
        });
    } else {
        router.post(`/form3a/${props.form?.id}/cases`, formCase.value);
    }
};

const resetCaseForm = () => {
    formCase.value = {
        satuanKerja: '',
        kategoriTindakPidana: '',
        noRegBendaSitaan: '',
        noRegPenyidikan: '',
        identitasTersangka: '',
        pasalDisangkakan: '',
        statusDiselesaikan: 'Belum Selesai',
        tglPelaksanaanPutusan: '',
        keterangan: '',
        barangBuktiList: [createEmptyBarangBukti()],
    };
};
</script>

<template>
    <Head :title="isNewForm ? 'Buat Form 3A Baru' : 'Tambah Case Form 3A'" />

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
                                ? 'Buat Form 3A & Input Case'
                                : 'Tambah Case Baru'
                        }}
                    </h1>
                    <p class="mt-1 text-xs text-slate-500">
                        Laporan Bulanan Benda Sitaan & Barang Bukti Berdasarkan
                        Kategori Tindak Pidana
                    </p>
                </div>

                <button
                    type="button"
                    class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-300 px-3.5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100"
                    @click="router.get('/form3a')"
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
                            placeholder="Contoh: FORM 3A Juli 2026"
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
                    <!-- IDENTITAS PERKARA -->
                    <div
                        class="h-fit space-y-5 rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs lg:col-span-5"
                    >
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
                                IDENTITAS PERKARA & SATKER
                            </h2>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                            >
                                SATUAN KERJA
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="formCase.satuanKerja"
                                required
                                class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                            >
                                <option value="" disabled>
                                    Pilih Satuan Kerja...
                                </option>
                                <option
                                    v-for="opt in satuanKerjaOptions"
                                    :key="opt"
                                    :value="opt"
                                >
                                    {{ opt }}
                                </option>
                            </select>
                        </div>

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

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >NO. REG BENDA SITAAN
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="formCase.noRegBendaSitaan"
                                    type="text"
                                    required
                                    placeholder="B-000/O.1.10/..."
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >NO. REG PENYIDIKAN
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="formCase.noRegPenyidikan"
                                    type="text"
                                    required
                                    placeholder="PRINT-00/O.1.10/..."
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >IDENTITAS TERSANGKA / TERDAKWA
                                <span class="text-red-500">*</span></label
                            >
                            <textarea
                                v-model="formCase.identitasTersangka"
                                rows="3"
                                required
                                placeholder="Nama lengkap, alias, NIK, umur, pekerjaan..."
                                class="w-full resize-none rounded-lg border border-transparent bg-[#F4F6F8] p-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                            ></textarea>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >PASAL YANG DISANGKAKAN / DIDAKWAKAN
                                <span class="text-red-500">*</span></label
                            >
                            <input
                                v-model="formCase.pasalDisangkakan"
                                type="text"
                                required
                                placeholder="Contoh: Pasal 114 ayat (1) UU No. 35 Tahun 2009"
                                class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                            />
                        </div>

                        <div class="space-y-4 border-t border-slate-100 pt-3">
                            <div
                                class="flex items-center gap-2 text-xs font-bold text-slate-800"
                            >
                                <CheckSquare class="h-4 w-4 text-slate-600" />
                                <span>STATUS & PUTUSAN</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-slate-600 uppercase"
                                        >DISELESAIKAN</label
                                    >
                                    <select
                                        v-model="formCase.statusDiselesaikan"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3 py-2 text-xs text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    >
                                        <option value="Belum Selesai">-</option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-slate-600 uppercase"
                                        >TGL PUTUSAN</label
                                    >
                                    <input
                                        v-model="formCase.tglPelaksanaanPutusan"
                                        type="date"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3 py-2 text-xs text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                </div>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-bold text-slate-600 uppercase"
                                    >KETERANGAN</label
                                >
                                <select
                                    v-model="formCase.keterangan"
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3 py-2 text-xs text-slate-800 transition-all outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                >
                                    <option value="" disabled>
                                        Pilih Keterangan Tahap...
                                    </option>
                                    <option value="Tahap Persidangan">
                                        Tahap Persidangan
                                    </option>
                                    <option value="Tahap II">Tahap II</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- REPEATER MULTI BARANG BUKTI -->
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
                                        DAFTAR BARANG BUKTI
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
                                    class="cursor-pointer rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-50 hover:text-red-700"
                                    @click="removeBarangBukti(index)"
                                    title="Hapus Barang Bukti Ini"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        JENIS BARANG BUKTI
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="bb.jenisBarangBukti"
                                        required
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    >
                                        <option value="" disabled>
                                            Pilih Jenis Barang Bukti...
                                        </option>
                                        <option
                                            v-for="opt in jenisBbOptions"
                                            :key="opt"
                                            :value="opt"
                                        >
                                            {{ opt }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                        >NAMA BARANG BUKTI
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="bb.namaBarangBukti"
                                        type="text"
                                        required
                                        placeholder="e.g. Sabu Paket / Honda Vario"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                        >JUMLAH
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <!-- INPUT BISA ANGKA DESIMAL (step="any") -->
                                    <input
                                        v-model="bb.jumlah"
                                        type="number"
                                        step="any"
                                        required
                                        placeholder="0.00"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                        >SATUAN
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <!-- SELECT DROPDOWN SATUAN -->
                                    <select
                                        v-model="bb.satuan"
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

                            <div>
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >UKURAN / DETAIL URAIAN BARANG
                                    <span class="text-red-500">*</span></label
                                >
                                <textarea
                                    v-model="bb.ukuranDetail"
                                    rows="2"
                                    required
                                    placeholder="Contoh: Plastik klip transparan berisi kristal putih..."
                                    class="w-full resize-none rounded-lg border border-transparent bg-[#F4F6F8] p-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

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
                            class="flex cursor-pointer items-center gap-2 rounded-lg bg-slate-100 px-5 py-2.5 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-200"
                            @click="showPreviewModal = true"
                        >
                            <Eye class="h-4 w-4" />
                            <span>PREVIEW FORM</span>
                        </button>
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

        <!-- PREVIEW MODAL (CASES TERSIMPAN + DRAFT BARU) -->
        <div
            v-if="showPreviewModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
            >
                <div
                    class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4"
                >
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Preview Laporan Form -
                            {{
                                isNewForm
                                    ? formHeader.name || 'Form Baru'
                                    : props.form?.name
                            }}
                        </h2>
                        <p class="mt-0.5 text-[11px] text-slate-500">
                            Menampilkan total
                            {{ (props.form?.cases?.length || 0) + 1 }} Perkara
                            ({{ props.form?.cases?.length || 0 }} Tersimpan + 1
                            Draft Baru)
                        </p>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer text-slate-500 hover:text-slate-700"
                        @click="showPreviewModal = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="overflow-y-auto p-5">
                    <div
                        class="overflow-x-auto rounded border border-slate-300"
                    >
                        <table class="w-full border-collapse text-left">
                            <thead
                                class="bg-slate-800 text-[10px] tracking-wider text-white uppercase"
                            >
                                <tr>
                                    <th class="border border-slate-700 p-3">
                                        Satker & Kategori
                                    </th>
                                    <th class="border border-slate-700 p-3">
                                        No. Register Sitaan & Sidik
                                    </th>
                                    <th class="border border-slate-700 p-3">
                                        Tersangka & Pasal
                                    </th>
                                    <th class="border border-slate-700 p-3">
                                        Daftar Barang Bukti
                                    </th>
                                    <th
                                        class="border border-slate-700 p-3 text-center"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-xs text-slate-700">
                                <!-- 1. CASES TERSIMPAN SEBELUMNYA -->
                                <tr
                                    v-for="(c, cIdx) in props.form?.cases || []"
                                    :key="'prev-' + cIdx"
                                    class="border-b border-slate-200 bg-slate-50/80"
                                >
                                    <td
                                        class="border border-slate-300 p-3 align-top"
                                    >
                                        <span
                                            class="mb-1 inline-block rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800"
                                        >
                                            Case #{{ cIdx + 1 }} (Tersimpan)
                                        </span>
                                        <div class="font-bold text-slate-900">
                                            {{ c.satuanKerja || '-' }}
                                        </div>
                                        <div
                                            class="mt-0.5 text-[10px] text-slate-500"
                                        >
                                            {{ c.kategoriTindakPidana || '-' }}
                                        </div>
                                    </td>
                                    <td
                                        class="border border-slate-300 p-3 align-top"
                                    >
                                        <div>
                                            <span
                                                class="font-semibold text-slate-900"
                                                >Sitaan:</span
                                            >
                                            {{ c.noRegBendaSitaan || '-' }}
                                        </div>
                                        <div class="mt-1">
                                            <span
                                                class="font-semibold text-slate-900"
                                                >Sidik:</span
                                            >
                                            {{ c.noRegPenyidikan || '-' }}
                                        </div>
                                    </td>
                                    <td
                                        class="border border-slate-300 p-3 align-top"
                                    >
                                        <div
                                            class="font-semibold text-slate-900"
                                        >
                                            {{ c.identitasTersangka || '-' }}
                                        </div>
                                        <div class="mt-1 text-slate-500">
                                            Pasal:
                                            {{ c.pasalDisangkakan || '-' }}
                                        </div>
                                    </td>
                                    <td
                                        class="border border-slate-300 p-3 align-top"
                                    >
                                        <div class="space-y-2">
                                            <div
                                                v-for="(
                                                    bb, idx
                                                ) in c.barangBuktiList || []"
                                                :key="idx"
                                                class="rounded border border-slate-200 bg-white p-2 text-xs"
                                            >
                                                <div
                                                    class="font-bold text-slate-900"
                                                >
                                                    {{ idx + 1 }}.
                                                    {{
                                                        bb.namaBarangBukti ||
                                                        'Barang Bukti'
                                                    }}
                                                    ({{
                                                        bb.jenisBarangBukti ||
                                                        '-'
                                                    }})
                                                </div>
                                                <div
                                                    class="mt-0.5 text-[11px] text-slate-600"
                                                >
                                                    {{ bb.ukuranDetail || '-' }}
                                                </div>
                                                <div
                                                    class="mt-1 flex items-center justify-between text-[11px]"
                                                >
                                                    <span
                                                        class="font-bold text-blue-700"
                                                        >Jumlah:
                                                        {{ bb.jumlah || 0 }}
                                                        {{ bb.satuan }}</span
                                                    >
                                                    <span class="text-slate-500"
                                                        >Lokasi:
                                                        {{
                                                            bb.tempatPenyimpanan ||
                                                            '-'
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="border border-slate-300 p-3 text-center align-top"
                                    >
                                        <span
                                            class="mb-2 inline-block rounded bg-slate-200 px-2 py-1 text-[10px] font-bold text-slate-800"
                                        >
                                            {{ c.statusDiselesaikan || '-' }}
                                        </span>
                                        <div
                                            class="max-w-[120px] truncate text-[10px] text-slate-500 italic"
                                            :title="c.keterangan"
                                        >
                                            {{
                                                c.keterangan || 'Tidak ada ket.'
                                            }}
                                        </div>
                                    </td>
                                </tr>

                                <!-- 2. CASE DRAFT BARU YANG SEDANG DIISI -->
                                <tr
                                    class="border-2 border-amber-300 bg-amber-50/50"
                                >
                                    <td
                                        class="border border-slate-300 p-3 align-top"
                                    >
                                        <span
                                            class="mb-1 inline-block rounded bg-amber-200 px-1.5 py-0.5 text-[10px] font-bold text-amber-900"
                                        >
                                            Draft Baru
                                        </span>
                                        <div class="font-bold text-slate-900">
                                            {{ formCase.satuanKerja || '-' }}
                                        </div>
                                        <div
                                            class="mt-0.5 text-[10px] text-slate-500"
                                        >
                                            {{
                                                formCase.kategoriTindakPidana ||
                                                '-'
                                            }}
                                        </div>
                                    </td>
                                    <td
                                        class="border border-slate-300 p-3 align-top"
                                    >
                                        <div>
                                            <span
                                                class="font-semibold text-slate-900"
                                                >Sitaan:</span
                                            >
                                            {{
                                                formCase.noRegBendaSitaan || '-'
                                            }}
                                        </div>
                                        <div class="mt-1">
                                            <span
                                                class="font-semibold text-slate-900"
                                                >Sidik:</span
                                            >
                                            {{
                                                formCase.noRegPenyidikan || '-'
                                            }}
                                        </div>
                                    </td>
                                    <td
                                        class="border border-slate-300 p-3 align-top"
                                    >
                                        <div
                                            class="font-semibold text-slate-900"
                                        >
                                            {{
                                                formCase.identitasTersangka ||
                                                '-'
                                            }}
                                        </div>
                                        <div class="mt-1 text-slate-500">
                                            Pasal:
                                            {{
                                                formCase.pasalDisangkakan || '-'
                                            }}
                                        </div>
                                    </td>
                                    <td
                                        class="border border-slate-300 p-3 align-top"
                                    >
                                        <div class="space-y-2">
                                            <div
                                                v-for="(
                                                    bb, idx
                                                ) in formCase.barangBuktiList"
                                                :key="idx"
                                                class="rounded border border-amber-200 bg-white p-2 text-xs"
                                            >
                                                <div
                                                    class="font-bold text-slate-900"
                                                >
                                                    {{ idx + 1 }}.
                                                    {{
                                                        bb.namaBarangBukti ||
                                                        'Barang Bukti'
                                                    }}
                                                    ({{
                                                        bb.jenisBarangBukti ||
                                                        '-'
                                                    }})
                                                </div>
                                                <div
                                                    class="mt-0.5 text-[11px] text-slate-600"
                                                >
                                                    {{ bb.ukuranDetail || '-' }}
                                                </div>
                                                <div
                                                    class="mt-1 flex items-center justify-between text-[11px]"
                                                >
                                                    <span
                                                        class="font-bold text-blue-700"
                                                        >Jumlah:
                                                        {{ bb.jumlah || 0 }}
                                                        {{ bb.satuan }}</span
                                                    >
                                                    <span class="text-slate-500"
                                                        >Lokasi:
                                                        {{
                                                            bb.tempatPenyimpanan ||
                                                            '-'
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="border border-slate-300 p-3 text-center align-top"
                                    >
                                        <span
                                            class="mb-2 inline-block rounded bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800"
                                        >
                                            {{
                                                formCase.statusDiselesaikan ||
                                                '-'
                                            }}
                                        </span>
                                        <div
                                            class="max-w-[120px] truncate text-[10px] text-slate-500 italic"
                                            :title="formCase.keterangan"
                                        >
                                            {{
                                                formCase.keterangan ||
                                                'Tidak ada ket.'
                                            }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
