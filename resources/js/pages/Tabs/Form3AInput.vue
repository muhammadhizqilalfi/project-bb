<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Gavel,
    Package,
    CheckSquare,
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
    jenisBarangBukti: string;
    jumlah: number | string;
    uraianBarangBukti: string;
    tempatPenyimpanan: string;
    jenisNarkotika: string;
    jumlahNarkotika: string | number;
    satuanNarkotika: string;
};

type SavedCase = {
    id?: string;
    case_index?: number;
    satuanKerja?: string;
    kategoriTindakPidana?: string;
    noRegBendaSitaan?: string;
    tglPenerimaan?: string;
    noRegPenyidikan?: string;
    tglRegPenyidikan?: string;
    identitasTersangka?: string;
    pasalDisangkakan?: string;
    pasalDidakwakan?: string;
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
    // Prop tambahan saat mengedit data perkara existing dari Laporan
    caseData: {
        type: Object as PropType<SavedCase | null>,
        default: null,
    },
    // Prop Opsi Dropdown Dinamis dari Backend Master Pengaturan Form
    dropdownOptions: {
        type: Object as PropType<Record<string, string[]>>,
        default: () => ({}),
    },
});

const isEditingCase = computed(() => !!props.caseData);
const isNewForm = computed(() => !props.form && !props.caseData);
const currentStep = ref(props.form || props.caseData ? 2 : 1);

const formHeader = ref({
    name: props.form?.name || '',
    month: props.form?.month || new Date().getMonth() + 1,
    year: props.form?.year || new Date().getFullYear(),
});

const createEmptyBarangBukti = (): BarangBukti => ({
    jenisBarangBukti: 'NARKOTIKA',
    jumlah: 1,
    uraianBarangBukti: '',
    tempatPenyimpanan: '',
    jenisNarkotika: '',
    jumlahNarkotika: '',
    satuanNarkotika: '',
});

const DEFAULT_SATKER = 'Kejari Banda Aceh';

// Inisialisasi Form dengan data existing (jika mode edit)
const formCase = ref<SavedCase & { barangBuktiList: BarangBukti[] }>({
    satuanKerja:
        props.caseData?.satuanKerja ||
        DEFAULT_SATKER,
    kategoriTindakPidana: props.caseData?.kategoriTindakPidana || '',
    noRegBendaSitaan:
        props.caseData?.noRegBendaSitaan || '',
    tglPenerimaan: props.caseData?.tglPenerimaan || '',
    noRegPenyidikan:
        props.caseData?.noRegPenyidikan || '',
    tglRegPenyidikan: props.caseData?.tglRegPenyidikan || '',
    identitasTersangka: props.caseData?.identitasTersangka || '',
    pasalDisangkakan:
        props.caseData?.pasalDisangkakan ||
        props.caseData?.pasalDidakwakan ||
        '',
    statusDiselesaikan: props.caseData?.statusDiselesaikan || '-',
    tglPelaksanaanPutusan: props.caseData?.tglPelaksanaanPutusan || '',
    keterangan: props.caseData?.keterangan || '',
    barangBuktiList:
        props.caseData?.barangBuktiList &&
        props.caseData.barangBuktiList.length > 0
            ? props.caseData.barangBuktiList.map((bb) => ({
                  jenisBarangBukti: bb.jenisBarangBukti || 'Narkotika',
                  jumlah: bb.jumlah || 1,
                  uraianBarangBukti:
                      bb.uraianBarangBukti || (bb as any).namaBarangBukti || '',
                  tempatPenyimpanan: bb.tempatPenyimpanan || '',
                  jenisNarkotika: bb.jenisNarkotika || '',
                  jumlahNarkotika: bb.jumlahNarkotika || '',
                  satuanNarkotika:
                      bb.satuanNarkotika || (bb as any).satuan || '',
              }))
            : [createEmptyBarangBukti()],
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

// OPSI DROPDOWN DINAMIS BERDASARKAN MASTER DATA (WITH FALLBACK)
const kategoriPidanaOptions = computed(() => {
    return props.dropdownOptions?.kategori_pidana?.length
        ? props.dropdownOptions.kategori_pidana
        : ['KAMNEGTIBUM DAN TPUL','NARKOTIKA DAN ZAT ADITIF LAINNYA','OHARDA','TERORIS','KORUPSI',];
});

const jenisBbCategoryOptions = ['Narkotika', 'Lainnya'];

const jenisNarkotikaOptions = computed(() => {
    return props.dropdownOptions?.jenis_narkotika?.length
        ? props.dropdownOptions.jenis_narkotika
        : ['Sabu','Ganja','Ekstasi / Pil','Heroin','Tembakau Sintetis','Obat Keras','Lainnya',];
});

const satuanOptions = computed(() => {
    return props.dropdownOptions?.satuan?.length
        ? props.dropdownOptions.satuan
        : ['Gram','Kilogram (Kg)','Milliliter (ml)','Liter (L)','Unit','Paket','Pcs','Buah','Bungkus','Batang','Lembar',];
});

const tempatPenyimpananOptions = computed(() => {
    return props.dropdownOptions?.tempat_penyimpanan?.length
        ? props.dropdownOptions.tempat_penyimpanan
        : ['Gudang Barang Bukti Kejaksaan Negeri Banda Aceh', 'RUPBASAN', 'KEJATI',];
});

const keteranganTahapOptions = computed(() => {
    return props.dropdownOptions?.keterangan_tahap?.length
        ? props.dropdownOptions.keterangan_tahap
        : ['Tahap Persidangan', 'Tahap II', 'Tahap Pelimpahan'];
});

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
        case_index: props.caseData?.case_index ?? formCase.value.case_index,
        tglPenerimaan: formCase.value.tglPenerimaan,
        tglRegPenyidikan: formCase.value.tglRegPenyidikan,
        tglPelaksanaanPutusan: formCase.value.tglPelaksanaanPutusan,
    };

    if (isEditingCase.value && props.caseData?.id) {
        // Mode Update per Item
        router.put(`/form3a/${props.caseData.id}`, casePayload);
    } else if (isNewForm.value) {
        // Mode Buat Baru
        router.post('/forms/3a/wizard', {
            header: formHeader.value,
            case: casePayload,
        });
    } else {
        // Mode Tambah Case ke Form Existing
        router.post(`/form3a/${props.form?.id}/cases`, casePayload);
    }
};

