<template>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        :class="[
                            'table-header-cell text-table-header',
                            column.sortable ? 'table-header-cell-sortable' : ''
                        ]"
                        @click="column.sortable ? handleSort(column.key) : null"
                    >
                        <div class="flex items-center gap-2">
                            {{ column.label }}
                            <span v-if="column.sortable && props.sortColumn === column.key" class="text-sm">
                                {{ props.sortDirection === 'asc' ? '↑' : '↓' }}
                            </span>
                        </div>
                    </th>
                    <th class="table-header-cell-actions text-table-header">
                        {{ t('companyGoals.table.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="goal in companyGoals" :key="goal.id" class="table-row">
                    <td class="table-cell table-cell-primary text-body max-w-md">
                        {{ displayText(goal) }}
                    </td>
                    <td class="table-cell table-cell-secondary text-body">
                        {{ goal.ordering }}
                    </td>
                    <td class="table-cell table-cell-secondary text-body">
                        <span :class="statusBadgeClass(goal.is_active)">
                            {{ goal.is_active_formatted.label }}
                        </span>
                    </td>
                    <td class="table-cell table-cell-actions">
                        <button class="btn btn-primary me-2" @click="$emit('edit', goal)">
                            {{ t('companyGoals.table.edit') }}
                        </button>
                        <button class="btn btn-danger" @click="$emit('delete', goal)">
                            {{ t('companyGoals.table.delete') }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <EmptyState v-if="companyGoals.length === 0" :title="t('companyGoals.noGoalsFound')" />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import EmptyState from '../../Common/EmptyState.vue'
import { bilingualFieldKey, resolveBilingualField } from '../../../Composables/useBilingualContent.js'

const { t, locale } = useI18n()

const props = defineProps({
    companyGoals: {
        type: Array,
        required: true,
    },
    sortColumn: {
        type: String,
        default: 'ordering',
    },
    sortDirection: {
        type: String,
        default: 'asc',
    },
})

const emit = defineEmits(['edit', 'delete', 'sort'])

const textSortKey = computed(() => bilingualFieldKey('text', locale.value))

const columns = computed(() => [
    { key: textSortKey.value, label: t('companyGoals.table.text'), sortable: true },
    { key: 'ordering', label: t('companyGoals.table.ordering'), sortable: true },
    { key: 'is_active', label: t('companyGoals.table.status'), sortable: false },
])

const displayText = (goal) => resolveBilingualField(goal, 'text', locale.value)

const handleSort = (column) => {
    let newDirection = 'asc'

    if (props.sortColumn === column) {
        newDirection = props.sortDirection === 'asc' ? 'desc' : 'asc'
    }

    emit('sort', { column, direction: newDirection })
}

const statusBadgeClass = (isActive) => [
    'inline-flex items-center px-3.5 py-0.5 rounded-full',
    isActive
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
]
</script>
