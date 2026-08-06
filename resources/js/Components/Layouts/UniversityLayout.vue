<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseLayout from './BaseLayout.vue'
import Menu from '@/Components/Profiles/Menu.vue'
import { useUniversityPanelMenu } from '@/Composables/useUniversityPanelMenu.js'

const props = defineProps({
  activePage: {
    type: String,
    default: '',
  },
  showMenuRow: {
    type: Boolean,
    default: true,
  },
})
const { t } = useI18n()

const panelMenu = useUniversityPanelMenu(computed(() => props.activePage))
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <BaseLayout :active-page="activePage" :nav-items="panelMenu">
      <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div
          v-if="showMenuRow"
          class="mb-6 flex w-full flex-row items-center justify-center"
        >
          <Menu :items="panelMenu" />
        </div>
        <slot />
      </main>
    </BaseLayout>
  </div>
</template>
