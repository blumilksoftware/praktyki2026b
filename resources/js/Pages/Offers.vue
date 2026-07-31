<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import OfferMap from '@/Components/Offer/OfferMap.vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import ClientPagination from '@/Components/Common/ClientPagination.vue'
import { ROUTES } from '@/Helpers/routes'

const OFFERS_PER_PAGE = 6

const props = defineProps({
  offers: { type: Array, default: () => [] },
  hasCv: { type: Boolean, default: false },
  studyFields: { type: Array, default: () => [] },
  isGuest: { type: Boolean, default: false },
})
const { t } = useI18n()

const layoutComponent = computed(() => (props.isGuest ? BaseLayout : StudentPanelLayout))
const layoutProps = computed(() => (props.isGuest ? {} : { activePage: 'offers' }))

const displayMode = ref('list')

const query = ref('')
const city = ref('')
const workMode = ref('')
const verifiedOnly = ref(false)
const viewMode = ref('all')
const studyFieldIds = ref([])
const dateFrom = ref('')
const dateTo = ref('')

const workModes = ['remote', 'hybrid', 'onSite']

const workModeLabel = (mode) => t(`student.workModes.${mode}`)

const dateRangeError = computed(() => {
  if (dateFrom.value && dateTo.value && dateTo.value < dateFrom.value) {
    return 'student.offers.filters.dateRangeInvalid'
  }
  return null
})

const matchesDateRange = (offer) => {
  if (dateRangeError.value) return true
  if (dateFrom.value && offer.end_date && offer.end_date < dateFrom.value) return false
  if (dateTo.value && offer.start_date && offer.start_date > dateTo.value) return false
  return true
}

const filteredOffers = computed(() => props.offers.filter((offer) => {
  const queryValue = query.value.trim().toLowerCase()
  const matchesQuery = !queryValue || [offer.title, offer.company?.name, offer.city]
    .filter(Boolean)
    .some((value) => String(value).toLowerCase().includes(queryValue))

  const matchesCity = !city.value || offer.city === city.value
  const matchesWorkMode = !workMode.value || offer.work_mode === workMode.value
  const matchesVerified = !verifiedOnly.value || Boolean(offer.company?.is_verified)
  const matchesStudyFields = studyFieldIds.value.length === 0
    || studyFieldIds.value.some((id) => (offer.study_field_ids ?? []).includes(id))
  const matchesView = props.isGuest || viewMode.value === 'all'
    || (viewMode.value === 'applied' && Boolean(offer.has_applied))
    || (viewMode.value === 'notApplied' && !offer.has_applied)

  return matchesQuery && matchesCity && matchesWorkMode && matchesVerified
    && matchesStudyFields && matchesDateRange(offer) && matchesView
}))

const availableCities = computed(() => {
  return [...new Set(props.offers.map((offer) => offer.city).filter(Boolean))].sort()
})

const currentPage = ref(1)

const totalPages = computed(() => Math.max(1, Math.ceil(filteredOffers.value.length / OFFERS_PER_PAGE)))

const paginatedOffers = computed(() => {
  const start = (currentPage.value - 1) * OFFERS_PER_PAGE
  return filteredOffers.value.slice(start, start + OFFERS_PER_PAGE)
})

watch(filteredOffers, () => {
  currentPage.value = 1
})

const resetFilters = () => {
  query.value = ''
  city.value = ''
  workMode.value = ''
  verifiedOnly.value = false
  studyFieldIds.value = []
  dateFrom.value = ''
  dateTo.value = ''
  currentPage.value = 1
}
</script>

<template>
  <Head :title="isGuest ? t('offers.search.title') : t('student.nav.offers')" />

  <component :is="layoutComponent" v-bind="layoutProps">
    <div class="bg-background py-6 min-h-screen">
      <div v-if="!isGuest" class="flex flex-wrap justify-between items-center gap-3 mx-auto mb-4 max-w-7xl px-4 sm:px-6 lg:px-8">
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

      <header v-else class="mx-auto mb-4 max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="font-semibold text-text text-2xl tracking-tight">
          {{ t('offers.search.title') }}
        </h1>
        <p class="mt-1 text-additional text-sm">
          {{ t('offers.search.subtitle') }}
        </p>
      </header>

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

            <DynamicMultiSelect
              id="offers-filter-study-fields"
              v-model="studyFieldIds"
              :label="t('student.offers.filters.studyFields.label')"
              :placeholder="t('student.offers.filters.studyFields.placeholder')"
              :options="studyFields"
              :allow-custom="false"
            />

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

            <div>
              <p class="mb-2 font-medium text-text text-sm">
                {{ t('student.offers.filters.dateRange.label') }}
              </p>
              <div class="grid grid-cols-1 gap-3">
                <BaseInput
                  id="offers-filter-date-from"
                  v-model="dateFrom"
                  type="date"
                  stacked
                  :label="t('student.offers.filters.dateRange.from')"
                />
                <BaseInput
                  id="offers-filter-date-to"
                  v-model="dateTo"
                  type="date"
                  stacked
                  :label="t('student.offers.filters.dateRange.to')"
                />
              </div>
              <p
                v-if="dateRangeError"
                class="mt-2 text-error text-sm"
                role="alert"
              >
                {{ t(dateRangeError) }}
              </p>
            </div>

            <div class="bg-background px-4 py-3 border border-border rounded-2xl">
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
          <div class="flex flex-wrap justify-between items-end gap-4 mb-5 px-4 pt-5 sm:px-0 sm:pt-0">
            <div>
              <p class="font-medium text-additional text-sm">{{ t('student.offers.results.caption') }}</p>
              <h2 id="offers-list-heading" class="mt-1 font-semibold text-text text-3xl tracking-tight" aria-live="polite">
                {{ t('student.offers.results.count', { count: filteredOffers.length }) }}
              </h2>
            </div>

            <div class="flex bg-background p-1 border border-border rounded-2xl" role="group" :aria-label="t('student.offers.viewSwitcher.label')">
              <button
                type="button"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer"
                :class="displayMode === 'list' ? 'bg-white text-primary shadow-sm' : 'text-additional hover:text-text'"
                @click="displayMode = 'list'"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                {{ t('student.offers.viewSwitcher.list') }}
              </button>
              <button
                type="button"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer"
                :class="displayMode === 'map' ? 'bg-white text-primary shadow-sm' : 'text-additional hover:text-text'"
                @click="displayMode = 'map'"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                {{ t('student.offers.viewSwitcher.map') }}
              </button>
            </div>
          </div>

          <div v-if="!isGuest" role="tablist" class="mb-5 flex gap-2 overflow-x-auto border-b border-border px-4 sm:px-0" :aria-label="t('student.offers.filters.title')">
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

          <template v-if="displayMode === 'list'">
            <OffersList
              :offers="paginatedOffers"
              :has-cv="hasCv"
              :guest="isGuest"
              :empty-title="viewMode === 'applied' ? t('student.offers.emptyApplied.title') : undefined"
              :empty-description="viewMode === 'applied' ? t('student.offers.emptyApplied.description') : undefined"
            />
            <ClientPagination v-model:current-page="currentPage" :total-pages="totalPages" />
          </template>

          <template v-else>
            <OfferMap
              :offers="filteredOffers"
              :has-cv="hasCv"
              :guest="isGuest"
            />
          </template>
        </section>
      </div>
    </div>
  </component>
</template>
