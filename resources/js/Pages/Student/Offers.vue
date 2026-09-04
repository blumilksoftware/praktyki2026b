<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import BackButton from '@/Components/Common/BackButton.vue'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import { ROUTES } from '@/Helpers/routes'
import { useStudentFavorites } from '@/Composables/useStudentFavorites'

const props = defineProps({ offers: { type: Array, default: () => [] } })
const { t } = useI18n()
const { favoriteIds, toggleFavorite } = useStudentFavorites()

const query = ref('')
const city = ref('')
const workMode = ref('')
const verifiedOnly = ref(false)

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
</script>

<template>
  <Head :title="t('student.nav.offers')" />

  <AppLayout active-page="offers">
    <div class="bg-background px-4 sm:px-6 lg:px-8 py-6 min-h-screen">
      <div class="flex justify-between items-center gap-3 mx-auto mb-4 max-w-7xl">
        <BackButton :as="Link" :href="ROUTES.STUDENT_DASHBOARD" />

        <Link
          :href="ROUTES.STUDENT_FAVORITES"
          class="inline-flex items-center gap-2 bg-white hover:bg-background px-4 py-2 border border-border hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-sm transition"
        >
          {{ t('student.nav.favorites') }}
        </Link>
      </div>

      <div class="flex flex-col lg:items-start gap-6 lg:grid lg:grid-cols-[290px_minmax(0,1fr)] mx-auto max-w-7xl">
        <aside aria-labelledby="offers-filters-heading" class="lg:top-6 lg:sticky bg-white/90 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm p-5 border border-border/80 rounded-3xl">
          <div class="flex justify-between items-start gap-3">
            <div>
              <p class="font-semibold text-additional text-xs uppercase tracking-[0.24em]">{{ t('student.offers.filters.kicker') }}</p>
              <h1 id="offers-filters-heading" class="mt-2 font-semibold text-text text-2xl tracking-tight">
                {{ t('student.offers.filters.title') }}
              </h1>
            </div>
            <button
              type="button"
              class="hover:bg-background px-3 py-1.5 border border-border hover:border-primary/40 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 font-semibold text-text text-xs transition"
              :aria-label="t('student.offers.filters.resetAriaLabel')"
              @click="resetFilters"
            >
              {{ t('student.offers.filters.reset') }}
            </button>
          </div>

          <div class="space-y-4 mt-6">
            <label class="block">
              <span class="block mb-2 font-medium text-text text-sm">{{ t('student.offers.filters.search') }}</span>
              <input
                v-model="query"
                type="search"
                :placeholder="t('student.offers.filters.searchPlaceholder')"
                class="bg-background focus:bg-white px-4 py-3 border border-border focus:border-primary/50 rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 w-full text-text placeholder:text-additional text-sm transition"
                :aria-label="t('student.offers.filters.search')"
              >
            </label>

            <label class="block">
              <span class="block mb-2 font-medium text-text text-sm">{{ t('student.offers.filters.city') }}</span>
              <select
                v-model="city"
                class="bg-background focus:bg-white px-4 py-3 border border-border focus:border-primary/50 rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 w-full text-text text-sm transition"
                :aria-label="t('student.offers.filters.city')"
              >
                <option value="">{{ t('student.offers.filters.allCities') }}</option>
                <option v-for="availableCity in availableCities" :key="availableCity" :value="availableCity">
                  {{ availableCity }}
                </option>
              </select>
            </label>

            <label class="block">
              <span class="block mb-2 font-medium text-text text-sm">{{ t('student.offers.filters.workMode') }}</span>
              <select
                v-model="workMode"
                class="bg-background focus:bg-white px-4 py-3 border border-border focus:border-primary/50 rounded-2xl outline-none focus:ring-2 focus:ring-primary/20 w-full text-text text-sm transition"
                :aria-label="t('student.offers.filters.workMode')"
              >
                <option value="">{{ t('student.offers.filters.allWorkModes') }}</option>
                <option v-for="mode in workModes" :key="mode" :value="mode">
                  {{ workModeLabel(mode) }}
                </option>
              </select>
            </label>

            <div class="bg-background px-4 py-3 border border-border rounded-2xl">
              <label class="flex items-center gap-3 text-text text-sm">
                <input
                  v-model="verifiedOnly"
                  type="checkbox"
                  class="border-border rounded focus:ring-primary/30 w-4 h-4 text-primary"
                  :aria-label="t('student.offers.filters.verifiedOnly')"
                >
                <span>{{ t('student.offers.filters.verifiedOnly') }}</span>
              </label>
            </div>
          </div>
        </aside>

        <section aria-labelledby="offers-list-heading" class="bg-white/90 shadow-[0_14px_40px_rgba(11,26,48,0.08)] backdrop-blur-sm p-5 sm:p-6 border border-border/80 rounded-3xl">
          <div class="flex justify-between items-end gap-4 mb-5">
            <div>
              <p class="font-medium text-additional text-sm">{{ t('student.offers.results.caption') }}</p>
              <h2 id="offers-list-heading" class="mt-1 font-semibold text-text text-3xl tracking-tight" aria-live="polite">
                {{ t('student.offers.results.count', { count: filteredOffers.length }) }}
              </h2>
            </div>
            <p class="text-additional text-sm">{{ t('student.offers.results.helper') }}</p>
          </div>

          <OffersList
            :offers="filteredOffers"
            :favorite-ids="favoriteIds"
            @toggle-favorite="toggleFavorite"
          />
        </section>
      </div>
    </div>
  </AppLayout>
</template>
