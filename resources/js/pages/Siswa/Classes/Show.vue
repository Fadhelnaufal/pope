<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';

defineProps<{
    classroom: {
        id: number;
        class_name: string;
        class_code: string;
        description: string | null;
        teacher: {
            name: string;
        };
        topics: Array<{
            id: number;
            title: string;
            description: string;
            phases: Array<{ id: number; name: string; }>; 
            pivot: {
                is_open: boolean;
            };
        }>;
    };
}>();
</script>

<template>
    <Head :title="classroom.class_name + ' - EduChem'" />

    <main class="relative flex min-h-screen w-full flex-1 flex-col bg-[#F8FAFC] font-sans">
        
        <div class="relative overflow-hidden bg-gradient-to-r from-[#0B1E36] to-indigo-900 px-8 py-12">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
            <div class="relative z-10 mx-auto flex max-w-5xl flex-col justify-between gap-6 md:flex-row md:items-end">
                <div class="text-white">
                    <Link :href="route('siswa.classes.index')" class="mb-4 flex items-center gap-2 text-[13px] font-bold text-blue-300 transition-colors hover:text-white">
                        <i class="pi pi-arrow-left"></i> Kembali ke Kelas Saya
                    </Link>
                    <div class="mb-3 inline-block rounded-full border border-blue-400/30 bg-blue-500/20 px-3 py-1 text-[11px] font-bold tracking-wider text-blue-200 uppercase">
                        KODE: {{ classroom.class_code }}
                    </div>
                    <h1 class="mb-2 text-3xl font-black tracking-tight md:text-4xl">{{ classroom.class_name }}</h1>
                    <p class="max-w-2xl text-[14px] leading-relaxed text-blue-100/80">
                        {{ classroom.description || 'Selamat datang di kelas ini. Mari belajar kimia dengan pendekatan POE!' }}
                    </p>
                </div>
                <div class="flex min-w-[200px] items-center gap-4 rounded-2xl border border-white/20 bg-white/10 p-4 text-white backdrop-blur-md">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500 text-lg font-bold">
                        {{ classroom.teacher.name.charAt(0) }}
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-blue-200">Pengajar</p>
                        <p class="text-[14px] font-bold">{{ classroom.teacher.name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="mx-auto max-w-5xl">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-slate-800">Daftar Modul Materi</h2>
                    <span class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-[13px] font-bold text-slate-500 shadow-sm">
                        {{ classroom.topics.length }} Topik Tersedia
                    </span>
                </div>

                <div v-if="classroom.topics.length > 0" class="space-y-6">
                    <Card v-for="(topic, index) in classroom.topics" :key="topic.id" class="flex flex-col gap-5 rounded-2xl border-slate-200 bg-white p-6 transition-all hover:border-indigo-300 hover:shadow-md">
                        
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-xl font-black text-indigo-600">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-900">{{ topic.title }}</h3>
                                <p class="mt-1 text-[13px] text-slate-500">{{ topic.description || 'Pilih fase di bawah ini untuk mulai belajar.' }}</p>
                            </div>
                        </div>

                        <div class="h-px w-full bg-slate-100"></div>

                        <div>
                            <h4 class="mb-3 text-[11px] font-bold tracking-wider text-slate-400 uppercase">
                                Pilih Tahapan Pembelajaran:
                            </h4>
                            
                            <div v-if="topic.phases && topic.phases.length > 0" class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                                <Link 
                                    v-for="(phase, pIndex) in topic.phases" 
                                    :key="phase.id"
                                    :href="route('siswa.worksheet.show', { 
                                        classroom: classroom.id, 
                                        topic: topic.id, 
                                        phase: phase.id 
                                    })"
                                >
                                    <div class="group flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3 shadow-sm transition-all hover:border-indigo-600 hover:bg-indigo-600">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white text-[11px] font-bold text-slate-600 transition-colors group-hover:text-indigo-600">
                                                {{ pIndex + 1 }}
                                            </span>
                                            <span class="text-[13px] font-bold text-slate-700 transition-colors group-hover:text-white">
                                                {{ phase.name }}
                                            </span>
                                        </div>
                                        <i class="pi pi-arrow-right text-[11px] text-slate-400 transition-colors group-hover:text-indigo-200"></i>
                                    </div>
                                </Link>
                            </div>
                            
                            <div v-else class="inline-block rounded-xl border border-amber-100 bg-amber-50 p-3 text-[12px] font-medium text-amber-600">
                                <i class="pi pi-info-circle mr-1"></i> Guru belum menambahkan fase pembelajaran (Predict/Observe/Explain) ke dalam topik ini.
                            </div>
                        </div>

                    </Card>
                </div>

                <div v-else class="rounded-3xl border border-dashed border-slate-200 bg-white py-16 text-center">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-50">
                        <i class="pi pi-box text-3xl text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Belum Ada Materi Tersedia</h3>
                    <p class="mt-1 text-[13px] text-slate-500">Guru belum merilis topik pembelajaran untuk kelas ini.</p>
                </div>
            </div>
        </div>
    </main>
</template>