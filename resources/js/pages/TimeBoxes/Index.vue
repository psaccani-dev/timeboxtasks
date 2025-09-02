<template>
    <Layout title="Time Boxes">
        <div class="p-6 space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2
                        class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                        Time Boxes
                    </h2>
                    <p class="text-slate-400 text-sm">
                        Schedule your work into focused time blocks
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- View Switcher -->
                    <div class="flex rounded-lg bg-slate-800/50 p-1">
                        <button @click="viewMode = 'day'" :class="[
                            'px-3 py-1.5 rounded text-sm font-medium transition-all',
                            viewMode === 'day'
                                ? 'bg-slate-700 text-white'
                                : 'text-slate-400 hover:text-white'
                        ]">
                            Day
                        </button>
                        <button @click="viewMode = 'week'" :class="[
                            'px-3 py-1.5 rounded text-sm font-medium transition-all',
                            viewMode === 'week'
                                ? 'bg-slate-700 text-white'
                                : 'text-slate-400 hover:text-white'
                        ]">
                            Week
                        </button>
                        <button @click="viewMode = 'list'" :class="[
                            'px-3 py-1.5 rounded text-sm font-medium transition-all',
                            viewMode === 'list'
                                ? 'bg-slate-700 text-white'
                                : 'text-slate-400 hover:text-white'
                        ]">
                            List
                        </button>
                    </div>

                    <!-- Date Navigation -->
                    <div class="flex items-center gap-2">
                        <Button @click="navigateDate('prev')" variant="outline" size="sm"
                            class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                            <ChevronLeft class="w-4 h-4" />
                        </Button>

                        <Button @click="navigateDate('today')" variant="outline" size="sm"
                            class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                            {{ formatDateRange() }}
                        </Button>


                        <Button @click="navigateDate('next')" variant="outline" size="sm"
                            class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                            <ChevronRight class="w-4 h-4" />
                        </Button>
                    </div>

                    <Button @click="openCreateModal"
                        class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600">
                        <Plus class="w-4 h-4 mr-2" />
                        New Time Box
                    </Button>
                </div>
            </div>

            <!-- Current Date Display -->
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-200">
                    {{ formatDateRange() }}
                </h3>

                <div class="flex items-center gap-4 text-sm text-slate-400">
                    <span class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                        Focus
                    </span>
                    <span class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        Break
                    </span>
                    <span class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-purple-400"></div>
                        Meeting
                    </span>
                    <span class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-orange-400"></div>
                        Personal
                    </span>
                </div>
            </div>

            <!-- Calendar/List View -->
            <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                <CardContent class="p-6">
                    <!-- Week View -->
                    <div v-if="viewMode === 'week'" class="space-y-6">
                        <div class="grid grid-cols-8 gap-4">
                            <!-- Time Labels -->
                            <div class="space-y-12 pt-12">
                                <div v-for="hour in hours" :key="hour"
                                    class="h-12 text-xs text-slate-500 text-right pr-2">
                                    {{ formatHour(hour) }}
                                </div>
                            </div>

                            <!-- Days -->
                            <div v-for="day in weekDays" :key="day.date" class="relative">
                                <div
                                    class="sticky top-0 bg-slate-900/95 backdrop-blur z-10 pb-3 mb-3 border-b border-slate-800">
                                    <div class="text-center">
                                        <div class="text-xs text-slate-400">{{ day.dayName }}</div>
                                        <div :class="[
                                            'text-lg font-semibold',
                                            isToday(day.date) ? 'text-blue-400' : 'text-slate-200'
                                        ]">{{ day.dayNumber }}</div>
                                    </div>
                                </div>

                                <!-- Hour Grid -->
                                <div class="relative">
                                    <div v-for="hour in hours" :key="hour" class="h-12 border-t border-slate-800/30">
                                    </div>

                                    <!-- Time Boxes for this day -->
                                    <div v-for="timeBox in getTimeBoxesForDay(day.date)" :key="timeBox.id"
                                        :style="getTimeBoxStyle(timeBox)" @click="openEditModal(timeBox)" :class="[
                                            'absolute left-0 right-0 p-2 rounded-lg cursor-pointer transition-all hover:shadow-lg hover:scale-[1.02] border',
                                            getTimeBoxClasses(timeBox)
                                        ]">
                                        <div class="text-xs font-medium truncate">{{ timeBox.title }}</div>
                                        <div class="text-xs opacity-75">
                                            {{ formatTime(timeBox.start_at) }} - {{ formatTime(timeBox.end_at) }}
                                        </div>
                                        <div v-if="timeBox.tasks.length" class="mt-1">
                                            <Badge variant="secondary" class="text-xs px-1 py-0">
                                                {{ timeBox.tasks.length }} task(s)
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Day View -->
                    <div v-else-if="viewMode === 'day'" class="space-y-6">
                        <div class="grid grid-cols-[100px,1fr] gap-4">
                            <!-- Time Labels -->
                            <div class="space-y-12 pt-4">
                                <div v-for="hour in hours" :key="hour"
                                    class="h-12 text-sm text-slate-400 text-right pr-4">
                                    {{ formatHour(hour) }}
                                </div>
                            </div>

                            <!-- Day Schedule -->
                            <div class="relative">
                                <!-- Hour Grid -->
                                <div v-for="hour in hours" :key="hour" class="h-12 border-t border-slate-800/30">
                                </div>

                                <!-- Time Boxes -->
                                <div v-for="timeBox in todayTimeBoxes" :key="timeBox.id"
                                    :style="getTimeBoxStyle(timeBox)" @click="openEditModal(timeBox)" :class="[
                                        'absolute left-0 right-0 p-3 rounded-lg cursor-pointer transition-all hover:shadow-lg hover:scale-[1.01] border',
                                        getTimeBoxClasses(timeBox)
                                    ]">
                                    <div class="font-medium">{{ timeBox.title }}</div>
                                    <div class="text-sm opacity-75 mt-1">
                                        {{ formatTime(timeBox.start_at) }} - {{ formatTime(timeBox.end_at) }}
                                        ({{ timeBox.duration_minutes }} min)
                                    </div>
                                    <div v-if="timeBox.notes" class="text-sm mt-2 opacity-60">
                                        {{ timeBox.notes }}
                                    </div>
                                    <div v-if="timeBox.tasks.length" class="mt-2 space-y-1">
                                        <div v-for="task in timeBox.tasks" :key="task.id"
                                            class="text-xs bg-slate-800/50 rounded px-2 py-1">
                                            {{ task.title }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List View -->
                    <div v-else class="space-y-4">
                        <div v-if="sortedTimeBoxes.length === 0" class="text-center py-12">
                            <Clock class="w-12 h-12 text-slate-600 mx-auto mb-4" />
                            <p class="text-slate-400">No time boxes scheduled for this period</p>
                        </div>

                        <div v-for="timeBox in sortedTimeBoxes" :key="timeBox.id" @click="openEditModal(timeBox)"
                            :class="[
                                'p-4 rounded-lg border cursor-pointer transition-all hover:shadow-lg',
                                timeBox.is_past ? 'opacity-50' : '',
                                timeBox.is_active ? 'ring-2 ring-green-400/50' : '',
                                getTimeBoxListClasses(timeBox)
                            ]">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h4 class="font-medium text-slate-200">{{ timeBox.title }}</h4>
                                        <Badge :class="getTypeBadgeClasses(timeBox.type)">
                                            {{ formatType(timeBox.type) }}
                                        </Badge>
                                        <Badge v-if="timeBox.is_active"
                                            class="bg-green-400/20 text-green-400 border-green-400/30">
                                            Active Now
                                        </Badge>
                                    </div>

                                    <div class="flex items-center gap-4 mt-2 text-sm text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <Calendar class="w-4 h-4" />
                                            {{ formatDate(timeBox.start_at) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <Clock class="w-4 h-4" />
                                            {{ formatTime(timeBox.start_at) }} - {{ formatTime(timeBox.end_at) }}
                                        </span>
                                        <span>
                                            {{ timeBox.duration_minutes }} minutes
                                        </span>
                                    </div>

                                    <p v-if="timeBox.notes" class="text-sm text-slate-500 mt-2">
                                        {{ timeBox.notes }}
                                    </p>

                                    <div v-if="timeBox.tasks.length" class="flex flex-wrap gap-2 mt-3">
                                        <Badge v-for="task in timeBox.tasks" :key="task.id" variant="secondary"
                                            class="bg-slate-800 text-slate-300">
                                            {{ task.title }}
                                        </Badge>
                                    </div>
                                </div>

                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="sm" @click.stop>
                                            <MoreVertical class="w-4 h-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48 bg-slate-900 border-slate-700">
                                        <DropdownMenuItem @click.stop="openEditModal(timeBox)">
                                            <Edit class="mr-2 h-4 w-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click.stop="duplicateTimeBox(timeBox)">
                                            <Copy class="mr-2 h-4 w-4" />
                                            Duplicate
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator class="bg-slate-700" />
                                        <DropdownMenuItem class="text-red-400" @click.stop="deleteTimeBox(timeBox.id)">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Time Box Form Modal -->
        <TimeBoxFormModal :is-open="showModal" :time-box="editingTimeBox" :available-tasks="availableTasks"
            :errors="formErrors" :processing="formProcessing" @close="closeModal" @submit="handleSubmit" />
    </Layout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/layouts/Layout.vue'
import TimeBoxFormModal from '@/components/TimeBoxFormModal.vue'
import {
    Clock,
    Calendar,
    Plus,
    ChevronLeft,
    ChevronRight,
    MoreVertical,
    Edit,
    Copy,
    Trash2
} from 'lucide-vue-next'
import {
    Card,
    CardContent,
} from '@/components/ui/card'
import {
    Button,
} from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const props = defineProps({
    timeBoxes: Array,
    availableTasks: Array,
    filters: Object,
    currentWeek: Object
})

// State
const viewMode = ref('week') // 'day', 'week', 'list'
const currentDate = ref(new Date())
const showModal = ref(false)
const editingTimeBox = ref(null)
const formErrors = ref({})
const formProcessing = ref(false)





// Hours for grid (6 AM to 11 PM)
const hours = Array.from({ length: 18 }, (_, i) => i + 6)

// Computed
const weekDays = computed(() => {
    const days = []
    const startOfWeek = new Date(currentDate.value)
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay() + 1) // Monday

    for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek)
        date.setDate(startOfWeek.getDate() + i)
        days.push({
            date: date.toISOString().split('T')[0],
            dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
            dayNumber: date.getDate()
        })
    }
    return days
})

