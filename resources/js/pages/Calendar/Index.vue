<template>
    <Layout title="Calendar">
        <div class="p-6 space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2
                        class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                        Calendar
                    </h2>
                    <p class="text-slate-400 text-sm">
                        Monthly overview of your schedule
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Month Navigation -->
                    <div class="flex items-center gap-2">
                        <Button @click="navigateMonth('prev')" variant="outline" size="sm"
                            class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                            <ChevronLeft class="w-4 h-4" />
                        </Button>

                        <Button @click="navigateMonth('today')" variant="outline" size="sm"
                            class="border-slate-700 bg-slate-800/50 hover:bg-slate-800 px-4">
                            Today
                        </Button>

                        <Button @click="navigateMonth('next')" variant="outline" size="sm"
                            class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                            <ChevronRight class="w-4 h-4" />
                        </Button>
                    </div>

                    <!-- View Options -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm"
                                class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                                <Settings class="w-4 h-4 mr-2" />
                                View
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48 bg-slate-900 border-slate-700">
                            <DropdownMenuItem @click="showWeekends = !showWeekends">
                                <Check v-if="showWeekends" class="mr-2 h-4 w-4" />
                                <span class="ml-6" v-else></span>
                                Show Weekends
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="showTasks = !showTasks">
                                <Check v-if="showTasks" class="mr-2 h-4 w-4" />
                                <span class="ml-6" v-else></span>
                                Show Tasks
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="showTimeBoxes = !showTimeBoxes">
                                <Check v-if="showTimeBoxes" class="mr-2 h-4 w-4" />
                                <span class="ml-6" v-else></span>
                                Show Time Boxes
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Button @click="openCreateModal"
                        class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600">
                        <Plus class="w-4 h-4 mr-2" />
                        New Event
                    </Button>
                </div>
            </div>

            <!-- Month and Year Display -->
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-slate-200">
                    {{ currentMonthYear }}
                </h3>

                <!-- Legend -->
                <div class="flex items-center gap-4 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                        Time Box
                    </span>
                    <span class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        Task
                    </span>
                    <span class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                        Meeting
                    </span>
                    <span class="flex items-center gap-1.5">
                        <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                        Deadline
                    </span>
                </div>
            </div>

            <!-- Calendar Grid -->
            <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl overflow-hidden">
                <CardContent class="p-0">
                    <div class="p-4">
                        <!-- Days of Week Header -->
                        <div :class="[
                            'grid gap-px bg-slate-800/30',
                            showWeekends ? 'grid-cols-7' : 'grid-cols-5'
                        ]">
                            <div v-for="day in visibleDaysOfWeek" :key="day"
                                class="py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-900">
                                {{ day }}
                            </div>
                        </div>

                        <!-- Calendar Days Grid -->
                        <div :class="[
                            'grid gap-px bg-slate-800/30 mt-px',
                            showWeekends ? 'grid-cols-7' : 'grid-cols-5'
                        ]">
                            <div v-for="(day, index) in calendarDays" :key="index" :class="[
                                'min-h-[120px] bg-slate-900 p-2 transition-all',
                                day.isCurrentMonth ? '' : 'opacity-40',
                                day.isToday ? 'ring-2 ring-blue-400/50 bg-slate-800/30' : '',
                                day.isWeekend && !showWeekends ? 'hidden' : '',
                                'hover:bg-slate-800/30 cursor-pointer'
                            ]" @click="selectDate(day)">

                                <!-- Day Number -->
                                <div class="flex items-start justify-between mb-2">
                                    <span :class="[
                                        'text-sm font-medium',
                                        day.isToday ? 'text-blue-400' : 'text-slate-300',
                                        !day.isCurrentMonth ? 'text-slate-600' : ''
                                    ]">
                                        {{ day.day }}
                                    </span>

                                    <!-- Event Count Badge -->
                                    <div v-if="day.eventCount > 0" class="flex items-center gap-1">
                                        <Badge
                                            class="bg-purple-400/20 text-purple-400 border-purple-400/30 text-xs px-1.5 py-0">
                                            {{ day.eventCount }}
                                        </Badge>
                                    </div>
                                </div>

                                <!-- Events List -->
                                <div class="space-y-1">
                                    <!-- Time Boxes -->
                                    <div v-if="showTimeBoxes">
                                        <div v-for="tb in day.timeBoxes.slice(0, 2)" :key="'tb-' + tb.id"
                                            @click.stop="openEditTimeBox(tb)" :class="[
                                                'text-xs px-1.5 py-0.5 rounded truncate cursor-pointer transition-all hover:opacity-80',
                                                getEventColorClass(tb.type, 'timebox')
                                            ]">
                                            <span class="font-medium">{{ formatEventTime(tb.start_at) }}</span>
                                            <span class="ml-1 opacity-75">{{ tb.title }}</span>
                                        </div>
                                    </div>

                                    <!-- Tasks -->
                                    <div v-if="showTasks">
                                        <div v-for="task in day.tasks.slice(0, 2)" :key="'task-' + task.id"
                                            @click.stop="openEditTask(task)" :class="[
                                                'text-xs px-1.5 py-0.5 rounded truncate cursor-pointer transition-all hover:opacity-80',
                                                task.status === 'done'
                                                    ? 'bg-green-400/10 text-green-400/50 line-through'
                                                    : getTaskPriorityClass(task.priority)
                                            ]">
                                            <span class="font-medium">Due:</span>
                                            <span class="ml-1 opacity-75">{{ task.title }}</span>
                                        </div>
                                    </div>

                                    <!-- More indicator -->
                                    <div v-if="day.hasMore"
                                        class="text-xs text-slate-500 hover:text-slate-400 cursor-pointer">
                                        +{{ day.moreCount }} more
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-400">This Month</p>
                                <p class="text-2xl font-bold text-purple-400">{{ monthStats.totalEvents }}</p>
                                <p class="text-xs text-slate-500 mt-1">Total Events</p>
                            </div>
                            <Calendar class="w-8 h-8 text-purple-400/20" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-400">Time Boxes</p>
                                <p class="text-2xl font-bold text-blue-400">{{ monthStats.timeBoxes }}</p>
                                <p class="text-xs text-slate-500 mt-1">Scheduled</p>
                            </div>
                            <Clock class="w-8 h-8 text-blue-400/20" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-400">Tasks Due</p>
                                <p class="text-2xl font-bold text-green-400">{{ monthStats.tasksDue }}</p>
                                <p class="text-xs text-slate-500 mt-1">This Month</p>
                            </div>
                            <CheckSquare class="w-8 h-8 text-green-400/20" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-400">Completed</p>
                                <p class="text-2xl font-bold text-emerald-400">{{ monthStats.completed }}</p>
                                <p class="text-xs text-slate-500 mt-1">Tasks</p>
                            </div>
                            <CheckCircle2 class="w-8 h-8 text-emerald-400/20" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Day Detail Modal -->
        <DayDetailModal v-if="selectedDay" :is-open="showDayModal" :date="selectedDay.date"
            :time-boxes="selectedDay.timeBoxes" :tasks="selectedDay.tasks" @close="closeDayModal"
            @edit-timebox="openEditTimeBox" @edit-task="openEditTask" @create-event="openCreateModalForDate" />

        <!-- Time Box Form Modal -->
        <TimeBoxFormModal :is-open="showTimeBoxModal" :time-box="editingTimeBox" :available-tasks="availableTasks"
            :errors="formErrors" :processing="formProcessing" @close="closeTimeBoxModal"
            @submit="handleTimeBoxSubmit" />
    </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/layouts/Layout.vue'
