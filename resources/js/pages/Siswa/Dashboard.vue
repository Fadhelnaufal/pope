<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    VisXYContainer,
    VisArea,
    VisLine,
    VisAxis,
    VisCrosshair,
    VisTooltip,
} from '@unovis/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

// Mengimpor mesin asli Shadcn Vue Chart secara langsung beserta komponen Tooltip

defineProps<{
    classrooms?: Array<{
        id: number;
        class_name: string;
        class_code: string;
        description: string | null;
        topics_count: number;
    }>;
}>();

// Form Inertia untuk fitur Gabung Kelas
const form = useForm({
    class_code: '',
});

const submitJoinClass = () => {
    form.post(route('siswa.classes.join'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('class_code');
            alert('Berhasil bergabung ke kelas!');
        },
    });
};

// Data Dummy untuk Grafik
const chartData = ref([
    { name: 'Sen', 'Jam Belajar': 1.5 },
    { name: 'Sel', 'Jam Belajar': 2.5 },
    { name: 'Rab', 'Jam Belajar': 1.0 },
    { name: 'Kam', 'Jam Belajar': 3.0 },
    { name: 'Jum', 'Jam Belajar': 4.0 },
    { name: 'Sab', 'Jam Belajar': 3.5 },
    { name: 'Min', 'Jam Belajar': 2.0 },
]);

// Fungsi helper mutlak untuk Unovis
const x = (d: any, i: number) => i;
const y = (d: any) => d['Jam Belajar'];
const tickFormat = (i: number) => chartData.value[i]?.name || '';

// Template desain untuk isi Tooltip saat di-hover
const tooltipTemplate = (d: any) => `
    <div class="bg-white/95 backdrop-blur-sm border border-slate-200 shadow-lg rounded-xl px-3 py-2">
        <p class="text-[11px] font-medium text-slate-500 mb-0.5">${d.name}</p>
        <p class="text-[13px] font-black text-[#0D9488]">${d['Jam Belajar']} Jam</p>
    </div>
`;

// Dummy Data untuk Aktivitas & AI
const recentActivities = [
    {
        id: 1,
        title: 'Menyelesaikan Fase Observe',
        subject: 'Ikatan Kimia',
        time: '2 jam yang lalu',
        icon: 'pi-check-circle',
        color: 'text-green-500',
    },
    {
        id: 2,
        title: 'Bergabung dengan Kelas',
        subject: 'Kimia Dasar X',
        time: '5 jam yang lalu',
        icon: 'pi-user-plus',
        color: 'text-blue-500',
    },
    {
        id: 3,
        title: 'Menonton Video H5P',
        subject: 'Struktur Atom',
        time: 'Kemarin',
        icon: 'pi-video',
        color: 'text-purple-500',
    },
];

const aiInsights = [
    "Pemahamanmu pada topik 'Sifat Periodik' meningkat 12% setelah menonton video simulasi.",
    "Jangan lupa kerjakan Lembar Kerja (Worksheet) 'Energi Ionisasi' untuk membuka bab selanjutnya.",
    "Gaya belajarmu sangat baik di fase 'Observe' (Observasi visual).",
];
</script>

