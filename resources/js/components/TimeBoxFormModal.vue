<template>
    <Dialog :open="isOpen" @update:open="handleOpenChange">
        <DialogContent class="w-[60vw] max-w-[700px] max-h-[90vh] overflow-y-auto bg-slate-900 border-slate-800">
            <DialogHeader>
                <DialogTitle
                    class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                    {{ isEditing ? 'Edit Time Box' : 'Create Time Box' }}
                </DialogTitle>
                <DialogDescription class="text-slate-400">
                    {{ isEditing ? 'Update your time box details' : 'Schedule a focused work session' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 py-4">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Basic Info</h3>

                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="title" class="block text-sm font-medium text-slate-300">
                            Title <span class="text-red-400">*</span>
                        </label>
                        <input id="title" v-model="form.title" type="text" required
                            placeholder="e.g., Deep Work Session, Team Meeting..."
                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50 transition-all"
                            :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.title }" />
                        <p v-if="errors.title" class="text-red-400 text-sm">{{ errors.title }}</p>
                    </div>

                    <!-- Type -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">
                            Type <span class="text-red-400">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            <button v-for="type in timeBoxTypes" :key="type.value" type="button"
                                @click="form.type = type.value" :class="[
                                    'p-3 rounded-lg border transition-all text-center',
                                    form.type === type.value
                                        ? type.activeClass
                                        : 'border-slate-700 bg-slate-800/30 hover:bg-slate-800/50 text-slate-300'
                                ]">
                                <component :is="type.icon" class="w-5 h-5 mx-auto mb-1" />
                                <div class="text-xs font-medium">{{ type.label }}</div>
                            </button>
                        </div>
                        <p v-if="errors.type" class="text-red-400 text-sm">{{ errors.type }}</p>
                    </div>
                </div>

                <!-- Time Settings -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Schedule</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Start Time -->
                        <div class="space-y-2">
                            <label for="start_at" class="block text-sm font-medium text-slate-300">
                                Start Time <span class="text-red-400">*</span>
                            </label>
                            <input id="start_at" v-model="form.start_at" type="datetime-local" required
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50 transition-all"
                                :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.start_at }" />
                            <p v-if="errors.start_at" class="text-red-400 text-sm">{{ errors.start_at }}</p>
                        </div>

                        <!-- End Time -->
                        <div class="space-y-2">
                            <label for="end_at" class="block text-sm font-medium text-slate-300">
                                End Time <span class="text-red-400">*</span>
                            </label>
                            <input id="end_at" v-model="form.end_at" type="datetime-local" required
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50 transition-all"
                                :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.end_at }" />
                            <p v-if="errors.end_at" class="text-red-400 text-sm">{{ errors.end_at }}</p>
                        </div>
                    </div>

                    <!-- Duration Display -->
                    <div v-if="duration" class="p-3 bg-slate-800/30 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-400">Duration</span>
                            <span class="text-sm font-medium text-slate-200">{{ duration }}</span>
                        </div>
                    </div>

                    <!-- Allow Overlap -->
                    <div class="flex items-center justify-between p-3 bg-slate-800/30 rounded-lg">
                        <div class="space-y-0.5">
                            <label for="allow_overlap" class="text-sm font-medium text-slate-300">
                                Allow Overlap
                            </label>
                            <p class="text-xs text-slate-500">
                                Enable if this time box can overlap with others
                            </p>
                        </div>
                        <button type="button" @click="form.allow_overlap = !form.allow_overlap" :class="[
                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                            form.allow_overlap ? 'bg-blue-500' : 'bg-slate-700'
                        ]">
                            <span :class="[
                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                form.allow_overlap ? 'translate-x-6' : 'translate-x-1'
                            ]" />
                        </button>
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium text-slate-300">
                        Notes
                    </label>
                    <textarea id="notes" v-model="form.notes" rows="3"
                        placeholder="Add any additional notes or context..."
                        class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50 transition-all resize-none" />
                </div>

                <!-- Task Assignment -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Assign Tasks</h3>

                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <div v-if="availableTasks.length === 0" class="text-center py-8 text-slate-500">
                            No tasks available to assign
                        </div>

                        <label v-for="task in availableTasks" :key="task.id"
                            class="flex items-center gap-3 p-3 rounded-lg border border-slate-700/50 bg-slate-800/30 hover:bg-slate-800/50 cursor-pointer transition-all">
                            <input type="checkbox" :value="task.id" v-model="form.task_ids"
                                class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-400/20" />
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-200">{{ task.title }}</span>
                                    <Badge :class="getPriorityClasses(task.priority)" class="text-xs">
                                        {{ task.priority.toUpperCase() }}
                                    </Badge>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ task.estimated_minutes || '?' }} min estimated
                                </div>
                            </div>
                        </label>
                    </div>

                    <div v-if="form.task_ids.length > 0"
                        class="p-3 bg-blue-400/10 border border-blue-400/30 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-blue-300">
                                {{ form.task_ids.length }} task(s) selected
                            </span>
                            <span class="text-sm text-blue-300">
                                Total: {{ totalTaskMinutes }} min
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <DialogFooter class="gap-3 pt-4 border-t border-slate-800/30">
                    <Button type="button" variant="outline" @click="closeModal"
                        class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                        Cancel
                    </Button>

                    <Button type="submit" :disabled="processing"
                        class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 min-w-24">
                        <span v-if="processing" class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin">
                            </div>
                            {{ isEditing ? 'Saving...' : 'Creating...' }}
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <component :is="isEditing ? Save : Plus" class="w-4 h-4" />
                            {{ isEditing ? 'Save Changes' : 'Create Time Box' }}
                        </span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
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
    Save,
    Target,
    Coffee,
    Users,
    User
} from 'lucide-vue-next'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    timeBox: {
        type: Object,
        default: null
    },
    availableTasks: {
        type: Array,
        default: () => []
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

const isEditing = computed(() => !!props.timeBox)

const timeBoxTypes = [
    { value: 'focus', label: 'Focus', icon: Target, activeClass: 'bg-blue-400/20 border-blue-400/50 text-blue-300' },
    { value: 'break', label: 'Break', icon: Coffee, activeClass: 'bg-green-400/20 border-green-400/50 text-green-300' },
    { value: 'meeting', label: 'Meeting', icon: Users, activeClass: 'bg-purple-400/20 border-purple-400/50 text-purple-300' },
    { value: 'personal', label: 'Personal', icon: User, activeClass: 'bg-orange-400/20 border-orange-400/50 text-orange-300' },
]

// Helper function to get next hour rounded time
const getNextHourTime = () => {
    const now = new Date()
    now.setHours(now.getHours() + 1, 0, 0, 0)
    return now.toISOString().slice(0, 16)
}

const form = useForm({
    title: '',
    type: 'focus',
    start_at: '',
    end_at: '',
    allow_overlap: false,
    notes: '',
    task_ids: []
})

// Calculate duration
const duration = computed(() => {
    if (!form.start_at || !form.end_at) return ''

    const start = new Date(form.start_at)
    const end = new Date(form.end_at)
    const minutes = Math.floor((end - start) / 60000)

    if (minutes < 0) return 'Invalid time range'
    if (minutes === 0) return '0 minutes'

    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60

    if (hours === 0) return `${mins} minute${mins !== 1 ? 's' : ''}`
    if (mins === 0) return `${hours} hour${hours !== 1 ? 's' : ''}`
    return `${hours} hour${hours !== 1 ? 's' : ''} ${mins} minute${mins !== 1 ? 's' : ''}`
})

// Calculate total task minutes
const totalTaskMinutes = computed(() => {
    return props.availableTasks
        .filter(t => form.task_ids.includes(t.id))
        .reduce((sum, t) => sum + (t.estimated_minutes || 0), 0)
})

// Watch for timeBox changes to populate form
watch(() => props.timeBox, (newTimeBox) => {
    if (newTimeBox) {
        form.title = newTimeBox.title || ''
        form.type = newTimeBox.type || 'focus'
        form.start_at = newTimeBox.start_at ? new Date(newTimeBox.start_at).toISOString().slice(0, 16) : ''
        form.end_at = newTimeBox.end_at ? new Date(newTimeBox.end_at).toISOString().slice(0, 16) : ''
        form.allow_overlap = newTimeBox.allow_overlap || false
        form.notes = newTimeBox.notes || ''
        form.task_ids = newTimeBox.tasks?.map(t => t.id) || []
    } else {
        // New time box - set defaults
        form.reset()
        form.type = 'focus'
        form.allow_overlap = false
        form.task_ids = []

        // Set default times (next hour for 1 hour duration)
        const startTime = getNextHourTime()
        form.start_at = startTime
        const endDate = new Date(startTime)
        endDate.setHours(endDate.getHours() + 1)
        form.end_at = endDate.toISOString().slice(0, 16)
    }
}, { immediate: true })

// Auto-adjust end time when start time changes
watch(() => form.start_at, (newStart) => {
    if (newStart && !props.timeBox) {
        const start = new Date(newStart)
        const end = new Date(form.end_at)

        // If end is before start, set end to 1 hour after start
        if (end <= start) {
            start.setHours(start.getHours() + 1)
            form.end_at = start.toISOString().slice(0, 16)
        }
    }
})

const getPriorityClasses = (priority) => {
    const classes = {
        low: 'bg-slate-800 text-slate-300 border-slate-700',
        medium: 'bg-orange-400/20 text-orange-400 border-orange-400/30',
        high: 'bg-red-400/20 text-red-400 border-red-400/30',
        urgent: 'bg-red-500/20 text-red-300 border-red-500/30'
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

const submit = () => {
    if (!form.title || !form.type || !form.start_at || !form.end_at) {
        alert('Please fill in all required fields!')
        return
    }

    const formData = { ...form.data() }

    emit('submit', {
        data: formData,
        isEditing: isEditing.value,
        timeBoxId: props.timeBox?.id
    })
}
</script>