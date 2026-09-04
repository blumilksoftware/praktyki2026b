<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  title: { type: String, default: undefined },
})

const { t } = useI18n()
</script>

<template>
  <div class="w-full h-full bg-background flex flex-col px-4 py-4 md:py-6">
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-medium focus:text-primary"
    >
      {{ t('auth.layout.skipToContent') }}
    </a>

    <main id="main-content" class="m-auto w-full max-w-6xl bg-white border border-border shadow-lg rounded-xl px-4 py-8 md:px-16 md:py-16">
      <h1 v-if="title" class="text-center text-4xl md:text-5xl font-normal text-text mb-8">
        {{ title }}
      </h1>
      <slot />
    </main>
  </div>
</template>
