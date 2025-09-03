<template>
    <Layout title="Settings">
        <div class="p-6 space-y-6 max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="space-y-1">
                <h2
                    class="text-2xl font-bold bg-gradient-to-r from-slate-400 to-slate-600 bg-clip-text text-transparent">
                    Settings
                </h2>
                <p class="text-sm text-gray-500">Manage your account settings and preferences</p>
            </div>

            <!-- Profile Section -->
            <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile Information
                </h3>

                <form @submit.prevent="updateProfile" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                            <input v-model="profileForm.name" type="text" class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg 
                                          text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 
                                          focus:ring-2 focus:ring-blue-500/20 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                            <input v-model="profileForm.email" type="email" class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg 
                                          text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 
                                          focus:ring-2 focus:ring-blue-500/20 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Bio</label>
                        <textarea v-model="profileForm.bio" rows="3" class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg 
                                         text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 
                                         focus:ring-2 focus:ring-blue-500/20 transition-all resize-none"
                            placeholder="Tell us about yourself..."></textarea>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg 
                                   transition-colors duration-200 font-medium">
                        Save Profile
                    </button>
                </form>
            </div>

            <!-- Appearance Section -->
            <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    Appearance & Localization
                </h3>

                <div class="space-y-4">
                    <!-- Theme Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Theme</label>
                        <div class="grid grid-cols-3 gap-3">
                            <button v-for="theme in themes" :key="theme.value"
                                @click="updateSetting('theme', theme.value)" :class="[
                                    'p-3 rounded-lg border transition-all duration-200',
                                    settings.theme === theme.value
                                        ? 'bg-blue-600/20 border-blue-500 text-blue-400'
                                        : 'bg-gray-900/50 border-gray-700 text-gray-400 hover:border-gray-600'
                                ]">
                                <component :is="theme.icon" class="w-5 h-5 mx-auto mb-1" />
                                <span class="text-xs">{{ theme.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Language Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Language</label>
                        <select v-model="settings.language" @change="changeLanguage" class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg 
                                       text-white focus:outline-none focus:border-blue-500 
                                       focus:ring-2 focus:ring-blue-500/20 transition-all">
                            <option value="en">English</option>
                            <option value="pt">Português</option>


                            <option value="it">Italiano</option>

                        </select>
                    </div>

                    <!-- Date & Time Format -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Date Format</label>
                            <select v-model="settings.dateFormat" @change="updateSettings" class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg 
                                           text-white focus:outline-none focus:border-blue-500 
                                           focus:ring-2 focus:ring-blue-500/20 transition-all">
                                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Time Format</label>
                            <select v-model="settings.timeFormat" @change="updateSettings" class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg 
                                           text-white focus:outline-none focus:border-blue-500 
                                           focus:ring-2 focus:ring-blue-500/20 transition-all">
                                <option value="12h">12 Hour (AM/PM)</option>
                                <option value="24h">24 Hour</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Section -->
            <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                </h3>

                <div class="space-y-3">
                    <label v-for="notification in notifications" :key="notification.key" class="flex items-center justify-between p-3 bg-gray-900/30 rounded-lg 
                                  hover:bg-gray-900/50 transition-colors cursor-pointer">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-white">{{ notification.title }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ notification.description }}</p>
                        </div>
                        <div class="relative">
                            <input type="checkbox" v-model="notification.enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full 
                                        peer peer-checked:after:translate-x-full peer-checked:after:border-white 
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                        after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all 
                                        peer-checked:bg-blue-600"></div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Privacy & Security Section -->
            <div class="bg-gray-800/50 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Privacy & Security
                </h3>

                <div class="space-y-4">
                    <button class="w-full text-left p-4 bg-gray-900/30 rounded-lg hover:bg-gray-900/50 
                                   transition-colors group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-white">Change Password</p>
                                <p class="text-xs text-gray-400 mt-1">Update your password regularly for security</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-300 transition-colors" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </button>

                    <button class="w-full text-left p-4 bg-gray-900/30 rounded-lg hover:bg-gray-900/50 
                                   transition-colors group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-white">Two-Factor Authentication</p>
                                <p class="text-xs text-gray-400 mt-1">Add an extra layer of security</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-300 transition-colors" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </button>

                    <button @click="exportData" class="w-full text-left p-4 bg-gray-900/30 rounded-lg hover:bg-gray-900/50 
                                   transition-colors group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-white">Export Your Data</p>
                                <p class="text-xs text-gray-400 mt-1">Download all your data in JSON format</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-300 transition-colors" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-900/10 backdrop-blur-xl rounded-2xl p-6 border border-red-900/50">
                <h3 class="text-lg font-semibold text-red-400 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Danger Zone
                </h3>

                <div class="space-y-4">
                    <p class="text-sm text-gray-400">
                        Once you delete your account, there is no going back. Please be certain.
                    </p>

                    <button @click="showDeleteModal = true" class="px-4 py-2 bg-red-600/20 border border-red-600 text-red-400 rounded-lg 
                                   hover:bg-red-600/30 transition-colors duration-200 font-medium">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                <div class="bg-gray-800 rounded-2xl p-6 max-w-md w-full border border-gray-700">
                    <h3 class="text-xl font-bold text-white mb-4">Delete Account</h3>
                    <p class="text-gray-400 mb-6">
                        Are you sure you want to delete your account? This action cannot be undone and all your data
                        will be permanently removed.
                    </p>

                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg 
                                       transition-colors duration-200">
                            Cancel
                        </button>
                        <button @click="deleteAccount" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg 
                                       transition-colors duration-200">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Layout from '@/layouts/Layout.vue'
import { useAppearance } from '@/composables/useAppearance'
import { useTranslations } from '@/composables/useTranslations'
const { __ } = useTranslations()

const props = defineProps({
    user: Object,
    settings: Object
})

const page = usePage()
const showDeleteModal = ref(false)

// Usar o composable de aparência existente
const { appearance, updateAppearance } = useAppearance()

// Forms
const profileForm = reactive({
    name: props.user?.name || '',
    email: props.user?.email || '',
    bio: props.user?.bio || ''
})

const settings = reactive({
    theme: props.settings?.theme || appearance.value || 'dark',
    language: props.settings?.language || 'en',
    dateFormat: props.settings?.dateFormat || 'MM/DD/YYYY',
    timeFormat: props.settings?.timeFormat || '12h'
})

// Sincronizar tema inicial com o valor salvo
onMounted(() => {
    // Se tiver um tema salvo no localStorage, usar ele
    const savedAppearance = localStorage.getItem('appearance')
    if (savedAppearance) {
        settings.theme = savedAppearance
    }
})

// Theme options
const themes = [
    {
        value: 'light',
        label: 'Light',
        icon: {
            template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
        }
    },
    {
        value: 'dark',
        label: 'Dark',
        icon: {
            template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>'
        }
    },
    {
        value: 'system',
        label: 'System',
        icon: {
            template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
        }
    }
]

// Notifications
const notifications = reactive([
    {
        key: 'task_reminders',
        title: 'Task Reminders',
        description: 'Get notified about upcoming tasks',
        enabled: true
    },
    {
        key: 'timebox_alerts',
        title: 'Time Box Alerts',
        description: 'Alerts when time boxes are starting or ending',
        enabled: true
    },
    {
        key: 'weekly_summary',
        title: 'Weekly Summary',
        description: 'Receive a weekly summary of your productivity',
        enabled: false
    },
    {
        key: 'achievement_badges',
        title: 'Achievement Badges',
        description: 'Celebrate your productivity milestones',
        enabled: true
    }
])

// Methods
const updateProfile = () => {
    router.put(route('app-settings.profile'), profileForm, {
        preserveScroll: true,
        onSuccess: () => {
            // Show success toast or message
        }
    })
}

const updateSettings = () => {
    router.post(route('app-settings.update'), settings, {
        preserveScroll: true
    })
}

const updateSetting = (key, value) => {
    settings[key] = value

    // Se for tema, aplicar imediatamente usando o composable existente
    if (key === 'theme') {
        updateAppearance(value)
    }

    updateSettings()
}

const changeLanguage = () => {
    router.post(route('app-settings.language'), { language: settings.language }, {
        preserveScroll: true,
        onSuccess: () => {
            // A página será recarregada automaticamente pelo redirect do controller
        }
    })
}

const exportData = () => {
    window.location.href = route('app-settings.export')
}

const deleteAccount = () => {
    router.delete(route('app-settings.delete-account'), {
        onSuccess: () => {
            window.location.href = '/'
        }
    })
}
</script>