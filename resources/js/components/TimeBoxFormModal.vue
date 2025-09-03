<template>
    <Dialog :open="isOpen" @update:open="handleOpenChange">
        <DialogContent class="w-[600px] max-w-[90vw] max-h-[90vh] overflow-y-auto bg-slate-900 border-slate-800">
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
                <!-- Title -->
                <div class="space-y-2">
                    <label for="title" class="block text-sm font-medium text-slate-300">
                        Title <span class="text-red-400">*</span>
                    </label>
                    <input id="title" v-model="form.title" type="text" required
                        placeholder="e.g., Deep Work Session, Team Meeting..."
                        class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50 transition-all"
                        :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.title }" @keydown.enter.prevent />
                    <p v-if="errors.title" class="text-red-400 text-sm">{{ errors.title }}</p>
                </div>

                <!-- Type Selection -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">
                        Type <span class="text-red-400">*</span>
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        <button v-for="type in timeBoxTypes" :key="type.value" type="button"
                            @click="form.type = type.value" :class="[
                                'p-2.5 rounded-lg border transition-all text-center',
                                form.type === type.value
                                    ? type.activeClass
                                    : 'border-slate-700 bg-slate-800/30 hover:bg-slate-800/50 text-slate-300'
                            ]">
                            <component :is="type.icon" class="w-4 h-4 mx-auto mb-1" />
                            <div class="text-xs font-medium">{{ type.label }}</div>
                        </button>
                    </div>
                    <p v-if="errors.type" class="text-red-400 text-sm">{{ errors.type }}</p>
                </div>

                <!-- Date and Time -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Schedule</h3>

                    <!-- Date Picker -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">
                            Date
                        </label>
                        <input v-model="selectedDate" type="date"
                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50 transition-all" />
                    </div>

                    <!-- Time Picker - Modern Style -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-300">
                                Start Time
                            </label>
                            <div class="flex gap-2">
                                <select v-model="startHour"
                                    class="flex-1 px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50">
                                    <option v-for="h in 24" :key="h - 1" :value="h - 1">
                                        {{ String(h - 1).padStart(2, '0') }}
                                    </option>
                                </select>
                                <span class="text-slate-400 py-2">:</span>
                                <select v-model="startMinute"
                                    class="flex-1 px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50">
                                    <option value="0">00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-300">
                                Duration (minutes)
                            </label>
                            <div class="flex gap-2">
                                <input v-model.number="durationMinutes" type="number" min="15" step="15"
                                    class="flex-1 px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50" />
                                <!-- Quick duration buttons -->
                                <div class="flex gap-1">
                                    <button v-for="dur in [30, 60, 90]" :key="dur" type="button"
                                        @click="durationMinutes = dur" :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-all',
                                            durationMinutes === dur
                                                ? 'bg-blue-400/20 text-blue-300 border border-blue-400/50'
                                                : 'bg-slate-800/30 text-slate-400 border border-slate-700/50 hover:bg-slate-800/50'
                                        ]">
                                        {{ dur }}m
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Time Display -->
                    <div class="p-4 bg-slate-800/30 rounded-lg">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <Clock class="w-4 h-4 text-blue-400" />
                                <span class="text-slate-300 font-medium">
                                    {{ formatDisplayTime(computedStartTime) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-400">
                                <ArrowRight class="w-4 h-4" />
                                <span>{{ durationMinutes }} min</span>
                                <ArrowRight class="w-4 h-4" />
                            </div>
                            <div class="flex items-center gap-2">
                                <Clock class="w-4 h-4 text-green-400" />
                                <span class="text-slate-300 font-medium">
                                    {{ formatDisplayTime(computedEndTime) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Allow Overlap -->
                    <div class="flex items-center justify-between p-3 bg-slate-800/30 rounded-lg">
                        <div class="space-y-0.5">
                            <label for="allow_overlap" class="text-sm font-medium text-slate-300">
                                Allow Overlap
                            </label>
                            <p class="text-xs text-slate-500">
                                Enable if this can overlap with other time boxes
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
                    <textarea id="notes" v-model="form.notes" rows="2" placeholder="Any additional notes..."
                        class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400/20 focus:border-blue-400/50 transition-all resize-none" />
                </div>

                <!-- Task Assignment (Simplified) -->
                <div class="space-y-2" v-if="availableTasks.length > 0">
                    <label class="block text-sm font-medium text-slate-300">
                        Assign Tasks
                    </label>
                    <div class="max-h-32 overflow-y-auto space-y-1 p-2 bg-slate-800/30 rounded-lg">
                        <label v-for="task in availableTasks" :key="task.id"
                            class="flex items-center gap-2 p-2 rounded hover:bg-slate-800/50 cursor-pointer transition-all">
                            <input type="checkbox" :value="task.id" v-model="form.task_ids"
                                class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-400/20" />
                            <span class="text-sm text-slate-200 flex-1">{{ task.title }}</span>
                            <span class="text-xs text-slate-500">{{ task.estimated_minutes }}m</span>
                        </label>
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
    User,
    BookOpen,
    Briefcase,
    Home,
    Shuffle,
    Clock,
    ArrowRight
} from 'lucide-vue-next'

import { useTranslations } from '@/composables/useTranslations'
const { __ } = useTranslations()

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
    { value: 'meeting', label: 'Meeting', icon: Users, activeClass: 'bg-purple-400/20 border-purple-400/50 text-purple-300' },
    { value: 'break', label: 'Break', icon: Coffee, activeClass: 'bg-green-400/20 border-green-400/50 text-green-300' },
    { value: 'study', label: 'Study', icon: BookOpen, activeClass: 'bg-indigo-400/20 border-indigo-400/50 text-indigo-300' },
    { value: 'work', label: 'Work', icon: Briefcase, activeClass: 'bg-orange-400/20 border-orange-400/50 text-orange-300' },
    { value: 'house', label: 'House', icon: Home, activeClass: 'bg-yellow-400/20 border-yellow-400/50 text-yellow-300' },
    { value: 'random', label: 'Random', icon: Shuffle, activeClass: 'bg-pink-400/20 border-pink-400/50 text-pink-300' },
    { value: 'custom', label: 'Custom', icon: User, activeClass: 'bg-slate-400/20 border-slate-400/50 text-slate-300' },
]

// Time management state
const selectedDate = ref('')
const startHour = ref(0)
const startMinute = ref(0)
const durationMinutes = ref(60)

const form = useForm({
    title: '',
    type: 'focus',
    start_at: '',
    end_at: '',
    allow_overlap: false,
    notes: '',
    task_ids: []
})

// Computed times
const computedStartTime = computed(() => {
    const date = new Date(selectedDate.value || new Date())
    date.setHours(startHour.value, startMinute.value, 0, 0)
    return date
})

const computedEndTime = computed(() => {
    const end = new Date(computedStartTime.value)
    end.setMinutes(end.getMinutes() + durationMinutes.value)
    return end
})

// Update form when computed times change
watch([computedStartTime, computedEndTime], () => {
    form.start_at = computedStartTime.value.toISOString()
    form.end_at = computedEndTime.value.toISOString()
})

// Format time for display
const formatDisplayTime = (date) => {
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    })
}

