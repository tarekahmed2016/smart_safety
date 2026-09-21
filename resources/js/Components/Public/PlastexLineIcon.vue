<script setup>
import SmartSafetyIcon from './SmartSafetyIcon.vue'

defineProps({
  name: {
    type: String,
    required: true,
  },
})
</script>

<template>
  <SmartSafetyIcon :name="name" />
</template>
