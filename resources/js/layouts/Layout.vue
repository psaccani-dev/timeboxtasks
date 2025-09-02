<template>
    <div class="min-h-screen bg-slate-950">
        <!-- Sidebar for desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div
                class="flex grow flex-col gap-y-5 overflow-y-auto bg-slate-900/50 border-r border-slate-800/30 backdrop-blur-xl">
                <SidebarContent @new-task="handleNewTaskFromSidebar" />
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div v-if="sidebarOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="sidebarOpen = false"></div>
            <div class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900/95 border-r border-slate-800/30 backdrop-blur-xl">
                <SidebarContent @close="sidebarOpen = false" @new-task="handleNewTaskFromSidebar" />
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:pl-72">
            <!-- Top bar -->
            <div
                class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-slate-800/30 bg-slate-900/50 px-4 backdrop-blur-xl sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-slate-300 lg:hidden" @click="sidebarOpen = true">
                    <Menu class="h-6 w-6" />
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1"></div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- User menu -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 transition-colors">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                        <span class="text-sm font-medium text-slate-900">{{ userInitials }}</span>
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <p class="text-sm font-medium text-slate-200">{{ $page.props.auth.user.name }}
                                        </p>
                                        <p class="text-xs text-slate-400">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56 bg-slate-900 border-slate-700">
                                <DropdownMenuItem as-child>
                                    <Link href="/profile" class="flex w-full">
                                    <User class="mr-2 h-4 w-4" />
                                    Profile
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <Link href="/settings" class="flex w-full">
                                    <Settings class="mr-2 h-4 w-4" />
                                    Settings
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="bg-slate-700" />
                                <DropdownMenuItem as-child>
                                    <Link href="/logout" method="post" class="flex w-full text-red-400">
                                    <LogOut class="mr-2 h-4 w-4" />
                                    Sign out
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="py-10">
                <div class="px-4 sm:px-6 lg:px-8">

                    <Head :title="title" />
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import SidebarContent from '@/components/SidebarContent.vue'
import {
    Menu,
    User,
    Settings,
    LogOut,
} from 'lucide-vue-next'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

defineProps({
    title: String
})

// IMPORTANTE: Capturar a função emit
const emit = defineEmits(['open-task-modal'])

const page = usePage()
const sidebarOpen = ref(false)

const userInitials = computed(() => {
    const name = page.props.auth.user.name
    return name.split(' ').map(word => word[0]).join('').toUpperCase()
})

// Função corrigida - usando a função emit
const handleNewTaskFromSidebar = () => {
    emit('open-task-modal')
    sidebarOpen.value = false // Fechar sidebar mobile se aberto
}
</script>