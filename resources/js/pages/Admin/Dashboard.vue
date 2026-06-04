<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

// --- IMPORT KOMPONEN SHADCN-VUE ---
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Progress } from '@/components/ui/progress';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

defineProps<{
    stats: {
        total_users: number;
        total_guru: number;
        total_siswa: number;
    };
}>();

// --- DATA DUMMY ---
const recentUsers = [
    { id: 1, name: 'Dr. Hendra (Kimia)', role: 'GURU', status: 'Active', class: 'Kelas XI-IPA 1', last_login: '2 min ago' },
    { id: 2, name: 'Budi Santoso', role: 'SISWA', status: 'Active', class: 'Kelas X-IPA 3', last_login: '15 min ago' },
    { id: 3, name: 'Siti Aminah', role: 'SISWA', status: 'Inactive', class: '-', last_login: '2 days ago' },
];

const activeClasses = [
    { name: 'Kimia Dasar X', teacher: 'Dr. Hendra', students: 32, progress: 75, status: 'Active' },
    { name: 'Ikatan Kimia XI', teacher: 'Ibu Ratna', students: 28, progress: 45, status: 'Active' },
];

const aiAnalytics = { total: 1245, success: 98, error: 2 };
</script>

<template>
    <Head title="Dashboard Admin" />

    <div class="min-h-screen bg-[#F8FAFC] py-8 px-6 lg:px-10 font-sans">
        
        <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center max-w-7xl mx-auto">
            <div>
                <h1 class="text-[26px] font-bold tracking-tight text-slate-900">
                    EduChem POE System
                </h1>
                <p class="mt-1 text-[14px] font-medium text-slate-500">
                    Dashboard Administrasi & Monitoring Sistem Periodik
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative hidden w-[250px] md:block">
                    <i class="pi pi-search absolute top-1/2 left-3 -translate-y-1/2 text-sm text-slate-400"></i>
                    <Input
                        type="text"
                        placeholder="Cari data..."
                        class="h-9 w-full rounded-lg border-slate-200 bg-white pl-9 shadow-sm focus-visible:ring-indigo-500 text-[13px] placeholder:text-slate-400"
                    />
                </div>

                <Button
                    variant="outline"
                    size="icon"
                    class="h-9 w-9 rounded-lg border-slate-200 bg-white shadow-sm hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                >
                    <i class="pi pi-bell text-[14px]"></i>
                </Button>

                <div class="flex cursor-pointer items-center gap-3 rounded-full border border-slate-200 bg-white py-1.5 pr-4 pl-1.5 shadow-sm transition-colors hover:bg-slate-50">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white shadow-inner">
                        SA
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] leading-none font-bold text-slate-800">Super Admin</span>
                        <span class="mt-0.5 text-[9px] font-bold tracking-wider text-indigo-500">ROLE: ADMIN</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4 max-w-7xl mx-auto">
            <Card class="rounded-xl border-slate-200 bg-white shadow-sm">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Users</p>
                            <h2 class="text-3xl font-extrabold text-slate-900">{{ stats.total_users }}</h2>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i class="pi pi-users text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-[13px]">
                        <span class="flex items-center gap-1 rounded bg-emerald-50 px-1.5 py-0.5 font-semibold text-emerald-600">
                            <i class="pi pi-arrow-up text-[9px]"></i> 12%
                        </span>
                        <span class="text-slate-500 font-medium">dari bulan lalu</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border-slate-200 bg-white shadow-sm">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Guru</p>
                            <h2 class="text-3xl font-extrabold text-slate-900">{{ stats.total_guru }}</h2>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <i class="pi pi-briefcase text-lg"></i>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border-slate-200 bg-white shadow-sm">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="mb-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">Total Siswa</p>
                            <h2 class="text-3xl font-extrabold text-slate-900">{{ stats.total_siswa }}</h2>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <i class="pi pi-id-card text-lg"></i>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="relative overflow-hidden rounded-xl border-none bg-gradient-to-br from-[#4F46E5] to-[#4338CA] shadow-sm">
                <CardContent class="p-6">
                    <i class="pi pi-share-alt absolute -right-3 -bottom-3 text-7xl text-white opacity-10"></i>
                    <div class="relative z-10 flex items-start justify-between">
                        <div>
                            <p class="mb-2 text-[11px] font-bold tracking-wider text-indigo-100 uppercase">AI POE Requests</p>
                            <h2 class="text-3xl font-extrabold text-white">1,245</h2>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-white backdrop-blur-sm">
                            <i class="pi pi-sparkles text-lg"></i>
                        </div>
                    </div>
                    <div class="relative z-10 mt-4 flex items-center gap-2 text-sm">
                        <span class="flex items-center gap-1.5 text-[13px] font-medium text-white">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span> Flash API Active
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-5 xl:grid-cols-3 max-w-7xl mx-auto">
            
            <Card class="rounded-xl border-slate-200 bg-white shadow-sm xl:col-span-2 overflow-hidden">
                <CardHeader class="flex flex-row items-center justify-between border-b border-transparent p-6 pb-4">
                    <CardTitle class="text-[17px] font-bold text-slate-800">User Management Terbaru</CardTitle>
                    <Link :href="route('admin.users.index')">
                        <Button variant="link" class="h-auto p-0 font-semibold text-indigo-600 hover:text-indigo-700">Lihat Semua</Button>
                    </Link>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader class="bg-slate-50/80 border-y border-slate-200">
                            <TableRow class="border-none hover:bg-transparent">
                                <TableHead class="h-11 px-6 text-[11px] font-bold text-slate-500 uppercase tracking-wider align-middle">Nama Lengkap</TableHead>
                                <TableHead class="h-11 text-[11px] font-bold text-slate-500 uppercase tracking-wider align-middle">Role</TableHead>
                                <TableHead class="h-11 text-[11px] font-bold text-slate-500 uppercase tracking-wider align-middle">Kelas</TableHead>
                                <TableHead class="h-11 text-[11px] font-bold text-slate-500 uppercase tracking-wider align-middle">Status</TableHead>
                                <TableHead class="h-11 pr-6 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider align-middle">Last Login</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in recentUsers" :key="user.id" class="border-b border-slate-100 hover:bg-slate-50/60 transition-colors">
                                <TableCell class="px-6 py-4 font-semibold text-slate-900 text-[14px]">{{ user.name }}</TableCell>
                                <TableCell>
                                    <Badge
                                        variant="outline"
                                        :class="user.role === 'GURU'
                                                ? 'bg-indigo-50 text-indigo-700 border-indigo-200'
                                                : 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                        "
                                        class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                    >
                                        {{ user.role }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-[13px] font-medium text-slate-600">{{ user.class }}</TableCell>
                                <TableCell>
                                    <span class="flex items-center gap-1.5 text-[13px] font-medium text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="user.status === 'Active' ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                        {{ user.status }}
                                    </span>
                                </TableCell>
                                <TableCell class="pr-6 text-right text-[13px] font-medium text-slate-500">{{ user.last_login }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-5">
                <Card class="rounded-xl border-slate-200 bg-white shadow-sm">
                    <CardHeader class="pb-4 border-b border-slate-50">
                        <CardTitle class="text-[16px] font-bold text-slate-800">System Health</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <i class="pi pi-database text-[14px]"></i>
                                </div>
                                <span class="text-[13px] font-semibold text-slate-700">PostgreSQL</span>
                            </div>
                            <Badge variant="outline" class="border-emerald-200 bg-emerald-50 text-[9px] font-bold text-emerald-700 uppercase tracking-wider">Online</Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <i class="pi pi-bolt text-[14px]"></i>
                                </div>
                                <span class="text-[13px] font-semibold text-slate-700">Redis Queue</span>
                            </div>
                            <Badge variant="outline" class="border-emerald-200 bg-emerald-50 text-[9px] font-bold text-emerald-700 uppercase tracking-wider">Online</Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
                                    <i class="pi pi-microchip-ai text-[14px]"></i>
                                </div>
                                <span class="text-[13px] font-semibold text-slate-700">Gemini API</span>
                            </div>
                            <Badge variant="outline" class="border-orange-200 bg-orange-50 text-[9px] font-bold text-orange-700 uppercase tracking-wider">High Load</Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-xl border-slate-800 bg-slate-900 text-white shadow-sm">
                    <CardContent class="p-6">
                        <h3 class="mb-4 text-[11px] font-bold tracking-wider text-slate-400 uppercase">AI Success Rate</h3>
                        <div class="mb-3 flex items-baseline gap-2">
                            <span class="text-4xl font-extrabold tracking-tight">{{ aiAnalytics.success }}</span>
                            <span class="text-lg font-bold text-emerald-400">%</span>
                        </div>
                        <Progress :model-value="aiAnalytics.success" class="h-2 bg-slate-800" indicator-class="bg-indigo-500" />
                        <p class="mt-4 text-[12px] font-medium text-slate-400">{{ aiAnalytics.error }}% failed requests (Rate Limit Issue)</p>
                    </CardContent>
                </Card>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            <Card class="rounded-xl border-slate-200 bg-white shadow-sm">
                <CardHeader class="pb-4 border-b border-transparent">
                    <CardTitle class="text-[17px] font-bold text-slate-800">Class Monitoring (POE Progress)</CardTitle>
                </CardHeader>
                <CardContent class="pt-2">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div v-for="cls in activeClasses" :key="cls.name" class="rounded-xl border border-slate-100 bg-slate-50/50 p-5 transition-colors hover:bg-slate-50">
                            <div class="mb-4 flex items-start justify-between">
                                <div>
                                    <h4 class="text-[15px] font-bold text-slate-900">{{ cls.name }}</h4>
                                    <p class="mt-1 text-[13px] font-medium text-slate-500">
                                        <i class="pi pi-user mr-1.5 text-[11px]"></i>
                                        {{ cls.teacher }} • {{ cls.students }} Siswa
                                    </p>
                                </div>
                                <Badge variant="outline" class="border-emerald-200 bg-emerald-50 text-[9px] font-bold tracking-wider text-emerald-700 uppercase">ACTIVE</Badge>
                            </div>
                            <div class="mb-2 flex justify-between text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                                <span>Worksheet Progress</span>
                                <span class="text-indigo-600">{{ cls.progress }}%</span>
                            </div>
                            <Progress :model-value="cls.progress" class="h-1.5 bg-slate-200" indicator-class="bg-indigo-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

    </div>
</template>