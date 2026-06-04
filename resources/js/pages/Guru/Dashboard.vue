<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    classes: Array<{
        id: number;
        class_name: string;
        class_code: string;
        description: string | null;
        created_at: string;
    }>;
}>();

// --- LOGIKA PEMOTONGAN DATA KELAS ---
// Ambil maksimal 5 kelas terbaru untuk ditampilkan di Dashboard
const recentClasses = computed(() => {
    return props.classes.slice(0, 5);
});

// Hitung sisa kelas (jika ada lebih dari 5)
const remainingClassesCount = computed(() => {
    return props.classes.length > 5 ? props.classes.length - 5 : 0;
});

const form = useForm({
    class_name: '',
    description: '',
});

const submitClass = () => {
    const createdClassName = form.class_name;

    form.post(route('guru.classes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Kelas Aktif!', {
                description: `Kelas "${createdClassName}" berhasil dibuat. Kode unik siap dibagikan ke siswa Anda.`,
                icon: '🚀',
                duration: 3000,
                action: {
                    label: 'Siap!',
                    onClick: () => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    },
                },
            });
        },
        onError: () => {
            toast.error('Gagal Membuat Kelas', {
                description:
                    'Mohon periksa kembali isian form Anda. Nama kelas wajib diisi.',
                icon: '⚠️',
                duration: 5000,
            });
        },
    });
};

// --- DATA DUMMY ---
const stats = {
    total_students: 142, // Nanti bisa dihubungkan ke backend service Anda
    pending_reviews: 18,
};

const recentActivities = [
    {
        id: 1,
        student: 'Budi Santoso',
        action: 'menyelesaikan tahap Observe',
        class: 'Kimia Dasar X',
        time: '10 menit yang lalu',
    },
    {
        id: 2,
        student: 'Siti Aminah',
        action: 'mengirimkan hipotesis (Predict)',
        class: 'Ikatan Kimia XI',
        time: '1 jam yang lalu',
    },
    {
        id: 3,
        student: 'Andi Wijaya',
        action: 'bergabung ke kelas menggunakan kode',
        class: 'Kimia Dasar X',
        time: '2 jam yang lalu',
    },
];
</script>

