<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { useDialogAccessibility } from '../../Composables/General/useDialogAccessibility.js'
import { plainTextFromHtml } from '../../Composables/useRichText.js'
import RichTextContent from '../Common/RichTextContent.vue'
import LocalizedHeading from './LocalizedHeading.vue'
import PublicMediaPlaceholder from './PublicMediaPlaceholder.vue'

const props = defineProps({
  product: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close'])

const { t, locale } = useI18n()
const isOpen = computed(() => Boolean(props.product))

useDialogAccessibility(isOpen, () => emit('close'))

const productName = computed(() => resolveBilingualField(props.product || {}, 'name', locale.value))
const productDetails = computed(() => resolveBilingualField(props.product || {}, 'details', locale.value))
const hasDetails = computed(() => Boolean(plainTextFromHtml(productDetails.value).trim()))
const sizes = computed(() => (Array.isArray(props.product?.sizes) ? props.product.sizes : []).filter((size) => size?.value))
const specifications = computed(() => {
  const items = locale.value === 'ar'
    ? props.product?.specifications_ar
    : props.product?.specifications_en

  return (Array.isArray(items) ? items : []).map((item) => String(item).trim()).filter(Boolean)
})

const sizeLabel = (size) => [size.unit, size.value].filter(Boolean).join(' ')

const close = () => emit('close')
</script>

<template>
  <div
    v-if="product"
    class="px-product-modal-backdrop"
    role="dialog"
    aria-modal="true"
    :aria-labelledby="'public-product-details-title'"
    @click.self="close"
  >
    <div class="px-product-modal" :dir="locale === 'ar' ? 'rtl' : 'ltr'">
      <button
        type="button"
        class="px-product-modal-close"
        :aria-label="t('public.home.products.closeDetails')"
        @click="close"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M6 6l12 12M18 6L6 18" />
        </svg>
      </button>

      <div class="px-product-modal-scroll">
        <LocalizedHeading
          id="public-product-details-title"
          class="px-product-modal-title"
          :text="productName"
          tag="h2"
        />

        <div v-if="sizes.length" class="px-product-modal-sizes">
          <span v-for="(size, index) in sizes" :key="`${size.value}-${index}`" class="px-product-size-pill">
            {{ sizeLabel(size) }}
          </span>
        </div>

        <div class="px-product-modal-grid" :class="{ 'is-media-only': !hasDetails && !specifications.length }">
          <div class="px-product-modal-media">
            <img
              v-if="product.image"
              :src="product.image"
              :alt="productName"
            />
            <PublicMediaPlaceholder v-else icon="cube" />
          </div>

          <div class="px-product-modal-copy">
            <RichTextContent
              v-if="hasDetails"
              :content="productDetails"
              tag="div"
              class="px-product-modal-details"
            />

            <div v-if="specifications.length" class="px-product-modal-specs">
              <h3>{{ t('public.home.products.specificationsTitle') }}</h3>
              <ul>
                <li v-for="(item, index) in specifications" :key="`${item}-${index}`">
                  {{ item }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
