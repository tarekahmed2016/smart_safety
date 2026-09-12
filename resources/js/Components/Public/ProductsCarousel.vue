<script setup>
import { toRef } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { useHorizontalCarousel } from '../../Composables/useHorizontalCarousel.js'
import LocalizedHeading from './LocalizedHeading.vue'
import PublicMediaPlaceholder from './PublicMediaPlaceholder.vue'

const props = defineProps({
  products: {
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
} = useHorizontalCarousel(toRef(props, 'products'), {
  cardSelector: '.px-product-card',
})

const productName = (product) => resolveBilingualField(product, 'name', locale.value)

const productExcerpt = (product) =>
  resolveBilingualField(product, 'excerpt', locale.value)
    || t('public.home.products.noDescription')
</script>

<template>
  <div
    class="px-product-carousel px-horizontal-carousel"
    role="region"
    :aria-label="t('public.home.products.carouselLabel')"
  >
    <button
      type="button"
      class="px-product-carousel-nav px-product-carousel-nav--prev"
      :aria-label="t('public.home.products.previous')"
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
          v-for="(product, index) in carouselItems"
          :key="`${product.slug}-${index}`"
          class="px-product-card px-carousel-card"
          :aria-hidden="index >= products.length ? 'true' : undefined"
        >
          <div class="px-product-media">
            <img
              v-if="product.image"
              :src="product.image"
              :alt="productName(product)"
            />
            <PublicMediaPlaceholder v-else icon="cube" />
          </div>
          <div class="px-product-body" :dir="locale === 'ar' ? 'rtl' : 'ltr'">
            <LocalizedHeading :text="productName(product)" tag="h3" />
            <p>{{ productExcerpt(product) }}</p>
            <Link :href="route('public.products.show', { slug: product.slug })" class="px-text-link">
              {{ t('public.home.products.details') }}
            </Link>
          </div>
        </article>
      </div>
    </div>

    <button
      type="button"
      class="px-product-carousel-nav px-product-carousel-nav--next"
      :aria-label="t('public.home.products.next')"
      @click="scrollNext"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M9 18l6-6-6-6" />
      </svg>
    </button>
  </div>
</template>
