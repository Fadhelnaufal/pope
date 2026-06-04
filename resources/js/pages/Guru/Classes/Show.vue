<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    classroom: {
        id: number;
        class_name: string;
        class_code: string;
        description: string | null;
        created_at: string;
        // Data Topik (Bab)
        topics?: Array<{
            id: number;
            title: string;
            description: string | null;
            is_published?: boolean;
            pivot?: { is_open: boolean }; // Fallback jika menggunakan Pivot Tabel
        }>;
        // Data Siswa (Peserta Kelas)
        students?: Array<{
            id: number;
            name: string;
            email: string;
            pivot?: { created_at: string }; // Tanggal bergabung
        }>;
    };
}>();

const activeTab = ref<'topik' | 'siswa'>('topik');

// ==========================================
// LOGIKA SALIN KODE KELAS
// ==========================================
const copyCode = () => {
    navigator.clipboard.writeText(props.classroom.class_code);
    toast.success('Kode Disalin!', {
        description: `Kode kelas ${props.classroom.class_code} berhasil disalin ke clipboard.`,
        icon: '📋',
    });
};

// ==========================================
// LOGIKA BUAT TOPIK BARU
// ==========================================
const isCreateTopicModalOpen = ref(false);
const topicForm = useForm({ title: '', description: '' });

const openTopicModal = () => {
    isCreateTopicModalOpen.value = true;
};
const closeTopicModal = () => {
    isCreateTopicModalOpen.value = false;
    setTimeout(() => {
        topicForm.reset();
        topicForm.clearErrors();
    }, 200);
};

const submitTopic = () => {
    // Sesuai dengan route resource: guru.classes.topics.store
    topicForm.post(route('guru.classes.topics.store', props.classroom.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeTopicModal();
            toast.success('Topik Berhasil Dibuat', { 
                description: `Topik "${topicForm.title}" siap digunakan.`, 
                icon: '📚' 
            });
        },
        onError: () => {
            toast.error('Gagal Menyimpan', { 
                description: `Mohon periksa kembali form pengisian topik Anda.`, 
                icon: '⚠️' 
            });
        }
    });
};
</script>

