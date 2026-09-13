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

const contentDir = computed(() => (locale.value === 'ar' ? 'rtl' : 'ltr'))
const productName = computed(() => resolveBilingualField(props.product || {}, 'name', locale.value))
const productDetails = computed(() => resolveBilingualField(props.product || {}, 'details', locale.value))
const hasDetails = computed(() => Boolean(plainTextFromHtml(productDetails.value).trim()))
const sizes = computed(() => (Array.isArray(props.product?.sizes) ? props.product.sizes : []).filter((size) => size?.value))

const sizeLabel = (size) => [size.unit, size.value].filter(Boolean).join(' ')

const close = () => emit('close')
</script>

<template>
  <Teleport to="body">
    <div
      v-show="isOpen"
      class="px-product-modal-backdrop"
      role="dialog"
      aria-modal="true"
      :aria-hidden="isOpen ? 'false' : 'true'"
      aria-labelledby="public-product-details-title"
      @click.self="close"
    >
      <div v-if="product" class="px-product-modal">
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
          <div class="px-product-modal-layout">
            <div class="px-product-modal-copy" :dir="contentDir">
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

              <RichTextContent
                v-if="hasDetails"
                :content="productDetails"
                tag="div"
                class="px-product-modal-details"
              />
            </div>

            <div class="px-product-modal-media">
              <img
                v-if="product.image"
                :src="product.image"
                :alt="productName"
              />
              <PublicMediaPlaceholder v-else icon="cube" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
