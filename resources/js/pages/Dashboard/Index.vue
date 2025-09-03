<template>
    <Layout title="Dashboard">
        <div class="p-6 space-y-6">
            <!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-3xl font-bold text-slate-200">
                        Welcome back, {{ userName }}!
                    </h2>
                    <p class="text-slate-400">
                        {{ currentDateFormatted }} • {{ motivationalQuote }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button @click="openQuickTask"
                        class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600">
                        <Plus class="w-4 h-4 mr-2" />
                        Quick Task
                    </Button>
                    <Button @click="openQuickTimeBox"
                        class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600">
                        <Clock class="w-4 h-4 mr-2" />
                        Time Box
                    </Button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Today's Progress -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-400/10 to-emerald-400/10"></div>
                    <CardContent class="p-6 relative">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Today's Progress</p>
                                <div class="flex items-baseline gap-2 mt-2">
                                    <p class="text-3xl font-bold text-green-400">{{ stats.todayCompleted }}</p>
                                    <span class="text-slate-400 text-sm">/ {{ stats.todayTotal }}</span>
                                </div>
                                <div class="mt-3 w-full bg-slate-800 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-400 to-emerald-400 h-2 rounded-full transition-all duration-500"
                                        :style="`width: ${stats.todayProgress}%`"></div>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-green-400/20 flex items-center justify-center">
                                <TrendingUp class="w-5 h-5 text-green-400" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Active Time Box -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-400/10 to-cyan-400/10"></div>
                    <CardContent class="p-6 relative">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Current Focus</p>
                                <div v-if="activeTimeBox" class="mt-2">
                                    <p class="text-lg font-semibold text-blue-400 truncate">{{ activeTimeBox.title }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="flex-1 bg-slate-800 rounded-full h-1.5">
                                            <div class="bg-gradient-to-r from-blue-400 to-cyan-400 h-1.5 rounded-full transition-all duration-1000"
                                                :style="`width: ${activeTimeBox.progress}%`"></div>
                                        </div>
                                        <span class="text-xs text-slate-500">{{ activeTimeBox.remaining }}</span>
                                    </div>
                                </div>
                                <div v-else class="mt-2">
                                    <p class="text-sm text-slate-500">No active session</p>
                                    <button class="text-xs text-blue-400 hover:text-blue-300 mt-1">Start one →</button>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-blue-400/20 flex items-center justify-center">
                                <Timer class="w-5 h-5 text-blue-400" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Week Streak -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400/10 to-pink-400/10"></div>
                    <CardContent class="p-6 relative">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Week Streak</p>
                                <p class="text-3xl font-bold text-purple-400 mt-2">{{ stats.weekStreak }}</p>
                                <div class="flex gap-1 mt-3">
                                    <div v-for="day in weekDays" :key="day.name" :class="[
                                        'w-6 h-6 rounded flex items-center justify-center text-xs font-medium',
                                        day.completed
                                            ? 'bg-purple-400/20 text-purple-400 border border-purple-400/30'
                                            : 'bg-slate-800 text-slate-600'
                                    ]">
                                        {{ day.name[0] }}
                                    </div>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-purple-400/20 flex items-center justify-center">
                                <Flame class="w-5 h-5 text-purple-400" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Productivity Score -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-400/10 to-yellow-400/10"></div>
                    <CardContent class="p-6 relative">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Productivity</p>
                                <p class="text-3xl font-bold text-orange-400 mt-2">{{ stats.productivityScore }}%</p>
                                <p class="text-xs text-slate-500 mt-2">
                                    {{ stats.productivityTrend > 0 ? '↑' : '↓' }}
                                    {{ Math.abs(stats.productivityTrend) }}% from last week
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-orange-400/20 flex items-center justify-center">
                                <Zap class="w-5 h-5 text-orange-400" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - 2/3 width -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Today's Schedule -->
                    <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                        <CardHeader class="border-b border-slate-800/30">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-200">Today's Schedule</h3>
                                <Link href="/time-boxes" class="text-sm text-blue-400 hover:text-blue-300">
                                View all →
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent class="p-4">
                            <div class="relative">
                                <!-- Time line -->
                                <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-slate-800"></div>

                                <!-- Schedule items -->
                                <div class="space-y-4">
                                    <div v-for="item in todaySchedule" :key="item.id" class="flex gap-4 relative">
                                        <div :class="[
                                            'w-16 text-xs font-medium text-right',
                                            item.isNow ? 'text-blue-400' : 'text-slate-500'
                                        ]">
                                            {{ item.time }}
                                        </div>
                                        <div :class="[
                                            'w-3 h-3 rounded-full border-2 bg-slate-900',
                                            item.isNow ? 'border-blue-400' : 'border-slate-700'
                                        ]"></div>
                                        <div class="flex-1 -mt-1">
                                            <div :class="[
                                                'p-3 rounded-lg transition-all',
                                                item.isNow ? 'bg-blue-400/10 border border-blue-400/30' : 'bg-slate-800/30'
                                            ]">
                                                <div class="flex items-start justify-between">
                                                    <div>
                                                        <h4 class="font-medium text-slate-200">{{ item.title }}</h4>
                                                        <p class="text-xs text-slate-400 mt-1">{{ item.duration }}
                                                            minutes</p>
                                                    </div>
                                                    <Badge :class="getTypeBadgeClass(item.type)">
                                                        {{ item.type }}
                                                    </Badge>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty state -->
                                <div v-if="todaySchedule.length === 0" class="text-center py-8">
                                    <Calendar class="w-12 h-12 text-slate-700 mx-auto mb-3" />
                                    <p class="text-slate-500">No time boxes scheduled for today</p>
                                    <Button @click="openQuickTimeBox" size="sm" class="mt-3">
                                        Schedule your first session
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Activity Chart -->
                    <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                        <CardHeader class="border-b border-slate-800/30">
                            <h3 class="text-lg font-semibold text-slate-200">Weekly Activity</h3>
                        </CardHeader>
                        <CardContent class="p-4">
                            <div class="h-48">
                                <!-- Simple bar chart -->
                                <div class="flex items-end justify-between h-full gap-2">
                                    <div v-for="day in activityData" :key="day.date"
                                        class="flex-1 flex flex-col items-center justify-end gap-2">
                                        <div class="w-full bg-slate-800 rounded-t relative flex flex-col justify-end"
                                            :style="`height: ${day.percentage}%`">
                                            <div
                                                class="absolute -top-6 left-1/2 transform -translate-x-1/2 text-xs text-slate-400">
                                                {{ day.hours }}h
                                            </div>
                                            <div class="bg-gradient-to-t from-blue-400 to-cyan-400 rounded-t w-full"
                                                :style="`height: ${(day.completed / day.total) * 100}%`">
                                            </div>
                                        </div>
                                        <span class="text-xs text-slate-500">{{ day.label }}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column - 1/3 width -->
                <div class="space-y-6">
                    <!-- Upcoming Tasks -->
                    <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                        <CardHeader class="border-b border-slate-800/30">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-200">Upcoming Tasks</h3>
                                <Link href="/tasks" class="text-sm text-green-400 hover:text-green-300">
                                View all →
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent class="p-4">
                            <div class="space-y-3">
                                <div v-for="task in upcomingTasks" :key="task.id" class="flex items-start gap-3">
                                    <Checkbox :checked="task.status === 'done'" @click="toggleTask(task.id)"
                                        class="mt-0.5" />
                                    <div class="flex-1 min-w-0">
                                        <p :class="[
                                            'text-sm font-medium',
                                            task.status === 'done' ? 'line-through text-slate-500' : 'text-slate-200'
                                        ]">
                                            {{ task.title }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <Badge :class="getPriorityBadgeClass(task.priority)" class="text-xs">
                                                {{ task.priority }}
                                            </Badge>
                                            <span v-if="task.due_date" class="text-xs text-slate-500">
                                                {{ formatDueDate(task.due_date) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty state -->
                                <div v-if="upcomingTasks.length === 0" class="text-center py-6">
                                    <CheckCircle2 class="w-10 h-10 text-green-400/20 mx-auto mb-2" />
                                    <p class="text-sm text-slate-500">All caught up!</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Stats -->
                    <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                        <CardHeader class="border-b border-slate-800/30">
                            <h3 class="text-lg font-semibold text-slate-200">This Week</h3>
                        </CardHeader>
                        <CardContent class="p-4">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-400">Tasks Completed</span>
                                    <span class="text-sm font-semibold text-green-400">{{ weekStats.tasksCompleted
                                        }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-400">Focus Hours</span>
                                    <span class="text-sm font-semibold text-blue-400">{{ weekStats.focusHours }}h</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-400">Time Boxes</span>
                                    <span class="text-sm font-semibold text-purple-400">{{ weekStats.timeBoxes }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-400">Avg Productivity</span>
                                    <span class="text-sm font-semibold text-orange-400">{{ weekStats.avgProductivity
                                        }}%</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Quick Task Modal -->
        <TaskFormModal :is-open="showTaskModal" :task="null" :errors="{}" :processing="false"
            @close="showTaskModal = false" @submit="handleQuickTaskSubmit" />

        <!-- Quick Time Box Modal -->
        <TimeBoxFormModal :is-open="showTimeBoxModal" :time-box="null" :available-tasks="[]" :errors="{}"
            :processing="false" @close="showTimeBoxModal = false" @submit="handleQuickTimeBoxSubmit" />
    </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import Layout from '@/layouts/Layout.vue'
import TaskFormModal from '@/components/TaskFormModal.vue'
import TimeBoxFormModal from '@/components/TimeBoxFormModal.vue'
import {
    Plus,
    Clock,
    Calendar,
    TrendingUp,
    Timer,
    Flame,
    Zap,
    CheckCircle2
} from 'lucide-vue-next'
import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card'
import {
    Button,
} from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { useTranslations } from '@/composables/useTranslations'
const { __ } = useTranslations()

const props = defineProps({
    user: Object,
    stats: Object,
    todaySchedule: Array,
    upcomingTasks: Array,
    activeTimeBox: Object,
    weekStats: Object,
    activityData: Array
})

// State
const showTaskModal = ref(false)
const showTimeBoxModal = ref(false)

// Computed
const userName = computed(() => props.user?.name?.split(' ')[0] || 'User')

const currentDateFormatted = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric'
    })
})

const motivationalQuote = computed(() => {
    const quotes = [
        "Let's make today productive!",
        "Focus on what matters most",
        "One task at a time",
        "You've got this!",
        "Stay focused, stay strong"
    ]
    return quotes[Math.floor(Math.random() * quotes.length)]
})

const weekDays = computed(() => {
    return [
        { name: 'Mon', completed: true },
        { name: 'Tue', completed: true },
        { name: 'Wed', completed: true },
        { name: 'Thu', completed: false },
        { name: 'Fri', completed: false },
        { name: 'Sat', completed: false },
        { name: 'Sun', completed: false }
    ]
})

// Methods
const getTypeBadgeClass = (type) => {
    const classes = {
        'focus': 'bg-blue-400/20 text-blue-400',
        'meeting': 'bg-purple-400/20 text-purple-400',
        'break': 'bg-green-400/20 text-green-400',
        'work': 'bg-orange-400/20 text-orange-400'
    }
    return classes[type] || 'bg-slate-700 text-slate-300'
}

const getPriorityBadgeClass = (priority) => {
    const classes = {
        'urgent': 'bg-red-400/20 text-red-400',
        'high': 'bg-orange-400/20 text-orange-400',
        'medium': 'bg-yellow-400/20 text-yellow-400',
        'low': 'bg-slate-600/20 text-slate-400'
    }
    return classes[priority] || 'bg-slate-700 text-slate-300'
}

const formatDueDate = (date) => {
    const d = new Date(date)
    const today = new Date()
    const tomorrow = new Date(today)
    tomorrow.setDate(tomorrow.getDate() + 1)

    if (d.toDateString() === today.toDateString()) return 'Today'
    if (d.toDateString() === tomorrow.toDateString()) return 'Tomorrow'

    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const openQuickTask = () => {
    showTaskModal.value = true
}

const openQuickTimeBox = () => {
    showTimeBoxModal.value = true
}

const handleQuickTaskSubmit = ({ data }) => {
    router.post(route('tasks.store'), data, {
        onSuccess: () => {
            showTaskModal.value = false
        }
    })
}

const handleQuickTimeBoxSubmit = ({ data }) => {
    router.post(route('time-boxes.store'), data, {
        onSuccess: () => {
            showTimeBoxModal.value = false
        }
    })
}

const toggleTask = (taskId) => {
    router.patch(route('tasks.status', taskId), {
        status: 'done'
    })
}
</script>