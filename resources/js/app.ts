import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Adicionar um $t global temporário para evitar erros
        app.config.globalProperties.$t = (key: string) => {
            // Sistema simples de tradução sem vue-i18n
            const locale = localStorage.getItem('locale') || 'en';
            const translations: any = {
                en: {
                    'nav.dashboard': 'Dashboard',
                    'nav.tasks': 'Tasks',
                    'nav.timeboxes': 'Time Boxes',
                    'nav.analytics': 'Analytics',
                    'nav.settings': 'Settings',
                    'nav.profile': 'Profile',
                    'nav.logout': 'Logout',
                    'common.save': 'Save',
                    'common.cancel': 'Cancel',
                    'common.delete': 'Delete',
                    'common.edit': 'Edit',
                    'common.create': 'Create',
                },
                pt: {
                    'nav.dashboard': 'Painel',
                    'nav.tasks': 'Tarefas',
                    'nav.timeboxes': 'Blocos de Tempo',
                    'nav.analytics': 'Análises',
                    'nav.settings': 'Configurações',
                    'nav.profile': 'Perfil',
                    'nav.logout': 'Sair',
                    'common.save': 'Salvar',
                    'common.cancel': 'Cancelar',
                    'common.delete': 'Excluir',
                    'common.edit': 'Editar',
                    'common.create': 'Criar',
                },
                it: {
                    'nav.dashboard': 'Dashboard',
                    'nav.tasks': 'Compiti',
                    'nav.timeboxes': 'Blocchi di Tempo',
                    'nav.analytics': 'Analisi',
                    'nav.settings': 'Impostazioni',
                    'nav.profile': 'Profilo',
                    'nav.logout': 'Esci',
                    'common.save': 'Salva',
                    'common.cancel': 'Annulla',
                    'common.delete': 'Elimina',
                    'common.edit': 'Modifica',
                    'common.create': 'Crea',
                }
            };

            return translations[locale]?.[key] || key;
        };

        app.use(plugin);
        app.use(ZiggyVue);
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});