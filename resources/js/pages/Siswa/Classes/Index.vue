<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

defineProps<{
    classrooms: Array<{
        id: number;
        class_name: string;
        class_code: string;
        description: string | null;
        topics_count: number;
    }>;
}>();

// Form Inertia untuk fitur Gabung Kelas
const form = useForm({
    class_code: '',
});

const submitJoinClass = () => {
    form.post(route('siswa.classes.join'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('class_code');
            alert('Berhasil bergabung ke kelas!');
        },
    });
};
</script>

<template>
    <Head title="Kelas Saya - EduChem" />

    <main
        class="relative flex min-h-screen w-full flex-1 flex-col bg-[#F8FAFC] font-sans"
    >
        <div class="border-b border-slate-200 bg-white px-8 py-8">
            <div
                class="mx-auto flex max-w-7xl flex-col justify-between gap-6 md:flex-row md:items-center"
            >
                <div>
                    <h1
                        class="text-[28px] font-black tracking-tight text-slate-900"
                    >
                        Kelas Saya
                    </h1>
                    <p class="mt-1 text-[14px] font-medium text-slate-500">
                        Daftar kelas kimia yang sedang Anda ikuti.
                    </p>
                </div>

                <form
                    @submit.prevent="submitJoinClass"
                    class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-1.5 shadow-sm md:w-auto"
                >
                    <div class="relative w-full md:w-56">
                        <i
                            class="pi pi-key absolute top-1/2 left-3 -translate-y-1/2 text-[13px] text-slate-400"
                        ></i>
                        <Input
                            v-model="form.class_code"
                            placeholder="Kode Kelas (6 Digit)"
                            maxlength="6"
                            class="h-10 w-full border-none bg-transparent pr-3 pl-9 text-[13px] font-bold tracking-widest uppercase focus-visible:ring-0"
                        />
                    </div>
                    <Button
                        type="submit"
                        :disabled="
                            form.processing || form.class_code.length !== 6
                        "
                        class="h-10 rounded-xl bg-indigo-600 px-6 text-[13px] font-bold text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:opacity-50"
                    >
                        <span v-if="form.processing"
                            ><i class="pi pi-spin pi-spinner"></i
                        ></span>
                        <span v-else>Gabung Kelas</span>
                    </Button>
                </form>
            </div>
            <p
                v-if="form.errors.class_code"
                class="mx-auto mt-2 max-w-7xl text-right text-[12px] font-bold text-rose-500"
            >
                * {{ form.errors.class_code }}
            </p>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="mx-auto max-w-7xl">
                <div
                    v-if="classrooms && classrooms.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <Card
                        v-for="kelas in classrooms"
                        :key="kelas.id"
                        class="group flex cursor-pointer flex-col overflow-hidden rounded-2xl border-slate-200/60 shadow-sm transition-all hover:shadow-md"
                    >
                        <div
                            class="relative h-24 overflow-hidden bg-gradient-to-r from-indigo-500 to-blue-600 p-5"
                        >
                            <div class="absolute -top-4 -right-4 opacity-20">
                                <i
                                    class="pi pi-share-alt text-8xl text-white"
                                ></i>
                            </div>
                            <h3
                                class="relative z-10 truncate text-lg font-bold text-white"
                            >
                                {{ kelas.class_name }}
                            </h3>
                            <p
                                class="relative z-10 mt-1 text-[11px] font-semibold tracking-wider text-blue-100 uppercase"
                            >
                                Kode: {{ kelas.class_code }}
                            </p>
                        </div>

                        <div class="flex flex-1 flex-col bg-white p-5">
                            <p
                                class="mb-4 line-clamp-2 flex-1 text-[13px] text-slate-500"
                            >
                                {{
                                    kelas.description ||
                                    'Tidak ada deskripsi untuk kelas ini.'
                                }}
                            </p>

                            <div class="mt-auto space-y-2">
                                <div
                                    class="flex justify-between text-[11px] font-bold text-slate-500"
                                >
                                    <span>Progress Pembelajaran</span>
                                    <span class="text-indigo-600">0%</span>
                                </div>
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full bg-slate-100"
                                >
                                    <div
                                        class="h-full rounded-full bg-indigo-500"
                                        style="width: 0%"
                                    ></div>
                                </div>
                                <p
                                    class="pt-2 text-[11px] font-medium text-slate-400"
                                >
                                    <i class="pi pi-book mr-1"></i>
                                    {{ kelas.topics_count }} Topik Materi
                                    tersedia
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50 p-4">
                            <Link
                                :href="route('siswa.classes.show', kelas.id)"
                                class="flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white text-[13px] font-bold text-slate-700 shadow-sm transition-colors hover:border-indigo-200 hover:text-indigo-600"
                            >
                                Masuk ke Kelas
                                <i class="pi pi-arrow-right text-[11px]"></i>
                            </Link>
                        </div>
                    </Card>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-20 text-center"
                >
                    <div
                        class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100"
                    >
                        <i
                            class="pi pi-folder-open text-4xl text-slate-300"
                        ></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">
                        Belum Ada Kelas
                    </h2>
                    <p class="mt-2 max-w-md text-[14px] text-slate-500">
                        Anda belum bergabung ke kelas mana pun. Silakan masukkan
                        6 digit kode kelas yang diberikan oleh Guru Anda pada
                        kolom di atas.
                    </p>
                </div>
            </div>
        </div>
    </main>
</template>
