<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import { ref, onMounted } from 'vue';

const props = defineProps<{
    classroom: { id: number; class_name: string; };
    topic: { id: number; title: string; };
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
    // Menangkap data jawaban lama dari backend
    studentAnswers: Record<number, string>;
}>();

// State untuk menyimpan jawaban siswa (menggunakan 'any' agar mendukung Array untuk pilihan ganda kompleks)
const answers = ref<Record<number, any>>({});
const isSubmitting = ref<Record<number, boolean>>({});

// MENGISI JAWABAN LAMA SAAT HALAMAN DIMUAT
onMounted(() => {
    if (props.studentAnswers) {
        // Cek dan parse jawaban dari backend (karena checkbox disimpan sebagai stringified JSON di DB)
        for (const [key, value] of Object.entries(props.studentAnswers)) {
            try {
                // Coba parse jadi array (untuk jawaban eval_cmcq)
                answers.value[Number(key)] = JSON.parse(value);
            } catch {
                // Jika gagal parse, berarti teks biasa (eval_essay / eval_short)
                answers.value[Number(key)] = value;
            }
        }
    }

    // Pastikan konten tipe Checkbox (eval_cmcq) diinisialisasi sebagai Array kosong agar v-model berfungsi
    props.phase.contents.forEach(content => {
        if (content.type === 'eval_cmcq' && !answers.value[content.id]) {
            answers.value[content.id] = [];
        }
    });
});

// FUNGSI AUTO-SAVE (Memicu saat siswa klik di luar area / mengubah pilihan)
const saveAnswer = (contentId: number) => {
    let answerData = answers.value[contentId];
    
    // Jangan lakukan request jika jawaban kosong
    if (Array.isArray(answerData) && answerData.length === 0) return;
    if (!Array.isArray(answerData) && (!answerData || answerData.trim() === '')) return;

    // Jika formatnya Array (Checkbox), kita ubah jadi JSON String sebelum dikirim ke backend
    const answerText = Array.isArray(answerData) ? JSON.stringify(answerData) : answerData;

    isSubmitting.value[contentId] = true;

    router.post(route('siswa.answers.store', props.phase.id), {
        content_id: contentId,
        answer_text: answerText
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Tersimpan!', { 
                description: 'Jawaban Anda telah diamankan otomatis.',
                icon: '💾'
            });
        },
        onError: () => {
            toast.error('Gagal Menyimpan', {
                description: 'Periksa koneksi internet Anda.',
                icon: '⚠️'
            });
        },
        onFinish: () => {
            isSubmitting.value[contentId] = false;
        }
    });
};
</script>

