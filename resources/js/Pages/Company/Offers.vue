<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconPlus, IconUsers, IconClipboardText, IconDotsVertical } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'
import { ROUTES } from '@/Helpers/routes'
import CompanyOfferDeleteModal from '@/Components/Company/CompanyOfferDeleteModal.vue'
import CompanyOfferUnpublishModal from '@/Components/Company/CompanyOfferUnpublishModal.vue'

const props = defineProps({
  offers: { type: Array, default: () => [] },
  isCompanyVerified: { type: Boolean, default: false },
})

const { t } = useI18n()
const companyMenu = useCompanyPanelMenu('offers')
const isOfferDeleteModalOpen = ref(false)
const isOfferUnpublishModalOpen = ref(false)

const statusBadgeClass = computed(() => (status) => ({
  draft: 'bg-slate-100 text-slate-600',
  published: 'bg-green-100 text-green-700',
  closed: 'bg-slate-200 text-slate-500',
  expired: 'bg-slate-200 text-slate-500',
}[status] ?? 'bg-slate-100 text-slate-600'))

const processingOfferId = ref(null)
const deleteOfferId = ref(null)
const unpublishOfferId = ref(null)

const query = ref('')
const statusFilter = ref('all')
const statusTabs = ['all', 'draft', 'published', 'closed', 'expired']

const offerCountByStatus = computed(() => {
  const counts = { all: props.offers.length, draft: 0, published: 0, closed: 0, expired: 0 }

  props.offers.forEach((offer) => {
    if (counts[offer.status] !== undefined) {
      counts[offer.status] += 1
    }
  })

  return counts
})

const filteredOffers = computed(() => {
  const queryValue = query.value.trim().toLowerCase()

  return props.offers.filter((offer) => {
    const matchesQuery = !queryValue || offer.title.toLowerCase().includes(queryValue)
    const matchesStatus = statusFilter.value === 'all' || offer.status === statusFilter.value

    return matchesQuery && matchesStatus
  })
})

const deleteOfferTitle = computed(
  () => props.offers.find((offer) => offer.id === deleteOfferId.value)?.title ?? '',
)

const unpublishOfferTitle = computed(
  () => props.offers.find((offer) => offer.id === unpublishOfferId.value)?.title ?? '',
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

  const openMenu = event.target.closest(`[data-offer-menu="${openMenuOfferId.value}"]`)
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

  <BaseLayout
    active-page="offers"
    :nav-items="companyMenu"
    :navigation-buttons="companyMenu"
    navigation-variant="default"
  >
    <div class="flex flex-col gap-6">
      <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="font-semibold text-text text-2xl">
          {{ t('company.offers.index.title') }}
        </h1>

        <Link
          :href="ROUTES.COMPANY_OFFERS_CREATE"
          class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        >
          <IconPlus class="h-4 w-4" aria-hidden="true" />
          {{ t('company.offers.index.createAction') }}
        </Link>
      </div>

      <div v-if="offers.length > 0" class="flex flex-col gap-4">
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
            @click="statusFilter = tab"
          >
            {{ tab === 'all' ? t('company.offers.index.tabs.all') : t(`company.offers.index.status.${tab}`) }}
            <span class="ml-1 text-xs text-additional">({{ offerCountByStatus[tab] }})</span>
          </button>
        </div>
      </div>

      <div
        v-if="filteredOffers.length === 0"
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-white px-8 py-16 text-center"
        role="status"
      >
        <p class="font-medium text-text text-base">
          {{ offers.length === 0 ? t('company.offers.index.empty.title') : t('company.offers.index.noResults.title') }}
        </p>
        <p class="mt-1 text-additional text-sm">
          {{ offers.length === 0 ? t('company.offers.index.empty.description') : t('company.offers.index.noResults.description') }}
        </p>
      </div>

      <ul v-else class="flex flex-col gap-3">
        <li
          v-for="offer in filteredOffers"
          :key="offer.id"
          class="flex flex-col gap-4 rounded-2xl border border-border bg-white px-6 py-5 shadow-sm sm:flex-row sm:items-center sm:justify-between"
        >
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold tracking-wide"
                :class="statusBadgeClass(offer.status)"
              >
                {{ t(`company.offers.index.status.${offer.status}`) }}
              </span>
              <h2 class="truncate font-semibold text-text text-base">
                {{ offer.title }}
              </h2>
            </div>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-additional text-sm">
              <span class="inline-flex items-center gap-1">
                <IconUsers class="h-4 w-4" aria-hidden="true" />
                {{ t('company.offers.index.spotsLabel', { count: offer.spots }) }}
              </span>
              <span class="inline-flex items-center gap-1">
                <IconClipboardText class="h-4 w-4" aria-hidden="true" />
                {{ t('company.offers.index.applicationsCount', { count: offer.applications_count }) }}
              </span>
            </div>
            <p v-if="offer.status === 'draft' && !isCompanyVerified" class="mt-1 text-amber-600 text-xs">
              {{ t('company.offers.index.verificationRequiredHint') }}
            </p>
          </div>

          <div class="relative shrink-0 self-end sm:self-auto" :data-offer-menu="offer.id">
            <button
              type="button"
              class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-additional transition hover:bg-background hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
              :aria-label="t('company.offers.index.actionsMenu')"
              aria-haspopup="true"
              :aria-expanded="openMenuOfferId === offer.id"
              @click="toggleMenu(offer.id)"
            >
              <IconDotsVertical class="h-5 w-5" stroke="2" aria-hidden="true" />
            </button>

            <div
              v-if="openMenuOfferId === offer.id"
              class="absolute right-0 z-10 mt-2 w-48 overflow-hidden rounded-lg border border-border bg-white py-1 shadow-md"
              role="menu"
            >
              <Link
                :href="ROUTES.COMPANY_OFFERS_EDIT(offer.id)"
                role="menuitem"
                class="block px-4 py-2 text-left text-sm font-medium text-text transition hover:bg-background"
                @click="closeMenu"
              >
                {{ t('company.offers.index.editAction') }}
              </Link>

              <button
                v-if="offer.status === 'draft' && isCompanyVerified"
                type="button"
                role="menuitem"
                :disabled="processingOfferId === offer.id"
                class="block w-full cursor-pointer px-4 py-2 text-left text-sm font-medium text-text transition hover:bg-background disabled:cursor-not-allowed disabled:opacity-60"
                @click="publish(offer.id); closeMenu()"
              >
                {{ processingOfferId === offer.id ? t('company.offers.index.publishing') : t('company.offers.index.publishAction') }}
              </button>

              <button
                v-if="offer.status === 'published'"
                type="button"
                role="menuitem"
                class="block w-full cursor-pointer px-4 py-2 text-left text-sm font-medium text-text transition hover:bg-background"
                @click="openUnpublishConfirmationModal(offer.id); closeMenu()"
              >
                {{ t('company.offers.index.unpublishAction') }}
              </button>

              <button
                type="button"
                role="menuitem"
                :disabled="processingOfferId === offer.id"
                class="block w-full cursor-pointer px-4 py-2 text-left text-sm font-medium text-error transition hover:bg-error/10 disabled:cursor-not-allowed disabled:opacity-60"
                @click="openDeleteConfirmationModal(offer.id); closeMenu()"
              >
                {{ t('company.offers.index.deleteAction') }}
              </button>
            </div>
          </div>
        </li>
      </ul>
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
  </BaseLayout>
</template>


