<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
    days: {
        type: Array,
        default: () => [],
    },
})

const { t, locale } = useI18n()

const isRtl = computed(() => locale.value === 'ar')

const series = computed(() => [
    {
        key: 'unique_visitors',
        label: t('dashboard.chartVisitors'),
        color: '#2563eb',
    },
    {
        key: 'visits',
        label: t('dashboard.chartVisits'),
        color: '#16a34a',
    },
    {
        key: 'page_views',
        label: t('dashboard.chartPageViews'),
        color: '#d97706',
    },
])

const chartDays = computed(() => (Array.isArray(props.days) ? props.days : []).slice(-7))

const maxValue = computed(() => {
    const values = chartDays.value.flatMap((day) => [
        Number(day.unique_visitors || 0),
        Number(day.visits || 0),
        Number(day.page_views || 0),
    ])

    return Math.max(0, ...values)
})

const hasPlottableDays = computed(() => chartDays.value.length > 0)
const width = 640
const height = 280
const top = 24
const bottom = 48
const plotHeight = height - top - bottom

const horizontalPadding = computed(() => (
    isRtl.value
        ? { start: 20, end: 52 }
        : { start: 52, end: 20 }
))

const plotWidth = computed(() => width - horizontalPadding.value.start - horizontalPadding.value.end)

const xForIndex = (index, count) => {
    const { start } = horizontalPadding.value
    if (count <= 1) {
        return start + plotWidth.value / 2
    }

    const ratio = index / (count - 1)
    const aligned = isRtl.value ? 1 - ratio : ratio

    return start + (aligned * plotWidth.value)
}

const yForValue = (value) => {
    if (maxValue.value <= 0) {
        return top + plotHeight
    }

    return top + plotHeight - ((Number(value) / maxValue.value) * plotHeight)
}

const gridLines = computed(() => {
    const steps = 4
    const lines = []

    for (let i = 0; i <= steps; i += 1) {
        const ratio = i / steps
        const value = maxValue.value <= 0 ? 0 : Math.round((maxValue.value * (1 - ratio)))
        lines.push({
            y: top + (plotHeight * ratio),
            value,
        })
    }

    return lines
})

const polylines = computed(() => series.value.map((item) => {
    const points = chartDays.value.map((day, index) => {
        const x = xForIndex(index, chartDays.value.length)
        const y = yForValue(day[item.key] || 0)

        return { x, y, value: Number(day[item.key] || 0) }
    })

    return {
        ...item,
        points,
        path: points.map((point) => `${point.x},${point.y}`).join(' '),
    }
}))

const yAxisX = computed(() => (
    isRtl.value
        ? width - horizontalPadding.value.end + 8
        : horizontalPadding.value.start - 8
))

const formatDayLabel = (date) => {
    if (!date) {
        return ''
    }

    const parts = String(date).split('-')
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}`
    }

    return date
}
</script>

<template>
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
        <h3 class="text-section-title text-gray-900 dark:text-gray-100 mb-4">
            {{ t('dashboard.last7DaysChart') }}
        </h3>

        <ul class="flex flex-wrap items-center gap-x-5 gap-y-2 mb-4 text-sm text-gray-600 dark:text-gray-300">
            <li
                v-for="item in series"
                :key="item.key"
                class="inline-flex items-center gap-2"
            >
                <span
                    class="h-2.5 w-2.5 rounded-full"
                    :style="{ backgroundColor: item.color }"
                    aria-hidden="true"
                />
                {{ item.label }}
            </li>
        </ul>

        <div
            v-if="!hasPlottableDays"
            class="flex h-48 items-center justify-center text-sm text-gray-500 dark:text-gray-400"
        >
            {{ t('dashboard.chartEmpty') }}
        </div>

        <div
            v-else
            class="w-full overflow-x-auto"
            role="img"
            :aria-label="t('dashboard.last7DaysChart')"
        >
            <svg
                class="min-w-full h-64"
                :viewBox="`0 0 ${width} ${height}`"
                preserveAspectRatio="xMidYMid meet"
            >
                <line
                    v-for="line in gridLines"
                    :key="`grid-${line.y}`"
                    :x1="horizontalPadding.start"
                    :x2="width - horizontalPadding.end"
                    :y1="line.y"
                    :y2="line.y"
                    class="stroke-gray-200 dark:stroke-gray-700"
                    stroke-width="1"
                />

                <text
                    v-for="line in gridLines"
                    :key="`label-${line.y}`"
                    :x="yAxisX"
                    :y="line.y + 4"
                    :text-anchor="isRtl ? 'start' : 'end'"
                    class="fill-gray-500 dark:fill-gray-400"
                    font-size="11"
                >
                    {{ line.value }}
                </text>

                <polyline
                    v-for="item in polylines"
                    :key="`${item.key}-line`"
                    fill="none"
                    :stroke="item.color"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    :points="item.path"
                />

                <circle
                    v-for="point in polylines.flatMap((item) => item.points.map((entry) => ({ ...entry, color: item.color, key: item.key })))"
                    :key="`${point.key}-${point.x}`"
                    :cx="point.x"
                    :cy="point.y"
                    r="3.5"
                    :fill="point.color"
                />

                <text
                    v-for="(day, index) in chartDays"
                    :key="`day-${day.date}`"
                    :x="xForIndex(index, chartDays.length)"
                    :y="height - 16"
                    text-anchor="middle"
                    class="fill-gray-500 dark:fill-gray-400"
                    font-size="11"
                >
                    {{ formatDayLabel(day.date) }}
                </text>
            </svg>
        </div>
    </div>
</template>
