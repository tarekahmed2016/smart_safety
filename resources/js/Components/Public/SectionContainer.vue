<script setup>
defineProps({
  as: {
    type: String,
    default: 'div',
  },
})
</script>

<template>
  <component :is="as" class="ss-container px-container">
    <slot />
  </component>
</template>
