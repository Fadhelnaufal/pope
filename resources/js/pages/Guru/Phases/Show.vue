<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { toast } from 'vue-sonner';
// PASTIKAN FILE INI SUDAH ADA DI FOLDER COMPONENTS
import RichTextEditor from '@/components/RichTextEditor.vue';

const props = defineProps<{
    classroom: {
        id: number;
        class_name: string;
    };
    topic: {
        id: number;
        title: string;
    };
    phase: {
        id: number;
        name: string;
        is_ai_enabled: boolean;
        ai_prompt_setting: string | null;
        contents: Array<{
            id: number;
            type: string;
            content_data: any;
            order: number;
        }>;
    };
}>();

// ==========================================
// 1. LOGIKA UPDATE FASE (AI SETTINGS)
// ==========================================
const isTogglingAI = ref(false);
const localAisEnabled = ref(!!props.phase.is_ai_enabled);
const aiPromptSetting = ref(props.phase.ai_prompt_setting ?? '');

watch(
    () => props.phase.is_ai_enabled,
    (newVal) => {
        localAisEnabled.value = !!newVal;
    },
);

watch(
    () => props.phase.ai_prompt_setting,
    (newVal) => {
        aiPromptSetting.value = newVal ?? '';
    },
);

const toggleAI = () => {
    if (isTogglingAI.value) return;
    isTogglingAI.value = true;
    localAisEnabled.value = !localAisEnabled.value;

    router.put(route('guru.phases.update', { phase: props.phase.id }), {
        name: props.phase.name,
        is_ai_enabled: localAisEnabled.value,
        ai_prompt_setting: aiPromptSetting.value,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.success(localAisEnabled.value ? 'AI Assistant Aktif' : 'AI Assistant Nonaktif', { icon: '🤖' }),
        onError: () => {
            localAisEnabled.value = !localAisEnabled.value;
            toast.error('Gagal mengubah status AI');
        },
        onFinish: () => (isTogglingAI.value = false),
    });
};

const saveAIPrompt = () => {
    router.put(route('guru.phases.update', { phase: props.phase.id }), {
        name: props.phase.name,
        is_ai_enabled: localAisEnabled.value,
        ai_prompt_setting: aiPromptSetting.value,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Instruksi AI Disimpan', { icon: '✨' }),
    });
};

// ==========================================
// 2. LOGIKA KONTEN DINAMIS (BUILDER)
// ==========================================
const localContents = ref<any[]>([]);

watch(
    () => props.phase.contents,
    (newContents) => {
        let cloned = JSON.parse(JSON.stringify(newContents || []));
        cloned.forEach((c: any) => {
            if (!c.content_data || Array.isArray(c.content_data)) c.content_data = {};
            
            // Inisialisasi struktur JSON sesuai Tipe Komponen
            if (c.type === 'text' && typeof c.content_data.body === 'undefined') c.content_data.body = '';
            if (c.type === 'image' && typeof c.content_data.url === 'undefined') c.content_data.url = '';
            if (c.type === 'h5p' && typeof c.content_data.path === 'undefined') c.content_data.path = '';
            
            // Migrasi tipe lama 'input_text' menjadi 'eval_essay'
            if (c.type === 'input_text') c.type = 'eval_essay';
            
            if (['eval_essay', 'eval_short'].includes(c.type) && typeof c.content_data.question === 'undefined') {
                c.content_data.question = c.content_data.label || ''; // Support legacy label
            }
            if (['eval_mcq', 'eval_cmcq'].includes(c.type)) {
                if (typeof c.content_data.question === 'undefined') c.content_data.question = '';
                if (!Array.isArray(c.content_data.options)) c.content_data.options = ['Opsi 1', 'Opsi 2'];
            }
        });
        localContents.value = cloned;
    },
    { immediate: true, deep: true },
);

