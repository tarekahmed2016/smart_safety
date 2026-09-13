<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { faChevronUp } from '@fortawesome/free-solid-svg-icons'
import { useSocialLinks } from '../../Composables/useSocialLinks.js'

const page = usePage()
const { t } = useI18n()
const companyInfo = computed(() => page.props.companyInfo || {})
const { floatingSocialLinks } = useSocialLinks(companyInfo)

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <div class="px-floating-actions" dir="ltr">
    <nav
      v-if="floatingSocialLinks.length"
      class="px-floating-social"
      :aria-label="t('public.home.floating.socialLabel')"
    >
      <a
        v-for="link in floatingSocialLinks"
        :key="link.key"
        :href="link.url"
        class="px-floating-action"
        :class="`px-floating-action--${link.key}`"
        target="_blank"
        rel="noopener noreferrer"
        :aria-label="link.label"
        :title="link.label"
        :data-tooltip="link.label"
      >
        <font-awesome-icon :icon="link.icon" aria-hidden="true" />
      </a>
    </nav>

    <button
      type="button"
      class="px-floating-action px-back-to-top"
      :aria-label="t('public.home.floating.backToTop')"
      :title="t('public.home.floating.backToTop')"
      :data-tooltip="t('public.home.floating.backToTop')"
      @click="scrollToTop"
    >
      <font-awesome-icon :icon="faChevronUp" aria-hidden="true" />
    </button>
  </div>
</template>
