<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import { ROUTES } from '@/Helpers/routes'
import { useStudentFavorites } from '@/composables/useStudentFavorites'

const props = defineProps({ offers: { type: Array, default: () => [] } })

const { t } = useI18n()
const { favoriteIds, favoriteCount, toggleFavorite, clearFavorites } = useStudentFavorites()

const favoriteOffers = computed(() => props.offers)
</script>

<template>
  <Head :title="t('common.nav.favorites')" />

  <StudentPanelLayout active-page="favorites">
    <div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
      <div class="mx-auto mb-4 flex max-w-7xl items-center justify-between gap-3">
        <Link
          :href="ROUTES.STUDENT_DASHBOARD"
          class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:border-blue-600/40 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30"
        >
          <span aria-hidden="true">←</span>
          {{ t('common.actions.backToDashboard') }}
        </Link>

        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-900">
          {{ t('common.nav.favorites') }}
          <span class="rounded-full bg-white px-2 py-0.5 text-xs text-slate-500">{{ favoriteCount }}</span>
        </div>
      </div>

      <section class="mx-auto max-w-7xl rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
          <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{{ t('common.nav.favorites') }}</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
              {{ t('common.titles.yourFavorites') }}
            </h1>
            <p class="mt-3 text-base leading-7 text-slate-500">
              {{ t('student.favorites.description') }}
            </p>
          </div>

          <button
            type="button"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:border-blue-600/40 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="favoriteCount === 0"
            @click="clearFavorites"
          >
            {{ t('common.filters.reset') }}
          </button>
        </div>

        <div class="mt-6">
          <OffersList
            v-if="favoriteOffers.length > 0"
            :offers="favoriteOffers"
            :favorite-ids="favoriteIds"
            :empty-title="t('student.favorites.empty.title')"
            :empty-description="t('student.favorites.empty.description')"
            @toggle-favorite="toggleFavorite"
          />

          <div v-else class="rounded-3xl border border-dashed border-slate-200/80 bg-white/75 p-8 text-center text-slate-500 backdrop-blur-sm">
            <p class="text-lg font-semibold text-slate-900">{{ t('student.favorites.empty.title') }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ t('student.favorites.empty.description') }}</p>
            <Link
              :href="ROUTES.STUDENT_OFFERS"
              class="mt-5 inline-flex items-center justify-center rounded-xl border border-blue-600 bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/40"
            >
              {{ t('common.actions.backToOffers') }}
            </Link>
          </div>
        </div>
      </section>
    </div>
  </StudentPanelLayout>
</template>
