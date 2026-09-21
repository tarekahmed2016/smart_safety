<script setup>
import PublicLayout from '../../Layouts/PublicLayout.vue'
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import LocalizedHeading from '../../Components/Public/LocalizedHeading.vue'
import PublicMediaPlaceholder from '../../Components/Public/PublicMediaPlaceholder.vue'

defineOptions({ layout: PublicLayout })

const { t, locale } = useI18n()
const page = usePage()
const products = computed(() => page.props.products || [])

const productName = (product) => resolveBilingualField(product, 'name', locale.value)
const productExcerpt = (product) =>
  resolveBilingualField(product, 'excerpt', locale.value)
    || t('public.home.products.noDescription')
</script>

<template>
  <section class="px-page-hero">
    <div class="px-container">
      <h1>{{ t('public.home.products.title') }}</h1>
      <p v-if="t('public.home.products.subtitle')">{{ t('public.home.products.subtitle') }}</p>
    </div>
  </section>

  <section class="px-products px-page-section">
    <div class="px-container">
      <div v-if="products.length" class="px-product-grid">
        <article v-for="product in products" :key="product.slug" class="px-product-card">
          <div class="px-product-media">
            <img
              v-if="product.image"
              :src="product.image"
              :alt="productName(product)"
            />
            <PublicMediaPlaceholder v-else icon="cube" />
          </div>
          <div class="px-product-body" :dir="locale === 'ar' ? 'rtl' : 'ltr'">
            <LocalizedHeading :text="productName(product)" tag="h2" />
            <p>{{ productExcerpt(product) }}</p>
            <Link :href="route('public.products.show', { slug: product.slug })" class="px-text-link">
              {{ t('public.home.products.details') }}
            </Link>
          </div>
        </article>
      </div>
      <p v-else class="px-empty">{{ t('public.home.products.empty') }}</p>
    </div>
  </section>
</template>
