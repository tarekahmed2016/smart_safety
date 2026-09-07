<script setup>
const props = defineProps({
  name: {
    type: String,
    required: true,
  },
})

const icons = {
  handshake: 'M7 12h3m4 0h3M8 16h8M9 8V6a2 2 0 012-2h2a2 2 0 012 2v2m-9 4a3 3 0 013-3h6a3 3 0 013 3v7H6v-7z',
  experience: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  flexible: 'M4 7h6v6H4V7zm10 0h6v6h-6V7zM4 17h16',
  quality: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  food: 'M12 4v7a4 4 0 01-4 4H6m6-11v16m0-16c1.5 2 4 3 6 3v5a4 4 0 01-4 4',
  agri: 'M12 20V10m0 0c-3 0-6-2-6-6 4 0 6 3 6 6zm0 0c3 0 6-2 6-6-4 0-6 3-6 6z',
  industry: 'M3 21h18M5 21V10l6 3V10l6 3V5h2v16',
  packing: 'M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z',
  home: 'M3 11l9-8 9 8v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9z',
  medical: 'M12 8v8m-4-4h8m7 0a9 9 0 11-18 0 9 9 0 0118 0z',
}
</script>

<template>
  <svg
    class="px-line-icon"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.6"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
  >
    <path :d="icons[props.name] || icons.quality" />
  </svg>
</template>
