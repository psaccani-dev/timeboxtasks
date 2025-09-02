<template>
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-800/30">
            <div
                class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center">
                <CheckSquare class="w-4 h-4 text-slate-900" />
            </div>
            <span
                class="text-lg font-semibold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
                TaskFlow
            </span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2">
            <Link v-for="item in navigation" :key="item.name" :href="item.href" :class="[
                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                isActive(item.href)
                    ? 'bg-gradient-to-r from-green-400/20 to-emerald-400/20 text-green-400 border border-green-400/30 shadow-lg shadow-green-400/10'
                    : 'text-slate-300 hover:text-green-400 hover:bg-slate-800/50'
            ]" @click="emit('close')">
            <component :is="item.icon" :class="[
                'w-5 h-5 transition-colors',
                isActive(item.href) ? 'text-green-400' : 'text-slate-400'
            ]" />
            <span>{{ item.name }}</span>
            <Badge v-if="item.badge" variant="secondary"
                class="ml-auto bg-orange-400/20 text-orange-400 border-orange-400/30">
                {{ item.badge }}
            </Badge>
            </Link>
        </nav>

        <!-- Quick Actions -->
        <div class="px-4 py-4 border-t border-slate-800/30">
            <button @click="handleNewTask"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 transition-all duration-200 shadow-lg shadow-purple-500/20">
                <Plus class="w-4 h-4" />
                New Task
            </button>
        </div>

        <!-- Stats -->
        <div class="px-4 py-4 border-t border-slate-800/30">
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Today's Progress</span>
                    <span class="text-green-400 font-medium">8/12</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2">
                    <div
                        class="bg-gradient-to-r from-green-400 to-emerald-400 h-2 rounded-full w-2/3 transition-all duration-500">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
    Home,
    CheckSquare,
    Clock,
    Calendar,
    Plus,
    Settings,
    BarChart3
} from 'lucide-vue-next'
import { Badge } from 'lucide-vue-next'

// IMPORTANTE: Capturar a função emit retornada pelo defineEmits
const emit = defineEmits(['close', 'new-task'])

const page = usePage()

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: Home },
    { name: 'Tasks', href: '/tasks', icon: CheckSquare, badge: '4' },
    { name: 'Time Boxes', href: '/time-boxes', icon: Clock },
    { name: 'Calendar', href: '/calendar', icon: Calendar },
    { name: 'Analytics', href: '/analytics', icon: BarChart3 },
    { name: 'Settings', href: '/settings', icon: Settings },
]

const isActive = (href) => {
    return page.url.startsWith(href)
}

// Função corrigida - usando a função emit
const handleNewTask = () => {
    emit('new-task')
    emit('close') // Fechar o sidebar se estiver em mobile
}
</script>