<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconPlus } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'
import { ROUTES } from '@/Helpers/routes'

defineProps({
  offers: { type: Array, default: () => [] },
  isCompanyVerified: { type: Boolean, default: false },
})

const { t } = useI18n()
const companyMenu = useCompanyPanelMenu('offers')

const statusBadgeClass = computed(() => (status) => ({
  draft: 'bg-slate-100 text-slate-600',
  published: 'bg-green-100 text-green-700',
  closed: 'bg-slate-200 text-slate-500',
  expired: 'bg-slate-200 text-slate-500',
}[status] ?? 'bg-slate-100 text-slate-600'))

const publishingId = ref(null)

function publish(offerId) {
  publishingId.value = offerId

  router.patch(ROUTES.COMPANY_OFFERS_PUBLISH(offerId), {}, {
    preserveScroll: true,
    onFinish: () => {
      publishingId.value = null
    },
  })
}
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

      <div
        v-if="offers.length === 0"
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-white px-8 py-16 text-center"
        role="status"
      >
        <p class="font-medium text-text text-base">
          {{ t('company.offers.index.empty.title') }}
        </p>
        <p class="mt-1 text-additional text-sm">
          {{ t('company.offers.index.empty.description') }}
        </p>
      </div>

      <ul v-else class="flex flex-col gap-3">
        <li
          v-for="offer in offers"
          :key="offer.id"
          class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide"
                :class="statusBadgeClass(offer.status)"
              >
                {{ t(`company.offers.index.status.${offer.status}`) }}
              </span>
              <h2 class="truncate font-semibold text-text text-base">
                {{ offer.title }}
              </h2>
            </div>
            <p class="mt-1 text-additional text-sm">
              {{ t('company.offers.index.spotsLabel', { count: offer.spots }) }}
              ·
              {{ t('company.offers.index.applicationsCount', { count: offer.applications_count }) }}
            </p>
            <p v-if="offer.status === 'draft' && !isCompanyVerified" class="mt-1 text-amber-600 text-xs">
              {{ t('company.offers.index.verificationRequiredHint') }}
            </p>
          </div>

          <div class="flex shrink-0 items-center gap-2">
            <Link
              :href="ROUTES.COMPANY_OFFERS_EDIT(offer.id)"
              class="inline-flex items-center justify-center rounded-lg border border-border px-4 py-2 text-sm font-semibold text-text transition hover:border-primary/40 hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
            >
              {{ t('company.offers.index.editAction') }}
            </Link>

            <button
              v-if="offer.status === 'draft' && isCompanyVerified"
              type="button"
              :disabled="publishingId === offer.id"
              class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
              @click="publish(offer.id)"
            >
              {{ publishingId === offer.id ? t('company.offers.index.publishing') : t('company.offers.index.publishAction') }}
            </button>
          </div>
        </li>
      </ul>
    </div>
  </BaseLayout>
</template>
