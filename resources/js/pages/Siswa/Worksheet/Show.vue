<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import { ref, onMounted, computed } from 'vue';
import { marked } from 'marked';
import FloatingChatbot from '@/components/FloatingChatbot.vue';
// Kita bisa menghapus FileUploadItem karena kita akan menggunakan native form Inertia yang lebih stabil

// IMPORT KATEX UNTUK RUMUS KIMIA
import katex from 'katex';
import 'katex/dist/katex.min.css';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps<{
    classroom: { id: number; class_name: string };
    topic: { id: number; title: string };
    phase: {
        id: number;
        name: string;
        is_ai_enabled: boolean;
        contents: Array<{
            id: number;
            type: string;
            content_data: any;
            order: number;
        }>;
    };
    studentAnswers: Record<number, string>;
    studentFiles: Record<number, string>; // TAMBAHAN: Menerima URL file dari Controller
    aiFeedbacks: Record<number, string>;
    studentScores: Record<number, number>;
    studentIsCorrect: Record<number, boolean>;
    isPhaseSubmitted: boolean; 
}>();

const answers = ref<Record<number, any>>({});
const answerFiles = ref<Record<number, File | null>>({}); // TAMBAHAN: State untuk file yang dipilih siswa
const isSubmitting = ref<Record<number, boolean>>({});

// STATE UNTUK LOADING AI REAL-TIME
const isWaitingForAI = ref<Record<number, boolean>>({});
const pollIntervals: Record<number, any> = {};
const pollAttempts: Record<number, number> = {};

// MENGHITUNG STATISTIK REKAPITULASI
const correctCount = computed(() => {
    if (!props.studentIsCorrect) return 0;
    return Object.values(props.studentIsCorrect).filter(
        (val) => val === 1 || val === true,
    ).length;
});

const incorrectCount = computed(() => {
    if (!props.studentIsCorrect) return 0;
    return Object.values(props.studentIsCorrect).filter(
        (val) => val === 0 || val === false,
    ).length;
});

const totalScore = computed(() => {
    if (!props.studentScores) return 0;
    const mcqContents = props.phase.contents.filter((c) =>
        ['eval_mcq', 'eval_cmcq'].includes(c.type),
    );
    if (mcqContents.length === 0) return 0;

    let currentScore = 0;
    Object.entries(props.studentScores).forEach(([key, val]) => {
        if (mcqContents.some((c) => c.id === Number(key))) {
            currentScore += Number(val);
        }
    });
    return Math.round(currentScore / mcqContents.length);
});

const hasMCQ = computed(() => {
    return props.phase.contents.some((c) =>
        ['eval_mcq', 'eval_cmcq'].includes(c.type),
    );
});

const hasEvaluation = computed(() => {
    return props.phase.contents.some((c) => c.type.startsWith('eval_'));
});

// FUNGSI RENDER MARKDOWN + KATEX
const renderMarkdown = (text: string) => {
    if (!text) return '';
    const mathBlocks: string[] = [];
    let processedText = text.replace(/\$\$(.+?)\$\$/gs, (match, math) => {
        try {
            const rendered = katex.renderToString(math, { displayMode: true });
            mathBlocks.push(
                `<div class="my-3 overflow-x-auto">${rendered}</div>`,
            );
            return `%%MATH_BLOCK_TOKEN_${mathBlocks.length - 1}%%`;
        } catch (e) {
            return match;
        }
    });
    processedText = processedText.replace(/\$(.+?)\$/g, (match, math) => {
        try {
            const rendered = katex.renderToString(math, { displayMode: false });
            mathBlocks.push(rendered);
            return `%%MATH_BLOCK_TOKEN_${mathBlocks.length - 1}%%`;
        } catch (e) {
            return match;
        }
    });
    let finalHtml = marked.parse(processedText, { breaks: true }) as string;
    mathBlocks.forEach((renderedMath, index) => {
        finalHtml = finalHtml
            .split(`%%MATH_BLOCK_TOKEN_${index}%%`)
            .join(renderedMath);
    });
    return finalHtml;
};

