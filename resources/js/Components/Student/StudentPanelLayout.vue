<script setup>
import { computed } from 'vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import { useStudentPanelMenu } from '@/Composables/useStudentPanelMenu'

const props = defineProps({
  activePage: {
    type: String,
    default: '',
  },
})

const panelMenu = useStudentPanelMenu(computed(() => props.activePage))
</script>

<template>
  <BaseLayout :active-page="activePage" :nav-items="panelMenu" :navigation-buttons="panelMenu">
    <slot />
  </BaseLayout>
</template>
