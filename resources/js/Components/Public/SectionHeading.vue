<script setup>
defineProps({
  eyebrow: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: '',
  },
  lede: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <header v-if="eyebrow || title || lede || $slots.default" class="ss-section-heading px-section-header">
    <p v-if="eyebrow" class="ss-section-heading__eyebrow px-about-eyebrow">{{ eyebrow }}</p>
    <h2 v-if="title" class="ss-section-heading__title">{{ title }}</h2>
    <p v-if="lede" class="ss-section-heading__lede px-section-lede">{{ lede }}</p>
    <slot />
  </header>
</template>
