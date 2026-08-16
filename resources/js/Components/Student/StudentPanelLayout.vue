<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'

const props = defineProps({
  activePage: {
    type: String,
    default: '',
  },
})

const { t } = useI18n()
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background">
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-medium focus:text-primary"
    >
      {{ t('student.layout.skipToContent') }}
    </a>

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 py-8 sm:px-6 lg:px-8">
      <main id="main-content">
        <slot />
      </main>
    </div>
  </div>
</template>
