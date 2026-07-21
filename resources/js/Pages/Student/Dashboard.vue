<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHeart, IconHome, IconBriefcase, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import { ROUTES } from '@/Helpers/routes'
import { useStudentFavorites } from '@/composables/useStudentFavorites'

const props = defineProps({ offers: { type: Array, default: () => [] } })

const { t } = useI18n()
const { favoriteIds, favoriteCount, toggleFavorite } = useStudentFavorites()
const offersCount = computed(() => props.offers.length)

const navItems = computed(() => [
  { key: 'dashboard', label: t('student.nav.dashboard'), href: ROUTES.STUDENT_DASHBOARD, icon: IconHome },
  { key: 'profile', label: t('student.nav.profile'), href: ROUTES.STUDENT_PROFILE, icon: IconUser },
  { key: 'offers', label: t('student.nav.offers'), href: ROUTES.STUDENT_OFFERS, icon: IconBriefcase },
  { key: 'favorites', label: t('student.nav.favorites'), href: ROUTES.STUDENT_FAVORITES, icon: IconHeart },
])
</script>

<template>
  <Head :title="t('student.dashboard.title')" />

  <BaseLayout active-page="dashboard" :nav-items="navItems" :logo-href="ROUTES.STUDENT_DASHBOARD" background-class="bg-white" :show-background="false" layout-scope="student" :show-user-section="false" :show-compact-menu="true" :compact-menu-items="navItems">
    <div class="min-h-screen bg-background px-4 py-6 sm:px-6 lg:px-8">
      <section class="mx-auto max-w-7xl rounded-3xl border border-border bg-white p-6 shadow-[0_14px_40px_rgba(11,26,48,0.08)] sm:p-8">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,0.95fr)] lg:items-center">
          <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-additional">{{ t('student.dashboard.title') }}</p>
            <h1 class="mt-2 text-4xl font-semibold tracking-tight text-text sm:text-5xl">
              {{ t('student.dashboard.heading') }}
            </h1>
            <p class="mt-4 text-base leading-7 text-additional sm:text-lg">
              {{ t('student.dashboard.description') }}
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
              <Link
                :href="ROUTES.STUDENT_OFFERS"
                class="inline-flex items-center justify-center rounded-xl border border-primary bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
              >
                {{ t('student.dashboard.primaryAction') }}
              </Link>
              <Link
                :href="ROUTES.STUDENT_FAVORITES"
                class="inline-flex items-center justify-center rounded-xl border border-border bg-white px-5 py-3 text-sm font-semibold text-text transition hover:border-primary/40 hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
              >
                {{ t('student.dashboard.secondaryAction') }}
              </Link>
            </div>
          </div>

          <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
            <Link
              :href="ROUTES.STUDENT_OFFERS"
              class="rounded-2xl border border-border bg-background/80 p-4 transition hover:-translate-y-0.5 hover:border-primary/30 hover:bg-white hover:shadow-[0_10px_28px_rgba(11,26,48,0.08)]"
            >
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-additional">{{ t('student.nav.offers') }}</p>
              <p class="mt-2 text-3xl font-semibold text-text">{{ offersCount }}</p>
              <p class="mt-1 text-sm text-additional">{{ t('student.dashboard.cards.offersDescription') }}</p>
            </Link>

            <Link
              :href="ROUTES.STUDENT_FAVORITES"
              class="rounded-2xl border border-border bg-background/80 p-4 transition hover:-translate-y-0.5 hover:border-primary/30 hover:bg-white hover:shadow-[0_10px_28px_rgba(11,26,48,0.08)]"
            >
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-additional">{{ t('student.nav.favorites') }}</p>
              <p class="mt-2 text-3xl font-semibold text-text">{{ favoriteCount }}</p>
              <p class="mt-1 text-sm text-additional">{{ t('student.dashboard.cards.favoritesDescription') }}</p>
            </Link>

            <Link
              :href="ROUTES.STUDENT_PROFILE"
              class="rounded-2xl border border-border bg-background/80 p-4 transition hover:-translate-y-0.5 hover:border-primary/30 hover:bg-white hover:shadow-[0_10px_28px_rgba(11,26,48,0.08)]"
            >
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-additional">{{ t('student.nav.profile') }}</p>
              <p class="mt-2 text-3xl font-semibold text-text">→</p>
              <p class="mt-1 text-sm text-additional">{{ t('student.profile.title') }}</p>
            </Link>
          </div>
        </div>
      </section>

      <section class="mx-auto mt-6 max-w-7xl rounded-3xl border border-border bg-white p-6 shadow-[0_14px_40px_rgba(11,26,48,0.08)] sm:p-8">
        <div class="flex items-end justify-between gap-4">
          <div>
            <p class="text-sm font-medium text-additional">{{ t('student.dashboard.previewTitle') }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-text">{{ t('student.dashboard.previewDescription') }}</h2>
          </div>
          <Link
            :href="ROUTES.STUDENT_OFFERS"
            class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-secondary"
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
    </div>
  </BaseLayout>
</template>