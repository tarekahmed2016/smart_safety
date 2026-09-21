<script setup>
import PublicLayout from '../../Layouts/PublicLayout.vue'
import RichTextContent from '../../Components/Common/RichTextContent.vue'
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import LocalizedHeading from '../../Components/Public/LocalizedHeading.vue'
import EmptyMediaPlaceholder from '../../Components/Public/EmptyMediaPlaceholder.vue'

defineOptions({ layout: PublicLayout })

const { t, locale } = useI18n()
const page = usePage()
const product = computed(() => page.props.product || {})

const productName = computed(() => resolveBilingualField(product.value, 'name', locale.value))
const productDescription = computed(() => resolveBilingualField(product.value, 'description', locale.value))
</script>

<template>
  <section class="px-page-hero">
    <div class="px-container">
      <p class="px-breadcrumb">
        <Link :href="route('home')">{{ t('public.home.nav.home') }}</Link>
        <span aria-hidden="true"> / </span>
        <Link :href="route('public.products.index')">{{ t('public.home.nav.products') }}</Link>
      </p>
      <LocalizedHeading :text="productName" tag="h1" />
    </div>
  </section>

  <section class="px-page-section">
    <div class="px-container px-product-detail">
      <div class="px-product-detail-media">
        <img
          v-if="product.image"
          :src="product.image"
          :alt="productName"
        />
        <EmptyMediaPlaceholder v-else icon="shield" tall />
      </div>
      <div class="px-product-detail-copy" :dir="locale === 'ar' ? 'rtl' : 'ltr'">
        <RichTextContent
          v-if="productDescription"
          :content="productDescription"
          tag="div"
        />
        <p v-else>{{ t('public.home.products.noDescription') }}</p>
        <a href="/#contact" class="px-btn px-btn-green">
          {{ t('public.home.hero.ctaQuote') }}
        </a>
      </div>
    </div>
  </section>
</template>
