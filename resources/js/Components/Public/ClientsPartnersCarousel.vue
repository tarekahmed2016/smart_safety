<script setup>
import { toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { useHorizontalCarousel } from '../../Composables/useHorizontalCarousel.js'
import PublicMediaPlaceholder from './PublicMediaPlaceholder.vue'

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})

const { t, locale } = useI18n()

const {
  trackRef,
  carouselItems,
  isHovered,
  setHovered,
  scrollPrevious,
  scrollNext,
  onPointerDown,
  onPointerMove,
  onPointerUp,
  onKeydown,
} = useHorizontalCarousel(toRef(props, 'items'), {
  cardSelector: '.px-client-card',
  followVisualMotion: true,
})

const itemName = (item) => resolveBilingualField(item, 'name', locale.value).trim()

const isValidType = (item) => item?.type === 'client' || item?.type === 'partner'

const showTypeBadge = (item) => (item.show_type_badge ?? true) && isValidType(item)

const itemTypeLabel = (item) => (
  item.type === 'partner'
    ? t('public.home.clientsPartners.partnerBadge')
    : t('public.home.clientsPartners.clientBadge')
)

const showTextArea = (item) => Boolean(showTypeBadge(item) || itemName(item))
</script>

<template>
  <div
    class="px-product-carousel px-horizontal-carousel"
    role="region"
    :aria-label="t('public.home.clientsPartners.carouselLabel')"
    @keydown="onKeydown"
  >
    <button
      type="button"
      class="px-product-carousel-nav px-product-carousel-nav--prev"
      :aria-label="t('public.home.clientsPartners.previous')"
      @click="scrollPrevious"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M15 18l-6-6 6-6" />
      </svg>
    </button>

    <div
      class="px-product-carousel-viewport px-horizontal-carousel-viewport"
      @mouseenter="setHovered(true)"
      @mouseleave="setHovered(false)"
      @pointerdown="onPointerDown"
      @pointermove="onPointerMove"
      @pointerup="onPointerUp"
      @pointercancel="onPointerUp"
    >
      <div ref="trackRef" class="px-product-carousel-track px-horizontal-carousel-track">
        <article
          v-for="(item, index) in carouselItems"
          :key="`${item.type}-${item.logo}-${index}`"
          class="px-client-card px-carousel-card"
          :class="{ 'px-client-card--media-only': !showTextArea(item) }"
          :aria-hidden="index >= items.length ? 'true' : undefined"
        >
          <div class="px-client-media">
            <img
              v-if="item.logo"
              :src="item.logo"
              :alt="itemName(item) || itemTypeLabel(item)"
            />
            <PublicMediaPlaceholder v-else icon="handshake" />
          </div>
          <div v-if="showTextArea(item)" class="px-client-body">
            <span v-if="showTypeBadge(item)" class="px-client-badge">{{ itemTypeLabel(item) }}</span>
            <h3 v-if="itemName(item)">{{ itemName(item) }}</h3>
            <a
              v-if="item.website"
              :href="item.website"
              class="px-text-link"
              target="_blank"
              rel="noopener noreferrer"
            >
              {{ t('public.home.clientsPartners.visitWebsite') }}
            </a>
          </div>
        </article>
      </div>
    </div>

    <button
      type="button"
      class="px-product-carousel-nav px-product-carousel-nav--next"
      :aria-label="t('public.home.clientsPartners.next')"
      @click="scrollNext"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M9 18l6-6-6-6" />
      </svg>
    </button>
  </div>
</template>

<style>
.px-clients-partners .px-client-card .px-client-body {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.px-clients-partners .px-client-card .px-client-body .px-client-badge {
  align-self: center;
  margin-inline: auto;
}

.px-clients-partners .px-client-card .px-client-body h3 {
  width: 100%;
  text-align: center;
}

.px-clients-partners .px-client-card .px-client-body p,
.px-clients-partners .px-client-card .px-client-body .px-text-link {
  text-align: center;
}

.px-clients-partners .px-client-card .px-client-body .px-text-link {
  display: inline-block;
  width: auto;
  align-self: center;
  margin-inline: auto;
}
</style>
