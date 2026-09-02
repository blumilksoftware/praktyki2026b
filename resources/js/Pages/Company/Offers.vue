<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconPlus, IconUsers, IconClipboardText, IconDotsVertical } from '@tabler/icons-vue'
import { ROUTES } from '@/Helpers/routes'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import CompanyOfferDeleteModal from '@/Components/Company/CompanyOfferDeleteModal.vue'
import CompanyOfferUnpublishModal from '@/Components/Company/CompanyOfferUnpublishModal.vue'
import OffersCards from '@/Components/Company/Offers/OffersCards.vue'
import BaseToast from '@/Components/Base/BaseToast.vue'

const props = defineProps({
  offers: { type: [Array, Object], default: () => [] },
  isCompanyVerified: { type: Boolean, default: false },
  search: { type: String, default: '' },
  status: { type: String, default: '' },
  statusCounts: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const isOfferDeleteModalOpen = ref(false)
const isOfferUnpublishModalOpen = ref(false)

const offersList = computed(() => (Array.isArray(props.offers) ? props.offers : props.offers?.data ?? []))

function goToApplications(offer) {
  closeMenu()
  router.visit(`${ROUTES.COMPANY_APPLICATIONS}?offer=${offer.id}`)
}

function editOffer(offer) {
  closeMenu()
  router.visit(ROUTES.COMPANY_OFFERS_EDIT(offer.id))
}

function toggleStatusOffer(offer) {
  if (offer.status === 'published') {
    openUnpublishConfirmationModal(offer.id)
    closeMenu()
    return
  }

  if (offer.status === 'draft' && !props.isCompanyVerified) {
    return
  }

  publish(offer.id)
  closeMenu()
}

function deleteOffer(offer) {
  closeMenu()
  openDeleteConfirmationModal(offer.id)
}

const processingOfferId = ref(null)
const deleteOfferId = ref(null)
const unpublishOfferId = ref(null)

const query = ref(props.search)
const statusFilter = ref(props.status || 'all')
const statusTabs = ['all', 'draft', 'published', 'closed', 'expired']

const offerCountByStatus = computed(() => ({
  all: Object.values(props.statusCounts).reduce((sum, count) => sum + count, 0),
  draft: props.statusCounts.draft ?? 0,
  published: props.statusCounts.published ?? 0,
  closed: props.statusCounts.closed ?? 0,
  expired: props.statusCounts.expired ?? 0,
}))

let searchDebounceTimer = null

function applyFilters() {
  router.get(
    ROUTES.COMPANY_OFFERS_INDEX,
    {
      search: query.value || undefined,
      status: statusFilter.value === 'all' ? undefined : statusFilter.value,
    },
    { preserveState: true, preserveScroll: true, replace: true },
  )
}

watch(query, () => {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(applyFilters, 350)
})

function selectStatusTab(tab) {
  statusFilter.value = tab
  applyFilters()
}

const paginationLinks = computed(() => (
  !Array.isArray(props.offers) && props.offers?.last_page > 1 ? props.offers.links : []
))

function paginationLabel(label) {
  return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»')
}

function goToPage(url) {
  if (!url) return
  router.get(url, {}, { preserveState: true, preserveScroll: true, replace: true })
}

onUnmounted(() => {
  clearTimeout(searchDebounceTimer)
})

const deleteOfferTitle = computed(
  () => offersList.value.find((offer) => offer.id === deleteOfferId.value)?.title ?? '',
)

const unpublishOfferTitle = computed(
  () => offersList.value.find((offer) => offer.id === unpublishOfferId.value)?.title ?? '',
)

function publish(offerId) {
  processingOfferId.value = offerId

  router.patch(ROUTES.COMPANY_OFFERS_PUBLISH(offerId), {}, {
    preserveScroll: true,
    onFinish: () => {
      processingOfferId.value = null
    },
  })
}

function openDeleteConfirmationModal(offerId) {
  deleteOfferId.value = offerId
  isOfferDeleteModalOpen.value = true
}

function openUnpublishConfirmationModal(offerId) {
  unpublishOfferId.value = offerId
  isOfferUnpublishModalOpen.value = true
}

const openMenuOfferId = ref(null)

function toggleMenu(offerId) {
  openMenuOfferId.value = openMenuOfferId.value === offerId ? null : offerId
}

function closeMenu() {
  openMenuOfferId.value = null
}

function handleClickOutside(event) {
  if (openMenuOfferId.value === null) {
    return
  }

  const openMenu = event.target.closest('[data-offer-menu]')
  if (!openMenu) {
    closeMenu()
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape' && openMenuOfferId.value !== null) {
    closeMenu()
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <Head :title="t('company.offers.index.title')" />
  <AppLayout active-page="offers">
    <BaseToast ref="toastRef" />
    <div class="flex flex-col gap-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="font-semibold text-text text-2xl">
          {{ t('company.offers.index.title') }}
        </h1>

        <Link
          :href="ROUTES.COMPANY_OFFERS_CREATE"
          class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 sm:w-auto"
        >
          <IconPlus class="h-4 w-4" aria-hidden="true" />
          {{ t('company.offers.index.createAction') }}
        </Link>
      </div>

      <div v-if="offerCountByStatus.all > 0" class="flex flex-col gap-4">
        <div class="relative w-full sm:max-w-sm">
          <input
            v-model="query"
            type="search"
            :placeholder="t('company.offers.index.searchPlaceholder')"
            :aria-label="t('company.offers.index.searchPlaceholder')"
            class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-text outline-none transition placeholder:text-additional focus:border-primary/50 focus:ring-2 focus:ring-primary/20"
          >
        </div>

        <div role="tablist" class="flex gap-2 overflow-x-auto border-b border-border" :aria-label="t('company.offers.index.title')">
          <button
            v-for="tab in statusTabs"
            :key="tab"
            type="button"
            role="tab"
            :aria-selected="statusFilter === tab"
            class="shrink-0 cursor-pointer whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
            :class="statusFilter === tab ? 'border-primary text-primary' : 'border-transparent text-additional hover:text-text'"
            @click="selectStatusTab(tab)"
          >
            {{ tab === 'all' ? t('company.offers.index.tabs.all') : t(`company.offers.index.status.${tab}`) }}
            <span class="ml-1 text-xs text-additional">({{ offerCountByStatus[tab] }})</span>
          </button>
        </div>
      </div>

      <div
        v-if="offersList.length === 0"
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-white px-8 py-16 text-center"
        role="status"
      >
        <p class="font-medium text-text text-base">
          {{ offerCountByStatus.all === 0 ? t('company.offers.index.empty.title') : t('company.offers.index.noResults.title') }}
        </p>
        <p class="mt-1 text-additional text-sm">
          {{ offerCountByStatus.all === 0 ? t('company.offers.index.empty.description') : t('company.offers.index.noResults.description') }}
        </p>
      </div>

      <OffersCards
        v-else
        :offers="offersList"
        :open-menu-id="openMenuOfferId"
        :hidden-on-md="false"
        :is-company-verified="isCompanyVerified"
        :show-verification-hint="true"
        verification-hint-key="company.offers.index.verificationRequiredHint"
        :labels="{
          menu: 'company.offers.index.actionsMenu',
          applications: 'company.offers.index.applicationsAction',
          edit: 'company.offers.index.editAction',
          activate: 'company.offers.index.publishAction',
          deactivate: 'company.offers.index.unpublishAction',
          delete: 'company.offers.index.deleteAction',
        }"
        :status-key-prefix="'company.offers.index.status'"
        @toggle-menu="toggleMenu"
        @applications="goToApplications"
        @edit="editOffer"
        @toggle-status="toggleStatusOffer"
        @delete="deleteOffer"
      />

      <div v-if="paginationLinks.length > 0" class="flex flex-wrap items-center justify-center gap-1">
        <button
          v-for="(link, index) in paginationLinks"
          :key="index"
          type="button"
          :disabled="!link.url"
          class="min-w-[2rem] rounded-md px-2.5 py-1 text-xs font-medium transition"
          :class="link.active
            ? 'bg-primary text-white'
            : link.url
              ? 'cursor-pointer text-text hover:bg-background'
              : 'cursor-not-allowed text-additional/50'"
          @click="goToPage(link.url)"
        >
          {{ paginationLabel(link.label) }}
        </button>
      </div>
    </div>

    <CompanyOfferDeleteModal
      :open="isOfferDeleteModalOpen"
      :offer-id="deleteOfferId"
      :offer-title="deleteOfferTitle"
      @close="isOfferDeleteModalOpen = false"
    />

    <CompanyOfferUnpublishModal
      :open="isOfferUnpublishModalOpen"
      :offer-id="unpublishOfferId"
      :offer-title="unpublishOfferTitle"
      @close="isOfferUnpublishModalOpen = false"
    />
  </AppLayout>
</template>
