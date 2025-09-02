<!-- resources/js/pages/tasks/Index.vue -->
<template>
    <Layout title="Tasks">
        <div class="p-6 space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2
                        class="text-2xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
                        Tasks
                    </h2>
                    <p class="text-slate-400 text-sm">
                        Manage your tasks and stay productive
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm"
                                class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                                <Filter class="w-4 h-4 mr-2" />
                                Filter
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56 bg-slate-900 border-slate-700">
                            <DropdownMenuItem>All Tasks</DropdownMenuItem>
                            <DropdownMenuItem>Todo</DropdownMenuItem>
                            <DropdownMenuItem>In Progress</DropdownMenuItem>
                            <DropdownMenuItem>Done</DropdownMenuItem>
                            <DropdownMenuSeparator class="bg-slate-700" />
                            <DropdownMenuItem>High Priority</DropdownMenuItem>
                            <DropdownMenuItem>Due Today</DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <!-- BOTÃO CORRIGIDO - SEM LINK, COM @CLICK -->
                    <Button @click="openCreateModal"
                        class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600">
                        <Plus class="w-4 h-4 mr-2" />
                        New Task
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-slate-400 text-sm">Total Tasks</p>
                                <p class="text-2xl font-bold text-green-400">{{ stats.total }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-lg bg-green-400/20 flex items-center justify-center">
                                <CheckSquare class="w-6 h-6 text-green-400" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-slate-400 text-sm">In Progress</p>
                                <p class="text-2xl font-bold text-orange-400">{{ stats.in_progress }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-lg bg-orange-400/20 flex items-center justify-center">
                                <Clock class="w-6 h-6 text-orange-400" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-slate-400 text-sm">Completed</p>
                                <p class="text-2xl font-bold text-purple-400">{{ stats.completed }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-lg bg-purple-400/20 flex items-center justify-center">
                                <CheckCircle class="w-6 h-6 text-purple-400" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-slate-400 text-sm">Due Today</p>
                                <p class="text-2xl font-bold text-red-400">{{ stats.due_today }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-lg bg-red-400/20 flex items-center justify-center">
                                <AlertCircle class="w-6 h-6 text-red-400" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tasks List -->
            <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                <CardContent class="p-0">
                    <div class="divide-y divide-slate-800/30">
                        <div v-for="task in tasks.data" :key="task.id"
                            class="p-6 hover:bg-slate-800/30 transition-colors cursor-pointer group"
                            @click.stop="openEditModal(task)">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4 flex-1 min-w-0">
                                    <!-- Status Checkbox -->
                                    <button @click.stop="toggleTaskStatus(task)" :class="[
                                        'w-5 h-5 rounded border-2 flex items-center justify-center mt-0.5 transition-all duration-200',
                                        task.status === 'done'
                                            ? 'bg-green-400 border-green-400'
                                            : 'border-slate-600 hover:border-green-400'
                                    ]">
                                        <Check v-if="task.status === 'done'" class="w-3 h-3 text-slate-900" />
                                    </button>

                                    <!-- Task Content -->
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <div class="flex items-center gap-3">
                                            <h3 :class="[
                                                'font-medium truncate',
                                                task.status === 'done' ? 'line-through text-slate-500' : 'text-slate-200'
                                            ]">
                                                {{ task.title }}
                                            </h3>

                                            <!-- Priority Badge -->
                                            <Badge :variant="getPriorityVariant(task.priority)"
                                                :class="getPriorityClasses(task.priority)" class="shrink-0">
                                                {{ task.priority.toUpperCase() }}
                                            </Badge>

                                            <!-- Type Badge -->
                                            <Badge variant="secondary"
                                                class="shrink-0 bg-slate-800 text-slate-300 border-slate-700">
                                                {{ formatType(task.type) }}
                                            </Badge>
                                        </div>

                                        <p v-if="task.description" class="text-slate-400 text-sm line-clamp-2">
                                            {{ task.description }}
                                        </p>

                                        <!-- Task Meta -->
                                        <div class="flex items-center gap-4 text-xs text-slate-500">
                                            <span v-if="task.due_date" class="flex items-center gap-1">
                                                <Calendar class="w-3 h-3" />
                                                {{ formatDate(task.due_date) }}
                                            </span>
                                            <span v-if="task.estimated_minutes" class="flex items-center gap-1">
                                                <Clock class="w-3 h-3" />
                                                {{ task.estimated_minutes }}min
                                            </span>
                                            <span v-if="task.time_boxes?.length" class="flex items-center gap-1">
                                                <Square class="w-3 h-3" />
                                                {{ task.time_boxes.length }} time box(es)
                                            </span>
                                            <!-- COUNTDOWN ADICIONADO -->
                                            <TaskCountdown v-if="task.due_date && task.status !== 'done'"
                                                :due-date="task.due_date" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="sm"
                                            class="opacity-0 group-hover:opacity-100 transition-opacity" @click.stop>
                                            <MoreHorizontal class="w-4 h-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48 bg-slate-900 border-slate-700">
                                        <!-- EDIT CORRIGIDO - CHAMA MODAL EM VEZ DE NAVEGAR -->
                                        <DropdownMenuItem @click.stop="openEditModal(task)">
                                            <Edit class="mr-2 h-4 w-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click.stop="duplicateTask(task)">
                                            <Copy class="mr-2 h-4 w-4" />
                                            Duplicate
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator class="bg-slate-700" />
                                        <DropdownMenuItem class="text-red-400" @click.stop="deleteTask(task.id)">
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

            <!-- Pagination -->
            <div v-if="tasks.last_page > 1" class="flex justify-center">
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" :disabled="!tasks.prev_page_url"
                        @click="navigateToPage(tasks.current_page - 1)"
                        class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                        Previous
                    </Button>

                    <span class="px-4 py-2 text-sm text-slate-400">
                        Page {{ tasks.current_page }} of {{ tasks.last_page }}
                    </span>

                    <Button variant="outline" size="sm" :disabled="!tasks.next_page_url"
                        @click="navigateToPage(tasks.current_page + 1)"
                        class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                        Next
                    </Button>
                </div>
            </div>
        </div>

        <!-- TASK FORM MODAL -->
        <TaskFormModal :is-open="showTaskModal" :task="editingTask" :errors="formErrors" :processing="formProcessing"
            @close="closeTaskModal" @submit="handleTaskSubmit" />
    </Layout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import Layout from '@/layouts/Layout.vue'
import TaskFormModal from '@/components/TaskFormModal.vue'
import TaskCountdown from '@/components/TaskCountdown.vue'
import {
    CheckSquare,
    Clock,
    Calendar,
    Plus,
    Filter,
    Check,
    CheckCircle,
    AlertCircle,
    MoreHorizontal,
    Edit,
    Copy,
    Trash2,
    Square
} from 'lucide-vue-next'
import {
    Card,
    CardContent,
} from '@/components/ui/card'
import {
    Button,
} from '@/components/ui/button'
import { Badge } from 'lucide-vue-next'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const props = defineProps({
    tasks: Object,
})

// Modal state
const showTaskModal = ref(false)
const editingTask = ref(null)
const formErrors = ref({})
const formProcessing = ref(false)

// Form for handling submissions
const form = useForm({})

// Computed stats baseado nas tasks
const stats = computed(() => {
    const allTasks = props.tasks.data || []
    return {
        total: allTasks.length,
        in_progress: allTasks.filter(t => t.status === 'in_progress').length,
        completed: allTasks.filter(t => t.status === 'done').length,
        due_today: allTasks.filter(t => {
            if (!t.due_date) return false
            const today = new Date().toDateString()
            return new Date(t.due_date).toDateString() === today
        }).length
    }
})

// Utility functions
const formatType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    })
}

