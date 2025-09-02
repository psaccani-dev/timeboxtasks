<template>
    <Layout title="Tasks" @open-task-modal="openCreateModal">
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
                    <!-- Search Bar -->
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input v-model="searchQuery" @input="debouncedSearch" type="text" placeholder="Search tasks..."
                            class="pl-10 pr-4 py-2 bg-slate-800/50 border border-slate-700 rounded-lg text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50 transition-all" />
                    </div>

                    <!-- Filter Dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm"
                                class="border-slate-700 bg-slate-800/50 hover:bg-slate-800 relative">
                                <Filter class="w-4 h-4 mr-2" />
                                Filter
                                <Badge v-if="activeFiltersCount > 0"
                                    class="ml-2 bg-green-400/20 text-green-400 border-green-400/30 px-1.5 py-0 h-5 min-w-[20px]">
                                    {{ activeFiltersCount }}
                                </Badge>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-64 bg-slate-900 border-slate-700 p-4">
                            <div class="space-y-4">
                                <!-- Status Filter -->
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-slate-400">Status</label>
                                    <select v-model="filters.status" @change="applyFilters"
                                        class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-md text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50">
                                        <option value="">All Status</option>
                                        <option value="todo">Todo</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="blocked">Blocked</option>
                                        <option value="done">Done</option>
                                    </select>
                                </div>

                                <!-- Priority Filter -->
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-slate-400">Priority</label>
                                    <select v-model="filters.priority" @change="applyFilters"
                                        class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-md text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50">
                                        <option value="">All Priorities</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>

                                <!-- Due Date Filter -->
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-slate-400">Due Date</label>
                                    <select v-model="filters.due_filter" @change="applyFilters"
                                        class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-md text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400/50">
                                        <option value="">All Dates</option>
                                        <option value="overdue">Overdue</option>
                                        <option value="today">Due Today</option>
                                        <option value="week">Next 7 Days</option>
                                        <option value="month">Next Month</option>
                                    </select>
                                </div>

                                <DropdownMenuSeparator class="bg-slate-700" />

                                <!-- Clear Filters Button -->
                                <Button v-if="activeFiltersCount > 0" @click="clearFilters" variant="ghost" size="sm"
                                    class="w-full text-red-400 hover:bg-red-400/10">
                                    <X class="w-4 h-4 mr-2" />
                                    Clear All Filters
                                </Button>
                            </div>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Button @click="openCreateModal"
                        class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600">
                        <Plus class="w-4 h-4 mr-2" />
                        New Task
                    </Button>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="activeFiltersCount > 0" class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-slate-400">Active filters:</span>

                <Badge v-if="filters.status" class="bg-slate-800 text-slate-300 border-slate-700 pr-1">
                    Status: {{ formatStatus(filters.status) }}
                    <button @click="filters.status = ''; applyFilters()" class="ml-2 hover:text-red-400">
                        <X class="w-3 h-3" />
                    </button>
                </Badge>

                <Badge v-if="filters.priority" :class="getPriorityClasses(filters.priority) + ' pr-1'">
                    Priority: {{ filters.priority.toUpperCase() }}
                    <button @click="filters.priority = ''; applyFilters()" class="ml-2 hover:text-red-400">
                        <X class="w-3 h-3" />
                    </button>
                </Badge>

                <Badge v-if="filters.due_filter" class="bg-orange-400/20 text-orange-400 border-orange-400/30 pr-1">
                    Due: {{ formatDueFilter(filters.due_filter) }}
                    <button @click="filters.due_filter = ''; applyFilters()" class="ml-2 hover:text-red-400">
                        <X class="w-3 h-3" />
                    </button>
                </Badge>

                <Badge v-if="searchQuery" class="bg-purple-400/20 text-purple-400 border-purple-400/30 pr-1">
                    Search: "{{ searchQuery }}"
                    <button @click="searchQuery = ''; applyFilters()" class="ml-2 hover:text-red-400">
                        <X class="w-3 h-3" />
                    </button>
                </Badge>
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
                    <div v-if="tasks.data.length === 0" class="p-12 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-lg bg-slate-800/50 flex items-center justify-center">
                            <CheckSquare class="w-10 h-10 text-slate-600" />
                        </div>
                        <h3 class="text-lg font-medium text-slate-300 mb-2">No tasks found</h3>
                        <p class="text-sm text-slate-500">
                            {{ activeFiltersCount > 0 ? 'Try adjusting your filters' : "Create your first task" }}
                        </p>
                    </div>

                    <div v-else class="divide-y divide-slate-800/30">
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
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { debounce } from 'lodash'
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
    Square,
    Search,
    X
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
    tasks: Object,
    filters: {
        type: Object,
        default: () => ({})
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            in_progress: 0,
            completed: 0,
            due_today: 0
        })
    }
})

// Modal state
const showTaskModal = ref(false)
const editingTask = ref(null)
const formErrors = ref({})
const formProcessing = ref(false)

// Filter state
const filters = ref({
    status: props.filters?.status || '',
    priority: props.filters?.priority || '',
    due_filter: props.filters?.due_filter || '',
})

const searchQuery = ref(props.filters?.search || '')

// Form for handling submissions
const form = useForm({})

// Computed active filters count
const activeFiltersCount = computed(() => {
    let count = 0
    if (filters.value.status) count++
    if (filters.value.priority) count++
    if (filters.value.due_filter) count++
    if (searchQuery.value) count++
    return count
})

// Apply filters
const applyFilters = () => {
    router.get(route('tasks.index'), {
        status: filters.value.status || undefined,
        priority: filters.value.priority || undefined,
        due_filter: filters.value.due_filter || undefined,
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Debounced search
const debouncedSearch = debounce(() => {
    applyFilters()
}, 500)

// Clear all filters
const clearFilters = () => {
    filters.value = {
        status: '',
        priority: '',
        due_filter: '',
    }
    searchQuery.value = ''
    applyFilters()
}

// Utility functions
const formatType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDueFilter = (filter) => {
    const labels = {
        'overdue': 'Overdue',
        'today': 'Due Today',
        'week': 'Next 7 Days',
        'month': 'Next Month'
    }
    return labels[filter] || filter
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
const navigateToPage = (page) => {
    router.get(route('tasks.index'), {
        ...filters.value,
        search: searchQuery.value,
        page
    }, {
        preserveState: true,
        preserveScroll: true,
    })
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

    router.post(route('tasks.store'), duplicatedData)
}

const deleteTask = (taskId) => {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('tasks.destroy', taskId))
    }
}
</script>