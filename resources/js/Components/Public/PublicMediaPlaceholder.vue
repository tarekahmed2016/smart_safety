<script setup>
import PlastexLineIcon from './PlastexLineIcon.vue'

defineProps({
  icon: {
    type: String,
    default: 'industry',
  },
  tall: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <div class="px-media-fallback" :class="{ 'px-media-fallback-tall': tall }" aria-hidden="true">
    <PlastexLineIcon :name="icon" />
  </div>
</template>
