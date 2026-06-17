<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { toast } from 'vue-sonner';

const props = defineProps<{
    classroom: {
        id: number;
        class_name: string;
    };
    topic: {
        id: number;
        title: string;
        description: string | null;
        is_published: boolean;
        phases: Array<{
            id: number;
            name: string;
            is_ai_enabled: boolean;
            order?: number;
        }>;
    };
}>();

// ==========================================
// 1. LOGIKA PUBLISH / UNPUBLISH (Optimistic)
// ==========================================
const isToggling = ref(false);
const localIsPublished = ref(!!props.topic.is_published);

watch(
    () => props.topic.is_published,
    (newVal) => {
        localIsPublished.value = !!newVal;
    },
);

const togglePublish = () => {
    if (isToggling.value) return;
    isToggling.value = true;

    // Optimistic Update
    localIsPublished.value = !localIsPublished.value;

    router.post(
        route('guru.classes.topics.toggle-publish', {
            classroom: props.classroom.id,
            topic: props.topic.id,
        }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    localIsPublished.value
                        ? '🚀 Materi Dipublikasikan'
                        : '🔒 Materi Disembunyikan (Draft)',
                    {
                        description: 'Status publikasi topik berhasil diperbarui ke siswa.'
                    },
                );
            },
            onError: () => {
                localIsPublished.value = !localIsPublished.value;
                toast.error('Gagal mengubah status publikasi!');
            },
            onFinish: () => {
                isToggling.value = false;
            },
        },
    );
};

// ==========================================
// 2. LOGIKA EDIT TOPIK
// ==========================================
const isEditModalOpen = ref(false);
const editForm = useForm({
    title: props.topic.title,
    description: props.topic.description || '',
});

const openEditModal = () => {
    editForm.title = props.topic.title;
    editForm.description = props.topic.description || '';
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    setTimeout(() => editForm.clearErrors(), 200);
};

const submitEdit = () => {
    editForm.put(
        route('guru.classes.topics.update', {
            classroom: props.classroom.id,
            topic: props.topic.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                closeEditModal();
                toast.success('Topik Diperbarui', {
                    description: 'Informasi topik berhasil disimpan.',
                });
            },
        },
    );
};

// ==========================================
// 3. LOGIKA HAPUS TOPIK
// ==========================================
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);

const openDeleteModal = () => isDeleteModalOpen.value = true;
const closeDeleteModal = () => isDeleteModalOpen.value = false;

const executeDelete = () => {
    isDeleting.value = true;
    router.delete(
        route('guru.classes.topics.destroy', {
            classroom: props.classroom.id,
            topic: props.topic.id,
        }),
        {
            onSuccess: () => {
                toast.success('Topik Dihapus', {
                    description: `Topik "${props.topic.title}" berhasil dihapus permanen.`,
                });
            },
            onError: () => {
                isDeleting.value = false;
                toast.error('Gagal Menghapus', {
                    description: 'Terjadi kesalahan saat menghapus topik.',
                });
            },
        },
    );
};

// ==========================================
// 4. LOGIKA TAMBAH FASE (MODAL)
// ==========================================
const isCreatePhaseModalOpen = ref(false);
const createPhaseForm = useForm({
    name: '',
});

const openCreatePhaseModal = () => isCreatePhaseModalOpen.value = true;
const closeCreatePhaseModal = () => {
    isCreatePhaseModalOpen.value = false;
    setTimeout(() => {
        createPhaseForm.reset();
        createPhaseForm.clearErrors();
    }, 200);
};

const submitCreatePhase = () => {
    createPhaseForm.post(route('guru.phases.store', { classroom: props.classroom.id, topic: props.topic.id }), {
        preserveScroll: true,
        onSuccess: () => {
            closeCreatePhaseModal();
            toast.success('✨ Fase Berhasil Dibuat', { 
                description: 'Fase baru telah ditambahkan ke dalam topik.'
            });
        },
    });
};

