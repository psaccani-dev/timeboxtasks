<!-- resources/js/components/TaskFormModal.vue -->
<template>
    <Dialog :open="isOpen" @update:open="handleOpenChange">
        <DialogContent class="w-[70vw] min-w-[800px] max-h-[95vh] overflow-y-auto bg-slate-900 border-slate-800">
            <DialogHeader>
                <DialogTitle
                    class="text-2xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
                    {{ isEditing ? 'Edit Task' : 'Create New Task' }}
                </DialogTitle>
                <DialogDescription class="text-slate-400">
                    {{ isEditing ? 'Update the task details below' : 'Fill in the details below to create a new task' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-8 py-4">
                <!-- Basic Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-200 border-b border-slate-800/30 pb-3">
                        Basic Information
                    </h3>

                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="title" class="block text-sm font-medium text-slate-300">
                            Title <span class="text-red-400">*</span>
                        </label>
                        <input id="title" v-model="form.title" type="text" required placeholder="Enter task title..."
                            class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all"
                            :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.title }" />
                        <p v-if="errors.title" class="text-red-400 text-sm">{{ errors.title }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-medium text-slate-300">
                            Description
                        </label>
                        <textarea id="description" v-model="form.description" rows="3"
                            placeholder="Describe your task in detail..."
                            class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all resize-none"
                            :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.description }" />
                        <p v-if="errors.description" class="text-red-400 text-sm">{{ errors.description }}</p>
                    </div>
                </div>

                <!-- Classification -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-200 border-b border-slate-800/30 pb-3">
                        Classification
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Type -->
                        <div class="space-y-2">
                            <label for="type" class="block text-sm font-medium text-slate-300">
                                Type <span class="text-red-400">*</span>
                            </label>
                            <select id="type" v-model="form.type" required
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all"
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
                            <label for="priority" class="block text-sm font-medium text-slate-300">
                                Priority <span class="text-red-400">*</span>
                            </label>
                            <select id="priority" v-model="form.priority" required
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all"
                                :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.priority }">
                                <option value="">Select priority...</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <p v-if="errors.priority" class="text-red-400 text-sm">{{ errors.priority }}</p>
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <label for="status" class="block text-sm font-medium text-slate-300">
                                Status
                            </label>
                            <select id="status" v-model="form.status"
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all">
                                <option value="todo">Todo</option>
                                <option value="in_progress">In Progress</option>
                                <option value="blocked">Blocked</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Time & Schedule -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-200 border-b border-slate-800/30 pb-3">
                        Time & Schedule
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Due Date -->
                        <div class="space-y-2">
                            <label for="due_date" class="block text-sm font-medium text-slate-300">
                                Due Date
                            </label>
                            <input id="due_date" v-model="form.due_date" type="datetime-local"
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all"
                                :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.due_date }" />
                            <p v-if="errors.due_date" class="text-red-400 text-sm">{{ errors.due_date }}</p>
                        </div>

                        <!-- Estimated Time -->
                        <div class="space-y-2">
                            <label for="estimated_minutes" class="block text-sm font-medium text-slate-300">
                                Estimated Time (minutes)
                            </label>
                            <input id="estimated_minutes" v-model="form.estimated_minutes" type="number" min="1"
                                max="1440" placeholder="30"
                                class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all"
                                :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.estimated_minutes }" />
                            <p v-if="errors.estimated_minutes" class="text-red-400 text-sm">{{ errors.estimated_minutes
                                }}</p>
                        </div>
                    </div>

                    <!-- Actual Time (only for editing) -->
                    <div v-if="isEditing" class="space-y-2">
                        <label for="actual_minutes" class="block text-sm font-medium text-slate-300">
                            Actual Time (minutes)
                        </label>
                        <input id="actual_minutes" v-model="form.actual_minutes" type="number" min="1" placeholder="45"
                            class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all"
                            :class="{ 'border-red-400/50 focus:ring-red-400/20': errors.actual_minutes }" />
                        <p v-if="errors.actual_minutes" class="text-red-400 text-sm">{{ errors.actual_minutes }}</p>
                    </div>
                </div>

                <!-- Labels -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-200 border-b border-slate-800/30 pb-3">
                        Labels
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <input v-model="newLabel" type="text" placeholder="Add a label..."
                                class="flex-1 px-4 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all"
                                @keypress.enter.prevent="addLabel" />
                            <Button type="button" @click="addLabel" variant="outline" size="sm"
                                class="border-slate-700 bg-slate-800/50 hover:bg-slate-800 shrink-0">
                                <Plus class="w-4 h-4" />
                            </Button>
                        </div>

                        <div v-if="form.labels.length > 0" class="flex flex-wrap gap-2">
                            <Badge v-for="(label, index) in form.labels" :key="index" variant="secondary"
                                class="bg-green-400/20 text-green-400 border-green-400/30 px-3 py-1">
                                {{ label }}
                                <button type="button" @click="removeLabel(index)"
                                    class="ml-2 hover:text-red-400 transition-colors">
                                    <X class="w-3 h-3" />
                                </button>
                            </Badge>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <DialogFooter class="gap-3 pt-6 border-t border-slate-800/30">
                    <Button type="button" variant="outline" @click="closeModal"
                        class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                        Cancel
                    </Button>

                    <Button type="submit" :disabled="props.processing"
                        class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 min-w-24">
                        <span v-if="props.processing" class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin">
                            </div>
                            {{ isEditing ? 'Saving...' : 'Creating...' }}
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <component :is="isEditing ? Save : Plus" class="w-4 h-4" />
                            {{ isEditing ? 'Save Changes' : 'Create Task' }}
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
import { Badge } from 'lucide-vue-next'
import {
    Plus,
    X,
    Save
} from 'lucide-vue-next'

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

