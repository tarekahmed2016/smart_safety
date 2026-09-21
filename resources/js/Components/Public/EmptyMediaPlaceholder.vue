<script setup>
import SmartSafetyIcon from './SmartSafetyIcon.vue'

defineProps({
  icon: {
    type: String,
    default: 'shield',
  },
  tall: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <div
    class="ss-empty-media px-media-fallback"
    :class="{ 'ss-empty-media--tall': tall, 'px-media-fallback-tall': tall }"
    aria-hidden="true"
  >
    <SmartSafetyIcon :name="icon" />
  </div>
</template>