<template>
    <Head title="Overview Siswa - EduChem" />

    <main
        class="relative flex min-h-screen w-full flex-1 flex-col bg-[#F5F8FA] font-sans"
    >
        <div
            class="pointer-events-none absolute top-0 right-0 -z-10 h-96 w-full bg-gradient-to-b from-teal-50/50 to-transparent"
        ></div>

        <header
            class="sticky top-0 z-20 flex h-[80px] items-center justify-end border-b border-slate-100 bg-white/50 px-8 backdrop-blur-md"
        >
            <div class="flex items-center gap-5">
                <button
                    class="relative text-slate-400 transition-colors hover:text-slate-600"
                >
                    <i class="pi pi-bell text-xl"></i>
                    <span
                        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-rose-500 text-[9px] font-bold text-white"
                        >2</span
                    >
                </button>
                <div class="h-8 w-[1px] bg-slate-200"></div>
                <div class="flex cursor-pointer items-center gap-3">
                    <div class="hidden text-right md:block">
                        <p
                            class="text-[13px] leading-tight font-bold text-slate-800"
                        >
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-[11px] font-medium text-slate-500">
                            {{ $page.props.auth.user.email }}
                        </p>
                    </div>
                    <img
                        src="https://ui-avatars.com/api/?name=Siswa&background=0F5A53&color=fff"
                        class="h-10 w-10 rounded-full border-2 border-white shadow-sm"
                        alt="Profile"
                    />
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="mx-auto max-w-7xl space-y-6">
                <div
                    class="mb-2 flex flex-col justify-between gap-4 md:flex-row md:items-center"
                >
                    <div>
                        <h1
                            class="text-[26px] font-black tracking-tight text-slate-900"
                        >
                            Overview
                        </h1>
                        <p
                            class="mt-0.5 text-[13px] font-medium text-slate-500"
                        >
                            Sistem belajar kimia berbasis
                            Predict-Observe-Explain (POE)
                        </p>
                    </div>

                    <form
                        @submit.prevent="submitJoinClass"
                        class="flex flex-col items-end gap-1"
                    >
                        <div
                            class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm"
                        >
                            <div class="relative w-full md:w-48">
                                <i
                                    class="pi pi-key absolute top-1/2 left-3 -translate-y-1/2 text-[12px] text-slate-400"
                                ></i>
                                <Input
                                    v-model="form.class_code"
                                    placeholder="Kode Kelas (6 Digit)"
                                    maxlength="6"
                                    class="h-9 w-full rounded-xl border-none bg-slate-50 pr-3 pl-8 text-[12px] font-bold tracking-widest uppercase focus-visible:ring-0"
                                />
                            </div>
                            <Button
                                type="submit"
                                :disabled="
                                    form.processing ||
                                    form.class_code.length !== 6
                                "
                                class="h-9 rounded-xl bg-[#0F5A53] px-5 text-[12px] font-bold text-white shadow-sm hover:bg-[#0d4f49] disabled:opacity-50"
                            >
                                <span v-if="form.processing"
                                    ><i class="pi pi-spin pi-spinner"></i
                                ></span>
                                <span v-else>Gabung</span>
                            </Button>
                        </div>
                        <span
                            v-if="form.errors.class_code"
                            class="mr-2 text-[11px] font-bold text-rose-500"
                        >
                            * {{ form.errors.class_code }}
                        </span>
                    </form>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <Card
                        class="rounded-2xl border-none bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-[13px] font-bold text-slate-600"
                                >Kelas Aktif</span
                            >
                            <div
                                class="rounded-lg bg-blue-50 p-2 text-blue-500"
                            >
                                <i class="pi pi-book"></i>
                            </div>
                        </div>
                        <h3 class="text-3xl font-black text-slate-900">
                            {{ classrooms?.length || 0 }}
                        </h3>
                        <p class="mt-2 text-[11px] font-medium text-slate-400">
                            <span class="font-bold text-emerald-500">+1</span>
                            bulan ini
                        </p>
                    </Card>
                    <Card
                        class="rounded-2xl border-none bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-[13px] font-bold text-slate-600"
                                >Progress Rata-rata</span
                            >
                            <div
                                class="rounded-lg bg-purple-50 p-2 text-purple-500"
                            >
                                <i class="pi pi-chart-line"></i>
                            </div>
                        </div>
                        <h3 class="text-3xl font-black text-slate-900">45%</h3>
                        <p class="mt-2 text-[11px] font-medium text-slate-400">
                            <span class="font-bold text-emerald-500">+12%</span>
                            vs minggu lalu
                        </p>
                    </Card>
                    <Card
                        class="rounded-2xl border-none bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-[13px] font-bold text-slate-600"
                                >Modul Selesai</span
                            >
                            <div
                                class="rounded-lg bg-orange-50 p-2 text-orange-500"
                            >
                                <i class="pi pi-verified"></i>
                            </div>
                        </div>
                        <h3 class="text-3xl font-black text-slate-900">8</h3>
                        <p class="mt-2 text-[11px] font-medium text-slate-400">
                            <span class="font-bold text-emerald-500">+2</span>
                            topik baru
                        </p>
                    </Card>
                    <Card
                        class="rounded-2xl border-none bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-[13px] font-bold text-slate-600"
                                >Rata-rata Tes</span
                            >
                            <div
                                class="rounded-lg bg-rose-50 p-2 text-rose-500"
                            >
                                <i class="pi pi-star-fill"></i>
                            </div>
                        </div>
                        <h3 class="text-3xl font-black text-slate-900">82.5</h3>
                        <p class="mt-2 text-[11px] font-medium text-slate-400">
                            <span class="font-bold text-emerald-500">+5.0</span>
                            vs tes sebelumnya
                        </p>
                    </Card>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        <Card
                            class="rounded-2xl border-none bg-white p-6 shadow-sm"
                        >
                            <div class="mb-6 flex items-center justify-between">
                                <h3
                                    class="text-[16px] font-extrabold text-slate-800"
                                >
                                    Aktivitas Belajar Harian
                                </h3>
                                <select
                                    class="rounded-lg border-slate-200 bg-slate-50 px-3 py-1.5 text-[12px] font-medium text-slate-500 focus:ring-0"
                                >
                                    <option>Minggu Ini</option>
                                    <option>Bulan Ini</option>
                                </select>
                            </div>

                            <div class="mt-4 h-[250px] w-full">
                                <VisXYContainer :data="chartData" :height="250">
                                    <VisArea
                                        :x="x"
                                        :y="y"
                                        color="#2DD4BF"
                                        :opacity="0.2"
                                    />
                                    <VisLine
                                        :x="x"
                                        :y="y"
                                        color="#0D9488"
                                        :lineWidth="3"
                                    />
                                    <VisAxis
                                        type="x"
                                        :tickFormat="tickFormat"
                                        :gridLine="false"
                                    />

                                    <VisCrosshair
                                        color="#0D9488"
                                        :template="tooltipTemplate"
                                    />
                                    <VisTooltip />
                                </VisXYContainer>
                            </div>
                        </Card>

                        <Card
                            class="rounded-2xl border-none bg-white p-6 shadow-sm"
                        >
                            <div class="mb-6 flex items-center justify-between">
                                <h3
                                    class="text-[16px] font-extrabold text-slate-800"
                                >
                                    Kelas Aktif Saya
                                </h3>
                                <Link
                                    href="#"
                                    class="text-[12px] font-bold text-[#0F5A53] hover:underline"
                                    >Lihat Semua</Link
                                >
                            </div>

                            <div
                                class="space-y-4"
                                v-if="classrooms && classrooms.length > 0"
                            >
                                <div
                                    v-for="kelas in classrooms"
                                    :key="kelas.id"
                                    class="group flex items-center justify-between rounded-xl border border-slate-100 p-4 transition-colors hover:border-teal-200 hover:bg-teal-50/30"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-400 to-emerald-600 text-lg font-black text-white shadow-sm"
                                        >
                                            {{ kelas.class_name.charAt(0) }}
                                        </div>
                                        <div>
                                            <h4
                                                class="text-[14px] font-bold text-slate-900 transition-colors group-hover:text-[#0F5A53]"
                                            >
                                                {{ kelas.class_name }}
                                            </h4>
                                            <p
                                                class="mt-0.5 text-[11px] font-medium text-slate-500"
                                            >
                                                <i
                                                    class="pi pi-folder mr-1"
                                                ></i>
                                                {{ kelas.topics_count }} Topik
                                                Materi
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        variant="outline"
                                        class="h-9 rounded-xl border-slate-200 px-4 text-[12px] font-bold text-[#0F5A53] shadow-sm transition-all hover:bg-[#0F5A53] hover:text-white"
                                    >
                                        Lanjutkan
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="py-8 text-center">
                                <p
                                    class="text-[13px] font-medium text-slate-500"
                                >
                                    Anda belum bergabung ke kelas manapun.
                                    Masukkan kode kelas di atas.
                                </p>
                            </div>
                        </Card>
                    </div>

                    <div class="space-y-6">
                        <Card
                            class="group relative overflow-hidden rounded-2xl border-none bg-white p-6 shadow-sm"
                        >
                            <div
                                class="absolute top-0 right-0 h-32 w-32 rounded-full bg-indigo-500/10 blur-3xl transition-all group-hover:bg-indigo-500/20"
                            ></div>

                            <div
                                class="relative z-10 mb-5 flex items-center justify-between"
                            >
                                <div>
                                    <h3
                                        class="flex items-center gap-1.5 text-[15px] font-extrabold text-slate-900"
                                    >
                                        <i
                                            class="pi pi-sparkles text-indigo-500"
                                        ></i>
                                        EduChem AI
                                    </h3>
                                    <p
                                        class="mt-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                    >
                                        Smart Insights (POE)
                                    </p>
                                </div>
                                <button
                                    class="h-7 rounded-lg border border-slate-200 px-2.5 text-[11px] font-bold text-slate-500 shadow-sm transition-colors hover:bg-slate-50"
                                >
                                    Refresh
                                </button>
                            </div>

                            <div class="relative z-10 space-y-3">
                                <div
                                    v-for="(insight, index) in aiInsights"
                                    :key="index"
                                    class="flex gap-3 rounded-xl border border-indigo-100/60 bg-indigo-50/50 p-3.5 transition-colors hover:bg-indigo-50"
                                >
                                    <i
                                        class="pi pi-asterisk mt-1 text-[10px] text-indigo-400"
                                    ></i>
                                    <p
                                        class="text-[12px] leading-relaxed font-medium text-slate-700"
                                    >
                                        {{ insight }}
                                    </p>
                                </div>
                            </div>

                            <Button
                                class="relative z-10 mt-5 h-10 w-full rounded-xl border border-indigo-200 bg-indigo-100 text-[13px] font-bold text-indigo-700 shadow-sm transition-colors hover:bg-indigo-200"
                            >
                                <i class="pi pi-comment mr-2"></i> Tanya AI
                                Sekarang
                            </Button>
                        </Card>

                        <Card
                            class="rounded-2xl border-none bg-white p-6 shadow-sm"
                        >
                            <h3
                                class="mb-5 text-[16px] font-extrabold text-slate-800"
                            >
                                Riwayat Aktivitas
                            </h3>
                            <div class="space-y-5">
                                <div
                                    v-for="activity in recentActivities"
                                    :key="activity.id"
                                    class="flex gap-4"
                                >
                                    <div class="mt-1">
                                        <div
                                            :class="`flex h-8 w-8 items-center justify-center rounded-full border border-slate-100 bg-slate-50 shadow-sm ${activity.color}`"
                                        >
                                            <i
                                                :class="`pi ${activity.icon} text-[14px]`"
                                            ></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[13px] font-bold text-slate-800"
                                        >
                                            {{ activity.title }}
                                        </p>
                                        <p
                                            class="mt-0.5 text-[12px] font-medium text-slate-500"
                                        >
                                            {{ activity.subject }}
                                        </p>
                                        <p
                                            class="mt-1.5 text-[10px] font-bold text-slate-400"
                                        >
                                            <i
                                                class="pi pi-clock mr-1 text-[9px]"
                                            ></i>
                                            {{ activity.time }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