const getPriorityVariant = (priority) => {
    const variants = {
        low: 'secondary',
        medium: 'secondary',
        high: 'destructive',
        urgent: 'destructive'
    }
    return variants[priority] || 'secondary'
}

const getPriorityClasses = (priority) => {
    const classes = {
        low: 'bg-slate-800 text-slate-300 border-slate-700',
        medium: 'bg-orange-400/20 text-orange-400 border-orange-400/30',
        high: 'bg-red-400/20 text-red-400 border-red-400/30',
        urgent: 'bg-red-500/20 text-red-300 border-red-500/30 animate-pulse'
    }
    return classes[priority] || 'bg-slate-800 text-slate-300'
}

// Modal functions
const openCreateModal = () => {
    editingTask.value = null
    formErrors.value = {}
    showTaskModal.value = true
}

const openEditModal = (task) => {
    editingTask.value = task
    formErrors.value = {}
    showTaskModal.value = true
}

const closeTaskModal = () => {
    showTaskModal.value = false
    editingTask.value = null
    formErrors.value = {}
    formProcessing.value = false
}

// Form submission handler
const handleTaskSubmit = ({ data, isEditing, taskId }) => {
    formProcessing.value = true
    formErrors.value = {}

    if (isEditing) {
        router.put(route('tasks.update', taskId), data, {
            onSuccess: () => {
                closeTaskModal()
                router.reload({ only: ['tasks'] })
            },
            onError: (errors) => {
                formErrors.value = errors
            },
            onFinish: () => {
                formProcessing.value = false
            }
        })
    } else {
        router.post(route('tasks.store'), data, {
            onSuccess: () => {
                closeTaskModal()
                router.reload({ only: ['tasks'] })
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

// Navigation functions
// const navigateToTask = (taskId) => {
//     router.visit(route('tasks.show', taskId))
// }

const navigateToPage = (page) => {
    router.visit(route('tasks.index', { page }))
}

// Actions
const toggleTaskStatus = (task) => {
    const newStatus = task.status === 'done' ? 'todo' : 'done'
    router.patch(route('tasks.status', task.id), {
        status: newStatus
    }, {
        preserveScroll: true
    })
}

const duplicateTask = (task) => {
    // Create new task with same data but reset some fields
    const duplicatedData = {
        title: `${task.title} (Copy)`,
        description: task.description,
        type: task.type,
        priority: task.priority,
        status: 'todo',
        estimated_minutes: task.estimated_minutes,
        labels: task.labels || [],
        due_date: ''
    }

    router.post(route('tasks.store'), duplicatedData, {
        onSuccess: () => {
            // Task will be added to the list automatically
        }
    })
}

const deleteTask = (taskId) => {
    router.delete(route('tasks.destroy', taskId))
}
</script>