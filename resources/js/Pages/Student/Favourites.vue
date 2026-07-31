<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import { ROUTES, studentOfferFavourite } from '@/Helpers/routes'

const props = defineProps({ favourites: { type: Array, default: () => [] } })

const { t } = useI18n()

const favourites = ref(props.favourites)
const removingId = ref(null)

function removeFavourite(offerId) {
  removingId.value = offerId

  router.delete(studentOfferFavourite(offerId), {
    preserveScroll: true,
    onSuccess: () => {
      favourites.value = favourites.value.filter((offer) => offer.id !== offerId)
    },
    onFinish: () => {
      removingId.value = null
    },
  })
}
</script>

<template>
  <Head :title="t('student.nav.favorites')" />

  <StudentPanelLayout active-page="favorites">
    <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
      <section class="mx-auto max-w-7xl rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{{ t('student.nav.favorites') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
          {{ t('student.favorites.heading') }}
        </h1>
        <p class="mt-3 text-base leading-7 text-slate-500">
          {{ t('student.favorites.description') }}
        </p>

        <div v-if="favourites.length > 0" class="mt-6 flex flex-col gap-4">
          <div
            v-for="offer in favourites"
            :key="offer.id"
            class="border border-slate-200 rounded-2xl p-5 bg-white shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3"
          >
            <div>
              <h3 class="font-semibold text-lg text-slate-900">{{ offer.title }}</h3>
              <p class="text-slate-500 text-sm mt-1">{{ offer.company_name }} &middot; {{ offer.city }}</p>
            </div>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-xl border cursor-pointer border-border bg-white px-4 py-2 text-sm font-semibold text-text transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="removingId === offer.id"
              @click="removeFavourite(offer.id)"
            >
              {{ t('student.offers.card.removeFromFavorites') }}
            </button>
          </div>
        </div>

        <div v-else class="mt-6 rounded-3xl border border-dashed border-slate-200/80 bg-white/75 p-8 text-center text-slate-500 backdrop-blur-sm">
          <p class="text-lg font-semibold text-slate-900">{{ t('student.favorites.empty.title') }}</p>
          <p class="mt-2 text-sm text-slate-500">{{ t('student.favorites.empty.description') }}</p>
          <Link
            :href="ROUTES.OFFERS"
            class="mt-5 inline-flex items-center justify-center rounded-xl border border-primary bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            {{ t('student.favorites.backToOffers') }}
          </Link>
        </div>
      </section>
    </div>
  </StudentPanelLayout>
</template>