const todayTimeBoxes = computed(() => {
    const today = currentDate.value.toISOString().split('T')[0]
    return props.timeBoxes.filter(tb => {
        const boxDate = new Date(tb.start_at).toISOString().split('T')[0]
        return boxDate === today
    })
})

const sortedTimeBoxes = computed(() => {
    return [...props.timeBoxes].sort((a, b) =>
        new Date(a.start_at) - new Date(b.start_at)
    )
})

// Methods
const formatDateRange = () => {
    if (viewMode.value === 'day') {
        return currentDate.value.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        })
    } else if (viewMode.value === 'week') {
        const start = weekDays.value[0].date
        const end = weekDays.value[6].date
        const startDate = new Date(start)
        const endDate = new Date(end)
        return `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`
    }
    return 'All Time Boxes'
}

const formatHour = (hour) => {
    const period = hour >= 12 ? 'PM' : 'AM'
    const displayHour = hour > 12 ? hour - 12 : hour
    return `${displayHour} ${period}`
}

const formatTime = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    })
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

const formatType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const isToday = (dateStr) => {
    const today = new Date().toISOString().split('T')[0]
    return dateStr === today
}

const getTimeBoxesForDay = (date) => {
    return props.timeBoxes.filter(tb => {
        const boxDate = new Date(tb.start_at).toISOString().split('T')[0]
        return boxDate === date
    })
}

