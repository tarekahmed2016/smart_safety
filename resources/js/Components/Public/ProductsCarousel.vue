<script setup>
import { ref, toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { useHorizontalCarousel } from '../../Composables/useHorizontalCarousel.js'
import LocalizedHeading from './LocalizedHeading.vue'
import PublicMediaPlaceholder from './PublicMediaPlaceholder.vue'
import ProductDetailsModal from './ProductDetailsModal.vue'

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
})

const { t, locale } = useI18n()
const selectedProduct = ref(null)
const emit = defineEmits(['inquire'])

const {
  trackRef,
  carouselItems,
  setHovered,
  scrollPrevious,
  scrollNext,
  onPointerDown,
  onPointerMove,
  onPointerUp,
  onKeydown,
  ignoreClick,
} = useHorizontalCarousel(toRef(props, 'products'), {
  cardSelector: '.px-product-card',
  followVisualMotion: true,
})

const productName = (product) => resolveBilingualField(product, 'name', locale.value)

const productExcerpt = (product) =>
  resolveBilingualField(product, 'excerpt', locale.value)
    || t('public.home.products.noDescription')

const openProduct = (product) => {
  if (!product || ignoreClick.value) {
    return
  }

  selectedProduct.value = product
}

const onCardKeydown = (product, event) => {
  if (event.target !== event.currentTarget) {
    return
  }

  if (event.key !== 'Enter' && event.key !== ' ') {
    return
  }

  event.preventDefault()
  openProduct(product)
}

const closeProductDetails = () => {
  selectedProduct.value = null
}

const inquireAboutProduct = (product) => {
  if (!product) {
    return
  }

  emit('inquire', product)
}
</script>

<template>
  <div
    class="px-product-carousel px-horizontal-carousel"
    role="region"
    :aria-label="t('public.home.products.carouselLabel')"
    @keydown="onKeydown"
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
          role="button"
          :tabindex="index >= products.length ? -1 : 0"
          :aria-hidden="index >= products.length ? 'true' : undefined"
          :aria-label="t('public.home.products.openDetails', { name: productName(product) })"
          @click.stop="openProduct(product)"
          @keydown="onCardKeydown(product, $event)"
        >
          <div class="px-product-media" @click.stop="openProduct(product)">
            <img
              v-if="product.image"
              :src="product.image"
              :alt="productName(product)"
              @click.stop="openProduct(product)"
            />
            <PublicMediaPlaceholder v-else icon="cube" />
          </div>
          <div class="px-product-body" :dir="locale === 'ar' ? 'rtl' : 'ltr'">
            <LocalizedHeading :text="productName(product)" tag="h3" />
            <p>{{ productExcerpt(product) }}</p>
            <button
              type="button"
              class="px-btn px-btn-green px-product-inquire"
              @click.stop="inquireAboutProduct(product)"
              @keydown.stop
            >
              {{ t('public.home.products.inquire') }}
            </button>
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

  <ProductDetailsModal :product="selectedProduct" @close="closeProductDetails" />
</template>
