<script setup>
import { computed } from 'vue'
import BaseLayout from './BaseLayout.vue'
import { useUniversityPanelMenu } from '@/Composables/useUniversityPanelMenu.js'

const props = defineProps({
  activePage: {
    type: String,
    default: '',
  },
})

const panelMenu = useUniversityPanelMenu(computed(() => props.activePage))
</script>

<template>
  <BaseLayout
    :active-page="activePage"
    :nav-items="panelMenu"
    :navigation-buttons="panelMenu"
  >
    <slot />
  </BaseLayout>
</template>