const resetCaseForm = () => {
    formCase.value = {
        satuanKerja: DEFAULT_SATKER,
        kategoriTindakPidana: '',
        noRegBendaSitaan: '',
        tglPenerimaan: '',
        noRegPenyidikan: '',
        tglRegPenyidikan: '',
        identitasTersangka: '',
        pasalDisangkakan: '',
        statusDiselesaikan: '-',
        tglPelaksanaanPutusan: '',
        keterangan: '',
        barangBuktiList: [createEmptyBarangBukti()],
    };
};
</script>

<template>
    <Head
        :title="
            isEditingCase
                ? 'Edit Case Form 3A'
                : isNewForm
                  ? 'Buat Form 3A Baru'
                  : 'Tambah Case Form 3A'
        "
    />

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
                            isEditingCase
                                ? 'Edit Data Case Form 3A'
                                : isNewForm
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
                    @click="router.get('/laporan')"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Laporan</span>
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

            <!-- BANNER EDIT/FORM EXISTING -->
            <div
                v-if="!isNewForm"
                class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-4"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-amber-800 uppercase"
                    >
                        {{
                            isEditingCase
                                ? 'Mengubah Data Perkara (Mode Edit):'
                                : 'Menambahkan Case Untuk Form Existing:'
                        }}
                    </p>
                    <p class="mt-0.5 text-sm font-extrabold text-slate-900">
                        {{
                            isEditingCase
                                ? formCase.noRegBendaSitaan
                                : `${form?.name} (Periode: ${monthOptions.find((m) => m.value === form?.month)?.label} ${form?.year})`
                        }}
                    </p>
                </div>
                <span
                    class="rounded-full bg-amber-200 px-2.5 py-1 text-[10px] font-bold text-amber-900 uppercase"
                    >{{ isEditingCase ? 'Mode Edit' : 'Form Terkunci' }}</span
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
                            </label>
                            <input
                                v-model="formCase.satuanKerja"
                                type="text"
                                readonly
                                class="w-full cursor-not-allowed rounded-lg border border-transparent bg-slate-200/70 px-3.5 py-2.5 text-xs font-bold text-slate-700 outline-none"
                            />
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

                        <!-- ROW 1: NO. REG BENDA SITAAN & TGL PENERIMAAN BARANG BUKTI -->
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
                                    placeholder="Cth. RB–33/Bna/Eku.2/06/..."
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >TGL PENERIMAAN BARANG BUKTI
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

                        <!-- ROW 2: NO. REG PENYIDIKAN & TGL REGISTER TAHAP PENYIDIKAN -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                                    placeholder="Cth. DM–36/Bna/Eku.2/06/..."
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >TGL REGISTER TAHAP PENYIDIKAN
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="formCase.tglRegPenyidikan"
                                    type="date"
                                    required
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
                                placeholder="Nama lengkap..."
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
                                placeholder="Cth. Pasal 114 ayat..."
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
                                        <option value="-">-</option>
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
                                >
                                    KETERANGAN
                                </label>
                                <select
                                    v-model="formCase.keterangan"
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3 py-2 text-xs text-slate-800 transition-all outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                >
                                    <option value="" disabled>
                                        Pilih Keterangan Tahap...
                                    </option>
                                    <option
                                        v-for="opt in keteranganTahapOptions"
                                        :key="opt"
                                        :value="opt"
                                    >
                                        {{ opt }}
                                    </option>
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
                                    class="flex cursor-pointer items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-500 transition-colors hover:bg-red-50 hover:text-red-700"
                                    @click="removeBarangBukti(index)"
                                    title="Hapus Barang Bukti Ini"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    <span>Hapus</span>
                                </button>
                            </div>

                            <!-- BARIS PALING ATAS: PILIH JENIS BARANG BUKTI (KHUSUS / UMUM) -->
                            <div
                                v-if="
                                    formCase.kategoriTindakPidana ===
                                    'NARKOTIKA DAN ZAT ADITIF LAINNYA'
                                "
                            >
                                <label
                                    class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >
                                    JENIS BARANG BUKTI
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="bb.jenisBarangBukti"
                                    required
                                    class="w-full rounded-lg border border-amber-300 bg-[#FFFBEB] px-3.5 py-2.5 text-xs font-bold text-slate-900 transition-all outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                >
                                    <option
                                        v-for="opt in jenisBbCategoryOptions"
                                        :key="opt"
                                        :value="opt"
                                    >
                                        {{ opt }}
                                    </option>
                                </select>
                            </div>

                            <!-- BARIS 2: JUMLAH (ANGKA) & URAIAN/KETERANGAN BARANG BUKTI -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <div class="sm:col-span-2">
                                    <label
                                        class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        JUMLAH
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="bb.jumlah"
                                        type="number"
                                        min="1"
                                        step="any"
                                        required
                                        placeholder="1"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-3.5 py-2.5 text-xs font-semibold text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                </div>
                                <div class="sm:col-span-10">
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
                                                const el =
                                                    e.target as HTMLTextAreaElement;
                                                el.style.height = 'auto';
                                                el.style.height =
                                                    el.scrollHeight + 'px';
                                            }
                                        "
                                        class="w-full resize-none overflow-hidden rounded-lg border border-transparent bg-[#F4F6F8] p-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- BARIS 3: TEMPAT PENYIMPANAN -->
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

                            <!-- BARIS 4: KHUSUS JIKA KATEGORI PIDANA NARKOTIKA & JENIS BB ADALAH NARKOTIKA -->
                            <div
                                v-if="
                                    formCase.kategoriTindakPidana ===
                                        'NARKOTIKA DAN ZAT ADITIF LAINNYA' &&
                                    bb.jenisBarangBukti === 'Narkotika'
                                "
                                class="space-y-3 rounded-lg border border-amber-200 bg-amber-50/60 p-4"
                            >
                                <p
                                    class="text-[11px] font-bold tracking-wider text-amber-900 uppercase"
                                >
                                    Rincian Kuantitatif Narkotika (Untuk
                                    Rekapitulasi)
                                </p>
                                <div
                                    class="grid grid-cols-1 gap-4 sm:grid-cols-3"
                                >
                                    <div>
                                        <label
                                            class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-700 uppercase"
                                        >
                                            JENIS NARKOTIKA
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="bb.jenisNarkotika"
                                            :required="
                                                formCase.kategoriTindakPidana ===
                                                    'NARKOTIKA DAN ZAT ADITIF LAINNYA' &&
                                                bb.jenisBarangBukti ===
                                                    'Narkotika'
                                            "
                                            class="w-full rounded-lg border border-transparent bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 transition-all outline-none focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000]"
                                        >
                                            <option value="" disabled>
                                                Pilih Jenis...
                                            </option>
                                            <option
                                                v-for="opt in jenisNarkotikaOptions"
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
                                        >
                                            JUMLAH (DESIMAL)
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="bb.jumlahNarkotika"
                                            type="number"
                                            step="any"
                                            :required="
                                                formCase.kategoriTindakPidana ===
                                                    'NARKOTIKA DAN ZAT ADITIF LAINNYA' &&
                                                bb.jenisBarangBukti ===
                                                    'Narkotika'
                                            "
                                            placeholder="0.00"
                                            class="w-full rounded-lg border border-transparent bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 transition-all outline-none focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000]"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1.5 block text-[11px] font-bold tracking-wider text-slate-700 uppercase"
                                        >
                                            SATUAN
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="bb.satuanNarkotika"
                                            :required="
                                                formCase.kategoriTindakPidana ===
                                                    'NARKOTIKA DAN ZAT ADITIF LAINNYA' &&
                                                bb.jenisBarangBukti ===
                                                    'Narkotika'
                                            "
                                            class="w-full rounded-lg border border-transparent bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-800 transition-all outline-none focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000]"
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
                                </div>
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
                                isEditingCase
                                    ? 'UPDATE CASE'
                                    : isNewForm
                                      ? 'SIMPAN FORM & CASE'
                                      : 'SIMPAN CASE'
                            }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
