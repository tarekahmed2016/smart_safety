<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'
import { resolveBilingualField } from '../../Composables/useBilingualContent.js'
import { usePublicNavLinks } from '../../Composables/usePublicNavLinks.js'
import { companyNameBrandCssVars, companyNameBrandArFontStyle, companyNameBrandEnFontStyle } from '../../Composables/useCompanyNameBrandTypography.js'

const { t, locale } = useI18n()
const page = usePage()
const { navLinks, isHomePage } = usePublicNavLinks()

const companyInfo = computed(() => page.props.companyInfo || {})
const businessCta = computed(() => page.props.businessCta || null)

const companyNameAr = computed(() => companyInfo.value.name_ar || t('public.home.defaultCompanyName'))
const companyNameEn = computed(() => companyInfo.value.name_en || 'Creative Industry')
const brandDir = computed(() => (locale.value === 'ar' ? 'rtl' : 'ltr'))
const brandNameStyle = computed(() => companyNameBrandCssVars(companyInfo.value))
const brandNameArStyle = computed(() => companyNameBrandArFontStyle(companyInfo.value))
const brandNameEnStyle = computed(() => companyNameBrandEnFontStyle(companyInfo.value))
const logoAlt = computed(() => companyNameAr.value || companyNameEn.value)
const logo = computed(() => companyInfo.value.logo || companyInfo.value.attachment?.asset_path || '/images/creative-industry/logo.jpeg')

const quoteLabel = computed(() => {
  const text = businessCta.value ? resolveBilingualField(businessCta.value, 'cta_text', locale.value) : ''
  return text || t('public.home.nav.quoteRequest')
})

const quoteUrl = computed(() => {
  if (businessCta.value?.cta_url) return businessCta.value.cta_url
  return isHomePage.value ? '#contact' : `${route('home')}#contact`
})

const isMenuOpen = ref(false)

watch(() => page.url, () => {
  isMenuOpen.value = false
})

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
  isMenuOpen.value = false
}

const setLanguage = (code) => {
  locale.value = code
  document.dir = code === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.lang = code
  localStorage.setItem('locale', code)
}

const homeHref = computed(() => (isHomePage.value ? '#home' : route('home')))
const otherLocaleShortLabel = computed(() => (
  locale.value === 'ar' ? t('public.home.nav.switchToEnglishShort') : t('public.home.nav.switchToArabicShort')
))
const otherLocaleCode = computed(() => (locale.value === 'ar' ? 'en' : 'ar'))
</script>

<template>
  <header class="px-header">
    <nav class="px-nav" :aria-label="t('public.home.nav.main')">
      <div class="px-nav-inner">
        <a :href="homeHref" class="px-nav-brand" :dir="brandDir" @click="closeMenu">
          <img
            :src="logo"
            :alt="logoAlt"
            class="px-nav-logo"
          />
          <span class="px-nav-brand-names" :style="brandNameStyle">
            <span class="px-nav-brand-name-ar" dir="rtl" :style="brandNameArStyle">{{ companyNameAr }}</span>
            <span class="px-nav-brand-name-en" dir="ltr" :style="brandNameEnStyle">{{ companyNameEn }}</span>
          </span>
        </a>

        <div class="px-nav-links" role="list">
          <a
            v-for="link in navLinks"
            :key="link.key"
            :href="link.href"
            role="listitem"
          >
            {{ link.label }}
          </a>
        </div>

        <div class="px-nav-actions">
          <button
            type="button"
            class="px-lang-switch"
            :aria-label="t('public.home.nav.selectLanguage')"
            @click="setLanguage(otherLocaleCode)"
          >
            <svg class="px-lang-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <circle cx="12" cy="12" r="9" />
              <path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18" />
            </svg>
            <span>{{ otherLocaleShortLabel }}</span>
          </button>
          <a :href="quoteUrl" class="px-btn px-btn-blue px-nav-cta">
            <svg class="px-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path d="M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V9z" />
              <path d="M14 3v6h6M9 13h6M9 17h4" />
            </svg>
            {{ quoteLabel }}
          </a>
        </div>

        <button
          type="button"
          class="px-nav-toggle"
          :class="{ open: isMenuOpen }"
          :aria-label="t('public.home.nav.toggleMenu')"
          :aria-expanded="isMenuOpen"
          @click="toggleMenu"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>

      <div v-if="isMenuOpen" class="px-mobile-nav">
        <a
          v-for="link in navLinks"
          :key="`mobile-${link.key}`"
          :href="link.href"
          @click="closeMenu"
        >
          {{ link.label }}
        </a>
        <button
          type="button"
          class="px-lang-switch px-lang-switch--mobile"
          @click="setLanguage(otherLocaleCode)"
        >
          <svg class="px-lang-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18" />
          </svg>
          <span>{{ otherLocaleShortLabel }}</span>
        </button>
        <a :href="quoteUrl" class="px-btn px-btn-blue" @click="closeMenu">
          {{ quoteLabel }}
        </a>
      </div>
    </nav>
  </header>
</template>
