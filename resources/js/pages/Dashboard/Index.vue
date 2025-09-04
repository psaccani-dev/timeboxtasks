<template>
    <Layout title="Dashboard">
        <div class="p-6 space-y-6">
            <!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-3">
                    <h2 class="text-3xl font-bold text-slate-200">
                        Welcome back, {{ userName }}!
                    </h2>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-slate-400">{{ currentDateFormatted }}</span>
                        <div
                            :class="['px-4 py-1.5 bg-gradient-to-r from-blue-400/20 to-cyan-400/20 rounded-full border border-blue-400/30', motivationalColorClass.bg, motivationalColorClass.border]">
                            <p :class="['text-sm font-medium italic', motivationalColorClass.text]">
                                {{ motivationalQuote }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button @click="openQuickTask"
                        class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600">
                        <Plus class="w-4 h-4 mr-2" />
                        Quick Task
                    </Button>
                    <Button @click="openCreateModal"
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
                                    <button @click="openCreateModal"
                                        class="text-xs text-blue-400 hover:text-blue-300 mt-1">Start one →</button>
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
                                            <div @click.stop="openEditTimeBox(item)" :class="[
                                                'p-3 rounded-lg transition-all cursor-pointer hover:opacity-80',
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
                                    <Button @click="openCreateModal" size="sm" class="mt-3">
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
                                        class="flex-1 flex flex-col items-center justify-end gap-2 h-full">
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

        <!-- Time Box Form Modal -->
        <TimeBoxFormModal :is-open="showTimeBoxModal" :time-box="editingTimeBox" :available-tasks="availableTasks"
            :errors="formErrors" :processing="formProcessing" @close="closeTimeBoxModal"
            @submit="handleTimeBoxSubmit" />
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

import { onMounted } from 'vue'

// ... resto do código ...

onMounted(() => {
    console.log('=== Activity Data Debug ===')
    if (props.activityData) {
        props.activityData.forEach(day => {
            console.log(`${day.label} (${day.date}):`, {
                hours: day.hours,
                tasks: `${day.completed}/${day.total}`,
                percentage: day.percentage,
                hasTimeBoxes: day.hours > 0,
                hasTasks: day.total > 0
            })
        })
    } else {
        console.log('Nenhum activityData recebido')
    }
})






const { __ } = useTranslations()

const props = defineProps({
    user: Object,
    stats: Object,
    todaySchedule: Array,
    upcomingTasks: Array,
    activeTimeBox: Object,
    weekStats: Object,
    activityData: Array,
    availableTasks: {
        type: Array,
        default: () => []
    }
})

// State
const showTaskModal = ref(false)
const showTimeBoxModal = ref(false)
const editingTimeBox = ref(null)
const formErrors = ref({})
const formProcessing = ref(false)

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
        "He who has a why to live can bear almost any how. – Friedrich Nietzsche",
        "What we think, we become. – Buddha",
        "Do not wait for extraordinary circumstances to do good; try to use ordinary situations. – Jean Paul Richter",
        "Act only according to that maxim whereby you can, at the same time, will that it should become a universal law. – Immanuel Kant",
        "Man is nothing else but what he makes of himself. – Jean-Paul Sartre",
        "The only way to deal with the future is to function efficiently in the now. – Alan Watts",
        "Knowing yourself is the beginning of all wisdom. – Aristotle",
        "All men dream, but not equally. – T.E. Lawrence",
        "You are the sky. Everything else is just the weather. – Pema Chödrön",
        "Be like a tree: stay grounded, keep growing, and let your branches reach for the sky.",
        "What lies behind us and what lies before us are tiny matters compared to what lies within us. – Ralph Waldo Emerson",
        "An unexamined life is not worth living. – Socrates",
        "The journey of a thousand miles begins with a single step. – Lao Tzu",
        "We are what we repeatedly do. Excellence, then, is not an act, but a habit. – Aristotle",
        "Courage is not the absence of fear, but the triumph over it. – Nelson Mandela",
        "Stars can't shine without darkness.",
        "Out of difficulties grow miracles. – Jean de La Bruyère",
        "The wound is the place where the Light enters you. – Rumi",
        "The only true wisdom is in knowing you know nothing. – Socrates",
        "Time is the wisest counselor of all. – Pericles",
        "Great minds discuss ideas; average minds discuss events; small minds discuss people. – Eleanor Roosevelt",
        "Happiness depends upon ourselves. – Aristotle",
        "In the middle of every difficulty lies opportunity. – Albert Einstein",
        "Do what you can, with what you have, where you are. – Theodore Roosevelt",
        "Rise above the storm and you will find the sunshine. – Mario Fernández",
        "The soul becomes dyed with the color of its thoughts. – Marcus Aurelius",
        "The man who moves a mountain begins by carrying away small stones. – Confucius",
        "To improve is to change; to be perfect is to change often. – Winston Churchill",
        "Fall seven times and stand up eight. – Japanese Proverb",
        "The secret of getting ahead is getting started. – Mark Twain",
        "The best way out is always through. – Robert Frost",
        "Do not go where the path may lead, go instead where there is no path and leave a trail. – Ralph Waldo Emerson",
        "Life is not measured by the number of breaths we take, but by the moments that take our breath away. – Maya Angelou",
        "Everything you can imagine is real. – Pablo Picasso",
        "Energy and persistence conquer all things. – Benjamin Franklin",
        "Your present circumstances don't determine where you can go; they merely determine where you start. – Nido Qubein",
        "A ship is safe in harbor, but that's not what ships are built for. – John A. Shedd",
        "The best dreams happen when you're awake. – Cherie Gilderbloom",
        "Life is either a daring adventure or nothing at all. – Helen Keller",
        "What you seek is seeking you. – Rumi",
        "Hope is a waking dream. – Aristotle",
        "Everything has beauty, but not everyone sees it. – Confucius",
        "Go confidently in the direction of your dreams. Live the life you have imagined. – Henry David Thoreau",
        "Do not pray for an easy life, pray for the strength to endure a difficult one. – Bruce Lee",
        "Turn your wounds into wisdom. – Oprah Winfrey",
        "Every strike brings me closer to the next home run. – Babe Ruth",
        "Even the darkest night will end and the sun will rise. – Victor Hugo",
        "Arise, awake, and stop not till the goal is reached. – Swami Vivekananda",
        "Dream big and dare to fail. – Norman Vaughan",
        "Be the change that you wish to see in the world. – Mahatma Gandhi"
    ]
    return quotes[Math.floor(Math.random() * quotes.length)]
})