const newLabel = ref('')

const isEditing = computed(() => !!props.task)

// Format due_date for datetime-local input
const formatDateForInput = (date) => {
    if (!date) return ''
    const d = new Date(date)
    return d.toISOString().slice(0, 16)
}

// Get tomorrow's date as default for new tasks
const getTomorrowDefault = () => {
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    tomorrow.setHours(9, 0, 0, 0) // 9:00 AM
    return tomorrow.toISOString().slice(0, 16)
}

const form = useForm({
    title: '',
    description: '',
    type: '',
    status: 'todo',
    priority: '',
    due_date: '',
    estimated_minutes: '',
    actual_minutes: '',
    labels: []
})

// Watch for task changes to populate form
watch(() => props.task, (newTask) => {
    if (newTask) {
        // Editing mode - populate with task data but keep defaults for empty fields
        form.title = newTask.title || ''
        form.description = newTask.description || ''
        form.type = newTask.type || 'random' // Default to random
        form.status = newTask.status || 'todo'
        form.priority = newTask.priority || 'medium' // Default to medium
        form.due_date = formatDateForInput(newTask.due_date) || getTomorrowDefault()
        form.estimated_minutes = newTask.estimated_minutes || 30 // Default 30 minutes even in edit
        form.actual_minutes = newTask.actual_minutes || ''
        form.labels = newTask.labels || []
    } else {
        // Create mode - reset form with defaults
        form.reset()
        form.type = 'random' // Default type
        form.status = 'todo'
        form.priority = 'medium' // Default priority
        form.due_date = getTomorrowDefault() // Default to tomorrow 9AM
        form.estimated_minutes = 30 // Default 30 minutes
        form.labels = []
    }
}, { immediate: true })

// Reset form when modal closes
watch(() => props.isOpen, (isOpen) => {
    if (!isOpen) {
        newLabel.value = ''
        if (!props.task) {
            form.reset()
            form.status = 'todo'
            form.labels = []
        }
    }
})

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
    // Validação básica
    if (!form.title || !form.type || !form.priority) {
        alert('Por favor, preencha todos os campos obrigatórios!')
        return
    }

    const formData = { ...form.data() }

    emit('submit', {
        data: formData,
        isEditing: isEditing.value,
        taskId: props.task?.id
    })
}
</script>