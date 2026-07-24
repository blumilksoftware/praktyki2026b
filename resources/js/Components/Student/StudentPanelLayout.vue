<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import Menu from '@/Components/Profiles/Menu.vue'
import { useStudentPanelMenu } from '@/Composables/useStudentPanelMenu'

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
const panelMenu = useStudentPanelMenu(computed(() => props.activePage))
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background">
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-medium focus:text-primary"
    >
      {{ t('student.layout.skipToContent') }}
    </a>

    <BaseNavbar show-hamburger :menu-items="panelMenu" />

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 py-8 sm:px-6 lg:px-8">
      <div
        v-if="showMenuRow"
        class="mb-6 flex w-full flex-row items-center justify-end"
      >
        <Menu :items="panelMenu" />
      </div>

      <main id="main-content">
        <slot />
      </main>
    </div>
  </div>
</template>
