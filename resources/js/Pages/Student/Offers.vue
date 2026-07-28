<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import { ROUTES } from '@/Helpers/routes'
import { useStudentFavorites } from '@/Composables/useStudentFavorites.ts'

const props = defineProps({
  offers: { type: Array, default: () => [] },
  hasCv: { type: Boolean, default: true },
})
const { t } = useI18n()
const { favoriteIds, toggleFavorite } = useStudentFavorites()

const query = ref('')
const city = ref('')
const workMode = ref('')
const verifiedOnly = ref(false)
const viewMode = ref('all')

const workModes = ['remote', 'hybrid', 'onSite']

const workModeLabel = (mode) => t(`student.workModes.${mode}`)

const filteredOffers = computed(() => props.offers.filter((offer) => {
  const queryValue = query.value.trim().toLowerCase()
  const matchesQuery = !queryValue || [offer.title, offer.company?.name, offer.city]
    .filter(Boolean)
    .some((value) => String(value).toLowerCase().includes(queryValue))

  const matchesCity = !city.value || offer.city === city.value
  const matchesWorkMode = !workMode.value || offer.work_mode === workMode.value
  const matchesVerified = !verifiedOnly.value || Boolean(offer.company?.is_verified)
  const matchesView = viewMode.value === 'all'
    || (viewMode.value === 'applied' && Boolean(offer.has_applied))
    || (viewMode.value === 'notApplied' && !offer.has_applied)

  return matchesQuery && matchesCity && matchesWorkMode && matchesVerified && matchesView
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
</script>

<template>
  <Head :title="t('student.nav.offers')" />

  <StudentPanelLayout active-page="offers">
    <div class="bg-background py-6 min-h-screen">
      <div class="flex flex-wrap justify-between items-center gap-3 mx-auto mb-4 max-w-7xl px-4 sm:px-6 lg:px-8">
        <Link
          :href="ROUTES.STUDENT_DASHBOARD"
          class="inline-flex items-center gap-2 bg-white hover:bg-background px-4 py-2 border border-border hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-sm transition"
        >
          <span aria-hidden="true">←</span>
          {{ t('student.favorites.backToDashboard') }}
        </Link>

        <Link
          :href="ROUTES.STUDENT_FAVORITES"
          class="inline-flex items-center gap-2 bg-white hover:bg-background px-4 py-2 border border-border hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-sm transition"
        >
          {{ t('student.nav.favorites') }}
        </Link>
      </div>

      <div class="flex flex-col lg:items-start gap-6 lg:grid lg:grid-cols-[290px_minmax(0,1fr)] mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <aside aria-labelledby="offers-filters-heading" class="lg:top-6 lg:sticky bg-white/90 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm p-5 border border-border/80 rounded-3xl">
          <div class="flex justify-between items-start gap-3">
            <div>
              <p class="font-semibold text-additional text-xs uppercase tracking-[0.24em]">{{ t('student.offers.filters.kicker') }}</p>
              <h2 id="offers-filters-heading" class="mt-2 font-semibold text-text text-2xl tracking-tight">
                {{ t('student.offers.filters.title') }}
              </h2>
            </div>
            <button
              type="button"
              class="hover:bg-background px-3 py-1.5 border border-border cursor-pointer hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-xs transition"
              :aria-label="t('student.offers.filters.reset')"
              @click="resetFilters"
            >
              {{ t('student.offers.filters.reset') }}
            </button>
          </div>

          <div class="space-y-4 mt-6">
            <label class="block" for="offers-filter-search">
              <span class="block mb-2 font-medium text-text text-sm">{{ t('student.offers.filters.search') }}</span>
              <input
                id="offers-filter-search"
                v-model="query"
                type="search"
                :placeholder="t('student.offers.filters.searchPlaceholder')"
                class="bg-background focus:bg-white px-4 py-3 border border-border focus:border-primary/50 rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 w-full text-text placeholder:text-additional text-sm transition"
              >
            </label>

            <label class="block" for="offers-filter-city">
              <span class="block mb-2 font-medium text-text text-sm">{{ t('student.offers.filters.city') }}</span>
              <select
                id="offers-filter-city"
                v-model="city"
                class="bg-background focus:bg-white px-4 py-3 border border-border focus:border-primary/50 rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 w-full text-text text-sm transition"
              >
                <option value="">{{ t('student.offers.filters.allCities') }}</option>
                <option v-for="availableCity in availableCities" :key="availableCity" :value="availableCity">
                  {{ availableCity }}
                </option>
              </select>
            </label>

            <label class="block" for="offers-filter-work-mode">
              <span class="block mb-2 font-medium text-text text-sm">{{ t('student.offers.filters.workMode') }}</span>
              <select
                id="offers-filter-work-mode"
                v-model="workMode"
                class="bg-background focus:bg-white px-4 py-3 border border-border focus:border-primary/50 rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 w-full text-text text-sm transition"
              >
                <option value="">{{ t('student.offers.filters.allWorkModes') }}</option>
                <option v-for="mode in workModes" :key="mode" :value="mode">
                  {{ workModeLabel(mode) }}
                </option>
              </select>
            </label>

            <div class="bg-background px-4 py-3 border  border-border rounded-2xl">
              <label class="flex items-center gap-3 text-text text-sm" for="offers-filter-verified-only">
                <input
                  id="offers-filter-verified-only"
                  v-model="verifiedOnly"
                  type="checkbox"
                  class="border-border cursor-pointer rounded focus:ring-primary/30 w-4 h-4 text-primary"
                >
                <span>{{ t('student.offers.filters.verifiedOnly') }}</span>
              </label>
            </div>
          </div>
        </aside>

        <section aria-labelledby="offers-list-heading" class="-mx-4 sm:mx-0 sm:rounded-3xl sm:border sm:border-border/80 sm:bg-white/90 sm:p-6 sm:shadow-[0_14px_40px_rgba(11,26,48,0.08)] sm:backdrop-blur-sm">
          <div class="flex justify-between items-end gap-4 mb-5 px-4 pt-5 sm:px-0 sm:pt-0">
            <div>
              <p class="font-medium text-additional text-sm">{{ t('student.offers.results.caption') }}</p>
              <h2 id="offers-list-heading" class="mt-1 font-semibold text-text text-3xl tracking-tight" aria-live="polite">
                {{ t('student.offers.results.count', { count: filteredOffers.length }) }}
              </h2>
            </div>
          </div>

          <div role="tablist" class="mb-5 flex gap-2 overflow-x-auto border-b border-border px-4 sm:px-0" :aria-label="t('student.offers.filters.title')">
            <button
              type="button"
              role="tab"
              :aria-selected="viewMode === 'all'"
              class="shrink-0 cursor-pointer whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
              :class="viewMode === 'all' ? 'border-primary text-primary' : 'border-transparent text-additional hover:text-text'"
              @click="viewMode = 'all'"
            >
              {{ t('student.offers.tabs.all') }}
            </button>
            <button
              type="button"
              role="tab"
              :aria-selected="viewMode === 'applied'"
              class="shrink-0 cursor-pointer whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
              :class="viewMode === 'applied' ? 'border-primary text-primary' : 'border-transparent text-additional hover:text-text'"
              @click="viewMode = 'applied'"
            >
              {{ t('student.offers.tabs.applied') }}
            </button>
            <button
              type="button"
              role="tab"
              :aria-selected="viewMode === 'notApplied'"
              class="shrink-0 cursor-pointer whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
              :class="viewMode === 'notApplied' ? 'border-primary text-primary' : 'border-transparent text-additional hover:text-text'"
              @click="viewMode = 'notApplied'"
            >
              {{ t('student.offers.tabs.notApplied') }}
            </button>
          </div>

          <OffersList
            :offers="filteredOffers"
            :favorite-ids="favoriteIds"
            :has-cv="hasCv"
            :empty-title="viewMode === 'applied' ? t('student.offers.emptyApplied.title') : undefined"
            :empty-description="viewMode === 'applied' ? t('student.offers.emptyApplied.description') : undefined"
            @toggle-favorite="toggleFavorite"
          />
        </section>
      </div>
    </div>
  </StudentPanelLayout>
</template>