// FUNGSI AUTO-POLLING AI
const startPollingAI = (contentId: number) => {
    isWaitingForAI.value[contentId] = true;
    pollAttempts[contentId] = 0;
    if (pollIntervals[contentId]) clearInterval(pollIntervals[contentId]);
    pollIntervals[contentId] = setInterval(() => {
        pollAttempts[contentId]++;
        router.reload({
            only: ['aiFeedbacks'],
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                if (props.aiFeedbacks && props.aiFeedbacks[contentId]) {
                    clearInterval(pollIntervals[contentId]);
                    isWaitingForAI.value[contentId] = false;
                    toast.success('✨ Evaluasi AI Selesai!');
                } else if (pollAttempts[contentId] >= 15) {
                    clearInterval(pollIntervals[contentId]);
                    isWaitingForAI.value[contentId] = false;
                    toast.error(
                        'Waktu tunggu AI habis. Silakan klik "Cek Hasil AI" nanti.',
                    );
                }
            },
        });
    }, 3000);
};

onMounted(() => {
    if (props.studentAnswers) {
        for (const [key, value] of Object.entries(props.studentAnswers)) {
            try {
                answers.value[Number(key)] = JSON.parse(value);
            } catch (e) {
                answers.value[Number(key)] = value;
            }
        }
    }
    props.phase.contents.forEach((content) => {
        if (content.type === 'eval_cmcq' && !answers.value[content.id]) {
            answers.value[content.id] = [];
        }
    });
});

// FUNGSI MENANGKAP FILE UPLOAD
const handleFileUpload = (event: Event, contentId: number) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        // Validasi ukuran di Frontend (10MB)
        if (file.size > 10 * 1024 * 1024) {
            toast.error('Ukuran file terlalu besar! Maksimal 10MB.');
            target.value = '';
            return;
        }
        answerFiles.value[contentId] = file;
    }
};

// AUTO SAVE JAWABAN (DIPERBARUI UNTUK MENERIMA FILE)
const saveAnswer = (content: any, showToast: boolean = true) => {
    if (props.isPhaseSubmitted) return;

    const contentId = content.id;
    let answerData = answers.value[contentId] || '';
    let fileData = answerFiles.value[contentId]; // Ambil file jika ada

    // Jangan lakukan apa-apa jika teks kosong DAN file tidak ada
    if ((Array.isArray(answerData) && answerData.length === 0) && !fileData) return;
    if (!Array.isArray(answerData) && (typeof answerData === 'string' && answerData.trim() === '') && !fileData) return;

    const answerText = Array.isArray(answerData)
        ? JSON.stringify(answerData)
        : answerData;
    
    isSubmitting.value[contentId] = true;
    const isAutoCorrect = ['eval_mcq', 'eval_cmcq'].includes(content.type);

    router.post(
        route('siswa.answers.store', props.phase.id),
        {
            content_id: contentId,
            answer_text: answerText,
            answer_file: fileData || null, // Kirim file ke backend
        },
        {
            preserveScroll: true,
            preserveState: true,
            forceFormData: true, // WAJIB DI-TRUE-KAN AGAR FILE BISA DIKIRIM (Multipart Form)
            onSuccess: () => {
                // Hapus file dari state sementara setelah berhasil dikirim
                if (fileData) answerFiles.value[contentId] = null;

                if (isAutoCorrect) {
                    router.reload({
                        only: ['studentScores', 'studentIsCorrect'],
                        preserveScroll: true,
                        preserveState: true,
                    });
                } else {
                    if (showToast)
                        toast.success('🚀 Jawaban & Lampiran berhasil dikirim!');
                    
                    // Reload data file dari database agar muncul tombol Lihat Lampiran
                    router.reload({ only: ['studentFiles'], preserveScroll: true });

                    if (props.aiFeedbacks) props.aiFeedbacks[contentId] = '';
                    
                    // Hanya jalankan polling AI jika siswa mengetik teks (AI tidak bisa menilai gambar langsung dari sini)
                    if (props.phase.is_ai_enabled && answerText) {
                        startPollingAI(contentId);
                    }
                }
            },
            onError: () => {
                if (showToast)
                    toast.error('⚠️ Gagal Mengirim, periksa ukuran file atau koneksi internet.');
            },
            onFinish: () => {
                isSubmitting.value[contentId] = false;
            },
        },
    );
};

