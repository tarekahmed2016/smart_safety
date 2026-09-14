<template>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <th v-for="column in columns" :key="column.key" class="table-header-cell text-table-header">
                        {{ column.label }}
                    </th>
                    <th class="table-header-cell-actions text-table-header">
                        {{ t('contactMessages.table.actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="record in contactMessages" :key="record.id" class="table-row">
                    <td class="table-cell table-cell-primary text-body">
                        {{ record.name }}
                    </td>
                    <td class="table-cell table-cell-secondary text-body max-w-xs truncate">
                        {{ contactSummary(record) }}
                    </td>
                    <td class="table-cell table-cell-secondary text-body max-w-xs truncate">
                        {{ record.subject || '—' }}
                    </td>
                    <td class="table-cell table-cell-secondary text-body">
                        <span :class="requestStatusBadgeClass(record.request_status)">
                            {{ requestStatusLabel(record) }}
                        </span>
                    </td>
                    <td class="table-cell table-cell-secondary text-body">
                        <span :class="readStatusBadgeClass(record.is_read)">
                            {{ readStatusLabel(record) }}
                        </span>
                    </td>
                    <td class="table-cell table-cell-secondary text-body whitespace-nowrap">
                        {{ formatDateTime(record.created_at) }}
                    </td>
                    <td class="table-cell table-cell-actions">
                        <button @click="$emit('view', record)" class="btn btn-secondary me-2">
                            {{ t('contactMessages.table.view') }}
                        </button>
                        <button
                            v-if="!record.is_read"
                            @click="$emit('mark-read', record)"
                            class="btn btn-primary me-2"
                        >
                            {{ t('contactMessages.table.markRead') }}
                        </button>
                        <button
                            v-else
                            @click="$emit('mark-unread', record)"
                            class="btn btn-secondary me-2"
                        >
                            {{ t('contactMessages.table.markUnread') }}
                        </button>
                        <button @click="$emit('delete', record)" class="btn btn-danger">
                            {{ t('contactMessages.table.delete') }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <EmptyState v-if="contactMessages.length === 0" :title="t('contactMessages.noMessagesFound')" />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import EmptyState from '../../Common/EmptyState.vue'

const { t, locale } = useI18n()

defineProps({
    contactMessages: {
        type: Array,
        required: true
    }
})

defineEmits(['view', 'mark-read', 'mark-unread', 'delete'])

const columns = computed(() => [
    { key: 'name', label: t('contactMessages.table.name') },
    { key: 'contact', label: t('contactMessages.table.contact') },
    { key: 'subject', label: t('contactMessages.table.subject') },
    { key: 'requestStatus', label: t('contactMessages.table.requestStatus') },
    { key: 'status', label: t('contactMessages.table.status') },
    { key: 'received_at', label: t('contactMessages.table.receivedAt') },
])

const contactSummary = (record) => {
    const parts = [record.email, record.phone].filter(Boolean)
    return parts.length ? parts.join(' · ') : '—'
}

const formatDateTime = (value) => {
    if (!value) return '—'

    return new Date(value).toLocaleString(locale.value === 'ar' ? 'ar' : 'en')
}

const readStatusLabel = (record) => {
    if (locale.value === 'ar') {
        return record.is_read_formatted?.label || (record.is_read ? 'مقروء' : 'غير مقروء')
    }

    return record.is_read_formatted?.name || (record.is_read ? 'Read' : 'Unread')
}

const requestStatusLabel = (record) => {
    const value = record.request_status_formatted?.value || record.request_status || 'new'

    return t(`contactMessages.requestStatuses.${value}`)
}

const readStatusBadgeClass = (isRead) => [
    'inline-flex items-center px-3.5 py-0.5 rounded-full text-xs font-medium',
    isRead
        ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
]

const requestStatusBadgeClass = (status) => {
    const value = typeof status === 'object' ? status?.value : status

    const colors = {
        new: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        under_review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        contacted: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        quote_sent: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        agreed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        completed: 'bg-emerald-200 text-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-200',
    }

    return [
        'inline-flex items-center px-3.5 py-0.5 rounded-full text-xs font-medium',
        colors[value] || colors.new,
    ]
}
</script>
