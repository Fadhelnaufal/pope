<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ref, onMounted } from 'vue';
import { toast } from 'vue-sonner';

// IMPORT MARKDOWN & KATEX UNTUK RENDER FEEDBACK AI
import { marked } from 'marked';
import katex from 'katex';
import 'katex/dist/katex.min.css';

const props = defineProps<{
    classroom: any;
    topic: any;
    phase: any;
    student: any;
    studentAnswers: Record<number, string>;
    studentScores: Record<number, number>;
    aiFeedbacks: Record<number, string>;
    studentFiles: Record<number, string>; // Menerima URL file dari Controller
}>();

// Form state untuk nilai manual
const manualScores = ref<Record<number, number>>({});
const isSaving = ref(false);

onMounted(() => {
    if (props.studentScores) {
        for (const [key, value] of Object.entries(props.studentScores)) {
            manualScores.value[Number(key)] = value;
        }
    }
});

const saveGrades = () => {
    isSaving.value = true;
    router.put(route('guru.phases.evaluations.update', { phase: props.phase.id, student: props.student.id }), {
        scores: manualScores.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('✨ Semua nilai berhasil disimpan!');
        },
        onError: () => toast.error('⚠️ Gagal menyimpan nilai. Periksa koneksi Anda.'),
        onFinish: () => { isSaving.value = false; }
    });
};

const parseAnswer = (data: string) => {
    try {
        const parsed = JSON.parse(data);
        if (Array.isArray(parsed)) return parsed.join(', ');
        return data;
    } catch (e) {
        return data;
    }
};

const isManualGrading = (type: string) => {
    return ['eval_essay', 'input_text', 'eval_short', 'eval_file'].includes(type);
};

const renderMarkdown = (text: string) => {
    if (!text) return '';
    const mathBlocks: string[] = [];
    let processedText = text.replace(/\$\$(.+?)\$\$/gs, (match, math) => {
        try {
            const rendered = katex.renderToString(math, { displayMode: true });
            mathBlocks.push(`<div class="my-3 overflow-x-auto">${rendered}</div>`);
            return `%%MATH_BLOCK_TOKEN_${mathBlocks.length - 1}%%`;
        } catch (e) { return match; }
    });
    processedText = processedText.replace(/\$(.+?)\$/g, (match, math) => {
        try {
            const rendered = katex.renderToString(math, { displayMode: false });
            mathBlocks.push(rendered);
            return `%%MATH_BLOCK_TOKEN_${mathBlocks.length - 1}%%`;
        } catch (e) { return match; }
    });
    let finalHtml = marked.parse(processedText, { breaks: true }) as string;
    mathBlocks.forEach((renderedMath, index) => {
        finalHtml = finalHtml.split(`%%MATH_BLOCK_TOKEN_${index}%%`).join(renderedMath);
    });
    return finalHtml;
};
</script>