import TimeBoxFormModal from '@/components/TimeBoxFormModal.vue'
import DayDetailModal from '@/components/DayDetailModal.vue'
import {
    Calendar,
    Clock,
    Plus,
    ChevronLeft,
    ChevronRight,
    Settings,
    Check,
    CheckSquare,
    CheckCircle2
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
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { useTranslations } from '@/composables/useTranslations'
const { __ } = useTranslations()

const props = defineProps({
    timeBoxes: {
        type: Array,
        default: () => []
    },
    tasks: {
        type: Array,
        default: () => []
    },
    availableTasks: {
        type: Array,
        default: () => []
    }
})

// State
const currentDate = ref(new Date())
const showWeekends = ref(true)
const showTasks = ref(true)
const showTimeBoxes = ref(true)
const selectedDay = ref(null)
const showDayModal = ref(false)
const showTimeBoxModal = ref(false)
const editingTimeBox = ref(null)
const formErrors = ref({})
const formProcessing = ref(false)

// Days of week
const daysOfWeek = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri']
const visibleDaysOfWeek = computed(() =>
    showWeekends.value ? daysOfWeek : daysOfWeek.slice(1, 6)
)

// Current month/year display
const currentMonthYear = computed(() => {
    return currentDate.value.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
    })
})

// Generate calendar days
const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    const firstDay = new Date(year, month, 1)
    const lastDay = new Date(year, month + 1, 0)
    const startDate = new Date(firstDay)
    const endDate = new Date(lastDay)

    // Adjust to start from Sunday or Monday
    const startDay = startDate.getDay()
    startDate.setDate(startDate.getDate() - startDay)

    // Adjust to end on Saturday or Friday
    const endDay = endDate.getDay()
    if (endDay < 6) {
        endDate.setDate(endDate.getDate() + (6 - endDay))
    }

    const days = []
    const today = new Date()
    today.setHours(0, 0, 0, 0)

    for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
        const date = new Date(d)
        const dateStr = date.toISOString().split('T')[0]

        // Get events for this day
        const dayTimeBoxes = props.timeBoxes.filter(tb => {
            const tbDate = new Date(tb.start_at).toISOString().split('T')[0]
            return tbDate === dateStr
        })

        const dayTasks = props.tasks.filter(task => {
            if (!task.due_date) return false
            const taskDate = new Date(task.due_date).toISOString().split('T')[0]
            return taskDate === dateStr
        })

        const eventCount = dayTimeBoxes.length + dayTasks.length
        const maxVisible = 4

        days.push({
            date: dateStr,
            day: date.getDate(),
            month: date.getMonth(),
            year: date.getFullYear(),
            isCurrentMonth: date.getMonth() === month,
            isToday: date.getTime() === today.getTime(),
            isWeekend: date.getDay() === 0 || date.getDay() === 6,
            timeBoxes: dayTimeBoxes,
            tasks: dayTasks,
            eventCount: eventCount,
            hasMore: eventCount > maxVisible,
            moreCount: Math.max(0, eventCount - maxVisible)
        })
    }

    return days
})

