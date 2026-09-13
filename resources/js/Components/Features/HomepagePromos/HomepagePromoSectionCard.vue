<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
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
    ? t('homepagePromos.sectionCard.sectionVisible')
    : t('homepagePromos.sectionCard.sectionHidden'),
)
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
            :title="t('homepagePromos.sectionCard.sectionVisibilityHint')"
          >
            {{ visibilityLabel }}
          </span>
        </div>
        <p class="text-sm text-muted muted-color">
          {{ t(`homepagePromos.sections.${card.key}.description`) }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <Link :href="card.edit_section_settings_url || '/homepage-sections'" class="btn btn-secondary px-3 py-1.5">
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