const getTimeBoxStyle = (timeBox) => {
    const start = new Date(timeBox.start_at)
    const end = new Date(timeBox.end_at)
    const startHour = start.getHours() + start.getMinutes() / 60
    const endHour = end.getHours() + end.getMinutes() / 60

    const top = (startHour - 6) * 48 // 48px per hour (h-12 in tailwind)
    const height = (endHour - startHour) * 48

    return {
        top: `${top}px`,
        height: `${height}px`,
        zIndex: timeBox.is_active ? 10 : 1
    }
}

const getTimeBoxClasses = (timeBox) => {
    const baseClasses = {
        'focus': 'bg-blue-400/20 text-blue-100 border-blue-400/50',
        'break': 'bg-green-400/20 text-green-100 border-green-400/50',
        'meeting': 'bg-purple-400/20 text-purple-100 border-purple-400/50',
        'personal': 'bg-orange-400/20 text-orange-100 border-orange-400/50',
    }

    return baseClasses[timeBox.type] || 'bg-slate-800/50 text-slate-200 border-slate-700'
}

const getTimeBoxListClasses = (timeBox) => {
    const baseClasses = {
        'focus': 'bg-blue-400/10 border-blue-400/30',
        'break': 'bg-green-400/10 border-green-400/30',
        'meeting': 'bg-purple-400/10 border-purple-400/30',
        'personal': 'bg-orange-400/10 border-orange-400/30',
    }

    return baseClasses[timeBox.type] || 'bg-slate-800/50 border-slate-700'
}

