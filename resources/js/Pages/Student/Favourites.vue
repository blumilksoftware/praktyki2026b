<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import { ROUTES, studentOfferFavourite, offerShow } from '@/Helpers/routes'

const props = defineProps({ favourites: { type: Array, default: () => [] } })

const { t } = useI18n()

const favourites = ref(props.favourites)
const removingId = ref(null)
const statusMessage = ref('')

function isExpired(offer){
  return offer.status === 'expired' || offer.deleted_at !== null
}

function removeFavourite(offerId) {
  removingId.value = offerId

  router.delete(studentOfferFavourite(offerId), {
    preserveScroll: true,
    onSuccess: () => {
      const removed = favourites.value.find((offer) => offer.id === offerId)

      favourites.value = favourites.value.filter((offer) => offer.id !== offerId)
      statusMessage.value = t('student.favorites.removedAnnouncement', { title: removed?.title ?? '' })
    },
    onFinish: () => {
      removingId.value = null
    },
  })
}
</script>

<template>
  <Head :title="t('student.nav.favorites')" />
  <AppLayout active-page="favorites">
    <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
      <section class="mx-auto max-w-7xl rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm sm:p-8">
        <p class="text-sm font-medium text-additional">{{ t('student.nav.favorites') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-text sm:text-4xl">
          {{ t('student.favorites.heading') }}
        </h1>
        <p class="mt-3 text-base leading-7 text-additional">
          {{ t('student.favorites.description') }}
        </p>

        <ul
          v-if="favourites.length > 0"
          class="mt-6 flex flex-col gap-4"
          :aria-label="t('student.favorites.listAriaLabel')"
        >
          <li
            v-for="offer in favourites"
            :key="offer.id"
            class="relative border border-slate-200 rounded-2xl p-5 bg-white shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 transition"
            :class="isExpired(offer) ? 'border-slate-200 bg-slate-50' : 'border-slate-200 bg-white hover:border-primary/40 hover:-translate-y-0.5 hover:shadow-[0_16px_45px_rgba(11,26,48,0.14)]'"
          >
            <Link
              v-if="!offer.deleted_at"
              :href="offerShow(offer.id)"
              class="absolute inset-0 rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
              :aria-label="t('student.offers.card.openOfferAria', { title: offer.title })"
            />
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <h2 class="font-semibold text-lg text-text">{{ offer.title }}</h2>
                <span
                  v-if="isExpired(offer)"
                  class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-semibold text-additional"
                >
                  {{ t('student.favorites.status.expired') }}
                </span>
              </div>
              <p class="text-additional text-sm mt-1">
                {{ offer.company_name }} &middot;
                {{ offer.city }} &middot;
                {{ t('student.offers.card.remainingSpots') }}: {{ offer.remaining_spots }}
              </p>
            </div>
            <button
              type="button"
              class="relative z-10 inline-flex w-full sm:w-auto items-center justify-center rounded-lg border cursor-pointer border-border bg-white px-4 py-3 text-sm font-semibold text-text transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="removingId === offer.id"
              :aria-busy="removingId === offer.id"
              @click="removeFavourite(offer.id)"
            >
              {{ t('student.offers.card.removeFromFavorites') }}
            </button>
          </li>
        </ul>

        <div v-else class="mt-6 rounded-3xl border border-dashed border-slate-200/80 bg-white/75 p-8 text-center text-additional backdrop-blur-sm">
          <p class="text-lg font-semibold text-text">{{ t('student.favorites.empty.title') }}</p>
          <p class="mt-2 text-sm text-additional">{{ t('student.favorites.empty.description') }}</p>
          <Link
            :href="ROUTES.OFFERS"
            class="mt-5 inline-flex items-center justify-center rounded-xl border border-primary bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            {{ t('student.favorites.backToOffers') }}
          </Link>
        </div>

        <p role="status" aria-live="polite" class="sr-only">{{ statusMessage }}</p>
      </section>
    </div>
    </AppLayout>
</template>