// Month statistics
const monthStats = computed(() => {
    const month = currentDate.value.getMonth()
    const year = currentDate.value.getFullYear()

    const monthTimeBoxes = props.timeBoxes.filter(tb => {
        const date = new Date(tb.start_at)
        return date.getMonth() === month && date.getFullYear() === year
    })

    const monthTasks = props.tasks.filter(task => {
        if (!task.due_date) return false
        const date = new Date(task.due_date)
        return date.getMonth() === month && date.getFullYear() === year
    })

    return {
        totalEvents: monthTimeBoxes.length + monthTasks.length,
        timeBoxes: monthTimeBoxes.length,
        tasksDue: monthTasks.filter(t => t.status !== 'done').length,
        completed: monthTasks.filter(t => t.status === 'done').length
    }
})

// Methods
const navigateMonth = (direction) => {
    const newDate = new Date(currentDate.value)

    if (direction === 'today') {
        currentDate.value = new Date()
    } else if (direction === 'prev') {
        newDate.setMonth(newDate.getMonth() - 1)
        currentDate.value = newDate
    } else if (direction === 'next') {
        newDate.setMonth(newDate.getMonth() + 1)
        currentDate.value = newDate
    }

    loadMonthData()
}

const loadMonthData = () => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    const startDate = new Date(year, month, 1)
    const endDate = new Date(year, month + 1, 0)

    router.get(route('calendar.index'), {
        start_date: startDate.toISOString().split('T')[0],
        end_date: endDate.toISOString().split('T')[0]
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const selectDate = (day) => {
    selectedDay.value = day
    showDayModal.value = true
}

const closeDayModal = () => {
    showDayModal.value = false
    selectedDay.value = null
}

const formatEventTime = (dateStr) => {
    const date = new Date(dateStr)
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).replace(' ', '').toLowerCase()
}

const getEventColorClass = (type, category) => {
    if (category === 'timebox') {
        const colors = {
            'focus': 'bg-blue-400/20 text-blue-300',
            'meeting': 'bg-purple-400/20 text-purple-300',
            'break': 'bg-green-400/20 text-green-300',
            'study': 'bg-indigo-400/20 text-indigo-300',
            'work': 'bg-orange-400/20 text-orange-300',
        }
        return colors[type] || 'bg-slate-700/50 text-slate-300'
    }
    return 'bg-slate-700/50 text-slate-300'
}

const getTaskPriorityClass = (priority) => {
    const classes = {
        'urgent': 'bg-red-400/20 text-red-300',
        'high': 'bg-orange-400/20 text-orange-300',
        'medium': 'bg-yellow-400/20 text-yellow-300',
        'low': 'bg-slate-600/20 text-slate-400'
    }
    return classes[priority] || 'bg-slate-700/50 text-slate-300'
}

// Modal methods
const openCreateModal = () => {
    editingTimeBox.value = null
    showTimeBoxModal.value = true
}

const openCreateModalForDate = (date) => {
    editingTimeBox.value = null
    // Set default date for new event
    showTimeBoxModal.value = true
}

const openEditTimeBox = (timeBox) => {
    editingTimeBox.value = timeBox
    showTimeBoxModal.value = true
    showDayModal.value = false
}

const openEditTask = (task) => {
    // Navigate to task edit page or open task modal
    router.get(route('tasks.edit', task.id))
}

const closeTimeBoxModal = () => {
    showTimeBoxModal.value = false
    editingTimeBox.value = null
    formErrors.value = {}
}

const handleTimeBoxSubmit = ({ data, isEditing, timeBoxId }) => {
    formProcessing.value = true

    if (isEditing) {
        router.put(route('time-boxes.update', timeBoxId), data, {
            onSuccess: () => {
                closeTimeBoxModal()
                loadMonthData()
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
                loadMonthData()
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

// Load initial data
onMounted(() => {
    loadMonthData()
})
</script>