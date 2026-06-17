<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { toast } from 'vue-sonner';

const props = defineProps<{
    classroom: any;
    topic: any;
    phase: any;
    discussions: Array<any>;
}>();

const isCreateModalOpen = ref(false);
const form = useForm({ title: '', description: '' });

const submitDiscussion = () => {
    form.post(route('guru.classes.topics.phases.discussions.store', { classroom: props.classroom.id, topic: props.topic.id, phase: props.phase.id }), {
        preserveScroll: true,
        onSuccess: () => { isCreateModalOpen.value = false; form.reset(); toast.success('✨ Forum Diskusi Fase dibuat!'); }
    });
};

const deleteDiscussion = (id: number) => {
    if (confirm('Hapus diskusi ini?')) {
        router.delete(route('guru.classes.topics.phases.discussions.destroy', { discussion: id }), { preserveScroll: true, onSuccess: () => toast.success('Forum dihapus.') });
    }
};
</script>

<template>
    <Head :title="`Forum Fase: ${phase.name}`" />

    <div class="min-h-screen bg-[#F4F7F9] px-6 py-8 font-sans lg:px-10">
        <div class="mx-auto max-w-5xl">
            <div class="mb-6 flex items-center gap-2 text-[12px] font-bold text-slate-500">
                <Link :href="route('guru.classes.topics.show', { classroom: classroom.id, topic: topic.id })" class="transition-colors hover:text-indigo-600">
                    <i class="pi pi-arrow-left mr-1"></i> Kembali ke Topik
                </Link>
            </div>

            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-900">Forum Fase: {{ phase.name }}</h1>
                    <p class="text-[14px] text-slate-500 mt-1">Kelola diskusi spesifik untuk tahapan ini.</p>
                </div>
                <Button @click="isCreateModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold h-11 px-6 rounded-xl">
                    <i class="pi pi-plus mr-2"></i> Buat Topik Diskusi
                </Button>
            </div>

            <div class="space-y-4">
                <div v-for="discuss in discussions" :key="discuss.id">
                    <Card class="p-6 rounded-2xl border-slate-200 bg-white shadow-sm flex justify-between items-start">
                        <div class="flex-1 pr-6">
                            <div class="flex gap-3 mb-2.5 text-[10px] font-black uppercase text-indigo-600">
                                <span><i class="pi pi-comments"></i> {{ discuss.replies_count || 0 }} Balasan</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-800">{{ discuss.title }}</h3>
                            <p class="text-[14px] text-slate-500 mt-1.5 line-clamp-2">{{ discuss.description }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link :href="route('guru.classes.topics.phases.discussions.show', { classroom: classroom.id, topic: topic.id, phase: phase.id, discussion: discuss.id })">
                                <Button class="bg-indigo-50 text-indigo-600 h-10 px-5 rounded-xl">Masuk Diskusi</Button>
                            </Link>
                            <button @click="deleteDiscussion(discuss.id)" class="text-rose-400 hover:text-rose-600"><i class="pi pi-trash"></i></button>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <Teleport to="body">
        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl">
                <form @submit.prevent="submitDiscussion">
                    <Input v-model="form.title" required placeholder="Judul Pemantik..." class="mb-4" />
                    <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm mb-4" placeholder="Deskripsi..."></textarea>
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="isCreateModalOpen = false">Batal</Button>
                        <Button type="submit" class="bg-indigo-600 text-white">Simpan</Button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>