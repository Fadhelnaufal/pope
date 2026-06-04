import { createInertiaApp } from '@inertiajs/vue3';
import Aura from '@primevue/themes/aura';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import 'primeicons/primeicons.css';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js'; // Harus dari 'ziggy-js'

// 1. IMPORT LAYOUT
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Ziggy } from './ziggy.js';  // Ini file fisik peta rute kita

const appName = import.meta.env.VITE_APP_NAME || 'EduChem';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        // Pemetaan folder 'pages' (huruf kecil)
        const pages = import.meta.glob('./pages/**/*.vue', { eager: true });
        const page: any = await resolvePageComponent(`./pages/${name}.vue`, pages);
        
        // PASANG LAYOUT SECARA OTOMATIS
        if (page.default.layout === undefined) {
            const lowerName = name.toLowerCase();

            if (lowerName.startsWith('auth/')) {
                page.default.layout = AuthLayout;
            } else {
                page.default.layout = AppLayout; // Sidebar dirender di sini
            }
        }
        
        return page;
    },
    setup({ el, App, props, plugin }) {
        // SOLUSI SSR 1: Hapus dark mode HANYA jika berjalan di sisi Client (Browser)
        if (typeof document !== 'undefined') {
            document.documentElement.classList.remove('dark');
        }
        
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        
        // Konfigurasi PrimeVue (Force Light Mode)
        app.use(PrimeVue, {
            theme: {
                preset: Aura,
                options: {
                    darkModeSelector: false, 
                }
            }
        });

        // SOLUSI SSR 2: Daftarkan ZiggyVue dengan menyuapkan objek Ziggy
        // Ini yang membuat route() bisa dibaca oleh server NodeJS!
        app.use(ZiggyVue, Ziggy);
        
        // Mount aplikasi HANYA jika elemen DOM (el) tersedia di browser
        if (el) {
            app.mount(el);
        }

        // WAJIB UNTUK SSR: Return instance aplikasi
        return app;
    },
    progress: { color: '#4F8CFF' }, // Warna loading biru EduChem
});