<template>
    <Head :title="`Kelas: ${classroom.class_name}`" />

    <div class="min-h-screen bg-[#F8FAFC] px-6 py-8 font-sans lg:px-10">
        <div class="mx-auto max-w-7xl">
            
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <div class="mb-3 flex items-center gap-2 text-[12px] font-bold text-slate-500">
                        <Link :href="route('guru.dashboard')" class="transition-colors hover:text-indigo-600">Dashboard</Link>
                        <i class="pi pi-chevron-right text-[8px]"></i>
                        <span class="transition-colors hover:text-indigo-600">Manajemen Kelas</span>
                        <i class="pi pi-chevron-right text-[8px]"></i>
                        <span class="text-indigo-600">{{ classroom.class_name }}</span>
                    </div>
                    <h1 class="flex items-center gap-3 text-[28px] font-extrabold tracking-tight text-slate-900">
                        {{ classroom.class_name }}
                    </h1>
                    <p class="mt-2 max-w-3xl text-[14px] leading-relaxed font-medium text-slate-600">
                        {{ classroom.description || 'Tidak ada deskripsi khusus untuk kelas ini.' }}
                    </p>
                </div>

                <button @click="copyCode" class="group flex shrink-0 cursor-pointer items-center gap-4 rounded-xl border border-indigo-100 bg-white p-3 shadow-sm transition-all hover:border-indigo-300 hover:shadow-md md:w-auto">
                    <div class="flex flex-col items-start">
                        <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Kode Kelas</span>
                        <span class="font-mono text-[20px] font-extrabold tracking-widest text-indigo-600 group-hover:text-indigo-700">
                            {{ classroom.class_code }}
                        </span>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-500 transition-colors group-hover:bg-indigo-600 group-hover:text-white">
                        <i class="pi pi-copy text-[15px]"></i>
                    </div>
                </button>
            </div>

            <div class="mb-6 flex items-center gap-6 border-b border-slate-200">
                <button @click="activeTab = 'topik'" :class="['relative pb-3 text-[14px] font-bold transition-all', activeTab === 'topik' ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-700']">
                    <i class="pi pi-book mr-1.5 text-[12px]"></i> Topik Pembelajaran
                    <div v-if="activeTab === 'topik'" class="absolute bottom-0 left-0 h-0.5 w-full rounded-t-full bg-indigo-600"></div>
                </button>
                <button @click="activeTab = 'siswa'" :class="['relative pb-3 text-[14px] font-bold transition-all', activeTab === 'siswa' ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-700']">
                    <i class="pi pi-users mr-1.5 text-[12px]"></i> Daftar Siswa
                    <span v-if="classroom.students" class="ml-1 bg-indigo-100 text-indigo-700 text-[10px] px-1.5 py-0.5 rounded-full">{{ classroom.students.length }}</span>
                    <div v-if="activeTab === 'siswa'" class="absolute bottom-0 left-0 h-0.5 w-full rounded-t-full bg-indigo-600"></div>
                </button>
            </div>

            <div v-show="activeTab === 'topik'" class="animate-in duration-300 fade-in">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-[16px] font-bold text-slate-800">Daftar Topik (Bab)</h2>
                    <Button @click="openTopicModal" class="h-9 bg-indigo-600 text-[12px] text-white hover:bg-indigo-700">
                        <i class="pi pi-plus mr-1.5"></i> Buat Topik Baru
                    </Button>
                </div>

                <div v-if="classroom.topics && classroom.topics.length > 0" class="flex flex-col gap-4">
                    <Card v-for="topic in classroom.topics" :key="topic.id" class="group overflow-hidden border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md">
                        <div class="flex flex-col justify-between gap-4 p-5 md:flex-row md:items-center">
                            
                            <div class="flex flex-1 items-start gap-4">
                                <div class="mt-1 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500 transition-colors group-hover:bg-indigo-600 group-hover:text-white md:mt-0">
                                    <i class="pi pi-folder text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-slate-900 transition-colors group-hover:text-indigo-600">
                                        {{ topic.title }}
                                        <span v-if="!(topic.is_published ?? topic.pivot?.is_open)" class="ml-2 inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600 border border-amber-200 align-middle">
                                            DRAFT
                                        </span>
                                    </h3>
                                    <p class="mt-1 line-clamp-2 max-w-2xl text-[13px] text-slate-500">{{ topic.description || 'Tidak ada deskripsi.' }}</p>
                                </div>
                            </div>

                            <div class="flex w-full items-center justify-end gap-4 self-end border-t border-slate-100 pt-3 md:w-auto md:self-auto md:border-none md:pt-0">
                                <Link :href="route('guru.classes.topics.show', { classroom: classroom.id, topic: topic.id })">
                                    <Button class="h-10 bg-indigo-600 px-5 text-[13px] text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md">
                                        Kelola Fase POE
                                        <i class="pi pi-arrow-right ml-2 text-[11px]"></i>
                                    </Button>
                                </Link>
                            </div>

                        </div>
                    </Card>
                </div>

                <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50">
                        <i class="pi pi-book text-3xl text-indigo-300"></i>
                    </div>
                    <h4 class="mb-1 text-[16px] font-bold text-slate-800">Belum Ada Topik</h4>
                    <p class="mb-5 max-w-[350px] text-[13px] font-medium text-slate-500">
                        Topik berfungsi sebagai folder/bab untuk mengelompokkan materi dan tugas siklus POE Anda.
                    </p>
                    <Button @click="openTopicModal" variant="outline" class="h-9 border-indigo-200 text-[12px] text-indigo-600 hover:bg-indigo-50">
                        <i class="pi pi-plus mr-1.5"></i> Buat Topik Pertama
                    </Button>
                </div>
            </div>

            <div v-show="activeTab === 'siswa'" class="animate-in duration-300 fade-in">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-[16px] font-bold text-slate-800">Siswa Terdaftar</h2>
                    <Button @click="copyCode" variant="outline" class="h-9 text-[12px] bg-white border-slate-200">
                        <i class="pi pi-copy mr-1.5"></i> Salin Kode
                    </Button>
                </div>
                
                <div v-if="classroom.students && classroom.students.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Card v-for="siswa in classroom.students" :key="siswa.id" class="flex items-center gap-4 p-4 border-slate-200 shadow-sm bg-white hover:border-indigo-200 transition-colors">
                        <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold uppercase shrink-0 border border-slate-200">
                            {{ siswa.name.substring(0, 2) }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <h4 class="text-[14px] font-bold text-slate-900 truncate">{{ siswa.name }}</h4>
                            <p class="text-[11px] text-slate-500 truncate">{{ siswa.email }}</p>
                        </div>
                    </Card>
                </div>

                <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50">
                        <i class="pi pi-users text-3xl text-slate-300"></i>
                    </div>
                    <h4 class="mb-1 text-[16px] font-bold text-slate-800">Belum Ada Siswa</h4>
                    <p class="max-w-[350px] text-[13px] font-medium text-slate-500">
                        Bagikan kode unik di atas kepada siswa Anda agar mereka dapat bergabung ke kelas ini.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <div v-if="isCreateTopicModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm">
            <div class="w-full max-w-[450px] animate-in overflow-hidden rounded-2xl bg-white shadow-2xl duration-200 zoom-in-95 fade-in">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <h3 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">
                        <i class="pi pi-folder-plus text-indigo-600"></i> Buat Topik Baru
                    </h3>
                    <button @click="closeTopicModal" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-500">
                        <i class="pi pi-times text-sm"></i>
                    </button>
                </div>
                <form @submit.prevent="submitTopic" class="p-6">
                    <div class="space-y-5">
                        <div>
                            <label class="mb-2 block text-[12px] font-bold tracking-wider text-slate-700 uppercase">Judul Topik <span class="text-rose-500">*</span></label>
                            <Input v-model="topicForm.title" type="text" required class="h-11 rounded-lg border-slate-200 text-[14px] shadow-sm focus-visible:ring-indigo-500" />
                            <span v-if="topicForm.errors.title" class="mt-1.5 block text-[11px] font-semibold text-rose-500">{{ topicForm.errors.title }}</span>
                        </div>
                        <div>
                            <label class="mb-2 block text-[12px] font-bold tracking-wider text-slate-700 uppercase">Deskripsi (Opsional)</label>
                            <textarea v-model="topicForm.description" rows="3" class="w-full resize-none rounded-lg border border-slate-200 p-3 text-[14px] shadow-sm transition-all outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="closeTopicModal" class="h-10 border-slate-200 px-5 font-semibold text-slate-600">Batal</Button>
                        <Button type="submit" :disabled="topicForm.processing" class="h-10 bg-indigo-600 px-6 font-semibold text-white shadow-md hover:bg-indigo-700">
                            <i v-if="topicForm.processing" class="pi pi-spinner pi-spin mr-2"></i>
                            <span v-else>Simpan Topik</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>