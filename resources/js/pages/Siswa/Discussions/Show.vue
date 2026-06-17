<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    classroom: any; topic: any; phase: any; discussion: any;
}>();

const replyForm = useForm({ content: '' });
const chatContainer = ref<HTMLElement | null>(null);
const scrollToBottom = async () => { await nextTick(); if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight; };
onMounted(() => scrollToBottom());

const submitReply = () => {
    if (!replyForm.content.trim()) return;
    replyForm.post(route('siswa.discussions.replies.store', { discussion: props.discussion.id }), {
        preserveScroll: true, onSuccess: () => { replyForm.reset(); scrollToBottom(); }
    });
};
</script>

<template>
    <Head :title="discussion.title" />
    <div class="min-h-screen bg-[#F8FAFC] px-6 py-8 font-sans lg:px-10 flex flex-col">
        <div class="mx-auto w-full max-w-4xl flex-1 flex flex-col">
            <div class="mb-4 text-[12px] font-bold text-slate-500">
                <Link :href="route('siswa.classes.topics.phases.discussions.index', { classroom: classroom.id, topic: topic.id, phase: phase.id })" class="hover:text-indigo-600">
                    <i class="pi pi-arrow-left mr-1"></i> Kembali ke Daftar Diskusi Fase
                </Link>
            </div>

            <Card class="mb-6 bg-white p-6 rounded-2xl flex gap-5 items-start">
                <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <i class="pi pi-comments text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900">{{ discussion.title }}</h1>
                    <p class="text-[14px] text-slate-600 mt-2">{{ discussion.description }}</p>
                </div>
            </Card>

            <Card class="flex-1 min-h-[450px] bg-white rounded-2xl flex flex-col overflow-hidden mb-6">
                <div ref="chatContainer" class="flex-1 p-6 overflow-y-auto bg-slate-50/50 flex flex-col gap-4">
                    <template v-if="discussion.replies && discussion.replies.length > 0">
                        <div v-for="reply in discussion.replies" :key="reply.id" class="flex gap-4 p-3 rounded-xl hover:bg-slate-100/60">
                            <div class="h-10 w-10 shrink-0 rounded-xl flex items-center justify-center font-black text-white bg-indigo-600">
                                {{ reply.user.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <div class="flex-1">
                                <span class="text-[14px] font-black text-slate-800">{{ reply.user.name }}</span>
                                <p class="text-[14px] text-slate-700 mt-1">{{ reply.content }}</p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="p-4 bg-white border-t border-slate-100">
                    <form @submit.prevent="submitReply" class="flex gap-3">
                        <textarea v-model="replyForm.content" rows="2" class="flex-1 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm outline-none resize-none" @keydown.enter.exact.prevent="submitReply"></textarea>
                        <Button type="submit" class="bg-indigo-600 text-white rounded-xl px-6">Kirim</Button>
                    </form>
                </div>
            </Card>
        </div>
    </div>
</template>