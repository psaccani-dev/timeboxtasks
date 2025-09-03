<!-- resources/js/components/TaskCountdown.vue -->
<template>
    <span v-if="dueDate" :class="getCountdownClasses()" class="text-xs font-medium flex items-center gap-1">
        <Clock class="w-3 h-3" />
        {{ countdownText }}
    </span>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Clock } from 'lucide-vue-next'
import { useTranslations } from '@/composables/useTranslations'

const props = defineProps({
    dueDate: {
        type: String,
        required: true
    }
})

const { __ } = useTranslations()

const now = ref(new Date())
let interval = null

const targetDate = computed(() => new Date(props.dueDate))

const timeDifference = computed(() => {
    return targetDate.value.getTime() - now.value.getTime()
})

const countdownText = computed(() => {
    const diff = timeDifference.value

    if (diff < 0) {
        const overdue = Math.abs(diff)
        const days = Math.floor(overdue / (1000 * 60 * 60 * 24))
        const hours = Math.floor((overdue % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        const minutes = Math.floor((overdue % (1000 * 60 * 60)) / (1000 * 60))

        if (days > 0) return `${days}d overdue`
        if (hours > 0) return `${hours}h overdue`
        return `${minutes}m overdue`
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

    if (days > 0) return `${days}d ${hours}h`
    if (hours > 0) return `${hours}h ${minutes}m`
    return `${minutes}m left`
})

const getCountdownClasses = () => {
    const diff = timeDifference.value
    const oneDay = 24 * 60 * 60 * 1000
    const oneHour = 60 * 60 * 1000

    if (diff < 0) {
        return 'text-red-400 animate-pulse'
    } else if (diff <= oneHour) {
        return 'text-red-300 animate-pulse'
    } else if (diff <= oneDay) {
        return 'text-orange-400'
    } else {
        return 'text-slate-400'
    }
}

onMounted(() => {
    interval = setInterval(() => {
        now.value = new Date()
    }, 60000) // Update every minute
})

onUnmounted(() => {
    if (interval) {
        clearInterval(interval)
        interval = null
    }
})
</script>