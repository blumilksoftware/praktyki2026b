<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppLayout from "@/Components/Layouts/AppLayout.vue";

const props = defineProps({
  activePage: {
    type: String,
    default: '',
  },
})

const { t } = useI18n()
const panelMenu = useStudentPanelMenu(computed(() => props.activePage))
</script>

<template>
  <AppLayout :active-page="activePage" :nav-items="panelMenu" :navigation-buttons="panelMenu">
    <slot />
  </AppLayout>
</template>
