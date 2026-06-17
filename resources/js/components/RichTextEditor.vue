<script setup lang="ts">
import { ref, watch } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits(['update:modelValue']);

// STATE LOKAL: Mencegah bug kursor melompat (jumping cursor) saat mengetik
const content = ref(props.modelValue || '');

watch(() => props.modelValue, (newVal) => {
    if (newVal !== content.value) {
        content.value = newVal || '';
    }
});

watch(content, (newVal) => {
    emit('update:modelValue', newVal);
});

// ==========================================
// KONFIGURASI TOOLBAR FULL FEATURE
// ==========================================
const toolbarOptions = [
    // 1. Heading & Font
    [{ 'header': [1, 2, 3, 4, false] }],
    
    // 2. Format Teks Dasar
    ['bold', 'italic', 'underline', 'strike'],
    
    // 3. Warna Teks & Highlight (Latar Belakang)
    [{ 'color': [] }, { 'background': [] }],
    
    // 4. Paragraf & Alignment (Rata Kiri, Tengah, Kanan, Justify)
    [{ 'align': [] }],
    
    // 5. List & Indentasi
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    [{ 'indent': '-1'}, { 'indent': '+1' }],
    
    // 6. Elemen Khusus & Kimia (Krusial untuk EduChem)
    ['blockquote', 'code-block'],
    [{ 'script': 'sub'}, { 'script': 'super' }], // Subscript (H₂O) & Superscript (Na⁺)
    
    // 7. MEDIA (Link, Gambar, Video)
    ['link', 'image', 'video'],
    
    // 8. Hapus Format
    ['clean']
];
</script>

<template>
    <div class="quill-custom-wrapper border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm transition-all focus-within:border-indigo-400 focus-within:ring-1 focus-within:ring-indigo-400">
        <QuillEditor
            v-model:content="content"
            contentType="html"
            theme="snow"
            :toolbar="toolbarOptions"
            :placeholder="placeholder || 'Ketik materi atau pertanyaan di sini...'"
        />
    </div>
</template>

<style scoped>
/* * OVERRIDE CSS QUILL (Menggunakan :deep agar tembus ke komponen anak)
 * Mengubah desain jadul Quill agar terlihat modern dan 
 * menyatu dengan tema Tailwind (SaaS Premium)
 */
:deep(.quill-custom-wrapper .ql-toolbar.ql-snow) {
    border: none;
    border-bottom: 1px solid #e2e8f0; /* slate-200 */
    background-color: #f8fafc; /* slate-50 */
    padding: 12px;
    font-family: inherit;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
}

:deep(.quill-custom-wrapper .ql-container.ql-snow) {
    border: none;
    font-family: inherit;
    font-size: 15px;
    color: #334155; /* slate-700 */
}

:deep(.quill-custom-wrapper .ql-editor) {
    min-height: 150px;
    padding: 20px;
}

:deep(.quill-custom-wrapper .ql-editor p) {
    margin-bottom: 0.75em;
    line-height: 1.6;
}

/* Mengubah warna placeholder bawaan Quill */
:deep(.quill-custom-wrapper .ql-editor.ql-blank::before) {
    color: #94a3b8; /* slate-400 */
    font-style: normal;
}

/* Memperbaiki tampilan ikon SVG pada toolbar agar lebih rapi */
:deep(.quill-custom-wrapper .ql-picker-label) {
    padding-left: 8px;
}
</style>