<template>
    <Head title="Workspace Guru" />

    <div class="min-h-screen bg-[#F8FAFC] px-6 py-8 font-sans lg:px-10">
        <div
            class="mx-auto mb-8 flex max-w-7xl flex-col items-start justify-between gap-4 md:flex-row md:items-center"
        >
            <div>
                <h1 class="text-[26px] font-bold tracking-tight text-slate-900">
                    Workspace Guru
                </h1>
                <p class="mt-1 text-[14px] font-medium text-slate-500">
                    Kelola kelas, pantau progress metode POE, dan evaluasi siswa
                    Anda.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <Button
                    variant="outline"
                    size="icon"
                    class="h-9 w-9 rounded-lg border-slate-200 bg-white shadow-sm transition-colors hover:bg-indigo-50 hover:text-indigo-600"
                >
                    <i class="pi pi-bell text-[14px]"></i>
                </Button>

                <div
                    class="flex cursor-pointer items-center gap-3 rounded-full border border-slate-200 bg-white py-1.5 pr-4 pl-1.5 shadow-sm transition-colors hover:bg-slate-50"
                >
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-[11px] font-bold text-white shadow-inner"
                    >
                        <i class="pi pi-user text-[10px]"></i>
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[13px] leading-none font-bold text-slate-800"
                            >Panel Guru</span
                        >
                        <span
                            class="mt-0.5 text-[9px] font-bold tracking-wider text-emerald-600"
                            >ROLE: GURU</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <div
            class="mx-auto mb-8 grid max-w-7xl grid-cols-1 gap-5 md:grid-cols-3"
        >
            <Card class="rounded-xl border-slate-200 bg-white shadow-sm">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Total Kelas Anda
                            </p>
                            <h2 class="text-3xl font-extrabold text-slate-900">
                                {{ classes.length }}
                            </h2>
                        </div>
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                        >
                            <i class="pi pi-book text-lg"></i>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border-slate-200 bg-white shadow-sm">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Total Siswa Aktif
                            </p>
                            <h2 class="text-3xl font-extrabold text-slate-900">
                                {{ stats.total_students }}
                            </h2>
                        </div>
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                        >
                            <i class="pi pi-users text-lg"></i>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card
                class="relative overflow-hidden rounded-xl border-none bg-gradient-to-br from-[#10B981] to-[#047857] shadow-sm"
            >
                <CardContent class="p-6">
                    <i
                        class="pi pi-check-circle absolute -right-3 -bottom-3 text-7xl text-white opacity-10"
                    ></i>
                    <div class="relative z-10 flex items-start justify-between">
                        <div>
                            <p
                                class="mb-2 text-[11px] font-bold tracking-wider text-emerald-100 uppercase"
                            >
                                Perlu Evaluasi (POE)
                            </p>
                            <h2 class="text-3xl font-extrabold text-white">
                                {{ stats.pending_reviews }}
                            </h2>
                        </div>
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-white backdrop-blur-sm"
                        >
                            <i class="pi pi-clipboard text-lg"></i>
                        </div>
                    </div>
                    <div
                        class="relative z-10 mt-4 flex items-center gap-2 text-sm"
                    >
                        <span
                            class="flex items-center gap-1.5 text-[12px] font-medium text-emerald-50"
                        >
                            Menunggu penilaian Anda
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 lg:grid-cols-3">
            
            <div class="flex flex-col gap-5 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-[17px] font-bold text-slate-800">
                        Kelas Terbaru
                    </h3>

                    <Link
                        v-if="classes.length > 0"
                        :href="route('guru.classes.index')"
                        class="flex items-center gap-1.5 text-[13px] font-bold text-indigo-600 transition-colors hover:text-indigo-700 hover:underline"
                    >
                        Lihat Semua Kelas
                        <i class="pi pi-arrow-right text-[10px]"></i>
                    </Link>
                </div>

                <div
                    v-if="recentClasses.length > 0"
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2"
                >
                    <Card
                        v-for="cls in recentClasses"
                        :key="cls.id"
                        class="group flex flex-col rounded-xl border-slate-200 bg-white shadow-sm transition-all hover:border-indigo-200 hover:shadow-md"
                    >
                        <CardContent class="flex flex-1 flex-col p-5">
                            <div class="mb-4 flex items-start justify-between">
                                <div class="pr-2">
                                    <h4
                                        class="line-clamp-1 text-[16px] font-bold text-slate-900 transition-colors group-hover:text-indigo-600"
                                        :title="cls.class_name"
                                    >
                                        {{ cls.class_name }}
                                    </h4>
                                    <p
                                        class="mt-1 flex items-center gap-1.5 text-[12px] text-slate-500"
                                    >
                                        <i class="pi pi-calendar text-[10px]"></i>
                                        Dibuat:
                                        {{
                                            new Date(cls.created_at).toLocaleDateString('id-ID')
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="shrink-0 rounded-md border border-indigo-100 bg-indigo-50 px-2.5 py-1 font-mono text-[12px] font-bold tracking-widest text-indigo-700"
                                    title="Bagikan kode ini ke siswa"
                                >
                                    {{ cls.class_code }}
                                </div>
                            </div>

                            <p class="mb-5 line-clamp-2 flex-1 text-[13px] text-slate-600">
                                {{
                                    cls.description ||
                                    'Belum ada deskripsi untuk kelas ini.'
                                }}
                            </p>

                            <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4">
                                <div class="flex -space-x-2 overflow-hidden">
                                    <div class="inline-block h-6 w-6 rounded-full bg-slate-200 ring-2 ring-white"></div>
                                    <div class="inline-block h-6 w-6 rounded-full bg-slate-300 ring-2 ring-white"></div>
                                    <div class="inline-block h-6 w-6 rounded-full bg-slate-400 ring-2 ring-white"></div>
                                </div>
                                
                                <Link 
                                    :href="route('guru.classes.show', cls.id)" 
                                    class="flex items-center text-[12px] font-semibold text-indigo-600 group-hover:text-indigo-700"
                                >
                                    Masuk Kelas
                                    <i class="pi pi-arrow-right ml-1.5 text-[10px]"></i>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <Link
                        v-if="remainingClassesCount > 0"
                        :href="route('guru.classes.index')"
                        class="group flex min-h-[180px] cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50/50 transition-all hover:bg-slate-50"
                    >
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 transition-colors group-hover:bg-indigo-200">
                            <span class="font-bold text-indigo-700">+{{ remainingClassesCount }}</span>
                        </div>
                        <h4 class="text-[14px] font-bold text-slate-700">Kelas Lainnya</h4>
                        <p class="mt-1 text-[12px] font-medium text-slate-500 transition-colors group-hover:text-indigo-600">
                            Lihat di Manajemen Kelas
                        </p>
                    </Link>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-20 text-slate-400"
                >
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50">
                        <i class="pi pi-folder-open text-3xl text-slate-300"></i>
                    </div>
                    <h4 class="mb-1 text-[16px] font-bold text-slate-700">
                        Belum Ada Kelas
                    </h4>
                    <p class="max-w-[250px] text-center text-[13px] font-medium text-slate-500">
                        Anda belum membuat kelas. Silakan buat kelas pertama
                        Anda melalui panel di samping.
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:col-span-1">
                <Card class="rounded-xl border-slate-200 bg-white shadow-sm">
                    <CardHeader class="border-b border-slate-50 pb-4">
                        <CardTitle
                            class="flex items-center gap-2 text-[16px] font-bold text-slate-800"
                        >
                            <i class="pi pi-plus-circle text-indigo-600"></i>
                            Buat Kelas Baru
                        </CardTitle>
                        <CardDescription class="mt-1 text-[12px] text-slate-500">
                            Sistem otomatis men-generate kode unik 6 digit.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-5">
                        <form @submit.prevent="submitClass" class="space-y-4">
                            <div>
                                <label class="mb-2 block text-[12px] font-bold tracking-wider text-slate-700 uppercase">
                                    Nama Kelas <span class="text-rose-500">*</span>
                                </label>
                                <Input
                                    v-model="form.class_name"
                                    type="text"
                                    required
                                    placeholder="Contoh: Kimia X IPA 1"
                                    class="h-10 rounded-lg border-slate-200 text-[13px] focus-visible:ring-indigo-500"
                                />
                                <span
                                    v-if="form.errors.class_name"
                                    class="mt-1.5 block text-[11px] font-semibold text-rose-500"
                                >
                                    {{ form.errors.class_name }}
                                </span>
                            </div>

                            <div>
                                <label class="mb-2 block text-[12px] font-bold tracking-wider text-slate-700 uppercase">
                                    Deskripsi Singkat
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Opsional: Tujuan pembelajaran..."
                                    class="w-full resize-none rounded-lg border border-slate-200 p-3 text-[13px] transition-all outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                ></textarea>
                            </div>

                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="mt-2 h-10 w-full rounded-lg bg-indigo-600 font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                            >
                                <i v-if="!form.processing" class="pi pi-check mr-2 text-[12px]"></i>
                                <i v-else class="pi pi-spinner pi-spin mr-2 text-[12px]"></i>
                                Generate Kelas
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card class="flex-1 rounded-xl border-slate-200 bg-white shadow-sm">
                    <CardHeader class="border-b border-slate-50 pb-4">
                        <CardTitle class="text-[15px] font-bold text-slate-800">
                            Aktivitas Siswa
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-0 pt-4">
                        <div class="divide-y divide-slate-100">
                            <div
                                v-for="act in recentActivities"
                                :key="act.id"
                                class="p-4 transition-colors hover:bg-slate-50/80"
                            >
                                <div class="flex gap-3">
                                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-50">
                                        <i class="pi pi-bolt text-[12px] text-indigo-500"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] leading-snug text-slate-700">
                                            <span class="font-bold text-slate-900">{{ act.student }}</span>
                                            {{ act.action }} di
                                            <span class="font-semibold">{{ act.class }}</span>.
                                        </p>
                                        <p class="mt-1 text-[11px] font-medium text-slate-400">
                                            {{ act.time }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-slate-100 p-4">
                            <Button
                                variant="link"
                                class="h-auto w-full p-0 text-[12px] font-bold text-indigo-600"
                            >
                                Lihat Semua Log
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
            
        </div>
    </div>
</template>