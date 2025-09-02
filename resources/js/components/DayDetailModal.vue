<template>
    <Dialog :open="isOpen" @update:open="handleOpenChange">
        <DialogContent class="w-[600px] max-w-[90vw] max-h-[80vh] overflow-y-auto bg-slate-900 border-slate-800">
            <DialogHeader>
                <DialogTitle class="text-xl font-bold text-slate-200">
                    {{ formattedDate }}
                </DialogTitle>
                <DialogDescription class="text-slate-400">
                    {{ eventCount }} event{{ eventCount !== 1 ? 's' : '' }} scheduled
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6 py-4">
                <!-- Time Boxes Section -->
                <div v-if="timeBoxes.length > 0" class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                        Time Boxes
                    </h3>
                    <div class="space-y-2">
                        <div v-for="tb in sortedTimeBoxes" :key="tb.id" @click="$emit('edit-timebox', tb)" :class="[
                            'p-3 rounded-lg border cursor-pointer transition-all hover:shadow-lg',
                            getTimeBoxClass(tb.type)
                        ]">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-medium text-slate-200">{{ tb.title }}</h4>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <Clock class="w-3 h-3" />
                                            {{ formatTime(tb.start_at) }} - {{ formatTime(tb.end_at) }}
                                        </span>
                                        <span>{{ tb.duration_minutes }} min</span>
                                    </div>
                                    <p v-if="tb.notes" class="text-sm text-slate-500 mt-2">
                                        {{ tb.notes }}
                                    </p>
                                </div>
                                <Badge :class="getTypeBadgeClass(tb.type)">
                                    {{ formatType(tb.type) }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tasks Section -->
                <div v-if="tasks.length > 0" class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                        Tasks Due
                    </h3>
                    <div class="space-y-2">
                        <div v-for="task in tasks" :key="task.id" @click="$emit('edit-task', task)" :class="[
                            'p-3 rounded-lg border cursor-pointer transition-all hover:shadow-lg',
                            task.status === 'done'
                                ? 'bg-green-400/10 border-green-400/30 opacity-50'
                                : getPriorityClass(task.priority)
                        ]">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 :class="[
                                        'font-medium',
                                        task.status === 'done' ? 'line-through text-slate-400' : 'text-slate-200'
                                    ]">
                                        {{ task.title }}
                                    </h4>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-slate-400">
                                        <span v-if="task.estimated_minutes">
                                            {{ task.estimated_minutes }} min estimated
                                        </span>
                                    </div>
                                </div>
                                <Badge v-if="task.status === 'done'" class="bg-green-400/20 text-green-400">
                                    Done
                                </Badge>
                                <Badge v-else :class="getPriorityBadgeClass(task.priority)">
                                    {{ task.priority.toUpperCase() }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="timeBoxes.length === 0 && tasks.length === 0" class="text-center py-8">
                    <Calendar class="w-12 h-12 text-slate-600 mx-auto mb-3" />
                    <p class="text-slate-400">No events scheduled for this day</p>
                </div>
            </div>

            <DialogFooter class="gap-3 pt-4 border-t border-slate-800/30">
                <Button variant="outline" @click="closeModal"
                    class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                    Close
                </Button>
                <Button @click="$emit('create-event', date)"
                    class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Event
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { computed } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import {
    Button,
} from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
    Clock,
    Calendar,
    Plus
} from 'lucide-vue-next'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    date: {
        type: String,
        required: true
    },
    timeBoxes: {
        type: Array,
        default: () => []
    },
    tasks: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['close', 'edit-timebox', 'edit-task', 'create-event'])

// Computed
const formattedDate = computed(() => {
    const date = new Date(props.date)
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
})

const eventCount = computed(() => {
    return props.timeBoxes.length + props.tasks.length
})

const sortedTimeBoxes = computed(() => {
    return [...props.timeBoxes].sort((a, b) =>
        new Date(a.start_at) - new Date(b.start_at)
    )
})

// Methods
const formatTime = (dateStr) => {
    return new Date(dateStr).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    })
}

const formatType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getTimeBoxClass = (type) => {
    const classes = {
        'focus': 'bg-blue-400/10 border-blue-400/30',
        'meeting': 'bg-purple-400/10 border-purple-400/30',
        'break': 'bg-green-400/10 border-green-400/30',
        'study': 'bg-indigo-400/10 border-indigo-400/30',
        'work': 'bg-orange-400/10 border-orange-400/30',
        'house': 'bg-yellow-400/10 border-yellow-400/30',
        'random': 'bg-pink-400/10 border-pink-400/30',
        'custom': 'bg-slate-400/10 border-slate-400/30',
    }
    return classes[type] || 'bg-slate-800/50 border-slate-700'
}

const getTypeBadgeClass = (type) => {
    const classes = {
        'focus': 'bg-blue-400/20 text-blue-400',
        'meeting': 'bg-purple-400/20 text-purple-400',
        'break': 'bg-green-400/20 text-green-400',
        'study': 'bg-indigo-400/20 text-indigo-400',
        'work': 'bg-orange-400/20 text-orange-400',
        'house': 'bg-yellow-400/20 text-yellow-400',
        'random': 'bg-pink-400/20 text-pink-400',
        'custom': 'bg-slate-400/20 text-slate-400',
    }
    return classes[type] || 'bg-slate-800 text-slate-300'
}

const getPriorityClass = (priority) => {
    const classes = {
        'urgent': 'bg-red-400/10 border-red-400/30',
        'high': 'bg-orange-400/10 border-orange-400/30',
        'medium': 'bg-yellow-400/10 border-yellow-400/30',
        'low': 'bg-slate-600/10 border-slate-600/30'
    }
    return classes[priority] || 'bg-slate-800/50 border-slate-700'
}

const getPriorityBadgeClass = (priority) => {
    const classes = {
        'urgent': 'bg-red-400/20 text-red-400',
        'high': 'bg-orange-400/20 text-orange-400',
        'medium': 'bg-yellow-400/20 text-yellow-400',
        'low': 'bg-slate-600/20 text-slate-400'
    }
    return classes[priority] || 'bg-slate-800 text-slate-300'
}

const handleOpenChange = (open) => {
    if (!open) {
        closeModal()
    }
}

const closeModal = () => {
    emit('close')
}
</script>