<template>
    <Head :title="`Materi: ${phase.name}`" />

    <div class="min-h-screen bg-[#F4F7F9] px-4 py-6 md:px-8">
        
        <div class="mx-auto max-w-4xl mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500 mb-1">
                    <Link :href="route('siswa.classes.show', classroom.id)" class="hover:text-indigo-600 transition-colors">
                        <span class="text-indigo-600">{{ classroom.class_name }}</span>
                    </Link>
                    <i class="pi pi-chevron-right text-[8px]"></i>
                    <span>{{ topic.title }}</span>
                </div>
                <h1 class="text-xl font-black text-slate-900">{{ phase.name }}</h1>
            </div>
            
            <div v-if="phase.is_ai_enabled" class="flex items-center gap-2 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                <i class="pi pi-sparkles text-indigo-500 text-sm"></i>
                <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-widest">AI Assistant Aktif</span>
            </div>
        </div>

        <div class="mx-auto max-w-4xl space-y-6 mb-12">
            <div v-for="content in phase.contents" :key="content.id">
                
                <div v-if="content.type === 'text'" 
                     class="prose prose-slate max-w-none text-[15px] leading-relaxed text-slate-700 bg-white p-6 rounded-2xl shadow-sm border border-slate-100"
                     v-html="content.content_data.body">
                </div>

                <div v-if="content.type === 'image' && content.content_data.url" class="flex justify-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                    <img :src="content.content_data.url" class="max-h-[500px] rounded-xl object-contain" alt="Materi Visual" />
                </div>

                <div v-if="content.type === 'h5p' && content.content_data.path" class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="w-full aspect-video rounded-xl overflow-hidden bg-slate-900">
                        <iframe 
                            :src="content.content_data.path" 
                            class="w-full h-full border-0"
                            allowfullscreen="allowfullscreen" 
                            allow="geolocation *; microphone *; camera *; midi *; encrypted-media *"
                            title="Interactive Video POE">
                        </iframe>
                    </div>
                </div>

                <div v-if="content.type === 'eval_mcq'" class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100 shadow-sm relative overflow-hidden">
                    <div class="absolute left-0 top-0 h-full w-1.5 bg-indigo-500"></div>
                    <div class="flex items-center gap-2 mb-4">
                        <i class="pi pi-question-circle text-indigo-500"></i>
                        <h4 class="font-bold text-slate-800" v-html="content.content_data.question"></h4>
                    </div>
                    <div class="space-y-2 pl-6">
                        <label v-for="(option, idx) in content.content_data.options" :key="idx" 
                               class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-indigo-200 cursor-pointer transition-all shadow-sm"
                               :class="{'border-indigo-400 ring-1 ring-indigo-400 bg-indigo-50/50': answers[content.id] === option}">
                            <input type="radio" :name="'mcq_'+content.id" :value="option" v-model="answers[content.id]" @change="saveAnswer(content.id)" class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span class="text-[14px] text-slate-700" v-html="option"></span>
                        </label>
                    </div>
                    <div class="mt-3 flex justify-end min-h-[20px]">
                        <span v-if="isSubmitting[content.id]" class="text-[11px] font-bold text-indigo-500"><i class="pi pi-spinner pi-spin mr-1"></i> Menyimpan...</span>
                    </div>
                </div>

                <div v-if="content.type === 'eval_cmcq'" class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100 shadow-sm relative overflow-hidden">
                    <div class="absolute left-0 top-0 h-full w-1.5 bg-indigo-500"></div>
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <i class="pi pi-list text-indigo-500"></i>
                        <h4 class="font-bold text-slate-800" v-html="content.content_data.question"></h4>
                        <span class="text-[9px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded uppercase font-black tracking-wider shadow-sm">Pilih lebih dari satu</span>
                    </div>
                    <div class="space-y-2 pl-6">
                        <label v-for="(option, idx) in content.content_data.options" :key="idx" 
                               class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-indigo-200 cursor-pointer transition-all shadow-sm"
                               :class="{'border-indigo-400 ring-1 ring-indigo-400 bg-indigo-50/50': answers[content.id]?.includes(option)}">
                            <input type="checkbox" :value="option" v-model="answers[content.id]" @change="saveAnswer(content.id)" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span class="text-[14px] text-slate-700" v-html="option"></span>
                        </label>
                    </div>
                    <div class="mt-3 flex justify-end min-h-[20px]">
                        <span v-if="isSubmitting[content.id]" class="text-[11px] font-bold text-indigo-500"><i class="pi pi-spinner pi-spin mr-1"></i> Menyimpan...</span>
                    </div>
                </div>

                <div v-if="content.type === 'eval_short'" class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100 shadow-sm relative overflow-hidden">
                    <div class="absolute left-0 top-0 h-full w-1.5 bg-indigo-500"></div>
                    <label class="block text-[14px] font-extrabold text-slate-800 mb-3 flex items-center gap-2">
                        <i class="pi pi-pencil text-indigo-500"></i> 
                        <span v-html="content.content_data.question"></span>
                    </label>
                    <input type="text" 
                        v-model="answers[content.id]"
                        @blur="saveAnswer(content.id)"
                        placeholder="Ketik jawaban singkat Anda..." 
                        class="w-full bg-white border border-indigo-200 rounded-xl text-[14px] p-3.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-inner transition-all text-slate-700 disabled:opacity-70 disabled:cursor-not-allowed"
                        :disabled="isSubmitting[content.id]"
                    />
                    <div class="mt-2 flex justify-end min-h-[20px]">
                        <span v-if="isSubmitting[content.id]" class="text-[11px] font-bold text-indigo-500"><i class="pi pi-spinner pi-spin mr-1"></i> Menyimpan jawaban...</span>
                        <span v-else class="text-[10px] font-bold text-slate-400"><i class="pi pi-check mr-1"></i> Tersimpan otomatis saat area luar diklik.</span>
                    </div>
                </div>

                <div v-if="content.type === 'eval_essay' || content.type === 'input_text'" class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100 shadow-sm relative overflow-hidden">
                    <div class="absolute left-0 top-0 h-full w-1.5 bg-indigo-500"></div>
                    
                    <label class="block text-[14px] font-extrabold text-slate-800 mb-3 flex items-center gap-2">
                        <i class="pi pi-align-left text-indigo-500"></i> 
                        <span v-html="content.content_data.question || content.content_data.label || 'Tuliskan jawaban atau prediksi Anda di bawah ini:'"></span>
                    </label>
                    
                    <textarea 
                        v-model="answers[content.id]"
                        @blur="saveAnswer(content.id)"
                        placeholder="Ketik uraian jawaban Anda di sini... (Jawaban akan otomatis tersimpan saat Anda selesai mengetik)" 
                        class="w-full bg-white border border-indigo-200 rounded-xl text-[14px] p-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 min-h-[140px] resize-y shadow-inner transition-all text-slate-700 disabled:opacity-70 disabled:cursor-not-allowed"
                        :disabled="isSubmitting[content.id]"
                    ></textarea>
                    
                    <div class="mt-2 flex justify-end min-h-[20px]">
                        <span v-if="isSubmitting[content.id]" class="text-[11px] font-bold text-indigo-500">
                            <i class="pi pi-spinner pi-spin mr-1"></i> Menyimpan jawaban...
                        </span>
                        <span v-else class="text-[10px] font-bold text-slate-400">
                            <i class="pi pi-check mr-1"></i> Jawaban tersimpan otomatis saat area diklik di luar.
                        </span>
                    </div>
                </div>

            </div>

            <div class="flex justify-end pt-4">
                <Link :href="route('siswa.classes.show', classroom.id)">
                    <Button class="h-11 px-8 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold shadow-md">
                        Selesai <i class="pi pi-check-circle ml-2"></i>
                    </Button>
                </Link>
            </div>
            
        </div>
    </div>
</template>