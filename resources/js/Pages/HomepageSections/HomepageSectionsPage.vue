<script setup>
import { computed, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import DashboardLayout from '../../Layouts/DashboardLayout.vue'

defineOptions({ layout: DashboardLayout })

const { t } = useI18n()
const page = usePage()
const sections = computed(() => page.props.homepageSections || [])

const form = useForm({
  sections: sections.value.map((section) => ({
    id: section.id,
    section_key: section.key,
    is_visible: Boolean(section.is_visible),
    ordering: section.ordering,
    title_ar: section.title_ar || '',
    title_en: section.title_en || '',
    headline_ar: section.settings?.headline_ar || '',
    headline_en: section.settings?.headline_en || '',
    highlight_ar: section.settings?.highlight_ar || '',
    highlight_en: section.settings?.highlight_en || '',
    show_in_navigation: Boolean(section.show_in_navigation),
    nav_label_ar: section.nav_label_ar || '',
    nav_label_en: section.nav_label_en || '',
    nav_order: section.nav_order ?? 0,
    anchor_id: section.anchor_id || '',
  })),
})

const draggedIndex = ref(null)

const sectionTypeLabel = (type) => t(`homepageSections.types.${type}`, type)

const showsSectionTitleFields = (section) => {
  const section_key = section.section_key || sectionMetaById.value[section.id]?.key

  if (section_key === 'hero' || section_key === 'features') {
    return false
  }

  return true
}

const sortedSections = computed(() =>
  [...form.sections].sort((left, right) => left.ordering - right.ordering),
)

const sectionMetaById = computed(() =>
  Object.fromEntries(sections.value.map((section) => [section.id, section])),
)

const reorderSections = (fromIndex, toIndex) => {
  if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0) {
    return
  }

  const items = [...sortedSections.value]
  const [moved] = items.splice(fromIndex, 1)
  items.splice(toIndex, 0, moved)

  items.forEach((item, index) => {
    item.ordering = index + 1
  })

  form.sections = items
}

const onDragStart = (index) => {
  draggedIndex.value = index
}

const onDrop = (index) => {
  if (draggedIndex.value === null) {
    return
  }

  reorderSections(draggedIndex.value, index)
  draggedIndex.value = null
}

const moveSection = (index, direction) => {
  reorderSections(index, index + direction)
}