const getTypeBadgeClasses = (type) => {
    const classes = {
        'focus': 'bg-blue-400/20 text-blue-400 border-blue-400/30',
        'break': 'bg-green-400/20 text-green-400 border-green-400/30',
        'meeting': 'bg-purple-400/20 text-purple-400 border-purple-400/30',
        'personal': 'bg-orange-400/20 text-orange-400 border-orange-400/30',
    }

    return classes[type] || 'bg-slate-800 text-slate-300 border-slate-700'
}

const navigateDate = (direction) => {
    if (direction === 'today') {
        currentDate.value = new Date()
    } else if (direction === 'prev') {
        const newDate = new Date(currentDate.value)
        if (viewMode.value === 'day') {
            newDate.setDate(newDate.getDate() - 1)
        } else {
            newDate.setDate(newDate.getDate() - 7)
        }
        currentDate.value = newDate
    } else if (direction === 'next') {
        const newDate = new Date(currentDate.value)
        if (viewMode.value === 'day') {
            newDate.setDate(newDate.getDate() + 1)
        } else {
            newDate.setDate(newDate.getDate() + 7)
        }
        currentDate.value = newDate
    }
    loadTimeBoxes()
}

const loadTimeBoxes = () => {
    const params = {}
    if (viewMode.value === 'day') {
        params.date = currentDate.value.toISOString().split('T')[0]
    } else if (viewMode.value === 'week') {
        params.week = weekDays.value[0].date
    }

    router.get(route('time-boxes.index'), params, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Modal Methods
const openCreateModal = () => {
    editingTimeBox.value = null
    formErrors.value = {}
    showModal.value = true
}

const openEditModal = (timeBox) => {
    editingTimeBox.value = timeBox
    formErrors.value = {}
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    editingTimeBox.value = null
    formErrors.value = {}
    formProcessing.value = false
}

const handleSubmit = ({ data, isEditing, timeBoxId }) => {
    formProcessing.value = true
    formErrors.value = {}

    if (isEditing) {
        router.put(route('time-boxes.update', timeBoxId), data, {
            onSuccess: () => {
                closeModal()
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
                closeModal()
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

const duplicateTimeBox = (timeBox) => {
    const tomorrow = new Date(timeBox.start_at)
    tomorrow.setDate(tomorrow.getDate() + 1)
    const endTomorrow = new Date(timeBox.end_at)
    endTomorrow.setDate(endTomorrow.getDate() + 1)

    const duplicatedData = {
        title: `${timeBox.title} (Copy)`,
        type: timeBox.type,
        start_at: tomorrow.toISOString(),
        end_at: endTomorrow.toISOString(),
        allow_overlap: timeBox.allow_overlap,
        notes: timeBox.notes,
        task_ids: timeBox.tasks.map(t => t.id)
    }

    router.post(route('time-boxes.store'), duplicatedData)
}

const deleteTimeBox = (timeBoxId) => {
    if (confirm('Are you sure you want to delete this time box?')) {
        router.delete(route('time-boxes.destroy', timeBoxId))
    }
}

// Auto-refresh for active time boxes
let refreshInterval = null

onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['timeBoxes'] })
    }, 60000) // Refresh every minute
})

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval)
        refreshInterval = null
    }
})

// Watch view mode changes
watch(viewMode, () => {
    loadTimeBoxes()
})
</script>