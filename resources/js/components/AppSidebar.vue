```vue
<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import NavUser from '@/components/NavUser.vue'

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
} from '@/components/ui/sidebar'

const page = usePage()

const userRole = computed(
    () => page.props.auth.user?.roles?.[0]?.name || 'SISWA'
)

const menuItems = computed(() => {
    if (userRole.value === 'ADMIN') {
        return [
            {
                label: 'Dashboard',
                icon: 'pi pi-home',
                route: 'admin.dashboard',
            },
            {
                label: 'Manajemen User',
                icon: 'pi pi-users',
                route: 'admin.users.index',
            },
        ]
    }

    if (userRole.value === 'GURU') {
        return [
            {
                label: 'Dashboard',
                icon: 'pi pi-home',
                route: 'guru.dashboard',
            },
            {
                label: 'Manajemen Kelas',
                icon: 'pi pi-book',
                route: 'guru.classes.index',
            },
        ]
    }

    return [
        {
            label: 'Dashboard',
            icon: 'pi pi-th-large',
            route: 'siswa.dashboard',
        },
        {
            label: 'Kelas Saya',
            icon: 'pi pi-book',
            route: 'siswa.classes.index',
        },
    ]
})
</script>

<template>
    <Sidebar variant="inset" class="border-none">
        <!-- Header -->
        <SidebarHeader class="border-b border-white/10 p-5">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 shadow-md"
                >
                    <i class="pi pi-graduation-cap text-xl text-white"></i>
                </div>

                <div class="overflow-hidden">
                    <h1 class="truncate text-lg font-bold text-white">
                        EduChem
                    </h1>

                    <p
                        class="truncate text-[11px] font-medium uppercase tracking-[0.15em] text-blue-200/80"
                    >
                        {{ userRole }} Workspace
                    </p>
                </div>
            </div>
        </SidebarHeader>

        <!-- Menu -->
        <SidebarContent class="px-3 py-4">
            <div class="mb-3 px-3">
                <p
                    class="text-[11px] font-semibold uppercase tracking-widest text-slate-400"
                >
                    Menu Utama
                </p>
            </div>

            <SidebarMenu>
                <SidebarMenuItem
                    v-for="item in menuItems"
                    :key="item.label"
                    class="mb-1"
                >
                    <Link
                        :href="route(item.route)"
                        class="flex h-11 items-center gap-3 rounded-xl px-4 transition-all duration-200"
                        :class="
                            route().current(item.route)
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                                : 'text-slate-300 hover:bg-white/10 hover:text-white'
                        "
                    >
                        <i :class="item.icon" class="text-lg"></i>

                        <span class="font-medium">
                            {{ item.label }}
                        </span>
                    </Link>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarContent>

        <!-- Footer -->
        <SidebarFooter class="border-t border-white/10 p-4">
            <NavUser
                class="rounded-xl text-white transition-all hover:bg-white/10"
            />
        </SidebarFooter>
    </Sidebar>
</template>

<style scoped>
:deep(.bg-sidebar) {
    background: #0b1e36 !important;
}

:deep(.text-sidebar-foreground) {
    color: #e2e8f0 !important;
}
</style>
```
