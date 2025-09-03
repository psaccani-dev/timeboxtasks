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
                        <button v-for="period in periods" :key="period.value" @click="selectedPeriod = period.value"
                            :class="[
                                'px-3 py-1.5 rounded text-sm font-medium transition-all',
                                selectedPeriod === period.value
                                    ? 'bg-slate-700 text-white'
                                    : 'text-slate-400 hover:text-white'
                            ]">
                            {{ period.label }}
                        </button>
                    </div>

                    <!-- Export Button -->
                    <Button variant="outline" size="sm" class="border-slate-700 bg-slate-800/50 hover:bg-slate-800">
                        <Download class="w-4 h-4 mr-2" />
                        Export
                    </Button>
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
                                    <span>100</span>
                                    <span>75</span>
                                    <span>50</span>
                                    <span>25</span>
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
                                    <svg class="absolute inset-0 w-full h-full">
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
                            <div class="relative">
                                <svg class="w-48 h-48 transform -rotate-90">
                                    <circle cx="96" cy="96" r="72" fill="none" stroke="rgb(30,41,59)"
                                        stroke-width="24" />

                                    <circle v-for="(segment, i) in timeDistribution" :key="i" cx="96" cy="96" r="72"
                                        fill="none" :stroke="segment.color" stroke-width="24"
                                        :stroke-dasharray="`${segment.value} ${300 - segment.value}`"
                                        :stroke-dashoffset="-segment.offset" class="transition-all duration-500" />
                                </svg>

                                <!-- Center text -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <p class="text-2xl font-bold text-slate-200">{{ totalHours }}h</p>
                                    <p class="text-xs text-slate-400">Total</p>
                                </div>
                            </div>

                            <!-- Legend -->
                            <div class="ml-8 space-y-2">
                                <div v-for="item in timeDistributionLegend" :key="item.label"
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
                            <div v-for="type in taskTypes" :key="type.name"
                                class="flex flex-col items-center p-3 rounded-lg bg-slate-800/30">
                                <component :is="type.icon" :class="type.colorClass" class="w-6 h-6 mb-2" />
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
                                <component :is="insight.icon" :class="[
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
    Coffee
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
    metrics: Object,
    chartData: Object,
    insights: Array
})

// State
const selectedPeriod = ref('30d')

const periods = [
    { label: '7 Days', value: '7d' },
    { label: '30 Days', value: '30d' },
    { label: '90 Days', value: '90d' },
    { label: 'Year', value: '1y' }
]

// Mock data (replace with real data from backend)
const metrics = computed(() => props.metrics || {
    tasksCompleted: { value: 0, change: 0 },
    focusHours: { value: 0, change: 0 },
    completionRate: { value: 0, change: 0 },
    productivity: { value: 0, change: 0 },
    streak: { value: 0, change: 0 }
})

const taskChartPoints = computed(() => {
    // Generate points for line chart
    const data = [65, 72, 68, 80, 75, 82, 78]
    const width = 100
    const height = 100
    const step = width / (data.length - 1)

    return data.map((value, i) => ({
        x: i * step,
        y: height - (value * height / 100)
    }))
})

const taskChartLine = computed(() => {
    const points = taskChartPoints.value
    return 'M' + points.map(p => `${p.x},${p.y}`).join(' L')
})

const taskChartArea = computed(() => {
    const points = taskChartPoints.value
    const line = taskChartLine.value
    return line + ` L${100},${100} L0,${100} Z`
})

const chartLabels = computed(() => {
    if (selectedPeriod.value === '7d') {
        return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
    } else if (selectedPeriod.value === '30d') {
        return ['Week 1', 'Week 2', 'Week 3', 'Week 4']
    }
    return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
})

const timeDistribution = computed(() => props.chartData?.timeDistribution || [])
const timeDistributionLegend = [
    { label: 'Focus', hours: 48, color: 'rgb(96, 165, 250)' },
    { label: 'Meetings', hours: 32, color: 'rgb(168, 85, 247)' },
    { label: 'Breaks', hours: 16, color: 'rgb(52, 211, 153)' },
    { label: 'Other', hours: 24, color: 'rgb(251, 146, 60)' }
]

const totalHours = computed(() =>
    timeDistributionLegend.reduce((sum, item) => sum + item.hours, 0)
)

const priorityDistribution = computed(() => props.chartData?.priorityDistribution || [])

const productiveHours = computed(() => props.chartData?.productiveHours || [])

const taskTypes = computed(() => props.chartData?.taskTypes || [])

const insights = computed(() => props.insights || [])

// Methods
const getChangeClass = (change) => {
    if (change > 0) return 'bg-green-400/20 text-green-400'
    if (change < 0) return 'bg-red-400/20 text-red-400'
    return 'bg-slate-600/20 text-slate-400'
}
</script>