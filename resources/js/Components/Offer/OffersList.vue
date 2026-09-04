<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import OfferCard from '@/Components/Offer/OfferCard.vue'

const props = defineProps({
  offers: { type: Object, required: true },
  hasCv: { type: Boolean, default: true },
  guest: { type: Boolean, default: false },
  canApply: { type: Boolean, default: false },
  emptyTitle: { type: String, default: undefined },
  emptyDescription: { type: String, default: undefined },
})
const { t } = useI18n()

const items = computed(() => props.offers?.data ?? [])
const links = computed(() => props.offers?.links ?? [])
const lastPage = computed(() => props.offers?.last_page ?? 1)

function paginationLabel(label) {
  const textarea = document.createElement('textarea')
  textarea.innerHTML = label
  return textarea.value
}

function goToPage(url) {
  if (!url) return
  router.get(url, {}, {
    preserveState: true,
    preserveScroll: false,
    replace: true,
    onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
  })
}
</script>

<template>
  <div>
    <div v-if="items.length === 0" class="mx-4 flex min-h-[28rem] items-center justify-center rounded-3xl border border-dashed border-border bg-white px-8 text-center text-additional sm:mx-0" role="status" aria-live="polite">
      <div>
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-border bg-white shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-additional" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <div class="mt-4 text-base font-medium text-text">{{ emptyTitle ?? t('student.offers.empty.title') }}</div>
        <p class="mt-1 text-sm text-additional">{{ emptyDescription ?? t('student.offers.empty.description') }}</p>
      </div>
    </div>
    <div class="space-y-1 py-2 sm:space-y-2 sm:px-2 sm:py-3">
      <OfferCard
        v-for="offer in items"
        :key="offer.id"
        :offer="offer"
        :has-cv="hasCv"
        :guest="guest"
        :can-apply="canApply"
      />
    </div>
    <div
      v-if="lastPage > 1"
      class="mt-6 flex items-center justify-center gap-1 flex-wrap px-4 sm:px-0"
      role="navigation"
      :aria-label="t('student.offers.pagination.label')"
    >
      <button
        v-for="(link, index) in links"
        :key="index"
        type="button"
        class="px-2.5 py-1 text-xs rounded-md min-w-[2rem]"
        :class="link.active
          ? 'bg-primary text-white'
          : link.url
            ? 'text-text hover:bg-gray-100 cursor-pointer'
            : 'text-additional cursor-not-allowed'"
        :disabled="!link.url"
        :aria-current="link.active ? 'page' : undefined"
        @click="goToPage(link.url)"
      >
        {{ paginationLabel(link.label) }}
      </button>
    </div>
  </div>
</template>
