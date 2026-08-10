<script setup>
import { computed, onMounted, ref, watch, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import debounce from 'lodash/debounce'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import OfferMap from '@/Components/Offer/Map/OfferMap.vue'
import OffersFilters from '@/Components/Offer/OffersFilters.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  offers: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  hasCv: { type: Boolean, default: false },
  studyFields: { type: Array, default: () => [] },
  isGuest: { type: Boolean, default: false },
  canApply: { type: Boolean, default: false },
  mapboxToken: { type: String, default: '' },
})

const { t } = useI18n()

const layoutComponent = computed(() => (props.canApply ? StudentPanelLayout : BaseLayout))
const layoutProps = computed(() => (props.canApply ? { activePage: 'offers' } : {}))

const displayMode = ref('list')
const targetOfferId = ref(null)

const studyFieldValueToLabel = (value) => props.studyFields.find((f) => f.value === value)?.label
const studyFieldLabelToValue = (label) => props.studyFields.find((f) => f.label === label)?.value

const filters = reactive({
  search: props.filters.search || '',
  cities: props.filters.cities || [],
  workModes: props.filters.work_modes || [],
  dateFrom: props.filters.date_from || '',
  dateTo: props.filters.date_to || '',
  studyFieldLabels: (props.filters.study_fields || [])
    .map(studyFieldValueToLabel)
    .filter((label) => label !== undefined),
})

const fetchOffers = () => {
  router.get(
    window.location.pathname,
    {
      search: filters.search || undefined,
      cities: filters.cities.length ? filters.cities : undefined,
      work_modes: filters.workModes.length ? filters.workModes : undefined,
      date_from: filters.dateFrom || undefined,
      date_to: filters.dateTo || undefined,
      study_fields: filters.studyFieldLabels.length
        ? filters.studyFieldLabels.map(studyFieldLabelToValue).filter((value) => value !== undefined)
        : undefined,
      view: displayMode.value === 'map' ? 'map' : undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  )
}

watch(
  filters,
  debounce(fetchOffers, 300),
  { deep: true },
)

watch(displayMode, fetchOffers)

const resetFilters = () => {
  filters.search = ''
  filters.cities = []
  filters.workModes = []
  filters.dateFrom = ''
  filters.dateTo = ''
  filters.studyFieldLabels = []
}

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search)

  if (urlParams.get('view') === 'map') {
    displayMode.value = 'map'
  }

  if (urlParams.get('offerId')) {
    targetOfferId.value = Number(urlParams.get('offerId')) || urlParams.get('offerId')
  }
})
</script>

<template>
  <Head :title="canApply ? t('common.nav.offers') : t('offers.search.title')" />

  <component :is="layoutComponent" v-bind="layoutProps">
    <div class="bg-background py-6 min-h-screen">
      <div v-if="canApply" class="flex flex-wrap justify-between items-center gap-3 mx-auto mb-4 max-w-7xl px-4 sm:px-6 lg:px-8">
        <Link
          :href="ROUTES.STUDENT_DASHBOARD"
          class="inline-flex items-center gap-2 bg-white hover:bg-background px-4 py-2 border border-border hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-sm transition"
        >
          <span aria-hidden="true">←</span>
          {{ t('common.actions.backToDashboard') }}
        </Link>

        <Link
          :href="ROUTES.STUDENT_FAVORITES"
          class="inline-flex items-center gap-2 bg-white hover:bg-background px-4 py-2 border border-border hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-sm transition"
        >
          {{ t('common.nav.favorites') }}
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

      <div v-if="canApply && !hasCv" class="mx-auto mb-4 max-w-7xl px-4 sm:px-6 lg:px-8">
        <p class="flex flex-wrap items-center gap-2 rounded-xl border border-border bg-white px-4 py-3 text-sm font-medium text-text">
          {{ t('common.actions.apply.noCvMessage') }}
          <Link
            :href="ROUTES.STUDENT_PROFILE_EDIT"
            class="font-bold text-link underline underline-offset-4 transition-colors hover:text-link/80"
          >
            {{ t('common.actions.apply.uploadCvPrompt') }}
          </Link>
        </p>
      </div>

      <div class="flex flex-col lg:items-start gap-6 lg:grid lg:grid-cols-[290px_minmax(0,1fr)] mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <OffersFilters
          v-model="filters"
          :study-fields="studyFields"
          @reset="resetFilters"
        />

        <section aria-labelledby="offers-list-heading" class="-mx-4 sm:mx-0 sm:rounded-3xl sm:border sm:border-border/80 sm:bg-white/90 sm:p-6 sm:shadow-[0_14px_40px_rgba(11,26,48,0.08)] sm:backdrop-blur-sm">
          <div class="flex justify-between items-end gap-4 mb-5 px-4 pt-5 sm:px-0 sm:pt-0">
            <div>
              <p class="font-medium text-additional text-sm">{{ t('student.offers.results.caption') }}</p>
              <h2 id="offers-list-heading" class="mt-1 font-semibold text-text text-3xl tracking-tight" aria-live="polite">
                {{ t('student.offers.results.count', { count: offers.total || offers.data.length }) }}
              </h2>
            </div>

            <div class="flex bg-background p-1 border border-border rounded-2xl" role="group">
              <button
                type="button"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer"
                :class="displayMode === 'list' ? 'bg-white text-primary shadow-sm' : 'text-additional hover:text-text'"
                @click="displayMode = 'list'"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                {{ t('common.words.listView') }}
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
                {{ t('common.words.mapView') }}
              </button>
            </div>
          </div>

          <template v-if="displayMode === 'list'">
            <OffersList
              :offers="offers"
              :has-cv="hasCv"
              :guest="isGuest"
              :can-apply="canApply"
            />
          </template>

          <template v-else>
            <OfferMap
              :offers="offers.data"
              :has-cv="hasCv"
              :guest="isGuest"
              :can-apply="canApply"
              :initial-offer-id="targetOfferId"
              :mapbox-token="mapboxToken"
            />
          </template>
        </section>
      </div>
    </div>
  </component>
</template>