<template>
    <Head :title="`Periksa: ${student.name}`" />

    <div class="min-h-screen bg-[#F8FAFC] px-6 py-8 font-sans lg:px-10 pb-32">
        <div class="mx-auto max-w-4xl">
            
            <div class="mb-6 flex items-center gap-2 text-[12px] font-bold text-slate-500">
                <Link :href="route('guru.phases.evaluations.index', { classroom: classroom.id, topic: topic.id, phase: phase.id })" class="hover:text-indigo-600 transition-colors">
                    <i class="pi pi-arrow-left mr-1"></i> Kembali ke Daftar Siswa
                </Link>
            </div>

            <Card class="mb-8 p-6 rounded-2xl border-none bg-indigo-600 text-white shadow-md flex justify-between items-center bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
                <div>
                    <span class="text-[11px] font-black tracking-widest text-indigo-200 uppercase mb-1 block">Lembar Jawaban Siswa</span>
                    <h1 class="text-2xl font-black">{{ student.name }}</h1>
                    <p class="text-sm text-indigo-100 mt-1"><i class="pi pi-envelope text-xs mr-1"></i> {{ student.email }}</p>
                </div>
            </Card>

            <div class="space-y-6">
                <template v-for="(content, idx) in phase.contents" :key="content.id">
                    
                    <Card v-if="content.type.startsWith('eval_') || content.type === 'input_text'" class="p-6 rounded-2xl border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                        
                        <div class="mb-5 pb-5 border-b border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-1 rounded">
                                Soal {{ idx + 1 }} • {{ content.type.replace('eval_', '').replace('input_text', 'essay').toUpperCase() }}
                            </span>
                            <div class="prose prose-sm max-w-none text-slate-800 font-medium mt-3" v-html="content.content_data.question || content.content_data.label || 'Pertanyaan/Instruksi' "></div>
                        </div>

                        <div class="mb-5 p-5 rounded-xl border" :class="(studentAnswers[content.id] || studentFiles[content.id]) ? 'bg-slate-50 border-slate-200' : 'bg-rose-50 border-rose-100'">
                            <span class="text-[11px] font-bold uppercase tracking-wider block mb-3" :class="(studentAnswers[content.id] || studentFiles[content.id]) ? 'text-indigo-600' : 'text-rose-400'">
                                <i class="pi pi-user mr-1"></i> Jawaban Siswa:
                            </span>
                            
                            <div class="text-[14px] text-slate-700">
                                <div v-if="studentAnswers[content.id]" class="whitespace-pre-wrap leading-relaxed bg-white p-4 rounded-lg border border-slate-100 mb-4">{{ parseAnswer(studentAnswers[content.id]) }}</div>
                                
                                <div v-if="studentFiles && studentFiles[content.id]" class="mt-2">
                                    <a :href="studentFiles[content.id]" target="_blank" class="inline-flex items-center gap-2 bg-pink-50 text-pink-700 hover:bg-pink-100 px-4 py-2 rounded-lg font-bold transition-colors border border-pink-100 text-sm">
                                        <i class="pi pi-paperclip"></i> Lihat Lampiran Siswa (Gambar/PDF)
                                    </a>
                                </div>
                                <div v-else-if="!studentAnswers[content.id]" class="text-[13px] italic text-rose-500">Siswa tidak mengisi / mengosongkan jawaban ini.</div>
                            </div>
                        </div>

                        <div v-if="aiFeedbacks && aiFeedbacks[content.id]" class="relative bg-gradient-to-br from-indigo-50 to-purple-50 p-6 rounded-2xl border border-indigo-100 shadow-sm mt-4 mb-6">
                            <div class="absolute -top-3 left-6 bg-white border border-indigo-200 px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm">
                                <i class="pi pi-sparkles text-indigo-500 text-[10px]"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Feedback Guru AI</span>
                            </div>
                            <div class="prose prose-sm prose-slate max-w-none mt-3 leading-relaxed" v-html="renderMarkdown(aiFeedbacks[content.id])"></div>
                        </div>

                        <div class="flex items-center justify-between bg-slate-800 p-4 rounded-xl border border-slate-700 shadow-inner">
                            <div class="flex flex-col">
                                <span class="text-[13px] font-bold text-white">Beri Nilai Soal Ini</span>
                                <span v-if="isManualGrading(content.type)" class="text-[10px] text-slate-400">Skala 0 - 100</span>
                                <span v-else class="text-[10px] text-emerald-400">Diperiksa otomatis oleh sistem</span>
                            </div>
                            
                            <div v-if="!isManualGrading(content.type)" class="flex items-center gap-3">
                                <input type="number" disabled :value="studentScores[content.id] || 0" class="w-20 h-10 text-center rounded-lg border-transparent bg-slate-700 text-slate-300 font-black cursor-not-allowed text-lg">
                            </div>

                            <div v-else class="relative">
                                <input type="number" min="0" max="100" v-model="manualScores[content.id]" class="w-20 h-10 text-center rounded-lg border-2 border-indigo-400 bg-white text-indigo-700 font-black focus:ring-4 focus:ring-indigo-500/30 shadow-sm text-lg" placeholder="0">
                            </div>
                        </div>

                    </Card>
                </template>
            </div>
        </div>
    </div>

    <div class="fixed bottom-8 right-8 z-[100]">
        <Button @click="saveGrades" :disabled="isSaving" class="h-14 px-8 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-black shadow-[0_8px_16px_rgba(16,185,129,0.3)] transition-all active:scale-95">
            <i class="pi mr-2" :class="isSaving ? 'pi-spinner pi-spin' : 'pi-check-circle'"></i>
            {{ isSaving ? 'Menyimpan...' : 'Simpan Semua Nilai' }}
        </Button>
    </div>
</template>