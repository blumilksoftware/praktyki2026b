<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconBriefcase, IconHeart, IconUser } from '@tabler/icons-vue'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import { ROUTES } from '@/Helpers/routes'
import { useStudentFavorites } from '@/composables/useStudentFavorites'

const props = defineProps({
  offers: { type: Array, default: () => [] },
})

const { t } = useI18n()
const { favoriteIds, toggleFavorite } = useStudentFavorites()

const offersCount = computed(() => props.offers.length)
const favoriteCount = computed(() => favoriteIds.value.length)

const navItems = computed(() => [
  { key: 'dashboard', label: t('student.nav.dashboard'), href: ROUTES.STUDENT_DASHBOARD, icon: IconHome },
  { key: 'profile', label: t('student.nav.profile'), href: ROUTES.STUDENT_PROFILE, icon: IconUser },
  { key: 'offers', label: t('student.nav.offers'), href: ROUTES.STUDENT_OFFERS, icon: IconBriefcase },
  { key: 'favorites', label: t('student.nav.favorites'), href: ROUTES.STUDENT_FAVORITES, icon: IconHeart },
])
</script>

<template>
  <Head :title="t('student.dashboard.title')" />
  <StudentPanelLayout active-page="dashboard" :nav-items="navItems">
    <div class="bg-slate-50 px-4 sm:px-6 lg:px-8 py-6 min-h-screen">
      <section class="bg-white shadow-[0_14px_40px_rgba(11,26,48,0.08)] mx-auto p-6 sm:p-8 border border-slate-200 rounded-3xl max-w-7xl">
        <div class="lg:items-center gap-6 grid lg:grid-cols-[minmax(0,1.35fr)_minmax(0,0.95fr)]">
          <div class="max-w-2xl">
            <p class="font-semibold text-slate-500 text-xs uppercase tracking-[0.24em]">{{ t('student.dashboard.title') }}</p>
            <h1 class="mt-2 font-semibold text-slate-900 text-4xl sm:text-5xl tracking-tight">
              {{ t('student.dashboard.heading') }}
            </h1>
            <p class="mt-4 text-slate-500 text-base sm:text-lg leading-7">
              {{ t('student.dashboard.description') }}
            </p>

            <div class="flex flex-wrap gap-3 mt-6">
              <Link
                :href="ROUTES.STUDENT_OFFERS"
                class="inline-flex justify-center items-center bg-blue-600 hover:bg-blue-700 px-5 py-3 border border-blue-600 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/40 font-semibold text-white text-sm transition"
              >
                {{ t('student.dashboard.primaryAction') }}
              </Link>
              <Link
                :href="ROUTES.STUDENT_FAVORITES"
                class="inline-flex justify-center items-center bg-white hover:bg-slate-50 px-5 py-3 border border-slate-200 hover:border-blue-600/40 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 font-semibold text-slate-900 text-sm transition"
              >
                {{ t('student.dashboard.secondaryAction') }}
              </Link>
            </div>
          </div>

          <div class="gap-3 grid sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
            <Link
              :href="ROUTES.STUDENT_OFFERS"
              class="bg-slate-50/80 hover:bg-white hover:shadow-[0_10px_28px_rgba(11,26,48,0.08)] p-4 border border-slate-200 hover:border-blue-600/30 rounded-2xl transition hover:-translate-y-0.5"
            >
              <p class="font-semibold text-slate-500 text-xs uppercase tracking-[0.18em]">{{ t('student.nav.offers') }}</p>
              <p class="mt-2 font-semibold text-slate-900 text-3xl">{{ offersCount }}</p>
              <p class="mt-1 text-slate-500 text-sm">{{ t('student.dashboard.cards.offersDescription') }}</p>
            </Link>

            <Link
              :href="ROUTES.STUDENT_FAVORITES"
              class="bg-slate-50/80 hover:bg-white hover:shadow-[0_10px_28px_rgba(11,26,48,0.08)] p-4 border border-slate-200 hover:border-blue-600/30 rounded-2xl transition hover:-translate-y-0.5"
            >
              <p class="font-semibold text-slate-500 text-xs uppercase tracking-[0.18em]">{{ t('student.nav.favorites') }}</p>
              <p class="mt-2 font-semibold text-slate-900 text-3xl">{{ favoriteCount }}</p>
              <p class="mt-1 text-slate-500 text-sm">{{ t('student.dashboard.cards.favoritesDescription') }}</p>
            </Link>
          </div>
        </div>
      </section>

      <section class="bg-white shadow-[0_14px_40px_rgba(11,26,48,0.08)] mx-auto mt-6 p-6 sm:p-8 border border-slate-200 rounded-3xl max-w-7xl">
        <div class="flex justify-between items-end gap-4">
          <div>
            <p class="font-medium text-slate-500 text-sm">{{ t('student.dashboard.previewTitle') }}</p>
            <h2 class="mt-1 font-semibold text-slate-900 text-2xl tracking-tight">{{ t('student.dashboard.previewDescription') }}</h2>
          </div>
          <Link
            :href="ROUTES.STUDENT_OFFERS"
            class="inline-flex items-center gap-2 font-semibold text-blue-600 hover:text-blue-700 text-sm transition"
          >
            {{ t('student.dashboard.primaryAction') }}
            <span aria-hidden="true">→</span>
          </Link>
        </div>

        <div class="mt-6">
          <OffersList
            :offers="offers"
            :favorite-ids="favoriteIds"
            @toggle-favorite="toggleFavorite"
          />
        </div>
      </section>
      <Head :title="t('student.layout.title')" />
      <div class="flex flex-col gap-6" />
    </div>
  </StudentPanelLayout>
</template>