// TOMBOL SUBMIT FINAL
const submitFinal = () => {
    if (hasEvaluation.value) {
        if (
            !confirm(
                'Apakah Anda yakin ingin mengumpulkan? Jawaban dan Lampiran tidak akan bisa diubah lagi setelah ini.',
            )
        ) {
            return;
        }
    }

    router.post(
        route('siswa.answers.store', props.phase.id),
        {
            is_final_submit: true,
        },
        {
            preserveScroll: false, 
            onSuccess: () => {
                toast.success(
                    '✨ Materi & Evaluasi berhasil dikumpulkan dan dikunci!',
                );
            },
        },
    );
};
</script>

<template>
    <Head :title="`Materi: ${phase.name}`" />

    <div class="relative min-h-screen bg-[#F4F7F9] px-4 py-6 md:px-8">
        <div class="mx-auto mb-6 flex max-w-4xl flex-col justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center">
            <div>
                <div class="mb-1 flex items-center gap-2 text-[11px] font-bold text-slate-500">
                    <Link :href="route('siswa.classes.show', classroom.id)" class="transition-colors hover:text-indigo-600">
                        <span class="text-indigo-600">{{ classroom.class_name }}</span>
                    </Link>
                    <i class="pi pi-chevron-right text-[8px]"></i>
                    <span>{{ topic.title }}</span>
                </div>
                <h1 class="text-xl font-black text-slate-900">{{ phase.name }}</h1>
            </div>

            <div class="flex items-center gap-3">
                <div v-if="isPhaseSubmitted" class="flex items-center gap-2 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-1.5">
                    <i class="pi pi-lock text-sm text-emerald-500"></i>
                    <span class="text-[11px] font-bold tracking-widest text-emerald-600 uppercase">Terkunci & Selesai</span>
                </div>
                <div v-if="phase.is_ai_enabled" class="flex items-center gap-2 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-1.5">
                    <i class="pi pi-sparkles text-sm text-indigo-500"></i>
                    <span class="text-[11px] font-bold tracking-widest text-indigo-600 uppercase">AI Assistant Aktif</span>
                </div>
            </div>
        </div>

        <div v-if="isPhaseSubmitted && hasMCQ" class="mx-auto mb-6 grid max-w-4xl animate-in grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm duration-500 fade-in slide-in-from-top-4 md:grid-cols-3">
            <div class="flex flex-col justify-center rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-center">
                <span class="text-5xl font-black text-indigo-600">{{ totalScore }}</span>
                <span class="mt-2 text-[11px] font-bold tracking-widest text-indigo-800 uppercase">Total Skor Ujian</span>
            </div>
            <div class="flex flex-col justify-center rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-center">
                <span class="text-3xl font-black text-emerald-600">{{ correctCount }}</span>
                <span class="mt-1 text-[11px] font-bold tracking-widest text-emerald-800 uppercase">Jawaban Benar</span>
            </div>
            <div class="flex flex-col justify-center rounded-xl border border-rose-100 bg-rose-50 p-4 text-center">
                <span class="text-3xl font-black text-rose-600">{{ incorrectCount }}</span>
                <span class="mt-1 text-[11px] font-bold tracking-widest text-rose-800 uppercase">Jawaban Salah</span>
            </div>
        </div>

        <div class="mx-auto mb-12 max-w-4xl space-y-6">
            <div v-for="content in phase.contents" :key="content.id">
                
                <div v-if="content.type === 'text'" class="ql-snow rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="ql-editor prose max-w-none !p-0 text-[15px] leading-relaxed text-slate-700 prose-slate" v-html="content.content_data.body"></div>
                </div>

                <div v-if="content.type === 'image' && content.content_data.url" class="flex justify-center rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <img :src="content.content_data.url" class="max-h-[500px] rounded-xl object-contain" alt="Materi Visual" />
                </div>

                <div v-if="content.type === 'h5p' && content.content_data.path" class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                    <div class="aspect-video w-full overflow-hidden rounded-xl bg-slate-900">
                        <iframe :src="content.content_data.path" class="h-full w-full border-0" allowfullscreen="allowfullscreen" allow="geolocation *; microphone *; camera *; midi *; encrypted-media *;" title="Interactive Video POE"></iframe>
                    </div>
                </div>

                <div v-if="content.type === 'eval_mcq'" class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" :class="{ 'opacity-80': isPhaseSubmitted }">
                    <div class="ql-snow mb-4 flex items-start gap-2">
                        <i class="pi pi-question-circle mt-1 text-indigo-500"></i>
                        <div class="ql-editor prose prose-sm max-w-none !p-0 font-bold text-slate-800" v-html="content.content_data.question"></div>
                    </div>
                    <div class="space-y-2 pl-6">
                        <label v-for="(option, idx) in content.content_data.options" :key="idx" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition-all"
                            :class="{
                                'border-indigo-400 bg-indigo-50/30 ring-1 ring-indigo-400': answers[content.id] === option,
                                'cursor-pointer hover:bg-slate-50': !isPhaseSubmitted,
                                'cursor-not-allowed': isPhaseSubmitted,
                            }">
                            <input type="radio" :name="'mcq_' + content.id" :value="option" v-model="answers[content.id]" @change="saveAnswer(content, false)" :disabled="isPhaseSubmitted" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50" />
                            <span class="text-[14px] text-slate-700" v-html="option"></span>
                        </label>
                    </div>

                    <div class="mt-3 flex min-h-[20px] justify-end">
                        <span v-if="isSubmitting[content.id] && !isPhaseSubmitted" class="text-[11px] font-bold text-indigo-500"><i class="pi pi-spinner pi-spin mr-1"></i>Menyimpan otomatis...</span>
                    </div>

                    <div v-if="isPhaseSubmitted && studentIsCorrect" class="mt-2 animate-in pl-6 slide-in-from-top-2">
                        <div v-if="studentIsCorrect[content.id]" class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-700">
                            <i class="pi pi-check-circle text-lg"></i>
                            <div>
                                <p class="text-sm font-bold">Jawaban Anda Benar!</p>
                                <p class="text-[11px] opacity-80">Skor Diperoleh: {{ studentScores[content.id] }} Poin</p>
                            </div>
                        </div>
                        <div v-else class="flex flex-col gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-700">
                            <div class="flex items-center gap-2"><i class="pi pi-times-circle text-lg"></i><p class="text-sm font-bold">Jawaban Anda Salah.</p></div>
                            <p class="rounded-lg border border-rose-100 bg-white p-2 text-[12px]">Kunci Jawaban: <strong class="text-slate-800" v-html="content.content_data.correct_answer"></strong></p>
                        </div>
                    </div>
                </div>

                <div v-if="content.type === 'eval_cmcq'" class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" :class="{ 'opacity-80': isPhaseSubmitted }">
                     <div class="ql-snow mb-4 flex flex-wrap items-start gap-2">
                        <i class="pi pi-list mt-1 text-indigo-500"></i>
                        <div class="ql-editor prose prose-sm max-w-none !p-0 font-bold text-slate-800" v-html="content.content_data.question"></div>
                        <span class="mt-1 ml-6 rounded bg-amber-100 px-2 py-0.5 text-[9px] font-black tracking-wider text-amber-700 uppercase shadow-sm">Pilih lebih dari satu</span>
                    </div>
                    <div class="space-y-2 pl-6">
                        <label v-for="(option, idx) in content.content_data.options" :key="idx" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition-all"
                            :class="{
                                'border-indigo-400 bg-indigo-50/30 ring-1 ring-indigo-400': answers[content.id]?.includes(option),
                                'cursor-pointer hover:bg-slate-50': !isPhaseSubmitted,
                                'cursor-not-allowed': isPhaseSubmitted,
                            }">
                            <input type="checkbox" :value="option" v-model="answers[content.id]" @change="saveAnswer(content, false)" :disabled="isPhaseSubmitted" class="h-4 w-4 rounded text-indigo-600 focus:ring-indigo-500 disabled:opacity-50" />
                            <span class="text-[14px] text-slate-700" v-html="option"></span>
                        </label>
                    </div>

                    <div class="mt-3 flex min-h-[20px] justify-end">
                        <span v-if="isSubmitting[content.id] && !isPhaseSubmitted" class="text-[11px] font-bold text-indigo-500"><i class="pi pi-spinner pi-spin mr-1"></i>Menyimpan otomatis...</span>
                    </div>

                    <div v-if="isPhaseSubmitted && studentIsCorrect" class="mt-2 animate-in pl-6 slide-in-from-top-2">
                        <div v-if="studentIsCorrect[content.id]" class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-700">
                            <i class="pi pi-check-circle text-lg"></i>
                            <div>
                                <p class="text-sm font-bold">Jawaban Anda Benar!</p>
                                <p class="text-[11px] opacity-80">Skor Diperoleh: {{ studentScores[content.id] }} Poin</p>
                            </div>
                        </div>
                        <div v-else class="flex flex-col gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-700">
                            <div class="flex items-center gap-2"><i class="pi pi-times-circle text-lg"></i><p class="text-sm font-bold">Kurang Tepat.</p></div>
                            <div class="rounded-lg border border-rose-100 bg-white p-2 text-[12px]">
                                Kunci Jawaban Benar:
                                <ul class="mt-1 ml-4 list-disc font-semibold text-slate-800">
                                    <li v-for="key in content.content_data.correct_answers" :key="key" v-html="key"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="content.type === 'eval_essay' || content.type === 'input_text' || content.type === 'eval_short' || content.type === 'eval_file'" 
                     class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" :class="{ 'opacity-80': isPhaseSubmitted }">
                    
                    <label class="ql-snow mb-3 block flex items-start gap-2 text-[14px] font-extrabold text-slate-800">
                        <i class="pi mt-1 text-indigo-500" :class="content.type === 'eval_file' ? 'pi-camera' : 'pi-align-left'"></i>
                        <div class="ql-editor prose prose-sm max-w-none !p-0" v-html="content.content_data.question || content.content_data.label || 'Tuliskan jawaban Anda di bawah ini:'"></div>
                    </label>
                    
                    <textarea v-if="content.type !== 'eval_short'" v-model="answers[content.id]"
                        placeholder="Ketik uraian jawaban atau keterangan file di sini..."
                        class="min-h-[120px] w-full resize-y rounded-xl border border-slate-200 bg-white p-4 text-[14px] text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-75"
                        :disabled="isSubmitting[content.id] || isWaitingForAI[content.id] || isPhaseSubmitted"
                    ></textarea>

                    <input v-else type="text" v-model="answers[content.id]"
                        placeholder="Ketik jawaban singkat Anda..."
                        class="w-full rounded-xl border border-slate-200 bg-white p-3.5 text-[14px] text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-75"
                        :disabled="isSubmitting[content.id] || isWaitingForAI[content.id] || isPhaseSubmitted"
                    />

                    <div class="mt-4 flex flex-col gap-3 border-t border-dashed border-slate-200 pt-4">
                        
                        <div v-if="studentFiles && studentFiles[content.id]" class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50/50 p-3 w-fit">
                            <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg"><i class="pi pi-file"></i></div>
                            <div class="flex-1 pr-4">
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Lampiran Tersimpan</p>
                                <a :href="studentFiles[content.id]" target="_blank" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                                    Lihat / Download File
                                </a>
                            </div>
                            <i class="pi pi-check-circle text-emerald-500 text-lg"></i>
                        </div>

                        <div v-if="!isPhaseSubmitted" class="flex items-center gap-3">
                            <label class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:border-indigo-400 rounded-xl transition-all shadow-sm text-sm text-slate-600 w-fit">
                                <i class="pi pi-paperclip text-indigo-500"></i>
                                <span v-if="answerFiles[content.id]" class="font-semibold text-indigo-600 line-clamp-1 max-w-[200px]">
                                    {{ answerFiles[content.id].name }}
                                </span>
                                <span v-else>Pilih File Tambahan (Opsional)</span>
                                <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf" @change="handleFileUpload($event, content.id)" />
                            </label>
                            
                            <button v-if="answerFiles[content.id]" @click="answerFiles[content.id] = null" type="button" class="text-rose-400 hover:text-rose-600 transition-colors" title="Batal Pilih File">
                                <i class="pi pi-times-circle text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div v-if="!isPhaseSubmitted" class="mt-4 flex min-h-[32px] items-center justify-end gap-3">
                        <Button
                            v-if="!isWaitingForAI[content.id]"
                            @click="saveAnswer(content, true)"
                            size="sm"
                            class="h-10 rounded-xl bg-indigo-600 px-6 text-xs font-bold text-white shadow-sm transition-all hover:scale-105 hover:bg-indigo-700 active:scale-95"
                            :disabled="isSubmitting[content.id]"
                        >
                            <i class="pi mr-1.5" :class="isSubmitting[content.id] ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                            Simpan Jawaban & Lampiran
                        </Button>
                    </div>

                    <div class="mt-4" v-if="phase.is_ai_enabled && studentAnswers[content.id]">
                        <div v-if="isWaitingForAI[content.id]" class="relative flex animate-in items-center gap-4 overflow-hidden rounded-2xl border border-indigo-100 bg-white px-6 py-5 shadow-sm duration-500 fade-in slide-in-from-bottom-2">
                            <div class="absolute inset-0 animate-pulse bg-gradient-to-r from-indigo-50 to-purple-50 opacity-50"></div>
                            <i class="pi pi-sparkles animate-spin text-2xl text-indigo-500" style="animation-duration: 3s"></i>
                            <div class="relative z-10 flex flex-col">
                                <span class="text-[14px] font-bold text-indigo-800">Guru AI sedang menganalisis jawabanmu...</span>
                            </div>
                        </div>
                        <div v-else-if="aiFeedbacks && aiFeedbacks[content.id]" class="relative animate-in rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-purple-50 p-6 shadow-sm duration-300 zoom-in-95">
                            <div class="absolute -top-3 left-6 flex items-center gap-1.5 rounded-full border border-indigo-200 bg-white px-3 py-1 shadow-sm">
                                <i class="pi pi-sparkles text-[10px] text-indigo-500"></i>
                                <span class="text-[10px] font-black tracking-widest text-indigo-600 uppercase">Feedback Guru AI</span>
                            </div>
                            <div class="prose prose-sm mt-3 max-w-none leading-relaxed prose-slate" v-html="renderMarkdown(aiFeedbacks[content.id])"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-8 flex justify-end border-t border-slate-200 pt-8 pb-12">
                <Button v-if="!isPhaseSubmitted" @click="submitFinal" class="h-12 rounded-xl bg-indigo-600 px-10 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:bg-indigo-700 active:scale-95">
                    Selesai
                </Button>
                <Link v-else :href="route('siswa.classes.show', classroom.id)">
                    <Button class="h-12 rounded-xl bg-slate-800 px-10 text-sm font-bold text-white shadow-md transition-all hover:scale-105 hover:bg-slate-900 active:scale-95">
                        Kembali ke Menu Kelas
                        <i class="pi pi-arrow-right ml-2 text-lg"></i>
                    </Button>
                </Link>
            </div>
        </div>
    </div>
    <FloatingChatbot />
</template>

<style scoped>
:deep(.katex-display) {
    margin: 0.5em 0 !important;
    overflow-x: auto;
    overflow-y: hidden;
}
:deep(.ql-editor) {
    min-height: auto;
}
:deep(.ql-editor > *:first-child) {
    margin-top: 0 !important;
}
:deep(.ql-editor > *:last-child) {
    margin-bottom: 0 !important;
}
</style>