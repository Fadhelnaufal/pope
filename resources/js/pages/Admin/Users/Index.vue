<script setup lang="ts">
import { ref, watch, h } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import DataTable from '@/components/DataTable.vue';
import type { ColumnDef } from '@tanstack/vue-table';

// IMPORT KOMPONEN ALERT DIALOG SHADCN
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

type User = {
    id: number;
    name: string;
    email: string;
    roles: Array<{ name: string }>;
};

const props = defineProps<{
    users: {
        data: User[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number;
        to: number;
        total: number;
    },
    filters?: { search?: string; }
}>();

const form = useForm({});
const searchQuery = ref(props.filters?.search || '');

let searchTimeout: any = null;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), { search: value }, { preserveState: true, replace: true });
    }, 300);
});

// --- STATE UNTUK ALERT DIALOG ---
const isDeleteDialogOpen = ref(false);
const userToDelete = ref<number | null>(null);

const editRole = (userId: number) => {
    router.get(route('admin.users.edit', userId));
};

// Fungsi ini sekarang hanya membuka pop-up, tidak langsung menghapus
const confirmDeleteUser = (userId: number) => {
    userToDelete.value = userId;
    isDeleteDialogOpen.value = true;
};

// Fungsi ini dieksekusi ketika tombol "Hapus Akun" di dalam pop-up ditekan
const executeDelete = () => {
    if (userToDelete.value) {
        form.delete(route('admin.users.destroy', userToDelete.value), { 
            preserveScroll: true,
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                userToDelete.value = null;
            }
        });
    }
};

// --- DEFINISI KOLOM TANSTACK ---
const columns: ColumnDef<User>[] = [
    {
        accessorKey: 'name',
        header: 'Nama Lengkap',
        cell: ({ row }) => h('div', { class: 'font-semibold text-slate-900 text-[14px]' }, row.getValue('name')),
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => h('div', { class: 'text-slate-500 text-[13px] font-medium' }, row.getValue('email')),
    },
    {
        accessorKey: 'roles',
        header: 'Role',
        cell: ({ row }) => {
            const roles: Array<{ name: string }> = row.getValue('roles');
            return h('div', { class: 'flex flex-wrap gap-1.5' }, roles.map(role => {
                let badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                if (role.name === 'ADMIN') badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                if (role.name === 'GURU') badgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                if (role.name === 'SISWA') badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                return h(Badge, { 
                    variant: 'outline', 
                    class: `${badgeClass} text-[10px] uppercase font-bold px-2 py-0.5 tracking-wider` 
                }, () => role.name);
            }));
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) => {
            const user = row.original;
            
            return h('div', { class: 'flex justify-end gap-2' }, [
                h(Button, {
                    size: 'icon',
                    variant: 'outline',
                    class: 'h-8 w-8 bg-white border-slate-200 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 shadow-sm transition-all',
                    onClick: () => editRole(user.id),
                    title: 'Ubah Role'
                }, () => h('i', { class: 'pi pi-pencil text-[12px]' })),
                
                // Memicu confirmDeleteUser alih-alih deleteUser langsung
                h(Button, {
                    size: 'icon',
                    variant: 'outline',
                    class: 'h-8 w-8 bg-white border-slate-200 text-rose-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 shadow-sm transition-all',
                    onClick: () => confirmDeleteUser(user.id),
                    disabled: form.processing,
                    title: 'Hapus Akun'
                }, () => h('i', { class: 'pi pi-trash text-[12px]' }))
            ]);
        },
    },
];
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <div class="min-h-screen bg-[#F8FAFC] py-8 px-6 lg:px-10 font-sans">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 max-w-7xl mx-auto">
            <div>
                <h2 class="text-[26px] font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h2>
                <p class="text-[14px] text-slate-500 mt-1 font-medium">Kelola hak akses dan peran (role) dari seluruh pengguna sistem.</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            <Card class="shadow-sm border-slate-200 rounded-xl bg-white overflow-hidden">
                <CardHeader class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-transparent">
                    <div>
                        <CardTitle class="text-[17px] font-bold text-slate-800">Daftar Pengguna</CardTitle>
                        <CardDescription class="text-[13px] text-slate-500 mt-1 font-medium">
                            Menampilkan <span class="font-semibold text-slate-700">{{ users.from || 0 }} - {{ users.to || 0 }}</span> dari <span class="font-semibold text-slate-700">{{ users.total || 0 }}</span>
                        </CardDescription>
                    </div>
                    
                    <div class="relative w-full sm:w-[300px]">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <Input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Cari nama atau email..." 
                            class="pl-9 h-9 bg-white border-slate-200 shadow-sm focus-visible:ring-indigo-500 rounded-lg w-full text-[13px] placeholder:text-slate-400" 
                        />
                    </div>
                </CardHeader>
                
                <CardContent class="p-0">
                    <DataTable :columns="columns" :data="users.data" />
                </CardContent>

                <div v-if="users.links && users.links.length > 3" class="border-t border-slate-200 bg-slate-50/50 p-4 px-6 flex items-center justify-between">
                    <span class="text-[13px] font-medium text-slate-500 hidden sm:block">Paginasi Halaman</span>
                    <div class="flex items-center gap-1">
                        <Component
                            :is="link.url ? Link : 'span'"
                            v-for="(link, index) in users.links"
                            :key="index"
                            :href="link.url"
                            class="px-3 py-1.5 rounded-md text-[13px] font-semibold transition-colors"
                            :class="link.active 
                                ? 'bg-white border border-slate-200 shadow-sm text-slate-900' 
                                : (link.url ? 'text-slate-600 hover:bg-slate-200/50' : 'text-slate-300 cursor-not-allowed')"
                        >
                            <span v-html="link.label"></span>
                        </Component>
                    </div>
                </div>
            </Card>
        </div>

        <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
            <AlertDialogContent class="bg-white border-slate-200 rounded-2xl shadow-lg sm:max-w-[425px]">
                <AlertDialogHeader>
                    <AlertDialogTitle class="text-slate-900 text-xl font-bold flex items-center gap-2">
                        <i class="pi pi-exclamation-triangle text-rose-500"></i> Hapus Akun?
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-slate-500 mt-2 text-[14px] leading-relaxed">
                        Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data pengguna secara permanen dari server dan mencabut semua akses yang mereka miliki.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="mt-6">
                    <AlertDialogCancel class="border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg">Batal</AlertDialogCancel>
                    <AlertDialogAction 
                        @click="executeDelete" 
                        class="bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-sm font-semibold transition-colors"
                    >
                        Ya, Hapus Akun
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </div>
</template>