// Initialize form based on props
watch(() => props.timeBox, (newTimeBox) => {
    if (newTimeBox) {
        // Editing existing time box
        const startDate = new Date(newTimeBox.start_at)
        const endDate = new Date(newTimeBox.end_at)

        form.title = newTimeBox.title || ''
        form.type = newTimeBox.type || 'focus'
        form.allow_overlap = newTimeBox.allow_overlap || false
        form.notes = newTimeBox.notes || ''
        form.task_ids = newTimeBox.tasks?.map(t => t.id) || []

        selectedDate.value = startDate.toISOString().split('T')[0]
        startHour.value = startDate.getHours()
        startMinute.value = Math.floor(startDate.getMinutes() / 15) * 15
        durationMinutes.value = Math.round((endDate - startDate) / 60000)
    } else {
        // New time box - set smart defaults
        const now = new Date()
        const nextHour = new Date()

        // Round to next 15 minute mark
        const minutes = now.getMinutes()
        const roundedMinutes = Math.ceil(minutes / 15) * 15

        if (roundedMinutes === 60) {
            nextHour.setHours(now.getHours() + 1, 0, 0, 0)
        } else {
            nextHour.setHours(now.getHours(), roundedMinutes, 0, 0)
        }

        form.reset()
        form.type = 'focus'
        form.allow_overlap = false
        form.task_ids = []

        selectedDate.value = nextHour.toISOString().split('T')[0]
        startHour.value = nextHour.getHours()
        startMinute.value = nextHour.getMinutes()
        durationMinutes.value = 60
    }
}, { immediate: true })

// Reset form when modal closes
watch(() => props.isOpen, (isOpen) => {
    if (!isOpen && !props.timeBox) {
        form.reset()
    }
})

const handleOpenChange = (open) => {
    if (!open) {
        closeModal()
    }
}

const closeModal = () => {
    emit('close')
}

const submit = () => {
    if (!form.title || !form.type) {
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