<script setup>
import { computed } from 'vue'
import BaseLayout from './BaseLayout.vue'
import { useAdminPanelMenu } from '@/Composables/useAdminPanelMenu'

const props = defineProps({ activePage: { type: String, default: 'dashboard' } })

const navItems = useAdminPanelMenu(computed(() => props.activePage))
</script>

<template>
  <BaseLayout :active-page="activePage" :nav-items="navItems">
    <slot />
  </BaseLayout>
</template>
