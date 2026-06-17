<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    classroom: any; topic: any; phase: any; discussions: Array<any>;
}>();
</script>

<template>
    <Head :title="`Forum Fase: ${phase.name}`" />
    <div class="min-h-screen bg-[#F8FAFC] px-6 py-8 font-sans lg:px-10">
        <div class="mx-auto max-w-5xl">
            <div class="mb-6 flex items-center gap-2 text-[12px] font-bold text-slate-500">
                <Link :href="route('siswa.worksheet.show', { classroom: classroom.id, topic: topic.id, phase: phase.id })" class="hover:text-indigo-600 transition-colors">
                    <i class="pi pi-arrow-left mr-1"></i> Kembali ke Materi Fase
                </Link>
            </div>

            <div class="mb-8">
                <h1 class="text-2xl font-black text-slate-900">Ruang Diskusi Fase: {{ phase.name }}</h1>
            </div>

            <div class="space-y-4">
                <div v-for="discuss in discussions" :key="discuss.id">
                    <Card class="p-6 rounded-2xl border-slate-200 bg-white shadow-sm flex justify-between items-start">
                        <div class="flex-1 pr-6">
                            <h3 class="text-lg font-extrabold text-slate-800">{{ discuss.title }}</h3>
                            <p class="text-[14px] text-slate-500 mt-1.5">{{ discuss.description }}</p>
                        </div>
                        <div class="shrink-0 mt-2">
                            <Link :href="route('siswa.classes.topics.phases.discussions.show', { classroom: classroom.id, topic: topic.id, phase: phase.id, discussion: discuss.id })">
                                <Button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold h-10 px-5 rounded-xl">Ikut Diskusi</Button>
                            </Link>
                        </div>
                    </Card>
                </div>
                <div v-if="discussions.length === 0" class="text-center py-20 bg-white rounded-3xl border border-dashed">
                    <p class="text-sm text-slate-500">Belum ada diskusi di fase ini.</p>
                </div>
            </div>
        </div>
    </div>
</template>