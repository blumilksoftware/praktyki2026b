<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconPlus } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()
const companyMenu = useCompanyPanelMenu('dashboard')
</script>

<template>
  <Head :title="t('company.layout.title')" />
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
  </BaseLayout>
</template>