const motivationalColorClass = computed(() => {
    const hour = new Date().getHours()
    const isOrangeHour = hour % 2 === 0 // horas pares = laranja, ímpares = roxo

    if (isOrangeHour) {
        return {
            gradient: 'from-orange-400 to-yellow-400',
            text: 'text-orange-300',
            bg: 'from-orange-400/20 to-yellow-400/20',
            border: 'border-orange-400/30'
        }
    } else {
        return {
            gradient: 'from-purple-400 to-pink-400',
            text: 'text-purple-300',
            bg: 'from-purple-400/20 to-pink-400/20',
            border: 'border-purple-400/30'
        }
    }
})

const weekDays = computed(() => {
    return props.stats?.weekDays || [
        { name: 'Mon', completed: false },
        { name: 'Tue', completed: false },
        { name: 'Wed', completed: false },
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

// Modal methods
const openCreateModal = () => {
    editingTimeBox.value = null
    showTimeBoxModal.value = true
}

const openEditTimeBox = (item) => {
    // Agora que o todaySchedule vem completo do backend, 
    // não precisa mais converter nada!
    editingTimeBox.value = item
    showTimeBoxModal.value = true
}

const closeTimeBoxModal = () => {
    showTimeBoxModal.value = false
    editingTimeBox.value = null
    formErrors.value = {}
}

const handleQuickTaskSubmit = ({ data }) => {
    router.post(route('tasks.store'), data, {
        onSuccess: () => {
            showTaskModal.value = false
        }
    })
}

const handleTimeBoxSubmit = ({ data, isEditing, timeBoxId }) => {
    formProcessing.value = true

    if (isEditing) {
        router.put(route('time-boxes.update', timeBoxId), data, {
            onSuccess: () => {
                closeTimeBoxModal()
                // Reload to update the schedule
                router.reload({ only: ['todaySchedule'] })
            },
            onError: (errors) => {
                formErrors.value = errors
            },
            onFinish: () => {
                formProcessing.value = false
            }
        })
    } else {
        router.post(route('time-boxes.store'), data, {
            onSuccess: () => {
                closeTimeBoxModal()
                // Reload to update the schedule
                router.reload({ only: ['todaySchedule'] })
            },
            onError: (errors) => {
                formErrors.value = errors
            },
            onFinish: () => {
                formProcessing.value = false
            }
        })
    }
}

const toggleTask = (taskId) => {
    router.patch(route('tasks.status', taskId), {
        status: 'done'
    })
}
</script>