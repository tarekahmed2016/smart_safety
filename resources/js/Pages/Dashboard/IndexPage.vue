<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import {
    faArrowUpRightFromSquare,
    faBriefcase,
    faBuilding,
    faCalendarDays,
    faEnvelope,
    faEye,
    faUserCheck,
    faUsers,
} from '@fortawesome/free-solid-svg-icons'
import VisitorStatsChart from '../../Components/Dashboard/VisitorStatsChart.vue'

const { t } = useI18n()

const showVisitorDetailsTable = false

const props = defineProps({
    visitorStats: {
        type: Object,
        default: () => ({
            today: { unique_visitors: 0, visits: 0, page_views: 0 },
            last_7_days: [],
            this_month: { unique_visitors: 0, visits: 0, page_views: 0 },
        }),
    },
})

const todayStats = computed(() => props.visitorStats?.today || {})
const last7Days = computed(() => props.visitorStats?.last_7_days || [])
const thisMonth = computed(() => props.visitorStats?.this_month || {})

const summaryCards = computed(() => [
    {
        key: 'unique_visitors',
        label: t('dashboard.todayUniqueVisitors'),
        value: todayStats.value.unique_visitors ?? 0,
        icon: faUsers,
    },
    {
        key: 'visits',
        label: t('dashboard.todayVisits'),
        value: todayStats.value.visits ?? 0,
        icon: faUserCheck,
    },
    {
        key: 'page_views',
        label: t('dashboard.todayPageViews'),
        value: todayStats.value.page_views ?? 0,
        icon: faEye,
    },
    {
        key: 'this_month',
        label: t('dashboard.thisMonthTotal'),
        value: thisMonth.value.unique_visitors ?? 0,
        icon: faCalendarDays,
    },
])

const quickLinks = computed(() => [
    {
        label: t('dashboard.manageCompanyInfo'),
        route: route('company-info.index'),
        icon: faBuilding,
    },
    {
        label: t('dashboard.manageServices'),
        route: route('services.index'),
        icon: faBriefcase,
    },
    {
        label: t('dashboard.viewContactMessages'),
        route: route('contact-messages.index'),
        icon: faEnvelope,
    },
    {
        label: t('dashboard.viewPublicSite'),
        route: route('home'),
        icon: faArrowUpRightFromSquare,
        external: true,
    },
])
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-900 p-3 md:p-6">
        <div class="max-w-7xl mx-auto space-y-8">
            <div>
                <h1 class="text-page-title text-gray-900 dark:text-gray-100">{{ t('dashboard.pageTitle') }}</h1>
                <p class="mt-2 text-muted muted-color">{{ t('dashboard.pageSubtitle') }}</p>
            </div>

            <section data-testid="dashboard-quick-links">
                <h2 class="text-section-title text-gray-900 dark:text-gray-100 mb-4">{{ t('dashboard.quickLinksTitle') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <component
                        v-for="link in quickLinks"
                        :key="link.label"
                        :is="link.external ? 'a' : Link"
                        :href="link.route"
                        :target="link.external ? '_blank' : undefined"
                        :rel="link.external ? 'noopener noreferrer' : undefined"
                        class="group flex items-start gap-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-md transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300">
                            <font-awesome-icon :icon="link.icon" class="h-4 w-4" />
                        </span>
                        <span class="text-body text-gray-900 dark:text-gray-100 group-hover:text-blue-700 dark:group-hover:text-blue-300">
                            {{ link.label }}
                        </span>
                    </component>
                </div>
            </section>

            <section data-testid="dashboard-visitor-stats">
                <h2 class="text-section-title text-gray-900 dark:text-gray-100 mb-4">{{ t('dashboard.visitorStatsTitle') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="card in summaryCards"
                        :key="card.key"
                        class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-muted muted-color text-sm">{{ card.label }}</p>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300">
                                <font-awesome-icon :icon="card.icon" class="h-4 w-4" />
                            </span>
                        </div>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                            {{ card.value }}
                        </p>
                    </div>
                </div>

                <div class="mt-6" data-testid="dashboard-visitor-chart">
                    <VisitorStatsChart :days="last7Days" />
                </div>

                <div
                    v-if="showVisitorDetailsTable"
                    class="mt-6 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                    data-testid="dashboard-visitor-details-table"
                >
                    <table class="min-w-full text-sm">
                        <caption class="sr-only">{{ t('dashboard.last7Days') }}</caption>
                        <thead class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-start font-medium">{{ t('dashboard.last7Days') }}</th>
                                <th class="px-4 py-3 text-start font-medium">{{ t('dashboard.todayUniqueVisitors') }}</th>
                                <th class="px-4 py-3 text-start font-medium">{{ t('dashboard.todayVisits') }}</th>
                                <th class="px-4 py-3 text-start font-medium">{{ t('dashboard.todayPageViews') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="day in last7Days"
                                :key="day.date"
                                class="border-t border-gray-100 dark:border-gray-700"
                            >
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ day.date }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ day.unique_visitors }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ day.visits }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ day.page_views }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>
