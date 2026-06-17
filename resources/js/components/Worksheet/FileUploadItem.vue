<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { UploadCloud, File, X, CheckCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    phaseId: number;
    contentId: number;
    existingAnswer?: string; 
}>();

const form = useForm({
    content_id: props.contentId,
    answer_text: null,
    answer_file: null as File | null,
});

const isSuccess = ref(false);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.answer_file = target.files[0];
        isSuccess.value = false; 
    }
};

const removeFile = () => {
    form.answer_file = null;
    isSuccess.value = false;
};

const submitFile = () => {
    form.post(route('siswa.worksheet.storeAnswer', { phase: props.phaseId }), {
        forceFormData: true, 
        preserveScroll: true,
        onSuccess: () => {
            form.reset('answer_file');
            isSuccess.value = true;
            
            setTimeout(() => {
                isSuccess.value = false;
            }, 3000);
        }
    });
};
</script>

<template>
    <div class="mt-4 flex flex-col gap-3">
        
        <div v-if="existingAnswer" class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl flex items-start gap-3">
            <CheckCircle class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" />
            <div>
                <p class="text-sm font-medium text-emerald-800">File jawaban sudah tersimpan.</p>
                <a :href="existingAnswer" target="_blank" class="text-xs text-emerald-600 hover:text-emerald-700 underline mt-1 block">
                    Lihat file yang diunggah
                </a>
            </div>
        </div>

        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 bg-slate-50 hover:bg-slate-100 transition-colors relative group">
            
            <div v-if="!form.answer_file" class="flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <UploadCloud class="w-6 h-6" />
                </div>
                <p class="text-sm font-medium text-slate-700">
                    {{ existingAnswer ? 'Pilih file baru untuk mengubah jawaban' : 'Klik untuk unggah foto/PDF jawaban' }}
                </p>
                <p class="text-xs text-slate-500 mt-1">Maksimal ukuran file: 10MB</p>
                <br>
                <p class="text-xs text-slate-500">Format yang diterima: .jpg, .jpeg, .png, .pdf</p>
                
                <input 
                    type="file" 
                    @change="handleFileSelect"
                    accept=".jpg,.jpeg,.png,.pdf" 
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                />
            </div>

            <div v-else class="flex items-center justify-between bg-white p-3 border border-indigo-100 rounded-xl shadow-sm z-10 relative">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-50 p-2 rounded-lg">
                        <File class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-sm font-semibold text-slate-700 truncate max-w-[200px]">{{ form.answer_file.name }}</span>
                        <span class="text-[11px] text-slate-400">{{ (form.answer_file.size / 1024 / 1024).toFixed(2) }} MB</span>
                    </div>
                </div>
                <button type="button" @click="removeFile" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <div v-if="form.progress" class="absolute bottom-0 left-0 h-1 bg-indigo-500 rounded-b-2xl transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
        </div>

        <div class="flex items-center gap-3">
            <button 
                v-if="form.answer_file"
                @click="submitFile" 
                :disabled="form.processing"
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-all active:scale-95 disabled:opacity-50"
            >
                {{ form.processing ? 'Mengunggah...' : 'Simpan File' }}
            </button>
            
            <transition leave-active-class="transition-opacity duration-500" leave-to-class="opacity-0">
                <span v-if="isSuccess" class="text-sm text-emerald-600 font-medium">✅ Berhasil dikirim!</span>
            </transition>
        </div>

    </div>
</template>