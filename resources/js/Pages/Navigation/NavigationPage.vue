<script setup>
import { computed, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import DashboardLayout from '../../Layouts/DashboardLayout.vue'

defineOptions({ layout: DashboardLayout })

const { t, locale } = useI18n()
const page = usePage()
const navigationItems = computed(() => page.props.navigationItems || [])

const form = useForm({
  items: navigationItems.value.map((item) => ({
    id: item.id,
    source: item.source,
    key: item.key,
    name: item.name,
    is_visible: Boolean(item.is_visible),
    show_in_navigation: Boolean(item.show_in_navigation),
    nav_label_ar: item.nav_label_ar || '',
    nav_label_en: item.nav_label_en || '',
    nav_order: item.nav_order,
    anchor_id: item.anchor_id || '',
    default_label_ar: item.default_label_ar || '',
    default_label_en: item.default_label_en || '',
    open_in_new_tab: Boolean(item.open_in_new_tab),
    slug: item.slug || '',
  })),
})

const draggedIndex = ref(null)

const sortedItems = computed(() =>
  [...form.items].sort((left, right) => left.nav_order - right.nav_order),
)

const previewLinks = computed(() =>
  sortedItems.value
    .filter((item) => item.is_visible && item.show_in_navigation)
    .map((item) => ({
      key: item.key,
      label: locale.value === 'ar'
        ? (item.nav_label_ar || item.default_label_ar || item.nav_label_en || item.default_label_en || item.name)
        : (item.nav_label_en || item.default_label_en || item.nav_label_ar || item.default_label_ar || item.name),
      type: item.source,
    })),
)

const reorderItems = (fromIndex, toIndex) => {
  if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0) {
    return
  }

  const items = [...sortedItems.value]
  const [moved] = items.splice(fromIndex, 1)
  items.splice(toIndex, 0, moved)

  items.forEach((item, index) => {
    item.nav_order = (index + 1) * 10
  })

  form.items = items
}

const onDragStart = (index) => {
  draggedIndex.value = index
}

const onDrop = (index) => {
  if (draggedIndex.value === null) {
    return
  }

  reorderItems(draggedIndex.value, index)
  draggedIndex.value = null
}

const moveItem = (index, direction) => {
  reorderItems(index, index + direction)
}

const itemTypeLabel = (source) => (
  source === 'section' ? t('navigation.types.section') : t('navigation.types.page')
)

const itemHrefPreview = (item) => {
  if (item.source === 'page') {
    return route('public.page.show', { slug: item.slug })
  }

  return `#${item.anchor_id || item.key}`
}

const submit = () => {
  form.put(route('navigation.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="bg-gray-50 dark:bg-gray-900 p-3 md:p-6">
    <div class="max-w-6xl mx-auto">
      <div class="mb-6 md:mb-8">
        <h1 class="text-page-title text-gray-900 dark:text-gray-100">{{ t('navigation.pageTitle') }}</h1>
        <p class="mt-2 text-muted muted-color">{{ t('navigation.pageSubtitle') }}</p>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_18rem] gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 md:p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <p class="text-muted muted-color">{{ t('navigation.help') }}</p>

            <div class="space-y-3">
              <div
                v-for="(item, index) in sortedItems"
                :key="`${item.source}-${item.id}`"
                class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/40"
                draggable="true"
                @dragstart="onDragStart(index)"
                @dragover.prevent
                @drop.prevent="onDrop(index)"
              >
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="min-w-0 flex-1 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="text-sm font-mono text-muted muted-color">#{{ item.nav_order }}</span>
                      <h2 class="text-card-title text-gray-900 dark:text-gray-100">{{ item.name }}</h2>
                      <span
                        class="rounded-full px-2 py-0.5 text-xs"
                        :class="item.source === 'section'
                          ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200'
                          : 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200'"
                      >
                        {{ itemTypeLabel(item.source) }}
                      </span>
                      <span
                        v-if="item.source === 'section' && !item.is_visible"
                        class="rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200 px-2 py-0.5 text-xs"
                      >
                        {{ t('navigation.hiddenOnHomepage') }}
                      </span>
                    </div>
                    <p class="text-sm text-muted muted-color">{{ t('navigation.dragHint') }}</p>
                    <p class="text-sm font-mono text-muted muted-color" dir="ltr">{{ itemHrefPreview(item) }}</p>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-secondary px-3 py-1.5" :disabled="index === 0" @click="moveItem(index, -1)">
                      {{ t('navigation.moveUp') }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-secondary px-3 py-1.5"
                      :disabled="index === sortedItems.length - 1"
                      @click="moveItem(index, 1)"
                    >
                      {{ t('navigation.moveDown') }}
                    </button>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                      <input v-model="item.show_in_navigation" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                      <span class="text-label">{{ t('navigation.showInMenuLabel') }}</span>
                    </label>
                  </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="form-label text-label">{{ t('navigation.labelAr') }}</label>
                    <input v-model="item.nav_label_ar" type="text" class="form-input text-body" :placeholder="item.default_label_ar" />
                  </div>
                  <div>
                    <label class="form-label text-label">{{ t('navigation.labelEn') }}</label>
                    <input v-model="item.nav_label_en" type="text" class="form-input text-body" :placeholder="item.default_label_en" />
                  </div>
                  <div v-if="item.source === 'section'">
                    <label class="form-label text-label">{{ t('navigation.anchorId') }}</label>
                    <input v-model="item.anchor_id" type="text" dir="ltr" class="form-input text-body font-mono" :placeholder="item.key" />
                  </div>
                  <div v-if="item.source === 'page'">
                    <label class="form-label text-label">{{ t('navigation.slug') }}</label>
                    <input :value="item.slug" type="text" dir="ltr" class="form-input text-body font-mono bg-gray-100 dark:bg-gray-900" readonly />
                    <p class="mt-1 text-sm text-muted muted-color">{{ t('navigation.slugHelp') }}</p>
                  </div>
                  <div v-if="item.source === 'page'" class="flex items-center">
                    <label class="inline-flex items-center gap-2 text-body text-gray-700 dark:text-gray-300">
                      <input v-model="item.open_in_new_tab" type="checkbox" class="rounded border-gray-300" />
                      {{ t('navigation.openInNewTab') }}
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button type="submit" class="btn btn-primary px-4 py-2" :disabled="form.processing">
                {{ form.processing ? t('navigation.saving') : t('navigation.save') }}
              </button>
            </div>
          </form>
        </div>

        <aside class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 md:p-6 h-fit">
          <h2 class="text-card-title text-gray-900 dark:text-gray-100">{{ t('navigation.previewTitle') }}</h2>
          <p class="mt-1 text-sm text-muted muted-color">{{ t('navigation.previewHelp') }}</p>

          <div class="mt-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 p-4">
            <p class="mb-3 text-xs text-muted muted-color">{{ t('navigation.previewCurrentLocale') }}</p>
            <ul class="space-y-2">
              <li
                v-for="link in previewLinks"
                :key="`preview-${link.key}`"
                class="flex items-center justify-between gap-2 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm"
              >
                <span>{{ link.label }}</span>
                <span
                  class="rounded-full px-2 py-0.5 text-[11px]"
                  :class="link.type === 'section'
                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200'
                    : 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200'"
                >
                  {{ itemTypeLabel(link.type) }}
                </span>
              </li>
            </ul>
            <p v-if="!previewLinks.length" class="text-sm text-muted muted-color">{{ t('navigation.previewEmpty') }}</p>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>
