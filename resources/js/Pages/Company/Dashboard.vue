<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconPlus } from '@tabler/icons-vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'
import OffersToolbar from '@/Components/Company/Offers/OffersToolbar.vue'
import OffersTable from '@/Components/Company/Offers/OffersTable.vue'
import OffersCards from '@/Components/Company/Offers/OffersCards.vue'
import OffersPagination from '@/Components/Company/Offers/OffersPagination.vue'
import { useOffersFilters } from '@/Composables/useOffersFilters'
import { useOfferActions } from '@/Composables/useOfferActions'
import AppLayout from "@/Components/Layouts/AppLayout.vue";
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const props = defineProps({
  offers: {
    type: Object,
    required: true,
  },
  sort: {
    type: String,
    default: 'created_at',
  },
  direction: {
    type: String,
    default: 'desc',
  },
  search: {
    type: String,
    default: '',
  },
  status: {
    type: String,
    default: '',
  },
})

const {
  searchQuery,
  statusFilter,
  applyQuery,
  onStatusFilterChange,
  sortBy,
  sortIcon,
} = useOffersFilters({
  search: props.search,
  status: props.status,
  sort: props.sort,
  direction: props.direction,
})

const {
  openMenuId,
  toggleMenu,
  editOffer,
  toggleStatusOffer,
  deleteOffer,
} = useOfferActions({
  t,
  onMutated: () => applyQuery(),
})

function goToApplications(event, offerId) {
  event.preventDefault()
  router.visit(`${ROUTES.COMPANY_APPLICATIONS}?offer=${offerId}`)
}

</script>

<template>
  <Head :title="t('company.layout.title')" />
  <AppLayout active-page="dashboard">
  <BaseLayout
    active-page="dashboard"
    :nav-items="companyMenu"
    :navigation-buttons="companyMenu"
    navigation-variant="default"
  >
    <div class="flex flex-col gap-6">
      <OnboardingBanner />

      <div class="flex flex-col items-start gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-semibold text-text text-sm">
            {{ t('company.dashboard.createOfferCard.title') }}
          </p>
          <p class="mt-0.5 text-slate-500 text-xs">
            {{ t('company.dashboard.createOfferCard.description') }}
          </p>
        </div>

        <Link
          :href="ROUTES.COMPANY_OFFERS_CREATE"
          class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        >
          <IconPlus class="h-4 w-4" aria-hidden="true" />
          {{ t('company.dashboard.createOfferCard.action') }}
        </Link>
      </div>
    </div>
    <div class="p-5 sm:p-6 space-y-6">
      <header class="mb-6">
        <h1 class="font-semibold text-text text-xl sm:text-2xl">
          {{ t('company.dashboard.offers.subtitle') }}
        </h1>
      </header>

      <div class="rounded-xl border border-border bg-white shadow-sm overflow-visible mt-10">
        <div class="px-4 py-3 border-b border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <h2 class="font-medium text-text">
            {{ t('company.dashboard.offers.title') }}
          </h2>

          <OffersToolbar
            v-model:search="searchQuery"
            v-model:status="statusFilter"
            @status-change="onStatusFilterChange"
          />
        </div>

        <div
          v-if="offers.data.length === 0"
          class="px-4 py-6 text-center text-additional text-sm"
        >
          {{ t('company.dashboard.offers.noOffers') }}
        </div>

        <template v-else>
          <OffersTable
            :offers="offers.data"
            :sort-icon="sortIcon"
            :open-menu-id="openMenuId"
            @sort="sortBy"
            @toggle-menu="toggleMenu"
            @edit="editOffer"
            @toggle-status="toggleStatusOffer"
            @delete="deleteOffer"
            @go-to-applications="goToApplications"
          />

          <OffersCards
            :offers="offers.data"
            :open-menu-id="openMenuId"
            @toggle-menu="toggleMenu"
            @edit="editOffer"
            @toggle-status="toggleStatusOffer"
            @delete="deleteOffer"
            @go-to-applications="goToApplications"
          />
        </template>

        <OffersPagination :offers="offers" />
      </div>
    </div>
  </BaseLayout>
  </AppLayout>
</template>