// ==========================================
// 5. LOGIKA UPDATE & HAPUS FASE
// ==========================================
const updatePhase = (phase: any) => {
    router.put(
        route('guru.phases.update', { phase: phase.id }),
        {
            name: phase.name,
            is_ai_enabled: !!phase.is_ai_enabled, 
        },
        {
            preserveScroll: true,
            onSuccess: () => toast.success('Nama fase diperbarui', {
                description: `Perubahan tersimpan otomatis.`,
            }),
        },
    );
};

const deletePhase = (id: number) => {
    if (
        confirm(
            'Hapus fase ini? Seluruh isi materi dan pertanyaan di dalamnya akan ikut terhapus permanen.',
        )
    ) {
        router.delete(route('guru.phases.destroy', { phase: id }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Fase Dihapus'),
        });
    }
};
</script>

<template>
    <Head :title="`Kelola Topik: ${topic.title}`" />

    <div class="min-h-screen bg-[#F4F7F9] px-6 py-8 font-sans lg:px-10">
        <div class="mx-auto max-w-5xl">
            <div class="mb-6 flex items-center gap-2 text-[12px] font-bold text-slate-500">
                <Link :href="route('guru.dashboard')" class="transition-colors hover:text-indigo-600">Dashboard</Link>
                <i class="pi pi-chevron-right text-[8px]"></i>
                <Link :href="route('guru.classes.show', classroom.id)" class="transition-colors hover:text-indigo-600">{{ classroom.class_name }}</Link>
                <i class="pi pi-chevron-right text-[8px]"></i>
                <span class="text-indigo-600">Kelola Topik</span>
            </div>

            <Card class="mb-8 overflow-hidden border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                    <div class="flex flex-1 items-start gap-5">
                        <div class="mt-1 flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500 md:mt-0">
                            <i class="pi pi-folder-open text-2xl"></i>
                        </div>
                        <div>
                            <div class="mb-1 flex items-center gap-3">
                                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                                    {{ topic.title }}
                                </h1>
                                <span v-if="!localIsPublished" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-600">
                                    DRAFT
                                </span>
                                <span v-else class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-600">
                                    PUBLISHED
                                </span>
                            </div>
                            <p class="max-w-2xl text-[14px] leading-relaxed text-slate-500">
                                {{ topic.description || 'Tidak ada deskripsi khusus untuk topik ini.' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-2 sm:justify-start">
                            <span class="text-[12px] font-bold text-slate-600">Publikasi Siswa</span>
                            <div class="cursor-pointer" @click.prevent="togglePublish">
                                <Switch :checked="localIsPublished" :disabled="isToggling" class="pointer-events-none data-[state=checked]:bg-emerald-500" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button @click="openEditModal" variant="outline" class="h-10 border-slate-200 text-slate-600 hover:bg-slate-50">
                                <i class="pi pi-pencil mr-2"></i> Edit
                            </Button>
                            <Button @click="openDeleteModal" variant="outline" class="h-10 border-rose-100 text-rose-600 hover:bg-rose-50 hover:text-rose-700">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- PENEMPATAN TOMBOL RUANG DISKUSI DAN TAMBAH FASE -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Daftar Fase Pembelajaran (POE)</h2>
                    <p class="text-[13px] text-slate-500">Buat alur tahapan pembelajaran dan kelola forum diskusi kelas.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <!-- TOMBOL FORUM DISKUSI -->
                    <Link :href="route('guru.classes.topics.discussions.index', { classroom: classroom.id, topic: topic.id })">
                        <Button variant="outline" class="border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100 h-10 rounded-xl text-xs font-bold shadow-sm transition-all hover:scale-105">
                            <i class="pi pi-comments mr-2"></i> Ruang Diskusi
                        </Button>
                    </Link>

                    <!-- TOMBOL TAMBAH FASE -->
                    <Button @click="openCreatePhaseModal" class="bg-indigo-600 text-white shadow-md hover:bg-indigo-700 h-10 rounded-xl text-xs font-bold transition-all hover:scale-105">
                        <i class="pi pi-plus mr-1.5"></i> Tambah Fase Baru
                    </Button>
                </div>
            </div>

            <div class="space-y-4">
                <div v-for="(phase, pIdx) in topic.phases" :key="phase.id" class="relative">
                    <Card class="flex flex-col justify-between gap-4 overflow-hidden rounded-2xl border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-indigo-300 hover:shadow-md md:flex-row md:items-center">
                        <div class="flex flex-1 items-center gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-[16px] font-black text-indigo-600">
                                {{ pIdx + 1 }}
                            </span>
                            <div class="flex-1">
                                <input
                                    v-model="phase.name"
                                    @blur="updatePhase(phase)"
                                    @keyup.enter="$event.target.blur()"
                                    class="w-full border-none bg-transparent p-0 text-[16px] font-extrabold text-slate-900 placeholder:text-slate-400 focus:ring-0 focus:outline-none"
                                    placeholder="Ketik Nama Fase... (contoh: Tahap Observe)"
                                />
                                <div class="mt-1 flex items-center gap-2 text-[11px] font-bold text-slate-500">
                                    <span v-if="phase.is_ai_enabled" class="text-emerald-500"><i class="pi pi-sparkles"></i> AI Assistant Aktif</span>
                                    <span v-else><i class="pi pi-minus-circle"></i> AI Nonaktif</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex w-full items-center justify-end gap-3 self-end border-t border-slate-100 pt-3 md:w-auto md:self-auto md:border-none md:pt-0">
                            <Link :href="route('guru.phases.show', { classroom: classroom.id, topic: topic.id, phase: phase.id })">
                                <Button class="h-10 border border-indigo-100 bg-indigo-50 px-5 text-indigo-600 shadow-sm hover:bg-indigo-100 hover:text-indigo-700">
                                    Kelola Isi Konten
                                    <i class="pi pi-arrow-right ml-2 text-[10px]"></i>
                                </Button>
                            </Link>

                            <button @click="deletePhase(phase.id)" class="flex h-10 w-10 items-center justify-center rounded-lg border border-rose-100 text-rose-500 transition-colors hover:bg-rose-50 hover:text-rose-600" title="Hapus Fase">
                                <i class="pi pi-trash text-sm"></i>
                            </button>
                        </div>
                    </Card>
                </div>
            </div>

            <div v-if="topic.phases.length === 0" class="mt-8 flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-300 bg-white/60 py-24 text-center shadow-sm">
                <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
                    <i class="pi pi-sitemap text-4xl text-slate-400"></i>
                </div>
                <h4 class="mb-2 text-[18px] font-extrabold text-slate-800">
                    Belum Ada Fase Pembelajaran
                </h4>
                <p class="mb-6 max-w-[420px] text-[14px] leading-relaxed font-medium text-slate-500">
                    Mulai bangun alur belajar siswa Anda. Tambahkan materi/fase seperti
                    <strong class="text-indigo-600">Predict</strong>,
                    <strong class="text-indigo-600">Observe</strong>, atau
                    <strong class="text-indigo-600">Explain</strong> secara fleksibel.
                </p>
                <Button @click="openCreatePhaseModal" class="h-11 bg-indigo-600 px-6 text-[13px] text-white shadow-md hover:bg-indigo-700">
                    <i class="pi pi-plus mr-2"></i> Buat Fase Pertama
                </Button>
            </div>
        </div>
    </div>

    <!-- MODAL FASE -->
    <Teleport to="body">
        <div v-if="isCreatePhaseModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
            <div class="w-full max-w-[450px] animate-in overflow-hidden rounded-2xl bg-white shadow-2xl duration-200 zoom-in-95 fade-in">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <h3 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">
                        <i class="pi pi-layer-group text-indigo-600"></i> Buat Fase Pembelajaran
                    </h3>
                    <button @click="closeCreatePhaseModal" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-500">
                        <i class="pi pi-times text-sm"></i>
                    </button>
                </div>
                <form @submit.prevent="submitCreatePhase" class="p-6">
                    <div class="space-y-5">
                        <div>
                            <label class="mb-2 block text-[12px] font-bold tracking-wider text-slate-700 uppercase">Nama Fase (Siklus POE) <span class="text-rose-500">*</span></label>
                            <Input v-model="createPhaseForm.name" type="text" required placeholder="Contoh: Tahap Predict, Mengamati Video..." class="h-11 rounded-lg border-slate-200 text-[14px] shadow-sm focus-visible:ring-indigo-500" />
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="closeCreatePhaseModal" class="h-10 border-slate-200 px-5 font-semibold text-slate-600">Batal</Button>
                        <Button type="submit" :disabled="createPhaseForm.processing" class="h-10 bg-indigo-600 px-6 font-semibold text-white shadow-md hover:bg-indigo-700">
                            <i v-if="createPhaseForm.processing" class="pi pi-spinner pi-spin mr-2"></i>
                            <span v-else>Simpan Fase</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>

    <!-- MODAL EDIT TOPIK -->
    <Teleport to="body">
        <div v-if="isEditModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
            <div class="w-full max-w-[450px] animate-in overflow-hidden rounded-2xl bg-white shadow-2xl duration-200 zoom-in-95 fade-in">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <h3 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">
                        <i class="pi pi-pencil text-indigo-600"></i> Edit Topik
                    </h3>
                    <button @click="closeEditModal" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-500">
                        <i class="pi pi-times text-sm"></i>
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="p-6">
                    <div class="space-y-5">
                        <div>
                            <label class="mb-2 block text-[12px] font-bold tracking-wider text-slate-700 uppercase">Judul Topik <span class="text-rose-500">*</span></label>
                            <Input v-model="editForm.title" type="text" required class="h-11 rounded-lg border-slate-200 text-[14px] shadow-sm focus-visible:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="mb-2 block text-[12px] font-bold tracking-wider text-slate-700 uppercase">Deskripsi</label>
                            <textarea v-model="editForm.description" rows="3" class="w-full resize-none rounded-lg border border-slate-200 p-3 text-[14px] shadow-sm transition-all outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="closeEditModal" class="h-10 border-slate-200 px-5 font-semibold text-slate-600">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing" class="h-10 bg-indigo-600 px-6 font-semibold text-white shadow-md hover:bg-indigo-700">
                            <i v-if="editForm.processing" class="pi pi-spinner pi-spin mr-2"></i>
                            <span v-else>Simpan Perubahan</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>

    <!-- MODAL HAPUS -->
    <Teleport to="body">
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
            <div class="w-full max-w-[400px] animate-in overflow-hidden rounded-2xl bg-white p-6 text-center shadow-2xl duration-200 zoom-in-95 fade-in">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-rose-50">
                    <i class="pi pi-exclamation-triangle text-3xl text-rose-500"></i>
                </div>
                <h3 class="text-xl font-extrabold tracking-tight text-slate-900">
                    Hapus Topik Ini?
                </h3>
                <p class="mt-2 text-[14px] leading-relaxed font-medium text-slate-500">
                    Anda yakin ingin menghapus topik ini secara permanen? Seluruh Fase & Konten di dalamnya akan ikut terhapus dan tidak bisa dikembalikan.
                </p>
                <div class="mt-8 flex flex-col-reverse justify-center gap-3 sm:flex-row">
                    <Button type="button" variant="outline" @click="closeDeleteModal" :disabled="isDeleting" class="h-11 w-full border-slate-200 px-6 font-semibold text-slate-600 sm:w-auto">
                        Batalkan
                    </Button>
                    <Button type="button" @click="executeDelete" :disabled="isDeleting" class="h-11 w-full bg-rose-600 px-6 font-semibold text-white shadow-md hover:bg-rose-700 sm:w-auto">
                        <i v-if="isDeleting" class="pi pi-spinner pi-spin mr-2"></i>
                        <span v-else>Ya, Hapus Topik</span>
                    </Button>
                </div>
            </div>
        </div>
    </Teleport>
</template>