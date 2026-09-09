<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../../Composables/useBilingualContent.js'
import { plainTextFromHtml } from '../../../Composables/useRichText.js'

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['edit', 'delete', 'toggle-active', 'move'])

const { t, locale } = useI18n()

const sortedItems = computed(() =>
  [...props.items].sort((left, right) => left.ordering - right.ordering),
)

const displayTitle = (item) =>
  resolveBilingualField(item, 'title', locale.value) || t('homepagePromos.items.untitled')

const displayDescription = (item) =>
  plainTextFromHtml(resolveBilingualField(item, 'description', locale.value))

const statusLabel = (item) => {
  const formatted = item.is_active_formatted

  if (formatted) {
    return locale.value === 'ar' ? formatted.label : formatted.name
  }

  return item.is_active ? t('homepagePromos.items.active') : t('homepagePromos.items.inactive')
}
</script>

<template>
  <div class="space-y-2">
    <div
      v-for="(item, index) in sortedItems"
      :key="item.id"
      class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3"
    >
      <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex min-w-0 flex-1 gap-3">
          <img
            v-if="item.attachment?.asset_path"
            :src="item.attachment.asset_path"
            :alt="displayTitle(item)"
            class="h-14 w-14 shrink-0 rounded border border-gray-200 dark:border-gray-700 object-cover"
          />
          <div class="min-w-0 space-y-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-sm font-mono text-muted muted-color">#{{ item.ordering }}</span>
              <h4 class="text-body font-medium text-gray-900 dark:text-gray-100">{{ displayTitle(item) }}</h4>
              <span
                :class="[
                  'rounded-full px-2 py-0.5 text-xs',
                  item.is_active
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                ]"
              >
                {{ statusLabel(item) }}
              </span>
            </div>
            <p v-if="displayDescription(item)" class="text-sm text-muted muted-color line-clamp-2">
              {{ displayDescription(item) }}
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            class="btn btn-secondary px-3 py-1.5"
            :disabled="index === 0"
            @click="emit('move', item, -1)"
          >
            {{ t('homepagePromos.items.moveUp') }}
          </button>
          <button
            type="button"
            class="btn btn-secondary px-3 py-1.5"
            :disabled="index === sortedItems.length - 1"
            @click="emit('move', item, 1)"
          >
            {{ t('homepagePromos.items.moveDown') }}
          </button>
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input
              :checked="item.is_active"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              @change="emit('toggle-active', item)"
            />
            <span class="text-label">{{ t('homepagePromos.items.visibleLabel') }}</span>
          </label>
          <button type="button" class="btn btn-primary px-3 py-1.5" @click="emit('edit', item)">
            {{ t('homepagePromos.table.edit') }}
          </button>
          <button type="button" class="btn btn-danger px-3 py-1.5" @click="emit('delete', item)">
            {{ t('homepagePromos.table.delete') }}
          </button>
        </div>
      </div>
    </div>

    <p v-if="sortedItems.length === 0" class="text-sm text-muted muted-color">
      {{ t('homepagePromos.items.empty') }}
    </p>
  </div>
</template>
