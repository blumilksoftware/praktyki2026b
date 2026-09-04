<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  offers: { type: Array, default: () => [] },
  offersCount: { type: Number, default: 0 },
  hasCv: { type: Boolean, default: true },
  favoritesCount: { type: Number, default: 0 },
})

const { t } = useI18n()

const offersPreview = computed(() => ({ data: props.offers }))
</script>

<template>
  <Head :title="t('student.dashboard.title')" />
  <AppLayout active-page="dashboard">
    <div class="bg-slate-50 px-4 sm:px-6 lg:px-8 py-6 min-h-screen">
      <section class="bg-white shadow-[0_14px_40px_rgba(11,26,48,0.08)] mx-auto p-6 sm:p-8 border border-slate-200 rounded-3xl max-w-7xl">
        <div class="lg:items-center gap-6 grid lg:grid-cols-[minmax(0,1.35fr)_minmax(0,0.95fr)]">
          <div class="max-w-2xl">
            <p class="font-medium text-additional text-sm">{{ t('student.dashboard.title') }}</p>
            <h1 class="mt-2 font-semibold text-text text-2xl tracking-tight">
              {{ t('student.dashboard.heading') }}
            </h1>
            <p class="mt-4 text-additional text-base sm:text-lg leading-7">
              {{ t('student.dashboard.description') }}
            </p>

            <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:flex-wrap">
              <Link
                :href="ROUTES.OFFERS"
                class="inline-flex w-full sm:w-fit justify-center items-center bg-primary hover:bg-primary/90 px-5 py-3 border border-primary rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 font-semibold text-white text-sm transition"
              >
                {{ t('student.dashboard.primaryAction') }}
              </Link>
              <Link
                :href="ROUTES.STUDENT_FAVORITES"
                class="inline-flex w-full sm:w-fit justify-center items-center bg-white hover:bg-slate-50 px-5 py-3 border border-slate-200 hover:border-primary/40 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-sm transition"
              >
                {{ t('student.dashboard.secondaryAction') }}
              </Link>
            </div>
          </div>

          <div class="gap-3 grid sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            <Link
              :href="ROUTES.OFFERS"
              class="bg-slate-50/80 hover:bg-white hover:shadow-[0_10px_28px_rgba(11,26,48,0.08)] p-4 border border-slate-200 hover:border-primary/30 rounded-2xl transition hover:-translate-y-0.5"
            >
              <h2 class="font-semibold text-additional text-xs uppercase tracking-wide">{{ t('student.nav.offers') }}</h2>
              <p class="mt-2 font-semibold text-text text-3xl">{{ offersCount }}</p>
              <p class="mt-1 text-additional text-sm">{{ t('student.dashboard.cards.offersDescription') }}</p>
            </Link>

            <Link
              :href="ROUTES.STUDENT_FAVORITES"
              class="bg-slate-50/80 hover:bg-white hover:shadow-[0_10px_28px_rgba(11,26,48,0.08)] p-4 border border-slate-200 hover:border-primary/30 rounded-2xl transition hover:-translate-y-0.5"
            >
              <h2 class="font-semibold text-additional text-xs uppercase tracking-wide">{{ t('student.nav.favorites') }}</h2>
              <p class="mt-2 font-semibold text-text text-3xl">{{ favoritesCount }}</p>
              <p class="mt-1 text-additional text-sm">{{ t('student.dashboard.cards.favoritesDescription') }}</p>
            </Link>
          </div>
        </div>
      </section>

      <section class="bg-white shadow-[0_14px_40px_rgba(11,26,48,0.08)] mx-auto mt-6 p-6 sm:p-8 border border-slate-200 rounded-3xl max-w-7xl">
        <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="font-medium text-additional text-sm">{{ t('student.dashboard.previewTitle') }}</p>
            <h2 class="mt-1 font-semibold text-text text-lg tracking-tight">{{ t('student.dashboard.previewDescription') }}</h2>
          </div>
          <Link
            :href="ROUTES.OFFERS"
            class="inline-flex items-center gap-2 font-semibold text-primary hover:text-primary/80 text-sm transition"
          >
            {{ t('student.dashboard.primaryAction') }}
            <span aria-hidden="true">→</span>
          </Link>
        </div>

        <div class="mt-6">
          <OffersList
            :offers="offersPreview"
            :has-cv="hasCv"
            can-apply
          />
        </div>
      </section>
    </div>
  </AppLayout>
</template>
