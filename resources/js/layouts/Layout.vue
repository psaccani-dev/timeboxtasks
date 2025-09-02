<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
    Menu,
    Search,
    Bell,
    User,
    Settings,
    LogOut,
    CheckSquare
} from 'lucide-vue-next'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import SidebarContent from '@/components/SidebarContent.vue'

defineProps({
    title: {
        type: String,
        default: 'Task Manager'
    }
})

const sidebarOpen = ref(false)
</script>



<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <!-- Sidebar Mobile Overlay -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-black/50" @click="sidebarOpen = false" />
            <div class="fixed inset-y-0 left-0 w-64 bg-slate-900 border-r border-slate-800/50">
                <SidebarContent @close="sidebarOpen = false" />
            </div>
        </div>

        <!-- Desktop Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
            <div class="flex grow flex-col bg-slate-900/50 border-r border-slate-800/30 backdrop-blur-xl">
                <SidebarContent />
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:pl-64">
            <!-- Header -->
            <header class="sticky top-0 z-30 bg-slate-950/80 backdrop-blur-xl border-b border-slate-800/30">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true"
                            class="lg:hidden p-2 rounded-lg hover:bg-slate-800/50 transition-colors">
                            <Menu class="w-5 h-5" />
                        </button>

                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center">
                                <CheckSquare class="w-4 h-4 text-slate-900" />
                            </div>
                            <h1
                                class="text-lg font-semibold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
                                {{ title }}
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Search -->
                        <div class="relative hidden sm:block">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input type="text" placeholder="Search..."
                                class="w-64 pl-10 pr-4 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50" />
                        </div>

                        <!-- Notifications -->
                        <button class="p-2 rounded-lg hover:bg-slate-800/50 transition-colors relative">
                            <Bell class="w-5 h-5" />
                            <span
                                class="absolute -top-1 -right-1 w-3 h-3 bg-orange-400 rounded-full animate-pulse"></span>
                        </button>

                        <!-- User Menu -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-800/50 transition-colors">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                        <User class="w-4 h-4 text-white" />
                                    </div>
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 bg-slate-900 border-slate-700">
                                <DropdownMenuLabel>{{ $page.props.auth.user.name }}</DropdownMenuLabel>
                                <DropdownMenuSeparator class="bg-slate-700" />
                                <DropdownMenuItem>
                                    <Settings class="mr-2 h-4 w-4" />
                                    Settings
                                </DropdownMenuItem>
                                <DropdownMenuItem>
                                    <Link :href="route('logout')" method="post" as="button"
                                        class="flex items-center w-full">
                                    <LogOut class="mr-2 h-4 w-4" />
                                    Logout
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-800/30 bg-slate-900/20 backdrop-blur-xl">
                <div class="px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-6 h-6 rounded bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center">
                                <CheckSquare class="w-3 h-3 text-slate-900" />
                            </div>
                            <span class="text-sm text-slate-400">Task Manager MVP</span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-slate-500">
                            <span> Made with 🧙‍♀️</span>
                            <span>Laravel + Vue + Inertia</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>