const submit = () => {
  form.put(route('homepage-sections.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="bg-gray-50 dark:bg-gray-900 p-3 md:p-6">
    <div class="max-w-5xl mx-auto">
      <div class="mb-6 md:mb-8">
        <h1 class="text-page-title text-gray-900 dark:text-gray-100">{{ t('homepageSections.pageTitle') }}</h1>
        <p class="mt-2 text-muted muted-color">{{ t('homepageSections.pageSubtitle') }}</p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 md:p-6">
        <form @submit.prevent="submit" class="space-y-6">
          <p class="text-muted muted-color">{{ t('homepageSections.help') }}</p>

          <div class="space-y-3">
            <div
              v-for="(section, index) in sortedSections"
              :key="section.id"
              class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/40"
              draggable="true"
              @dragstart="onDragStart(index)"
              @dragover.prevent
              @drop.prevent="onDrop(index)"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 flex-1 space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-mono text-muted muted-color">#{{ section.ordering }}</span>
                    <h2 class="text-card-title text-gray-900 dark:text-gray-100">
                      {{ sectionMetaById[section.id]?.name }}
                    </h2>
                    <span class="rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200 px-2 py-0.5 text-xs">
                      {{ sectionTypeLabel(sectionMetaById[section.id]?.type) }}
                    </span>
                  </div>
                  <p class="text-sm text-muted muted-color">{{ t('homepageSections.dragHint') }}</p>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" class="btn btn-secondary px-3 py-1.5" :disabled="index === 0" @click="moveSection(index, -1)">
                    {{ t('homepageSections.moveUp') }}
                  </button>
                  <button
                    type="button"
                    class="btn btn-secondary px-3 py-1.5"
                    :disabled="index === sortedSections.length - 1"
                    @click="moveSection(index, 1)"
                  >
                    {{ t('homepageSections.moveDown') }}
                  </button>
                  <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input v-model="section.is_visible" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <span class="text-label">{{ t('homepageSections.visibleLabel') }}</span>
                  </label>
                </div>
              </div>

              <div
                v-if="showsSectionTitleFields(section)"
                class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4"
              >
                <div>
                  <label class="form-label text-label">{{ t('homepageSections.titleArLabel') }}</label>
                  <input v-model="section.title_ar" type="text" class="form-input text-body" :placeholder="t('homepageSections.titleArPlaceholder')" />
                </div>
                <div>
                  <label class="form-label text-label">{{ t('homepageSections.titleEnLabel') }}</label>
                  <input v-model="section.title_en" type="text" class="form-input text-body" :placeholder="t('homepageSections.titleEnPlaceholder')" />
                </div>
              </div>

              <div
                v-if="['goals', 'vision_mission', 'why_us'].includes(sectionMetaById[section.id]?.type)"
                class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4"
              >
                <div>
                  <label class="form-label text-label">{{ t('homepageSections.headlineArLabel') }}</label>
                  <input v-model="section.headline_ar" type="text" class="form-input text-body" :placeholder="t('homepageSections.headlineArPlaceholder')" />
                </div>
                <div>
                  <label class="form-label text-label">{{ t('homepageSections.headlineEnLabel') }}</label>
                  <input v-model="section.headline_en" type="text" class="form-input text-body" :placeholder="t('homepageSections.headlineEnPlaceholder')" />
                </div>
              </div>

              <div
                v-if="sectionMetaById[section.id]?.type === 'why_us'"
                class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4"
              >
                <div>
                  <label class="form-label text-label">{{ t('homepagePromos.settingsFields.highlight_ar') }}</label>
                  <input v-model="section.highlight_ar" type="text" class="form-input text-body" />
                </div>
                <div>
                  <label class="form-label text-label">{{ t('homepagePromos.settingsFields.highlight_en') }}</label>
                  <input v-model="section.highlight_en" type="text" class="form-input text-body" />
                </div>
              </div>

              <div
                v-if="sectionMetaById[section.id]?.is_navigable"
                class="mt-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-4 space-y-4"
              >
                <h3 class="text-label font-medium text-gray-900 dark:text-gray-100">{{ t('homepageSections.navigationTitle') }}</h3>
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                  <input v-model="section.show_in_navigation" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                  <span class="text-label">{{ t('homepageSections.showInNavigationLabel') }}</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="form-label text-label">{{ t('homepageSections.navLabelAr') }}</label>
                    <input v-model="section.nav_label_ar" type="text" class="form-input text-body" :placeholder="t('homepageSections.navLabelArPlaceholder')" />
                  </div>
                  <div>
                    <label class="form-label text-label">{{ t('homepageSections.navLabelEn') }}</label>
                    <input v-model="section.nav_label_en" type="text" class="form-input text-body" :placeholder="t('homepageSections.navLabelEnPlaceholder')" />
                  </div>
                  <div>
                    <label class="form-label text-label">{{ t('homepageSections.navOrderLabel') }}</label>
                    <input v-model.number="section.nav_order" type="number" min="0" class="form-input text-body" />
                  </div>
                  <div>
                    <label class="form-label text-label">{{ t('homepageSections.anchorIdLabel') }}</label>
                    <input v-model="section.anchor_id" type="text" dir="ltr" class="form-input text-body font-mono" :placeholder="sectionMetaById[section.id]?.key" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" class="btn btn-primary px-4 py-2" :disabled="form.processing">
              {{ form.processing ? t('homepageSections.saving') : t('homepageSections.save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
