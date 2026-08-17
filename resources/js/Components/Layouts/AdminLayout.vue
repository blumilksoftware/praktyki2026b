<script setup>
import BaseLayout from './BaseLayout.vue'

defineProps({ activePage: { type: String, default: 'dashboard' } })
</script>

<template>
  <BaseLayout :active-page="activePage">
    <slot />
  </BaseLayout>
</template>
