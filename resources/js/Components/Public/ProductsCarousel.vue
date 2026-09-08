<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
})

const { t, locale } = useI18n()

const trackRef = ref(null)
const copyCount = ref(2)
const offset = ref(0)
const isHovered = ref(false)
const isManualPaused = ref(false)
const reduceMotion = ref(false)

const autoSpeed = 0.8
const manualPauseMs = 1200

const scrollDirection = computed(() => (locale.value === 'ar' ? -1 : 1))

let frameId = null
let manualPauseTimer = null
let cycleWidth = 0
let resizeObserver = null

const carouselProducts = computed(() => {
  if (!props.products.length) {
    return []
  }

  return Array.from({ length: copyCount.value }, () => props.products).flat()
})

const productName = (product) => resolveBilingualField(product, 'name', locale.value)

const productExcerpt = (product) =>
  resolveBilingualField(product, 'excerpt', locale.value)
    || t('public.home.products.noDescription')

const getGap = () => {
  if (!trackRef.value) {
    return 20
  }

  const style = getComputedStyle(trackRef.value)
  const gapValue = style.columnGap || style.gap || '20'

  return Number.parseFloat(gapValue) || 20
}

const measureCycle = () => {
  const track = trackRef.value

  if (!track || props.products.length === 0) {
    cycleWidth = 0
    return
  }

  const cards = track.querySelectorAll('.px-product-card')
  const perSet = props.products.length

  if (cards.length < perSet) {
    return
  }

  const gap = getGap()
  let setWidth = 0

  for (let index = 0; index < perSet; index += 1) {
    setWidth += cards[index].getBoundingClientRect().width
  }

  setWidth += gap * (perSet - 1)
  cycleWidth = setWidth

  const viewport = track.parentElement?.clientWidth ?? 0
  const neededCopies = Math.max(2, Math.ceil((viewport * 2) / Math.max(setWidth, 1)) + 1)

  if (copyCount.value !== neededCopies) {
    copyCount.value = neededCopies
    nextTick(() => measureCycle())
    return
  }

  normalizeOffset()
  applyTransform()
}

const normalizeOffset = () => {
  if (cycleWidth <= 0) {
    return
  }

  while (offset.value >= cycleWidth) {
    offset.value -= cycleWidth
  }

  while (offset.value < 0) {
    offset.value += cycleWidth
  }
}

const applyTransform = () => {
  if (!trackRef.value) {
    return
  }

  trackRef.value.style.transform = `translate3d(${-offset.value}px, 0, 0)`
}

const pauseBriefly = () => {
  isManualPaused.value = true
  clearTimeout(manualPauseTimer)
  manualPauseTimer = setTimeout(() => {
    isManualPaused.value = false
  }, manualPauseMs)
}

const scrollBy = (direction) => {
  const card = trackRef.value?.querySelector('.px-product-card')
  const stepSize = (card?.getBoundingClientRect().width ?? 260) + getGap()

  offset.value += direction * stepSize * scrollDirection.value
  normalizeOffset()
  applyTransform()
  pauseBriefly()
}

const scrollPrevious = () => scrollBy(-1)
const scrollNext = () => scrollBy(1)

const step = () => {
  const paused = isHovered.value || isManualPaused.value || reduceMotion.value

  if (!paused) {
    offset.value += autoSpeed * scrollDirection.value
    normalizeOffset()
    applyTransform()
  }

  frameId = requestAnimationFrame(step)
}

onMounted(() => {
  reduceMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches

  nextTick(() => {
    measureCycle()

    if (trackRef.value && typeof ResizeObserver !== 'undefined') {
      resizeObserver = new ResizeObserver(() => measureCycle())
      resizeObserver.observe(trackRef.value)

      if (trackRef.value.parentElement) {
        resizeObserver.observe(trackRef.value.parentElement)
      }
    }
  })

  window.addEventListener('resize', measureCycle)
  frameId = requestAnimationFrame(step)
})

onUnmounted(() => {
  cancelAnimationFrame(frameId)
  window.removeEventListener('resize', measureCycle)
  resizeObserver?.disconnect()
  clearTimeout(manualPauseTimer)
})

watch(
  () => props.products,
  () => {
    copyCount.value = 2
    offset.value = 0
    nextTick(() => measureCycle())
  },
  { deep: true },
)

watch(locale, () => nextTick(() => measureCycle()))
</script>

<template>
  <div
    class="px-product-carousel"
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
      class="px-product-carousel-viewport"
      @mouseenter="isHovered = true"
      @mouseleave="isHovered = false"
    >
      <div ref="trackRef" class="px-product-carousel-track">
        <article
          v-for="(product, index) in carouselProducts"
          :key="`${product.slug}-${index}`"
          class="px-product-card"
          :aria-hidden="index >= products.length ? 'true' : undefined"
        >
          <div class="px-product-media">
            <img
              v-if="product.image"
              :src="product.image"
              :alt="productName(product)"
            />
            <div v-else class="px-media-fallback" :aria-hidden="true"></div>
          </div>
          <div class="px-product-body">
            <h3>{{ productName(product) }}</h3>
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
