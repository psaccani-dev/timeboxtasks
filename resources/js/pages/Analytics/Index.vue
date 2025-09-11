<template>
    <Layout title="Analytics">
        <div class="p-6 space-y-6">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                        Analytics
                    </h2>
                    <p class="text-slate-400 text-sm">
                        Track your productivity and progress over time
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Period Selector -->
                    <div class="flex rounded-lg bg-slate-800/50 p-1">
                        <button v-for="period in periods" :key="period.value" @click="changePeriod(period.value)"
                            :class="[
                                'px-3 py-1.5 rounded text-sm font-medium transition-all',
                                selectedPeriod === period.value
                                    ? 'bg-slate-700 text-white'
                                    : 'text-slate-400 hover:text-white'
                            ]">
                            {{ period.label }}
                        </button>
                    </div>



                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <CheckCircle2 class="w-5 h-5 text-green-400/50" />
                            <Badge :class="getChangeClass(metrics.tasksCompleted.change)" class="text-xs">
                                {{ metrics.tasksCompleted.change > 0 ? '+' : '' }}{{ metrics.tasksCompleted.change }}%
                            </Badge>
                        </div>
                        <p class="text-2xl font-bold text-slate-200">{{ metrics.tasksCompleted.value }}</p>
                        <p class="text-xs text-slate-400 mt-1">Tasks Completed</p>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <Clock class="w-5 h-5 text-blue-400/50" />
                            <Badge :class="getChangeClass(metrics.focusHours.change)" class="text-xs">
                                {{ metrics.focusHours.change > 0 ? '+' : '' }}{{ metrics.focusHours.change }}%
                            </Badge>
                        </div>
                        <p class="text-2xl font-bold text-slate-200">{{ metrics.focusHours.value }}h</p>
                        <p class="text-xs text-slate-400 mt-1">Focus Hours</p>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <Target class="w-5 h-5 text-purple-400/50" />
                            <Badge :class="getChangeClass(metrics.completionRate.change)" class="text-xs">
                                {{ metrics.completionRate.change > 0 ? '+' : '' }}{{ metrics.completionRate.change }}%
                            </Badge>
                        </div>
                        <p class="text-2xl font-bold text-slate-200">{{ metrics.completionRate.value }}%</p>
                        <p class="text-xs text-slate-400 mt-1">Completion Rate</p>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <Zap class="w-5 h-5 text-orange-400/50" />
                            <Badge :class="getChangeClass(metrics.productivity.change)" class="text-xs">
                                {{ metrics.productivity.change > 0 ? '+' : '' }}{{ metrics.productivity.change }}%
                            </Badge>
                        </div>
                        <p class="text-2xl font-bold text-slate-200">{{ metrics.productivity.value }}</p>
                        <p class="text-xs text-slate-400 mt-1">Productivity Score</p>
                    </CardContent>
                </Card>

                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <Flame class="w-5 h-5 text-red-400/50" />
                            <Badge class="bg-red-400/20 text-red-400 text-xs">
                                {{ metrics.streak.value }} days
                            </Badge>
                        </div>
                        <p class="text-2xl font-bold text-slate-200">{{ metrics.streak.value }}</p>
                        <p class="text-xs text-slate-400 mt-1">Current Streak</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Row 1 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Task Completion Trend -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardHeader class="border-b border-slate-800/30">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-200">Task Completion Trend</h3>
                            <span class="text-xs text-slate-400">Last {{ selectedPeriod }}</span>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4">
                        <div class="h-64">
                            <div class="relative h-full">
                                <!-- Y-axis labels -->
                                <div
                                    class="absolute left-0 top-0 bottom-0 flex flex-col justify-between text-xs text-slate-500 pr-2">
                                    <span>{{ maxTaskValue }}</span>
                                    <span>{{ Math.round(maxTaskValue * 0.75) }}</span>
                                    <span>{{ Math.round(maxTaskValue * 0.5) }}</span>
                                    <span>{{ Math.round(maxTaskValue * 0.25) }}</span>
                                    <span>0</span>
                                </div>

                                <!-- Chart area -->
                                <div class="ml-8 h-full relative">
                                    <!-- Grid lines -->
                                    <div class="absolute inset-0 flex flex-col justify-between">
                                        <div class="border-t border-slate-800/30"></div>
                                        <div class="border-t border-slate-800/30"></div>
                                        <div class="border-t border-slate-800/30"></div>
                                        <div class="border-t border-slate-800/30"></div>
                                        <div class="border-t border-slate-800/30"></div>
                                    </div>

                                    <!-- Line chart -->
                                    <svg v-if="taskChartPoints.length > 0" class="absolute inset-0 w-full h-full">
                                        <defs>
                                            <linearGradient id="gradient1" x1="0%" y1="0%" x2="0%" y2="100%">
                                                <stop offset="0%" style="stop-color:rgb(34,197,94);stop-opacity:0.3" />
                                                <stop offset="100%" style="stop-color:rgb(34,197,94);stop-opacity:0" />
                                            </linearGradient>
                                        </defs>

                                        <!-- Area -->
                                        <path :d="taskChartArea" fill="url(#gradient1)" />

                                        <!-- Line -->
                                        <path :d="taskChartLine" fill="none" stroke="rgb(34,197,94)" stroke-width="2" />

                                        <!-- Points -->
                                        <circle v-for="(point, i) in taskChartPoints" :key="i" :cx="point.x"
                                            :cy="point.y" r="3" fill="rgb(34,197,94)" />
                                    </svg>

                                    <!-- X-axis labels -->
                                    <div
                                        class="absolute bottom-0 left-0 right-0 flex justify-between text-xs text-slate-500 pt-2">
                                        <span v-for="label in chartLabels" :key="label">{{ label }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Time Distribution -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardHeader class="border-b border-slate-800/30">
                        <h3 class="text-lg font-semibold text-slate-200">Time Distribution</h3>
                    </CardHeader>
                    <CardContent class="p-4">
                        <div class="h-64 flex items-center justify-center">
                            <!-- Donut Chart -->
                            <div class="relative" v-if="timeDistribution.length > 0">
                                <svg class="w-48 h-48 transform -rotate-90">
                                    <circle cx="96" cy="96" r="72" fill="none" stroke="rgb(30,41,59)"
                                        stroke-width="24" />

                                    <circle v-for="(segment, i) in timeDistributionSegments" :key="i" cx="96" cy="96"
                                        r="72" fill="none" :stroke="segment.color" stroke-width="24"
                                        :stroke-dasharray="`${segment.dashArray} ${circumference - segment.dashArray}`"
                                        :stroke-dashoffset="-segment.offset" class="transition-all duration-500" />
                                </svg>

                                <!-- Center text -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <p class="text-2xl font-bold text-slate-200">{{ totalHours }}h</p>
                                    <p class="text-xs text-slate-400">Total</p>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="text-center text-slate-400">
                                <Clock class="w-12 h-12 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No time data available</p>
                            </div>

                            <!-- Legend -->
                            <div v-if="timeDistribution.length > 0" class="ml-8 space-y-2">
                                <div v-for="item in timeDistribution.filter(i => i.hours > 0)" :key="item.type"
                                    class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" :style="`background: ${item.color}`"></div>
                                    <span class="text-sm text-slate-300">{{ item.label }}</span>
                                    <span class="text-sm text-slate-500 ml-auto">{{ item.hours }}h</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Row 2 -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Priority Distribution -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardHeader class="border-b border-slate-800/30">
                        <h3 class="text-lg font-semibold text-slate-200">Priority Distribution</h3>
                    </CardHeader>
                    <CardContent class="p-4">
                        <div class="space-y-3">
                            <div v-for="priority in priorityDistribution" :key="priority.name">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-slate-300">{{ priority.name }}</span>
                                    <span class="text-sm text-slate-500">{{ priority.count }} tasks</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-2">
                                    <div :class="priority.colorClass"
                                        class="h-2 rounded-full transition-all duration-500"
                                        :style="`width: ${priority.percentage}%`"></div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Best Productive Hours -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardHeader class="border-b border-slate-800/30">
                        <h3 class="text-lg font-semibold text-slate-200">Most Productive Hours</h3>
                    </CardHeader>
                    <CardContent class="p-4">
                        <div class="space-y-2">
                            <div v-for="(hour, index) in productiveHours" :key="hour.hour"
                                class="flex items-center gap-3">
                                <span :class="[
                                    'text-lg font-bold w-6',
                                    index === 0 ? 'text-yellow-400' :
                                        index === 1 ? 'text-slate-400' :
                                            index === 2 ? 'text-orange-600' : 'text-slate-600'
                                ]">
                                    {{ index + 1 }}
                                </span>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm text-slate-300">{{ hour.label }}</span>
                                        <span class="text-xs text-slate-500">{{ hour.tasks }} tasks</span>
                                    </div>
                                    <div class="w-full bg-slate-800 rounded h-1.5">
                                        <div class="bg-gradient-to-r from-blue-400 to-cyan-400 h-1.5 rounded transition-all duration-500"
                                            :style="`width: ${hour.percentage}%`"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Task Types -->
                <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                    <CardHeader class="border-b border-slate-800/30">
                        <h3 class="text-lg font-semibold text-slate-200">Task Types</h3>
                    </CardHeader>
                    <CardContent class="p-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="type in taskTypes.slice(0, 4)" :key="type.name"
                                class="flex flex-col items-center p-3 rounded-lg bg-slate-800/30">
                                <component :is="getTaskIcon(type.icon)" :class="type.colorClass" class="w-6 h-6 mb-2" />
                                <span class="text-xs text-slate-400">{{ type.name }}</span>
                                <span class="text-lg font-bold text-slate-200">{{ type.count }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Insights -->
            <Card class="bg-slate-900/50 border-slate-800/30 backdrop-blur-xl">
                <CardHeader class="border-b border-slate-800/30">
                    <div class="flex items-center gap-2">
                        <Lightbulb class="w-5 h-5 text-yellow-400" />
                        <h3 class="text-lg font-semibold text-slate-200">Insights & Recommendations</h3>
                    </div>
                </CardHeader>
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="insight in insights" :key="insight.id" :class="[
                            'p-4 rounded-lg border',
                            insight.type === 'positive' ? 'bg-green-400/10 border-green-400/30' :
                                insight.type === 'warning' ? 'bg-orange-400/10 border-orange-400/30' :
                                    'bg-blue-400/10 border-blue-400/30'
                        ]">
                            <div class="flex items-start gap-3">
                                <component :is="getInsightIcon(insight.icon)" :class="[
                                    'w-5 h-5 mt-0.5',
                                    insight.type === 'positive' ? 'text-green-400' :
                                        insight.type === 'warning' ? 'text-orange-400' :
                                            'text-blue-400'
                                ]" />
                                <div>
                                    <h4 class="font-medium text-slate-200 mb-1">{{ insight.title }}</h4>
                                    <p class="text-sm text-slate-400">{{ insight.message }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </Layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '@/layouts/Layout.vue'
import {
    CheckCircle2,
    Clock,
    Target,
    Zap,
    Flame,
    Download,
    TrendingUp,
    TrendingDown,
    AlertCircle,
    Lightbulb,
    BookOpen,
    Briefcase,
    Home,
    Coffee,
    HelpCircle,
    StickyNote,
    Bell,
    Shuffle,
    Activity,
    Award,
    Circle
} from 'lucide-vue-next'
import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card'
import {
    Button,
} from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

import { useTranslations } from '@/composables/useTranslations'
const { __ } = useTranslations()

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            tasksCompleted: { value: 0, change: 0 },
            focusHours: { value: 0, change: 0 },
            completionRate: { value: 0, change: 0 },
            productivity: { value: 0, change: 0 },
            streak: { value: 0, change: 0 }
        })
    },
    chartData: {
        type: Object,
        default: () => ({})
    },
    insights: {
        type: Array,
        default: () => []
    },
    currentPeriod: {
        type: String,
        default: '30d'
    }
})

