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

                    <Button @click="toggleFullDay" variant="outline" size="sm"
                        class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                        {{ startHour === 0 ? 'Show Working Hours' : 'Show Full Day' }}
                    </Button>

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
                            Today
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

                <div class="flex items-center gap-3 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-400"></div>
                        Focus
                    </span>
                    <span class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-purple-400"></div>
                        Meeting
                    </span>
                    <span class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                        Break
                    </span>
                    <span class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-indigo-400"></div>
                        Study
                    </span>
                    <span class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-orange-400"></div>
                        Work
                    </span>
                </div>
            </div>

            <!-- Calendar/List View -->
            <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl overflow-hidden">
                <CardContent class="p-0">
                    <!-- Week View -->
                    <div v-if="viewMode === 'week'" class="p-4">
                        <div class="overflow-x-auto">
                            <div class="min-w-[1000px]">
                                <!-- Days Header - Fixed horizontal layout -->
                                <div class="flex mb-2">
                                    <div class="w-20 shrink-0"></div>
                                    <div class="flex-1 grid grid-cols-7">
                                        <div v-for="day in weekDays" :key="day.date"
                                            class="text-center px-2 py-2 border-l border-slate-800/30">
                                            <div class="text-xs text-slate-400 uppercase">{{ day.dayName }}</div>
                                            <div :class="[
                                                'text-lg font-semibold mt-1',
                                                isToday(day.date) ? 'text-blue-400' : 'text-slate-200'
                                            ]">{{ day.dayNumber }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Time Grid with horizontal days -->
                                <div class="flex">
                                    <!-- Time Column -->
                                    <div class="w-20 shrink-0">
                                        <div v-for="hour in displayHours" :key="hour"
                                            class="h-12 flex items-center justify-end pr-3 text-xs text-slate-500 border-t border-slate-800/20">
                                            {{ formatHour(hour) }}
                                        </div>
                                    </div>

                                    <!-- Days Container -->
                                    <div class="flex-1 grid grid-cols-7">
                                        <!-- Day Columns -->
                                        <div v-for="day in weekDays" :key="day.date"
                                            class="relative border-l border-slate-800/30">
                                            <!-- Hour Slots -->
                                            <div v-for="hour in displayHours" :key="hour"
                                                class="h-12 border-t border-slate-800/20 hover:bg-slate-800/10"
                                                @drop="handleDrop($event, day.date, hour)" @dragover.prevent
                                                @dragenter.prevent>
                                            </div>

                                            <!-- Time Boxes for this day - positioned absolutely within the column -->
                                            <div v-for="tb in getTimeBoxesForDay(day.date)" :key="tb.id"
                                                :style="calculatePosition(tb)" :draggable="true"
                                                @dragstart="handleDragStart($event, tb)" @click="openEditModal(tb)"
                                                :class="[
                                                    'absolute inset-x-1 px-2 py-1 rounded cursor-move transition-all hover:shadow-lg hover:z-20 border text-xs',
                                                    getTimeBoxClasses(tb),
                                                    draggedItem?.id === tb.id ? 'opacity-50' : ''
                                                ]">
                                                <div class="font-semibold truncate">{{ tb.title }}</div>
                                                <div class="text-[10px] opacity-75">
                                                    {{ formatTime(tb.start_at) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Day View -->
                    <div v-else-if="viewMode === 'day'" class="p-4">
                        <div class="flex">
                            <!-- Time Column -->
                            <div class="w-20 shrink-0">
                                <div v-for="hour in displayHours" :key="hour"
                                    class="h-16 flex items-center justify-end pr-4 text-sm text-slate-400 border-t border-slate-800/20">
                                    {{ formatHour(hour) }}
                                </div>
                            </div>

                            <!-- Schedule Column with relative positioning container -->
                            <div class="flex-1 relative">
                                <!-- Background grid -->
                                <div class="absolute inset-0 border-l border-slate-800/30">
                                    <!-- Hour Slots -->
                                    <div v-for="hour in displayHours" :key="hour"
                                        class="h-16 border-t border-slate-800/20 hover:bg-slate-800/10"
                                        @drop="handleDrop($event, currentDateString, hour)" @dragover.prevent
                                        @dragenter.prevent>
                                        <!-- 15 minute lines -->
                                        <div class="h-4 border-t border-slate-800/10"></div>
                                        <div class="h-4 border-t border-slate-800/10"></div>
                                        <div class="h-4 border-t border-slate-800/10"></div>
                                    </div>
                                </div>

                                <!-- Time Boxes container - absolute positioned on top of grid -->
                                <div class="absolute inset-0 pointer-events-none">
                                    <div v-for="tb in todayTimeBoxes" :key="tb.id" :style="calculatePositionDay(tb)"
                                        :draggable="true" @dragstart="handleDragStart($event, tb)"
                                        @click="openEditModal(tb)" :class="[
                                            'absolute left-2 right-2 px-3 py-2 rounded cursor-move transition-all hover:shadow-lg hover:z-20 border pointer-events-auto',
                                            getTimeBoxClasses(tb),
                                            draggedItem?.id === tb.id ? 'opacity-50' : ''
                                        ]">
                                        <div class="font-semibold">{{ tb.title }}</div>
                                        <div class="text-xs opacity-75 mt-1">
                                            {{ formatTime(tb.start_at) }} - {{ formatTime(tb.end_at) }}
                                        </div>
                                        <div v-if="tb.notes" class="text-xs mt-2 opacity-60 line-clamp-2">
                                            {{ tb.notes }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List View -->
                    <div v-else class="p-6 space-y-4">
                        <div v-if="sortedTimeBoxes.length === 0" class="text-center py-12">
                            <Clock class="w-12 h-12 text-slate-600 mx-auto mb-4" />
                            <p class="text-slate-400">No time boxes scheduled</p>
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
                                            Active
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
                                        <span>{{ timeBox.duration_minutes }} min</span>
                                    </div>

                                    <p v-if="timeBox.notes" class="text-sm text-slate-500 mt-2">
                                        {{ timeBox.notes }}
                                    </p>
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
import { ref, computed, watch } from 'vue'
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

import { useTranslations } from '@/composables/useTranslations'
const { __ } = useTranslations()

const props = defineProps({
    timeBoxes: Array,
    availableTasks: Array,
    filters: Object,
    currentWeek: Object
})

const startHour = ref(0)
const endHour = ref(23)

// State
const viewMode = ref('week')
const currentDate = ref(new Date())
const showModal = ref(false)
const editingTimeBox = ref(null)
const formErrors = ref({})
const formProcessing = ref(false)
const draggedItem = ref(null)

// Hours from 0 to 23 (full day)
const displayHours = computed(() => {
    return Array.from(
        { length: endHour.value - startHour.value + 1 },
        (_, i) => i + startHour.value
    )
})

// Computed
const currentDateString = computed(() => currentDate.value.toISOString().split('T')[0])

const weekDays = computed(() => {
    const days = []
    const startOfWeek = new Date(currentDate.value)
    const day = startOfWeek.getDay()
    const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1)
    startOfWeek.setDate(diff)

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
    return props.timeBoxes.filter(tb => {
        const boxDate = new Date(tb.start_at).toISOString().split('T')[0]
        return boxDate === currentDateString.value
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
        const start = new Date(weekDays.value[0].date)
        const end = new Date(weekDays.value[6].date)
        return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`
    }
    return 'All Time Boxes'
}

const formatHour = (hour) => {
    if (hour === 0) return '12 AM'
    if (hour === 12) return '12 PM'
    if (hour < 12) return `${hour} AM`
    return `${hour - 12} PM`
}

const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    })
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
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

// Calculate position for week view (48px per hour)
const calculatePosition = (timeBox) => {
    const start = new Date(timeBox.start_at)
    const end = new Date(timeBox.end_at)
    const startHour = start.getHours() + start.getMinutes() / 60
    const endHour = end.getHours() + end.getMinutes() / 60

    const top = startHour * 48
    const height = Math.max((endHour - startHour) * 48, 20)

    return {
        top: `${top}px`,
        height: `${height}px`,
        zIndex: timeBox.is_active ? 10 : 1
    }
}

// Calculate position for day view (64px per hour)
const calculatePositionDay = (timeBox) => {
    const start = new Date(timeBox.start_at)
    const end = new Date(timeBox.end_at)
    const startHour = start.getHours() + start.getMinutes() / 60
    const endHour = end.getHours() + end.getMinutes() / 60

    const top = startHour * 64
    const height = Math.max((endHour - startHour) * 64, 30)

    return {
        top: `${top}px`,
        height: `${height}px`,
        zIndex: timeBox.is_active ? 10 : 1
    }
}

const getTimeBoxClasses = (timeBox) => {
    const baseClasses = {
        'focus': 'bg-blue-400/20 text-blue-100 border-blue-400/50',
        'meeting': 'bg-purple-400/20 text-purple-100 border-purple-400/50',
        'break': 'bg-green-400/20 text-green-100 border-green-400/50',
        'study': 'bg-indigo-400/20 text-indigo-100 border-indigo-400/50',
        'work': 'bg-orange-400/20 text-orange-100 border-orange-400/50',
        'house': 'bg-yellow-400/20 text-yellow-100 border-yellow-400/50',
        'random': 'bg-pink-400/20 text-pink-100 border-pink-400/50',
        'custom': 'bg-slate-400/20 text-slate-100 border-slate-400/50',
    }

    return baseClasses[timeBox.type] || 'bg-slate-800/50 text-slate-200 border-slate-700'
}

const getTimeBoxListClasses = (timeBox) => {
    const baseClasses = {
        'focus': 'bg-blue-400/10 border-blue-400/30',
        'meeting': 'bg-purple-400/10 border-purple-400/30',
        'break': 'bg-green-400/10 border-green-400/30',
        'study': 'bg-indigo-400/10 border-indigo-400/30',
        'work': 'bg-orange-400/10 border-orange-400/30',
        'house': 'bg-yellow-400/10 border-yellow-400/30',
        'random': 'bg-pink-400/10 border-pink-400/30',
        'custom': 'bg-slate-400/10 border-slate-400/30',
    }

    return baseClasses[timeBox.type] || 'bg-slate-800/50 border-slate-700'
}

const getTypeBadgeClasses = (type) => {
    const classes = {
        'focus': 'bg-blue-400/20 text-blue-400 border-blue-400/30',
        'meeting': 'bg-purple-400/20 text-purple-400 border-purple-400/30',
        'break': 'bg-green-400/20 text-green-400 border-green-400/30',
        'study': 'bg-indigo-400/20 text-indigo-400 border-indigo-400/30',
        'work': 'bg-orange-400/20 text-orange-400 border-orange-400/30',
        'house': 'bg-yellow-400/20 text-yellow-400 border-yellow-400/30',
        'random': 'bg-pink-400/20 text-pink-400 border-pink-400/30',
        'custom': 'bg-slate-400/20 text-slate-400 border-slate-400/30',
    }

    return classes[type] || 'bg-slate-800 text-slate-300 border-slate-700'
}

// Drag and Drop
const handleDragStart = (event, timeBox) => {
    draggedItem.value = timeBox
    event.dataTransfer.effectAllowed = 'move'
}

const handleDrop = (event, date, hour) => {
    event.preventDefault()

    if (!draggedItem.value) return

    const duration = draggedItem.value.duration_minutes || 60

    // Create new start date - ensure we're using the correct date
    const year = date.substring(0, 4)
    const month = date.substring(5, 7)
    const day = date.substring(8, 10)

    const newStart = new Date(year, month - 1, day, hour, 0, 0)
    const newEnd = new Date(newStart)
    newEnd.setMinutes(newEnd.getMinutes() + duration)

    console.log('Dropping time box:', {
        id: draggedItem.value.id,
        date: date,
        hour: hour,
        newStart: newStart.toISOString(),
        newEnd: newEnd.toISOString(),
        duration: duration
    })

    // Update via Inertia - include current filters
    const updateData = {
        start_at: newStart.toISOString(),
        end_at: newEnd.toISOString()
    }

    // Add current filter params to maintain view state
    if (viewMode.value === 'day') {
        updateData.date = currentDateString.value
    } else if (viewMode.value === 'week') {
        updateData.week = weekDays.value[0].date
    }

    router.patch(route('time-boxes.update-time', draggedItem.value.id), updateData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            console.log('Time box updated successfully')
            // Force reload to refresh the data
            loadTimeBoxes()
        },
        onError: (errors) => {
            console.error('Error updating time box:', errors)
            alert('Failed to update time box: ' + (errors.message || 'Unknown error'))
        },
        onFinish: () => {
            draggedItem.value = null
        }
    })
}

// Navigation
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
        params.date = currentDateString.value
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

    router.post(route('time-boxes.store'), {
        title: `${timeBox.title} (Copy)`,
        type: timeBox.type,
        start_at: tomorrow.toISOString(),
        end_at: endTomorrow.toISOString(),
        allow_overlap: timeBox.allow_overlap,
        notes: timeBox.notes,
        task_ids: timeBox.tasks?.map(t => t.id) || []
    })
}

const deleteTimeBox = (timeBoxId) => {
    if (confirm('Are you sure you want to delete this time box?')) {
        router.delete(route('time-boxes.destroy', timeBoxId))
    }
}

// Watch view mode changes
watch(viewMode, () => {
    loadTimeBoxes()
})



const toggleFullDay = () => {
    if (startHour.value === 0) {
        startHour.value = 6
        endHour.value = 22
    } else {
        startHour.value = 0
        endHour.value = 23
    }
}


</script>