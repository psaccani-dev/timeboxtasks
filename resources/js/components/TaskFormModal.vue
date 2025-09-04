<template>
    <Dialog :open="isOpen" @update:open="handleOpenChange">
        <DialogContent class="w-[600px] max-w-[90vw] max-h-[90vh] overflow-y-auto bg-slate-900 border-slate-800">
            <DialogHeader>
                <DialogTitle
                    class="text-2xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
                    {{ isEditing ? 'Edit Task' : 'Create New Task' }}
                </DialogTitle>
                <DialogDescription class="text-slate-400">
                    {{ isEditing ? 'Update the task details below' : 'Fill in the details to create a new task' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 py-4">
                <!-- Title -->
                <div class="space-y-2">
                    <label for="title" class="block text-sm font-medium text-slate-300">
                        Title <span class="text-red-400">*</span>
                    </label>
                    <input id="title" v-model="form.title" type="text" required placeholder="What needs to be done?"
                        class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all"
                        :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.title }" />
                    <p v-if="errors.title" class="text-red-400 text-sm">{{ errors.title }}</p>
                </div>

                <!-- Type and Priority Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Type -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">
                            Type <span class="text-red-400">*</span>
                        </label>
                        <select v-model="form.type" required
                            class="w-full px-3 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50"
                            :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.type }">
                            <option value="">Select type...</option>
                            <option value="study">Study</option>
                            <option value="work">Work</option>
                            <option value="question">Question</option>
                            <option value="quick_note">Quick Note</option>
                            <option value="reminder">Reminder</option>
                            <option value="house">House</option>
                            <option value="random">Random</option>
                        </select>
                        <p v-if="errors.type" class="text-red-400 text-sm">{{ errors.type }}</p>
                    </div>

                    <!-- Priority -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">
                            Priority <span class="text-red-400">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-1">
                            <button v-for="priority in priorities" :key="priority.value" type="button"
                                @click="form.priority = priority.value" :class="[
                                    'py-2 px-1 text-xs font-medium rounded transition-all',
                                    form.priority === priority.value
                                        ? priority.activeClass
                                        : 'bg-slate-800/30 border border-slate-700/50 text-slate-400 hover:bg-slate-800/50'
                                ]">
                                {{ priority.label }}
                            </button>
                        </div>
                        <p v-if="errors.priority" class="text-red-400 text-sm">{{ errors.priority }}</p>
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">
                        Status
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        <button v-for="status in statuses" :key="status.value" type="button"
                            @click="form.status = status.value" :class="[
                                'py-2 px-3 text-sm rounded-lg border transition-all flex items-center justify-center gap-1',
                                form.status === status.value
                                    ? status.activeClass
                                    : 'border-slate-700 bg-slate-800/30 text-slate-400 hover:bg-slate-800/50'
                            ]">
                            <component :is="status.icon" class="w-3 h-3" />
                            <span>{{ status.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Due Date and Time -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Due Date</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Date -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-300">
                                Date
                            </label>
                            <input v-model="dueDate" type="date"
                                class="w-full px-3 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50" />
                        </div>

                        <!-- Time -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-300">
                                Time
                            </label>
                            <div class="flex gap-2">
                                <select v-model="dueHour"
                                    class="flex-1 px-2 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-400/20">
                                    <option :value="null">--</option>
                                    <option v-for="h in 24" :key="h - 1" :value="h - 1">
                                        {{ String(h - 1).padStart(2, '0') }}
                                    </option>
                                </select>
                                <span class="text-slate-400 py-2.5">:</span>
                                <select v-model="dueMinute"
                                    class="flex-1 px-2 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-400/20">
                                    <option :value="null">--</option>
                                    <option value="0">00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Quick date buttons -->
                    <div class="flex gap-2">
                        <button v-for="quick in quickDates" :key="quick.label" type="button"
                            @click="setQuickDate(quick.value)"
                            class="px-3 py-1.5 text-xs font-medium bg-slate-800/30 border border-slate-700/50 rounded-lg text-slate-400 hover:bg-slate-800/50 hover:text-slate-300 transition-all">
                            {{ quick.label }}
                        </button>
                    </div>

                    <!-- Visual due date display -->
                    <div v-if="dueDate" class="p-3 bg-slate-800/30 rounded-lg">
                        <div class="flex items-center gap-2 text-sm">
                            <Calendar class="w-4 h-4 text-green-400" />
                            <span class="text-slate-300">
                                Due: {{ formatDueDateTime() }}
                            </span>
                            <button v-if="dueDate" type="button" @click="clearDueDate"
                                class="ml-auto text-slate-500 hover:text-red-400 transition-colors">
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estimated Time -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">
                        Estimated Time (minutes)
                    </label>
                    <div class="flex gap-2">
                        <input v-model.number="form.estimated_minutes" type="number" min="5" step="5" placeholder="30"
                            class="flex-1 px-3 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20" />
                        <!-- Quick time buttons -->
                        <div class="flex gap-1">
                            <button v-for="time in [15, 30, 60, 120]" :key="time" type="button"
                                @click="form.estimated_minutes = time" :class="[
                                    'px-2 py-1 rounded text-xs font-medium transition-all',
                                    form.estimated_minutes === time
                                        ? 'bg-green-400/20 text-green-300 border border-green-400/50'
                                        : 'bg-slate-800/30 text-slate-400 border border-slate-700/50 hover:bg-slate-800/50'
                                ]">
                                {{ time < 60 ? time + 'm' : (time / 60) + 'h' }} </button>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label for="description" class="block text-sm font-medium text-slate-300">
                        Description
                    </label>
                    <textarea id="description" v-model="form.description" rows="3"
                        placeholder="Add any additional details..."
                        class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all resize-none" />
                </div>

                <!-- Labels -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">
                        Labels
                    </label>
                    <div class="flex gap-2">
                        <input v-model="newLabel" type="text" placeholder="Add a label..."
                            class="flex-1 px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20"
                            @keypress.enter.prevent="addLabel" />
                        <Button type="button" @click="addLabel" variant="outline" size="sm"
                            class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                            <Plus class="w-4 h-4" />
                        </Button>
                    </div>
                    <div v-if="form.labels.length > 0" class="flex flex-wrap gap-2">
                        <Badge v-for="(label, index) in form.labels" :key="index"
                            class="bg-green-400/20 text-green-400 border-green-400/30 px-2 py-1">
                            {{ label }}
                            <button type="button" @click="removeLabel(index)"
                                class="ml-2 hover:text-red-400 transition-colors">
                                <X class="w-3 h-3" />
                            </button>
                        </Badge>
                    </div>
                </div>

                <!-- Form Actions -->
                <DialogFooter class="gap-3 pt-4 border-t border-slate-800/30">
                    <Button v-if="isEditing" type="button" variant="destructive" @click="deleteTask(task.id)"
                        class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                        Delete
                    </Button>

                    <Button type="button" variant="outline" @click="closeModal"
                        class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                        Cancel
                    </Button>

                    <Button type="submit" :disabled="processing"
                        class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 min-w-24">
                        <span v-if="processing" class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin">
                            </div>
                            {{ isEditing ? 'Saving...' : 'Creating...' }}
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <component :is="isEditing ? Save : Plus" class="w-4 h-4" />
                            {{ isEditing ? 'Save' : 'Create' }}
                        </span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
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
    Plus,
    X,
    Save,
    Calendar,
    Circle,
    PlayCircle,
    PauseCircle,
    CheckCircle
} from 'lucide-vue-next'
import { useTranslations } from '@/composables/useTranslations'