// State
const selectedPeriod = ref(props.currentPeriod)

const periods = [
    { label: '7 Days', value: '7d' },
    { label: '30 Days', value: '30d' }

]

// Computed properties
const metrics = computed(() => props.metrics)

// Task Completion Chart
const taskTrend = computed(() => {
    return props.chartData?.taskTrend || []
})

const chartLabels = computed(() => {
    return props.chartData?.labels || []
})

const maxTaskValue = computed(() => {
    const values = taskTrend.value.map(d => d.value)
    return Math.max(...values, 10) // minimum 10 for scale
})

const taskChartPoints = computed(() => {
    const data = taskTrend.value
    if (!data || data.length === 0) return []

    const width = 100
    const height = 100
    const step = width / Math.max(data.length - 1, 1)
    const max = maxTaskValue.value

    return data.map((item, i) => ({
        x: i * step,
        y: height - ((item.value / max) * height)
    }))
})

const taskChartLine = computed(() => {
    const points = taskChartPoints.value
    if (points.length === 0) return ''
    return 'M' + points.map(p => `${p.x},${p.y}`).join(' L')
})

const taskChartArea = computed(() => {
    const line = taskChartLine.value
    if (!line) return ''
    return line + ` L${100},${100} L0,${100} Z`
})

// Time Distribution
const timeDistribution = computed(() => {
    return props.chartData?.timeDistribution || []
})

