<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseLayout from './BaseLayout.vue'
import Menu from '@/Components/Profiles/Menu.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu.js'

const props = defineProps({
  activePage: {
    type: String,
    default: 'dashboard',
  },
  showMenuRow: {
    type: Boolean,
    default: true,
  },
})

const { t } = useI18n()

const panelMenu = useCompanyPanelMenu(computed(() => props.activePage))
</script>

<template>
  <BaseLayout :active-page="activePage" :nav-items="panelMenu">
    <div
      v-if="showMenuRow"
      class="mb-6 flex w-full flex-row items-center justify-center"
    >
      <Menu :items="panelMenu" />
    </div>
    <slot />
  </BaseLayout>
</template>
