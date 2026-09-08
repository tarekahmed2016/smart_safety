<script setup>
import { toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { useHorizontalCarousel } from '../../Composables/useHorizontalCarousel.js'
import RichTextContent from '../Common/RichTextContent.vue'

const props = defineProps({
  members: {
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
} = useHorizontalCarousel(toRef(props, 'members'), {
  cardSelector: '.px-team-card',
})

const memberName = (member) => resolveBilingualField(member, 'name', locale.value)

const memberPosition = (member) => resolveBilingualField(member, 'position', locale.value)

const memberBioHtml = (member) => resolveBilingualField(member, 'bio', locale.value)
</script>

<template>
  <div
    class="px-product-carousel px-horizontal-carousel"
    role="region"
    :aria-label="t('public.home.team.carouselLabel')"
  >
    <button
      type="button"
      class="px-product-carousel-nav px-product-carousel-nav--prev"
      :aria-label="t('public.home.team.previous')"
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
          v-for="(member, index) in carouselItems"
          :key="`${memberName(member)}-${index}`"
          class="px-team-card px-carousel-card"
          :aria-hidden="index >= members.length ? 'true' : undefined"
        >
          <div class="px-team-media">
            <img
              v-if="member.image"
              :src="member.image"
              :alt="memberName(member)"
            />
            <div v-else class="px-media-fallback" :aria-hidden="true"></div>
          </div>
          <div class="px-team-body">
            <h3>{{ memberName(member) }}</h3>
            <p v-if="memberPosition(member)" class="px-team-position">{{ memberPosition(member) }}</p>
            <RichTextContent
              v-if="memberBioHtml(member)"
              :content="memberBioHtml(member)"
              tag="div"
              class="px-team-bio"
            />
            <p v-else class="px-team-bio">{{ t('public.home.team.noBio') }}</p>
            <a
              v-if="member.linkedin_url"
              :href="member.linkedin_url"
              class="px-text-link"
              target="_blank"
              rel="noopener noreferrer"
            >
              {{ t('public.home.team.viewLinkedIn') }}
            </a>
          </div>
        </article>
      </div>
    </div>

    <button
      type="button"
      class="px-product-carousel-nav px-product-carousel-nav--next"
      :aria-label="t('public.home.team.next')"
      @click="scrollNext"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M9 18l6-6-6-6" />
      </svg>
    </button>
  </div>
</template>