const { __ } = useTranslations()

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    task: {
        type: Object,
        default: null
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    processing: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'submit'])

const isEditing = computed(() => !!props.task)

// Priority options
const priorities = [
    { value: 'low', label: 'Low', activeClass: 'bg-slate-600/20 text-slate-300 border border-slate-600/50' },
    { value: 'medium', label: 'Med', activeClass: 'bg-yellow-400/20 text-yellow-300 border border-yellow-400/50' },
    { value: 'high', label: 'High', activeClass: 'bg-orange-400/20 text-orange-300 border border-orange-400/50' },
    { value: 'urgent', label: 'Urgent', activeClass: 'bg-red-400/20 text-red-300 border border-red-400/50' }
]

// Status options
const statuses = [
    { value: 'todo', label: 'Todo', icon: Circle, activeClass: 'bg-slate-600/20 text-slate-300 border-slate-600/50' },
    { value: 'in_progress', label: 'In Progress', icon: PlayCircle, activeClass: 'bg-blue-400/20 text-blue-300 border-blue-400/50' },
    { value: 'blocked', label: 'Blocked', icon: PauseCircle, activeClass: 'bg-orange-400/20 text-orange-300 border-orange-400/50' },
    { value: 'done', label: 'Done', icon: CheckCircle, activeClass: 'bg-green-400/20 text-green-300 border-green-400/50' }
]

