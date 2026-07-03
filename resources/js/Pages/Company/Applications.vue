<script setup>
defineProps({
  applications: {
    type: Object,
    default: () => ({ data: [] }),
  },
  offers: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({ offer: null, status: null }),
  },
})
</script>

<template>
  <div>
    <h1>Company Applications</h1>
  </div>
</template>
