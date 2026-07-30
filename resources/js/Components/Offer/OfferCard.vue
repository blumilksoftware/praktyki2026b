<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconHeart, IconHeartFilled } from '@tabler/icons-vue'

const props = defineProps({
  offer: { type: Object, required: true },
  isFavorite: { type: Boolean, default: false },
})

defineEmits(['toggle-favorite'])

const { t } = useI18n()

const companyInitial = computed(() => props.offer.company?.name?.charAt(0) || 'O')

const workModeLabel = computed(() => t(`student.workModes.${props.offer.work_mode}`))
const isExpired = computed(() => props.offer.status === 'expired')

</script>

<template>
    <article
      class="group overflow-hidden rounded-3xl border border-border bg-white shadow-[0_8px_30px_rgba(11,26,48,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_16px_45px_rgba(11,26,48,0.14)]"
      :class="{ 'opacity-60 grayscale-[0.3]': isExpired }"
    >
    <div class="grid gap-0 lg:grid-cols-[96px_minmax(0,1fr)]">
      <div class="flex items-center justify-center bg-white p-5 lg:p-4">
        <img
          v-if="offer.company.logo_path"
          :src="offer.company.logo_path"
          :alt="t('student.offers.card.logoAlt', { company: offer.company.name })"
          class="h-16 w-16 rounded-2xl object-cover ring-4 ring-white"
        >
        <div v-else class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-lg font-semibold text-additional ring-4 ring-white">
          {{ companyInitial }}
        </div>
      </div>

      <div class="p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <p class="text-sm font-semibold uppercase tracking-[0.18em] text-additional">
                {{ offer.company.name }}
              </p>
              <span
                v-if="offer.company.is_verified"
                class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-success"
                :aria-label="t('student.offers.card.verifiedAriaLabel')"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M20 6L9 17l-5-5" />
                </svg>
                <span aria-hidden="true">{{ t('student.offers.card.verified') }}</span>
              </span>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-2">
              <h3 class="text-2xl font-semibold tracking-tight text-text">
                {{ offer.title }}
              </h3>
              <span
                v-if="isExpired"
                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500"
              >
                {{ t('student.offers.card.expiredBadge') }}
              </span>
            </div>
          </div>

          <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-additional sm:justify-end">
            <span class="rounded-full border border-border bg-white px-3 py-1.5">{{ offer.city }}</span>
            <span class="rounded-full border border-border bg-white px-3 py-1.5">{{ workModeLabel }}</span>
          </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
          <div class="rounded-2xl border border-border bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-additional">{{ t('student.offers.card.dateRange') }}</p>
            <p class="mt-1 text-sm font-medium text-text">{{ offer.start_date }} - {{ offer.end_date }}</p>
          </div>

          <div class="rounded-2xl border border-border bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-additional">{{ t('student.offers.card.remainingSpots') }}</p>
            <p class="mt-1 text-sm font-medium text-text">{{ offer.remaining_spots }}</p>
          </div>

          <div class="rounded-2xl border border-border bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-additional">{{ t('student.offers.card.location') }}</p>
            <p class="mt-1 text-sm font-medium text-text">{{ offer.city }}</p>
          </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-xl border border-border bg-white px-4 py-2.5 text-sm font-semibold text-text transition hover:border-primary/40 hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
            disabled
            :aria-disabled="true"
            :title="t('student.offers.card.mapComingSoon')"
          >
            {{ t('student.offers.card.showOnMap') }}
          </button>

          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
            :class="isFavorite
              ? 'border-primary/40 bg-primary/10 text-primary hover:bg-primary/15'
              : 'border-border bg-white text-text hover:border-primary/40 hover:bg-background'"
            :aria-pressed="isFavorite"
            :aria-label="isFavorite
              ? t('student.offers.card.removeFromFavorites')
              : t('student.offers.card.addToFavorites')"
            @click="$emit('toggle-favorite', offer.id)"
          >
            <IconHeartFilled v-if="isFavorite" class="h-4 w-4" aria-hidden="true" />
            <IconHeart v-else class="h-4 w-4" aria-hidden="true" />
            {{ isFavorite ? t('student.offers.card.removeFromFavorites') : t('student.offers.card.addToFavorites') }}
          </button>


          <Link
            v-if="!isExpired"
            :href="`/student/offers/${offer.id}/apply`"
            method="post"
            as="button"
            type="button"
            class="inline-flex items-center justify-center rounded-xl border border-primary bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            {{ t('student.offers.card.applyNow') }}
          </Link>
          <span
            v-else
            class="inline-flex items-center justify-center rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-semibold text-additional"
          >
            {{ t('student.offers.card.expiredNotice') }}
          </span>
        </div>
      </div>
    </div>
  </article>
</template>

<style scoped>
/* keep styling via Tailwind classes */
</style>