const addContent = (type: string) => {
    let initialData = {};
    if (type === 'text') initialData = { body: '' };
    if (['eval_mcq', 'eval_cmcq'].includes(type)) initialData = { question: '', options: ['Pilihan A', 'Pilihan B'] };
    if (['eval_essay', 'eval_short'].includes(type)) initialData = { question: '' };
    
    router.post(route('guru.contents.store', { phase: props.phase.id }), { 
        type: type,
        content_data: initialData
    }, { 
        preserveScroll: true,
        onSuccess: () => toast.success(`Blok ditambahkan.`)
    });
};

const saveContent = (content: any) => {
    router.put(route('guru.contents.update', { content: content.id }), {
        content_data: content.content_data,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Tersimpan', { icon: '💾' }),
    });
};

const removeContent = (id: number) => {
    if (confirm('Yakin ingin menghapus blok materi ini permanen?')) {
        router.delete(route('guru.contents.destroy', { content: id }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Blok dihapus.'),
        });
    }
};

// HELPER UNTUK OPSI PILIHAN GANDA
const addOption = (content: any) => {
    content.content_data.options.push(`Pilihan Baru`);
};
const removeOption = (content: any, index: number) => {
    content.content_data.options.splice(index, 1);
};
</script>

<template>
    <Head :title="`Builder: ${phase.name}`" />

    <div class="min-h-screen bg-[#F8FAFC] px-6 py-8 font-sans lg:px-10">
        <div class="mx-auto max-w-4xl">
            
            <div class="mb-6 flex items-center gap-2 text-[12px] font-bold text-slate-500">
                <Link :href="route('guru.classes.show', classroom.id)" class="transition-colors hover:text-indigo-600">{{ classroom.class_name }}</Link>
                <i class="pi pi-chevron-right text-[8px]"></i>
                <Link :href="route('guru.classes.topics.show', { classroom: classroom.id, topic: topic.id })" class="transition-colors hover:text-indigo-600">{{ topic.title }}</Link>
                <i class="pi pi-chevron-right text-[8px]"></i>
                <span class="text-indigo-600">Builder Fase: {{ phase.name }}</span>
            </div>

            <Card class="mb-8 overflow-hidden rounded-2xl border-none bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-4 bg-slate-900 px-8 py-6 text-white md:flex-row md:items-center">
                    <div>
                        <span class="mb-1 block text-[10px] font-black tracking-widest text-indigo-400 uppercase">Siklus POE</span>
                        <h1 class="text-2xl font-black">{{ phase.name }}</h1>
                    </div>
                    <div class="flex items-center gap-4 rounded-xl border border-slate-700 bg-slate-800 px-5 py-3 shadow-inner">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-bold tracking-wider text-slate-300 uppercase">AI Assistant Feedback</span>
                            <span class="text-[10px] text-slate-400">{{ localAisEnabled ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="ml-2 cursor-pointer" @click.prevent="toggleAI">
                            <Switch :checked="localAisEnabled" :disabled="isTogglingAI" class="pointer-events-none data-[state=checked]:bg-indigo-500" />
                        </div>
                    </div>
                </div>

                <div v-if="localAisEnabled" class="border-b border-indigo-50 bg-indigo-50/50 p-8 animate-in fade-in duration-300">
                    <label class="mb-2 flex items-center gap-2 text-[12px] font-black tracking-widest text-indigo-600 uppercase">
                        <i class="pi pi-sparkles"></i> Prompt Instruksi AI (Opsional)
                    </label>
                    <p class="mb-4 text-[12px] text-slate-500">
                        Atur bagaimana AI harus mengevaluasi jawaban siswa pada fase ini. Contoh: <i>"Jika siswa salah, berikan clue terkait ciri atom karbon, jangan langsung beritahu jawabannya."</i>
                    </p>
                    <textarea
                        v-model="aiPromptSetting"
                        @blur="saveAIPrompt"
                        placeholder="Ketik instruksi evaluator AI di sini..."
                        class="min-h-[100px] w-full resize-y rounded-xl border border-indigo-200 bg-white p-4 text-[14px] text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    ></textarea>
                </div>
            </Card>

            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-extrabold text-slate-900">Konstruksi Lembar Kerja Siswa</h2>
                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold text-slate-400 shadow-sm">
                    <i class="pi pi-cloud-upload mr-1"></i> Auto-Save
                </span>
            </div>

            <div class="mb-8 space-y-6">
                <template v-if="localContents.length > 0">
                    <div v-for="(content, index) in localContents" :key="content.id" class="group relative rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:border-indigo-300 hover:shadow-md animate-in slide-in-from-bottom-2 duration-300">
                        
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4 rounded-t-2xl">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-indigo-100 text-[11px] font-black text-indigo-700">{{ index + 1 }}</span>
                                <span class="text-[12px] font-bold uppercase tracking-wider text-slate-600">
                                    <i class="pi pi-align-left mr-1 text-slate-400" v-if="content.type === 'text'"></i>
                                    <i class="pi pi-video mr-1 text-indigo-400" v-if="content.type === 'h5p'"></i>
                                    <i class="pi pi-image mr-1 text-blue-400" v-if="content.type === 'image'"></i>
                                    <i class="pi pi-check-circle mr-1 text-emerald-500" v-if="content.type === 'eval_mcq'"></i>
                                    <i class="pi pi-list mr-1 text-emerald-500" v-if="content.type === 'eval_cmcq'"></i>
                                    <i class="pi pi-pencil mr-1 text-amber-500" v-if="content.type === 'eval_short' || content.type === 'eval_essay'"></i>
                                    Blok {{ content.type.replace('eval_', '') }}
                                </span>
                            </div>
                            <button @click="removeContent(content.id)" class="text-slate-300 hover:text-rose-500 transition-colors" title="Hapus Blok">
                                <i class="pi pi-trash"></i>
                            </button>
                        </div>

                        <div class="p-6">
                            <div v-if="content.type === 'text'">
                                <RichTextEditor v-model="content.content_data.body" placeholder="Tuliskan narasi penjelasan materi di sini..." />
                                <div class="mt-3 flex justify-end"><Button @click="saveContent(content)" size="sm" class="bg-slate-800 text-white">Simpan Teks</Button></div>
                            </div>

                            <div v-if="content.type === 'image'" class="space-y-4">
                                <Input v-model="content.content_data.url" @blur="saveContent(content)" placeholder="Paste URL Link Gambar di sini (https://...)" class="bg-slate-50" />
                                <div v-if="content.content_data.url" class="flex justify-center bg-slate-50 rounded-xl p-4 border border-slate-100">
                                    <img :src="content.content_data.url" class="max-h-64 rounded-lg object-contain" />
                                </div>
                            </div>

                            <div v-if="content.type === 'h5p'">
                                <Input v-model="content.content_data.path" @blur="saveContent(content)" placeholder="Paste Link Embed H5P/Video Interaktif di sini..." class="mb-3 bg-slate-50" />
                                <div v-if="content.content_data.path" class="w-full aspect-video overflow-hidden rounded-xl border border-slate-200 bg-slate-900">
                                    <iframe :src="content.content_data.path" class="h-full w-full border-0"></iframe>
                                </div>
                            </div>

                            <div v-if="['eval_mcq', 'eval_cmcq'].includes(content.type)" class="space-y-5">
                                <div>
                                    <label class="mb-2 block text-[12px] font-bold text-slate-700">Pertanyaan Soal</label>
                                    <RichTextEditor v-model="content.content_data.question" placeholder="Ketik pertanyaan di sini..." />
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                                    <label class="mb-3 block text-[12px] font-bold text-slate-700">Pilihan Jawaban</label>
                                    <div class="space-y-3 mb-4">
                                        <div v-for="(opt, oIdx) in content.content_data.options" :key="oIdx" class="flex items-center gap-3">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-slate-200 text-[11px] font-black text-slate-500">{{ String.fromCharCode(65 + oIdx) }}</span>
                                            <Input v-model="content.content_data.options[oIdx]" class="flex-1 bg-white border-slate-200" placeholder="Ketik opsi jawaban..." />
                                            <button @click="removeOption(content, oIdx)" class="text-rose-400 hover:text-rose-600"><i class="pi pi-times"></i></button>
                                        </div>
                                    </div>
                                    <Button @click="addOption(content)" type="button" variant="outline" class="h-8 border-dashed border-slate-300 text-[11px] text-slate-600"><i class="pi pi-plus mr-1"></i> Tambah Opsi</Button>
                                </div>
                                <div class="flex justify-end"><Button @click="saveContent(content)" size="sm" class="bg-indigo-600 text-white hover:bg-indigo-700">Simpan Pertanyaan</Button></div>
                            </div>

                            <div v-if="['eval_short', 'eval_essay'].includes(content.type)" class="space-y-4">
                                <label class="block text-[12px] font-bold text-slate-700">Pertanyaan / Instruksi Kerja</label>
                                <RichTextEditor v-model="content.content_data.question" placeholder="Ketik pertanyaan / perintah untuk siswa..." />
                                <div class="flex justify-end"><Button @click="saveContent(content)" size="sm" class="bg-amber-600 text-white hover:bg-amber-700">Simpan Evaluasi</Button></div>
                            </div>

                        </div>
                    </div>
                </template>

                <div v-else class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-300 bg-white/50 py-16 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-400"><i class="pi pi-th-large text-3xl"></i></div>
                    <h3 class="text-[16px] font-bold text-slate-800">Lembar Kerja Kosong</h3>
                    <p class="mt-1 text-[13px] text-slate-500">Mulai susun modul Anda dengan menu di bawah ini.</p>
                </div>
            </div>
  
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-6 text-center shadow-sm">
                <h3 class="mb-4 text-[13px] font-bold text-indigo-900"><i class="pi pi-plus-circle mr-1 text-indigo-500"></i> Tambah Komponen Baru ke Fase Ini</h3>
                <div class="flex flex-wrap justify-center gap-3">
                    <Button @click="addContent('text')" variant="outline" class="border-slate-200 bg-white text-slate-700 hover:bg-slate-50"><i class="pi pi-align-left mr-2 text-slate-400"></i> Teks Materi</Button>
                    <Button @click="addContent('h5p')" variant="outline" class="border-slate-200 bg-white text-slate-700 hover:bg-slate-50"><i class="pi pi-video mr-2 text-indigo-400"></i> Media H5P</Button>
                    
                    <div class="w-px h-8 bg-indigo-200 mx-1 hidden sm:block"></div>
                    
                    <Button @click="addContent('eval_mcq')" variant="outline" class="border-slate-200 bg-white text-slate-700 hover:bg-slate-50"><i class="pi pi-check-circle mr-2 text-emerald-500"></i> Pilihan Ganda</Button>
                    <Button @click="addContent('eval_cmcq')" variant="outline" class="border-slate-200 bg-white text-slate-700 hover:bg-slate-50"><i class="pi pi-list mr-2 text-emerald-500"></i> Pilihan Kompleks</Button>
                    <Button @click="addContent('eval_short')" variant="outline" class="border-slate-200 bg-white text-slate-700 hover:bg-slate-50"><i class="pi pi-minus mr-2 text-amber-500"></i> Jawaban Singkat</Button>
                    <Button @click="addContent('eval_essay')" variant="outline" class="border-slate-200 bg-white text-slate-700 hover:bg-slate-50"><i class="pi pi-align-justify mr-2 text-amber-500"></i> Esai Panjang</Button>
                </div>
            </div>

        </div>
    </div>
</template>