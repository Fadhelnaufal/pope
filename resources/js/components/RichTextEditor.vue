<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const { modelValue, placeholder } = defineProps<{
    modelValue: string
    placeholder?: string
}>()

defineEmits(['update:modelValue'])

// Konfigurasi Toolbar (Cocok untuk Materi Kimia)
const toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'],
    [{ 'header': [1, 2, 3, false] }],
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    // Fitur krusial untuk EduChem (Subscript untuk H2O, Superscript untuk ion Na+)
    [{ 'script': 'sub'}, { 'script': 'super' }], 
    [{ 'align': [] }],
    ['clean'] // Tombol untuk menghapus format
]
</script>

<template>
    <div class="quill-custom-wrapper border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm transition-all focus-within:border-indigo-400 focus-within:ring-1 focus-within:ring-indigo-400">
        <QuillEditor
            :content="modelValue"
            contentType="html"
            @update:content="$emit('update:modelValue', $event)"
            :placeholder="placeholder || 'Ketik materi atau pertanyaan di sini...'"
            theme="snow"
            :toolbar="toolbarOptions"
        />
    </div>
</template>

<style>
/* 
 * OVERRIDE CSS QUILL 
 * Mengubah desain jadul Quill agar terlihat modern dan 
 * menyatu dengan tema Tailwind (SaaS Premium)
 */
.quill-custom-wrapper .ql-toolbar.ql-snow {
    border: none;
    border-bottom: 1px solid #e2e8f0; /* slate-200 */
    background-color: #f8fafc; /* slate-50 */
    padding: 12px;
    font-family: inherit;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
}

.quill-custom-wrapper .ql-container.ql-snow {
    border: none;
    font-family: inherit;
    font-size: 15px;
    color: #334155; /* slate-700 */
}

.quill-custom-wrapper .ql-editor {
    min-height: 150px;
    padding: 20px;
}

.quill-custom-wrapper .ql-editor p {
    margin-bottom: 0.75em;
    line-height: 1.6;
}

/* Mengubah warna placeholder bawaan Quill */
.quill-custom-wrapper .ql-editor.ql-blank::before {
    color: #94a3b8; /* slate-400 */
    font-style: normal;
}

/* Memperbaiki tampilan ikon SVG pada toolbar agar lebih rapi */
.quill-custom-wrapper .ql-picker-label {
    padding-left: 8px;
}
</style>