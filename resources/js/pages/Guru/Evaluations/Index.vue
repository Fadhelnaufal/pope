<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    classroom: any;
    topic: any;
    phase: any;
    students: Array<{
        id: number;
        name: string;
        email: string;
        is_submitted: boolean;
        submitted_at: string | null;
        score: number;
    }>;
}>();

// ==========================================
// LOGIKA DATATABLE (SEARCH, SORT, PAGINATION)
// ==========================================
const searchQuery = ref('');
const sortKey = ref('name');
const sortOrder = ref(1); // 1 = Ascending, -1 = Descending
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Fungsi untuk mengubah urutan (Sort)
const sortBy = (key: string) => {
    if (sortKey.value === key) {
        sortOrder.value *= -1; // Balik urutan jika kolom yang sama diklik
    } else {
        sortKey.value = key;
        sortOrder.value = 1;
    }
};

// Computed property untuk Search & Sort
const filteredAndSortedStudents = computed(() => {
    let result = [...props.students];

    // 1. Fitur Pencarian (Search)
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(s => 
            s.name.toLowerCase().includes(q) || 
            s.email.toLowerCase().includes(q)
        );
    }

    // 2. Fitur Pengurutan (Sort)
    result.sort((a: any, b: any) => {
        let valA = a[sortKey.value];
        let valB = b[sortKey.value];

        // Normalisasi string untuk sorting
        if (typeof valA === 'string') valA = valA.toLowerCase();
        if (typeof valB === 'string') valB = valB.toLowerCase();
        
        // Handle null values (seperti submitted_at yang belum kumpul)
        if (valA === null) return 1; 
        if (valB === null) return -1;

        if (valA < valB) return -1 * sortOrder.value;
        if (valA > valB) return 1 * sortOrder.value;
        return 0;
    });

    return result;
});

// Hitung total halaman
const totalPages = computed(() => {
    return Math.ceil(filteredAndSortedStudents.value.length / itemsPerPage.value) || 1;
});

// Potong array untuk Paginasi
const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredAndSortedStudents.value.slice(start, end);
});

// Kembalikan ke halaman 1 jika user sedang mengetik di kolom pencarian
watch(searchQuery, () => {
    currentPage.value = 1;
});

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};
</script>

