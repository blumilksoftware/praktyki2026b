<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconBriefcase, IconHeart, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import { ROUTES } from '@/Helpers/routes'
import { useStudentFavorites } from '@/composables/useStudentFavorites'

const props = defineProps({ offers: { type: Array, default: () => [] } })
const { t } = useI18n()
const { favoriteIds, toggleFavorite } = useStudentFavorites()

const query = ref('')
const city = ref('')
const workMode = ref('')
const verifiedOnly = ref(false)

const workModes = ['remote', 'hybrid', 'office']

const workModeLabel = (mode) => t(`student.offers.workModes.${mode}`)

const filteredOffers = computed(() => props.offers.filter((offer) => {
  const queryValue = query.value.trim().toLowerCase()
  const matchesQuery = !queryValue || [offer.title, offer.company?.name, offer.city]
    .filter(Boolean)
    .some((value) => String(value).toLowerCase().includes(queryValue))

  const matchesCity = !city.value || offer.city === city.value
  const matchesWorkMode = !workMode.value || offer.work_mode === workMode.value
  const matchesVerified = !verifiedOnly.value || Boolean(offer.company?.is_verified)

  return matchesQuery && matchesCity && matchesWorkMode && matchesVerified
}))

const availableCities = computed(() => {
  return [...new Set(props.offers.map((offer) => offer.city).filter(Boolean))].sort()
})

const resetFilters = () => {
  query.value = ''
  city.value = ''
  workMode.value = ''
  verifiedOnly.value = false
}

const navItems = computed(() => [
  { key: 'dashboard', label: t('student.nav.dashboard'), href: ROUTES.STUDENT_DASHBOARD, icon: IconHome },
  { key: 'profile', label: t('student.nav.profile'), href: ROUTES.STUDENT_PROFILE, icon: IconUser },
  { key: 'offers', label: t('student.nav.offers'), href: ROUTES.STUDENT_OFFERS, icon: IconBriefcase },
  { key: 'favorites', label: t('student.nav.favorites'), href: ROUTES.STUDENT_FAVORITES, icon: IconHeart },
])
</script>

<template>
  <Head :title="t('student.offers.title')" />

  <BaseLayout active-page="offers" :nav-items="navItems" :logo-href="ROUTES.STUDENT_OFFERS" background-class="bg-white" :show-background="false" layout-scope="student" :show-user-section="false" :show-compact-menu="true" :compact-menu-items="navItems">
    <div class="min-h-screen bg-background px-4 py-6 sm:px-6 lg:px-8">
      <div class="mx-auto mb-4 flex max-w-7xl items-center justify-between gap-3">
        <Link
          :href="ROUTES.STUDENT_DASHBOARD"
          class="inline-flex items-center gap-2 rounded-full border border-border bg-white px-4 py-2 text-sm font-semibold text-text transition hover:border-primary/40 hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
        >
          <span aria-hidden="true">←</span>
          {{ t('student.favorites.backToDashboard') }}
        </Link>

        <Link
          :href="ROUTES.STUDENT_FAVORITES"
          class="inline-flex items-center gap-2 rounded-full border border-border bg-white px-4 py-2 text-sm font-semibold text-text transition hover:border-primary/40 hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
        >
          {{ t('student.nav.favorites') }}
        </Link>
      </div>

      <div class="mx-auto flex max-w-7xl flex-col gap-6 lg:grid lg:grid-cols-[290px_minmax(0,1fr)] lg:items-start">
        <aside aria-labelledby="offers-filters-heading" class="rounded-3xl border border-border/80 bg-white/90 p-5 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm lg:sticky lg:top-6">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-additional">{{ t('student.offers.filters.kicker') }}</p>
              <h1 id="offers-filters-heading" class="mt-2 text-2xl font-semibold tracking-tight text-text">
                {{ t('student.offers.filters.title') }}
              </h1>
            </div>
            <button
              type="button"
              class="rounded-full border border-border px-3 py-1.5 text-xs font-semibold text-text transition hover:border-primary/40 hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
              :aria-label="t('student.offers.filters.resetAriaLabel')"
              @click="resetFilters"
            >
              {{ t('student.offers.filters.reset') }}
            </button>
          </div>

          <div class="mt-6 space-y-4">
            <label class="block">
              <span class="mb-2 block text-sm font-medium text-text">{{ t('student.offers.filters.search') }}</span>
              <input
                v-model="query"
                type="search"
                :placeholder="t('student.offers.filters.searchPlaceholder')"
                class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-text outline-none transition placeholder:text-additional focus:border-primary/50 focus:bg-white focus:ring-2 focus:ring-primary/20"
                :aria-label="t('student.offers.filters.search')"
              >
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-text">{{ t('student.offers.filters.city') }}</span>
              <select
                v-model="city"
                class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-text outline-none transition focus:border-primary/50 focus:bg-white focus:ring-2 focus:ring-primary/20"
                :aria-label="t('student.offers.filters.city')"
              >
                <option value="">{{ t('student.offers.filters.allCities') }}</option>
                <option v-for="availableCity in availableCities" :key="availableCity" :value="availableCity">
                  {{ availableCity }}
                </option>
              </select>
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-text">{{ t('student.offers.filters.workMode') }}</span>
              <select
                v-model="workMode"
                class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm text-text outline-none transition focus:border-primary/50 focus:bg-white focus:ring-2 focus:ring-primary/20"
                :aria-label="t('student.offers.filters.workMode')"
              >
                <option value="">{{ t('student.offers.filters.allWorkModes') }}</option>
                <option v-for="mode in workModes" :key="mode" :value="mode">
                  {{ workModeLabel(mode) }}
                </option>
              </select>
            </label>

            <div class="rounded-2xl border border-border bg-background px-4 py-3">
              <label class="flex items-center gap-3 text-sm text-text">
                <input
                  v-model="verifiedOnly"
                  type="checkbox"
                  class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                  :aria-label="t('student.offers.filters.verifiedOnly')"
                >
                <span>{{ t('student.offers.filters.verifiedOnly') }}</span>
              </label>
            </div>
          </div>
        </aside>

        <section aria-labelledby="offers-list-heading" class="rounded-3xl border border-border/80 bg-white/90 p-5 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm sm:p-6">
          <div class="mb-5 flex items-end justify-between gap-4">
            <div>
              <p class="text-sm font-medium text-additional">{{ t('student.offers.results.caption') }}</p>
              <h2 id="offers-list-heading" class="mt-1 text-3xl font-semibold tracking-tight text-text" aria-live="polite">
                {{ t('student.offers.results.count', { count: filteredOffers.length }) }}
              </h2>
            </div>
            <p class="text-sm text-additional">{{ t('student.offers.results.helper') }}</p>
          </div>

          <OffersList
            :offers="filteredOffers"
            :favorite-ids="favoriteIds"
            @toggle-favorite="toggleFavorite"
          />
        </section>
      </div>
    </div>
  </BaseLayout>
</template>
