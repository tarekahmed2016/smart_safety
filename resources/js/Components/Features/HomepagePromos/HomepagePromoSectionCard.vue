<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import HomepagePromoItemsList from './HomepagePromoItemsList.vue'

const props = defineProps({
  card: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['add-promo', 'edit-promo', 'delete-promo', 'toggle-promo', 'move-promo'])

const { t } = useI18n()

const sectionTypeLabel = (type) => t(`homepageSections.types.${type}`, type)

const visibilityLabel = computed(() =>
  props.card.section?.is_visible
    ? t('homepagePromos.sectionCard.visible')
    : t('homepagePromos.sectionCard.hidden'),
)

const hasCompanySettings = computed(() => Object.keys(props.card.company_settings || {}).length > 0)
const hasSectionSettings = computed(() => Object.keys(props.card.section_settings || {}).length > 0)
const hasSettings = computed(() => hasCompanySettings.value || hasSectionSettings.value)

const settingsForm = useForm({
  company: { ...(props.card.company_settings || {}) },
  section: { ...(props.card.section_settings || {}) },
})

const saveSettings = () => {
  settingsForm.put(route('homepage-promos.section-settings.update', props.card.key), {
    preserveScroll: true,
  })
}

const settingsFieldLabel = (field) => t(`homepagePromos.settingsFields.${field}`, field)
</script>

<template>
  <article class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-4 md:p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div class="min-w-0 flex-1 space-y-2">
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-sm font-mono text-muted muted-color">#{{ card.section.ordering }}</span>
          <h2 class="text-card-title text-gray-900 dark:text-gray-100">
            {{ t(`homepagePromos.sections.${card.key}.titleEn`) }}
            <span class="text-muted muted-color font-normal"> / {{ t(`homepagePromos.sections.${card.key}.titleAr`) }}</span>
          </h2>
          <span class="rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200 px-2 py-0.5 text-xs">
            {{ sectionTypeLabel(card.section.type) }}
          </span>
          <span
            :class="[
              'rounded-full px-2 py-0.5 text-xs',
              card.section.is_visible
                ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200'
                : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
            ]"
          >
            {{ visibilityLabel }}
          </span>
        </div>
        <p class="text-sm text-muted muted-color">
          {{ t(`homepagePromos.sections.${card.key}.description`) }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <Link :href="route('homepage-sections.index')" class="btn btn-secondary px-3 py-1.5">
          {{ t('homepagePromos.sectionCard.editSectionSettings') }}
        </Link>
        <Link
          v-if="card.manage_route"
          :href="route(card.manage_route)"
          class="btn btn-secondary px-3 py-1.5"
        >
          {{ t(`homepagePromos.sections.${card.key}.manageLink`) }}
        </Link>
        <button
          v-if="card.can_add_promo"
          type="button"
          class="btn btn-primary px-3 py-1.5"
          @click="emit('add-promo', card)"
        >
          {{ t('homepagePromos.sectionCard.addContent') }}
        </button>
      </div>
    </div>

    <form v-if="hasSettings" class="mt-5 space-y-4" @submit.prevent="saveSettings">
      <h3 class="text-label font-medium text-gray-900 dark:text-gray-100">
        {{ t('homepagePromos.sectionCard.contentSettings') }}
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <template v-for="(value, field) in card.company_settings" :key="field">
          <div :class="field === 'products_homepage_limit' ? '' : ''">
            <label class="form-label text-label">{{ settingsFieldLabel(field) }}</label>
            <textarea
              v-if="String(field).includes('subtitle')"
              v-model="settingsForm.company[field]"
              rows="2"
              class="form-input text-body"
            />
            <input
              v-else-if="field === 'products_homepage_limit'"
              v-model="settingsForm.company[field]"
              type="number"
              min="0"
              max="100"
              class="form-input text-body"
            />
            <input
              v-else
              v-model="settingsForm.company[field]"
              type="text"
              class="form-input text-body"
            />
          </div>
        </template>

        <template v-if="hasSectionSettings">
          <div v-if="card.section_settings.title_ar !== undefined && card.key !== 'hero' && card.key !== 'features'">
            <label class="form-label text-label">{{ t('homepageSections.titleArLabel') }}</label>
            <input v-model="settingsForm.section.title_ar" type="text" class="form-input text-body" />
          </div>
          <div v-if="card.section_settings.title_en !== undefined && card.key !== 'hero' && card.key !== 'features'">
            <label class="form-label text-label">{{ t('homepageSections.titleEnLabel') }}</label>
            <input v-model="settingsForm.section.title_en" type="text" class="form-input text-body" />
          </div>
          <div v-if="card.section_settings.max_items !== undefined">
            <label class="form-label text-label">{{ t('homepagePromos.settingsFields.max_items') }}</label>
            <input
              v-model="settingsForm.section.max_items"
              type="number"
              min="1"
              max="50"
              class="form-input text-body"
            />
          </div>
          <div v-if="card.section_settings.headline_ar !== undefined">
            <label class="form-label text-label">{{ t('homepagePromos.settingsFields.headline_ar') }}</label>
            <input v-model="settingsForm.section.headline_ar" type="text" class="form-input text-body" />
          </div>
          <div v-if="card.section_settings.headline_en !== undefined">
            <label class="form-label text-label">{{ t('homepagePromos.settingsFields.headline_en') }}</label>
            <input v-model="settingsForm.section.headline_en" type="text" class="form-input text-body" />
          </div>
          <div v-if="card.section_settings.highlight_ar !== undefined">
            <label class="form-label text-label">{{ t('homepagePromos.settingsFields.highlight_ar') }}</label>
            <input v-model="settingsForm.section.highlight_ar" type="text" class="form-input text-body" />
          </div>
          <div v-if="card.section_settings.highlight_en !== undefined">
            <label class="form-label text-label">{{ t('homepagePromos.settingsFields.highlight_en') }}</label>
            <input v-model="settingsForm.section.highlight_en" type="text" class="form-input text-body" />
          </div>
        </template>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="btn btn-primary px-4 py-2" :disabled="settingsForm.processing">
          {{ settingsForm.processing ? t('homepagePromos.sectionCard.saving') : t('homepagePromos.sectionCard.saveSettings') }}
        </button>
      </div>
    </form>

    <div v-if="card.can_add_promo" class="mt-5 space-y-3">
      <h3 class="text-label font-medium text-gray-900 dark:text-gray-100">
        {{ t('homepagePromos.sectionCard.sectionItems') }}
      </h3>
      <HomepagePromoItemsList
        :items="card.items"
        @edit="emit('edit-promo', $event)"
        @delete="emit('delete-promo', $event)"
        @toggle-active="emit('toggle-promo', $event)"
        @move="(item, direction) => emit('move-promo', card, item, direction)"
      />
    </div>
  </article>
</template>
