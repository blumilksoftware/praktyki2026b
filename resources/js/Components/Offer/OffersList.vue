<script setup>
import { useI18n } from 'vue-i18n'
import OfferCard from '@/Components/Offer/OfferCard.vue'
const props = defineProps({ offers: { type: Array, default: () => [] } })

const { t } = useI18n()
</script>

<template>
  <div class="max-h-[calc(100vh-10rem)] overflow-y-auto pr-1">
    <div v-if="offers.length === 0" class="flex min-h-[28rem] items-center justify-center rounded-3xl border border-dashed border-border bg-background px-8 text-center text-additional" role="status" aria-live="polite">
      <div>
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-border bg-white shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-additional" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <div class="mt-4 text-base font-medium text-text">{{ t('student.offers.empty.title') }}</div>
        <p class="mt-1 text-sm text-additional">{{ t('student.offers.empty.description') }}</p>
      </div>
    </div>

    <div v-else class="space-y-4 px-4 py-6">
      <OfferCard v-for="offer in offers" :key="offer.id" :offer="offer" />
    </div>
  </div>
</template>

<style scoped>
/* rely on Tailwind for layout */
</style>