const totalHours = computed(() => {
    return timeDistribution.value.reduce((sum, item) => sum + item.hours, 0)
})

const circumference = 2 * Math.PI * 72

const timeDistributionSegments = computed(() => {
    let cumulativeOffset = 0
    const total = totalHours.value

    if (total === 0) return []

    return timeDistribution.value
        .filter(item => item.hours > 0)
        .map((item) => {
            const percentage = (item.hours / total) * 100
            const dashArray = (percentage / 100) * circumference
            const offset = cumulativeOffset
            cumulativeOffset += dashArray

            return {
                ...item,
                dashArray,
                offset,
                percentage
            }
        })
})

// Priority Distribution
const priorityDistribution = computed(() => {
    return props.chartData?.priorityDistribution || []
})

// Productive Hours
const productiveHours = computed(() => {
    return props.chartData?.productiveHours || []
})

// Task Types
const taskTypes = computed(() => {
    return props.chartData?.taskTypes || []
})

// Insights
const insights = computed(() => props.insights || [])

// Icon mapping for task types
const taskIconMap = {
    'BookOpen': BookOpen,
    'Briefcase': Briefcase,
    'HelpCircle': HelpCircle,
    'StickyNote': StickyNote,
    'Bell': Bell,
    'Home': Home,
    'Shuffle': Shuffle,
    'Coffee': Coffee,
    'Circle': Circle
}

const insightIconMap = {
    'TrendingUp': TrendingUp,
    'TrendingDown': TrendingDown,
    'AlertCircle': AlertCircle,
    'Lightbulb': Lightbulb,
    'Clock': Clock,
    'Activity': Activity,
    'Award': Award,
    'Target': Target
}

// Methods
const getChangeClass = (change) => {
    if (change > 0) return 'bg-green-400/20 text-green-400'
    if (change < 0) return 'bg-red-400/20 text-red-400'
    return 'bg-slate-600/20 text-slate-400'
}

const getTaskIcon = (iconName) => {
    return taskIconMap[iconName] || Circle
}

const getInsightIcon = (iconName) => {
    return insightIconMap[iconName] || Lightbulb
}

// Handle period change
const changePeriod = (newPeriod) => {
    selectedPeriod.value = newPeriod
    router.get('/analytics', { period: newPeriod }, {
        preserveState: true,
        preserveScroll: true
    })
}
</script>