<template>
    <Head :title="`Evaluasi: ${phase.name}`" />

    <div class="min-h-screen bg-[#F8FAFC] px-6 py-8 font-sans lg:px-10 pb-20">
        <div class="mx-auto max-w-5xl">
            
            <div class="mb-6 flex items-center gap-2 text-[12px] font-bold text-slate-500">
                <Link :href="route('guru.classes.show', classroom.id)" class="hover:text-indigo-600 transition-colors">{{ classroom.class_name }}</Link>
                <i class="pi pi-chevron-right text-[8px]"></i>
                <Link :href="route('guru.classes.topics.show', { classroom: classroom.id, topic: topic.id })" class="hover:text-indigo-600 transition-colors">{{ topic.title }}</Link>
                <i class="pi pi-chevron-right text-[8px]"></i>
                <span class="text-indigo-600">Evaluasi: {{ phase.name }}</span>
            </div>

            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Evaluasi Jawaban Siswa</h1>
                    <p class="text-[13px] text-slate-500 mt-1">Pantau progres dan berikan nilai pada tugas esai siswa di fase ini.</p>
                </div>
                <Link :href="route('guru.phases.show', { classroom: classroom.id, topic: topic.id, phase: phase.id })">
                    <Button variant="outline" class="border-slate-300 text-slate-600 h-10 shadow-sm transition-all hover:bg-slate-50">
                        <i class="pi pi-pencil mr-2"></i> Edit Materi Pembelajaran
                    </Button>
                </Link>
            </div>

            <Card class="overflow-hidden rounded-2xl border-slate-200 bg-white shadow-sm">
                
                <div class="p-5 border-b border-slate-100 bg-white flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="relative w-full sm:w-72">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" v-model="searchQuery" placeholder="Cari nama atau email siswa..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-500 font-medium w-full sm:w-auto justify-end">
                        Tampilkan:
                        <select v-model="itemsPerPage" class="border-slate-200 bg-slate-50 rounded-lg text-sm py-1.5 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option :value="5">5</option>
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500 select-none">
                            <tr>
                                <th @click="sortBy('name')" class="px-6 py-4 cursor-pointer hover:text-indigo-600 transition-colors group">
                                    Nama Siswa
                                    <i class="pi text-[10px] ml-1 transition-opacity" :class="sortKey === 'name' ? (sortOrder === 1 ? 'pi-sort-amount-up text-indigo-500' : 'pi-sort-amount-down text-indigo-500') : 'pi-sort opacity-0 group-hover:opacity-50'"></i>
                                </th>
                                <th @click="sortBy('is_submitted')" class="px-6 py-4 text-center cursor-pointer hover:text-indigo-600 transition-colors group">
                                    Status Pengerjaan
                                    <i class="pi text-[10px] ml-1 transition-opacity" :class="sortKey === 'is_submitted' ? (sortOrder === 1 ? 'pi-sort-amount-up text-indigo-500' : 'pi-sort-amount-down text-indigo-500') : 'pi-sort opacity-0 group-hover:opacity-50'"></i>
                                </th>
                                <th @click="sortBy('submitted_at')" class="px-6 py-4 text-center cursor-pointer hover:text-indigo-600 transition-colors group">
                                    Waktu Kumpul
                                    <i class="pi text-[10px] ml-1 transition-opacity" :class="sortKey === 'submitted_at' ? (sortOrder === 1 ? 'pi-sort-amount-up text-indigo-500' : 'pi-sort-amount-down text-indigo-500') : 'pi-sort opacity-0 group-hover:opacity-50'"></i>
                                </th>
                                <th @click="sortBy('score')" class="px-6 py-4 text-center cursor-pointer hover:text-indigo-600 transition-colors group">
                                    Total Nilai
                                    <i class="pi text-[10px] ml-1 transition-opacity" :class="sortKey === 'score' ? (sortOrder === 1 ? 'pi-sort-amount-up text-indigo-500' : 'pi-sort-amount-down text-indigo-500') : 'pi-sort opacity-0 group-hover:opacity-50'"></i>
                                </th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            
                            <tr v-for="student in paginatedStudents" :key="student.id" class="transition-colors hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-bold text-slate-800">
                                    {{ student.name }}
                                    <span class="block text-[11px] font-medium text-slate-400 mt-0.5">{{ student.email }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="student.is_submitted" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold text-emerald-700">
                                        <i class="pi pi-check-circle"></i> Selesai
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-bold text-amber-700">
                                        <i class="pi pi-clock"></i> Belum Selesai
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-[12px] font-medium text-slate-500">
                                    {{ student.submitted_at || '-' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-lg font-black" :class="student.score > 0 ? 'text-indigo-600' : 'text-slate-300'">{{ student.score }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('guru.phases.evaluations.show', { classroom: classroom.id, topic: topic.id, phase: phase.id, student: student.id })">
                                        <Button size="sm" :variant="student.is_submitted ? 'default' : 'outline'" class="h-8 rounded-lg text-xs font-bold transition-all hover:scale-105 active:scale-95" :class="{'bg-indigo-600 text-white hover:bg-indigo-700': student.is_submitted, 'border-slate-200 text-slate-500 hover:bg-slate-50': !student.is_submitted}">
                                            <i class="pi pi-search mr-1.5"></i> Periksa
                                        </Button>
                                    </Link>
                                </td>
                            </tr>
                            
                            <tr v-if="paginatedStudents.length === 0">
                                <td colspan="5" class="py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="pi pi-inbox text-4xl text-slate-300 mb-3"></i>
                                        <span class="text-slate-500 font-medium">Tidak ada data siswa yang ditemukan.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">
                        Menampilkan {{ filteredAndSortedStudents.length === 0 ? 0 : ((currentPage - 1) * itemsPerPage) + 1 }} - 
                        {{ Math.min(currentPage * itemsPerPage, filteredAndSortedStudents.length) }} dari {{ filteredAndSortedStudents.length }} siswa
                    </span>
                    
                    <div class="flex items-center gap-2">
                        <button @click="prevPage" :disabled="currentPage === 1" class="h-8 px-3 rounded-lg border border-slate-200 bg-white text-slate-600 text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-100 transition-colors">
                            <i class="pi pi-chevron-left text-[10px] mr-1"></i> Prev
                        </button>
                        <span class="text-xs font-bold text-slate-700 mx-2">{{ currentPage }} / {{ totalPages }}</span>
                        <button @click="nextPage" :disabled="currentPage === totalPages" class="h-8 px-3 rounded-lg border border-slate-200 bg-white text-slate-600 text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-100 transition-colors">
                            Next <i class="pi pi-chevron-right text-[10px] ml-1"></i>
                        </button>
                    </div>
                </div>

            </Card>
        </div>
    </div>
</template>