// Quick date options
const quickDates = [
    { label: 'Today', value: 0 },
    { label: 'Tomorrow', value: 1 },
    { label: 'In 3 days', value: 3 },
    { label: 'Next week', value: 7 },
    { label: 'No due date', value: null }
]

// Form state
const form = useForm({
    title: '',
    description: '',
    type: 'random',
    status: 'todo',
    priority: 'medium',
    due_date: null,
    estimated_minutes: null,
    actual_minutes: null,
    labels: []
})

// Date/time state
const dueDate = ref('')
const dueHour = ref(null)
const dueMinute = ref(null)
const newLabel = ref('')

// Watch for date/time changes and update form
watch([dueDate, dueHour, dueMinute], () => {
    if (dueDate.value && dueHour.value !== null && dueMinute.value !== null) {
        const date = new Date(dueDate.value)
        date.setHours(dueHour.value, dueMinute.value, 0, 0)
        form.due_date = date.toISOString()
    } else if (dueDate.value && (dueHour.value === null || dueMinute.value === null)) {
        // If only date is set, set time to end of day (23:59)
        const date = new Date(dueDate.value)
        date.setHours(23, 59, 0, 0)
        form.due_date = date.toISOString()
    } else {
        form.due_date = null
    }
})

// Initialize form when task changes
watch(() => props.task, (newTask) => {
    if (newTask) {
        form.title = newTask.title || ''
        form.description = newTask.description || ''
        form.type = newTask.type || 'random'
        form.status = newTask.status || 'todo'
        form.priority = newTask.priority || 'medium'
        form.estimated_minutes = newTask.estimated_minutes || null
        form.actual_minutes = newTask.actual_minutes || null
        form.labels = newTask.labels || []

        if (newTask.due_date) {
            const date = new Date(newTask.due_date)
            dueDate.value = date.toISOString().split('T')[0]
            dueHour.value = date.getHours()
            dueMinute.value = Math.floor(date.getMinutes() / 15) * 15
            form.due_date = newTask.due_date
        } else {
            clearDueDate()
        }
    } else {
        // New task - set smart defaults
        form.reset()
        form.type = 'random'
        form.status = 'todo'
        form.priority = 'medium'
        form.labels = []
        form.estimated_minutes = 30

        // Set default due date to tomorrow at 17:00
        const tomorrow = new Date()
        tomorrow.setDate(tomorrow.getDate() + 1)
        tomorrow.setHours(17, 0, 0, 0)

        dueDate.value = tomorrow.toISOString().split('T')[0]
        dueHour.value = 17
        dueMinute.value = 0
    }
}, { immediate: true })

// Methods
const setQuickDate = (days) => {
    if (days === null) {
        clearDueDate()
    } else {
        const date = new Date()
        date.setDate(date.getDate() + days)
        dueDate.value = date.toISOString().split('T')[0]

        // Set default time based on days
        if (days === 0) {
            // Today - set to end of workday
            dueHour.value = 18
            dueMinute.value = 0
        } else {
            // Future - set to mid-day
            dueHour.value = 12
            dueMinute.value = 0
        }
    }
}

const clearDueDate = () => {
    dueDate.value = ''
    dueHour.value = null
    dueMinute.value = null
    form.due_date = null
}

const formatDueDateTime = () => {
    if (!form.due_date) return ''

    const date = new Date(form.due_date)
    const dateStr = date.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
    })

    if (dueHour.value !== null && dueMinute.value !== null) {
        const timeStr = date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        })
        return `${dateStr} at ${timeStr}`
    }

    return dateStr
}

const addLabel = () => {
    if (newLabel.value.trim() && !form.labels.includes(newLabel.value.trim())) {
        form.labels.push(newLabel.value.trim())
        newLabel.value = ''
    }
}

const removeLabel = (index) => {
    form.labels.splice(index, 1)
}

const handleOpenChange = (open) => {
    if (!open) {
        closeModal()
    }
}

const closeModal = () => {
    emit('close')
}

const submit = () => {
    if (!form.title || !form.type || !form.priority) {
        alert('Please fill in all required fields!')
        return
    }

    const formData = { ...form.data() }

    emit('submit', {
        data: formData,
        isEditing: isEditing.value,
        taskId: props.task?.id
    })
}




const deleteTask = (taskId) => {

    router.delete(route('tasks.destroy', taskId))
    emit('close')

}
</script>