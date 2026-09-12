<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { mixedScriptParts, sanitizeLocalizedTitle } from '../../Composables/useBilingualContent.js'

const props = defineProps({
  text: {
    type: String,
    default: '',
  },
  tag: {
    type: String,
    default: 'h3',
  },
})

const { locale } = useI18n()

const displayText = computed(() => sanitizeLocalizedTitle(props.text, locale.value))
const parts = computed(() => mixedScriptParts(displayText.value))
const textDirection = computed(() => (locale.value === 'ar' ? 'rtl' : 'ltr'))
</script>

<template>
  <component :is="tag" class="px-product-title" :dir="textDirection" :lang="locale">
    <template v-for="(part, index) in parts" :key="`${part.text}-${index}`">
      <bdi v-if="part.isolate" dir="ltr">{{ part.text }}</bdi>
      <template v-else>{{ part.text }}</template>
    </template>
  </component>
</template>
