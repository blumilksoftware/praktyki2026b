<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import {
  IconPlus,
  IconBriefcase,
  IconFileText,
  IconBell,
  IconArrowUpRight,
  IconArrowDownLeftCircle,
  IconClock,
  IconUsers,
  IconMailForward,
  IconBuildingBank,
} from '@tabler/icons-vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'
import { ROUTES } from '@/Helpers/routes'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'

const { t } = useI18n()

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      total_offers: 0,
      published_offers: 0,
      draft_offers: 0,
      offers_closing_soon: 0,
      total_spots: 0,
      remaining_spots: 0,
      applications_count: 0,
      pending_applications_count: 0,
      accepted_applications_count: 0,
      team_size: 0,
      pending_invitations_count: 0,
      accepted_invitations_count: 0,
      university_partnerships_count: 0,
      open_partnership_requests_count: 0,
      unread_notifications_count: 0,
    }),
  },
})

const statsCards = computed(() => [
  {
    label: t('company.dashboard.stats.totalOffers'),
    value: props.stats.total_offers,
    accent: 'text-primary',
    icon: IconBriefcase,
    href: ROUTES.COMPANY_OFFERS_INDEX,
  },
  {
    label: t('company.dashboard.stats.publishedOffers'),
    value: props.stats.published_offers,
    accent: 'text-emerald-600',
    icon: IconFileText,
    href: `${ROUTES.COMPANY_OFFERS_INDEX}?status=published`,
  },
  {
    label: t('company.dashboard.stats.draftOffers'),
    value: props.stats.draft_offers,
    accent: 'text-amber-600',
    icon: IconFileText,
    href: `${ROUTES.COMPANY_OFFERS_INDEX}?status=draft`,
  },
  {
    label: t('company.dashboard.stats.closingSoon'),
    value: props.stats.offers_closing_soon,
    accent: 'text-orange-600',
    icon: IconClock,
    href: `${ROUTES.COMPANY_OFFERS_INDEX}?closing_soon=true`,
  },
  {
    label: t('company.dashboard.stats.pendingApplications'),
    value: props.stats.pending_applications_count,
    accent: 'text-sky-600',
    icon: IconArrowDownLeftCircle,
    href: `${ROUTES.COMPANY_APPLICATIONS}?status=pending`,
  },
  {
    label: t('company.dashboard.stats.teamSize'),
    value: props.stats.team_size,
    accent: 'text-indigo-600',
    icon: IconUsers,
    href: ROUTES.TEAM,
  },
  {
    label: t('company.dashboard.stats.pendingInvitations'),
    value: props.stats.pending_invitations_count,
    accent: 'text-indigo-600',
    icon: IconMailForward,
    href: ROUTES.TEAM,
  },
  {
    label: t('company.dashboard.stats.universityPartnerships'),
    value: props.stats.university_partnerships_count,
    accent: 'text-teal-600',
    icon: IconBuildingBank,
    href: ROUTES.COMPANY_UNIVERSITIES,
  },
  {
    label: t('company.dashboard.stats.openPartnershipRequests'),
    value: props.stats.open_partnership_requests_count,
    accent: 'text-teal-600',
    icon: IconBuildingBank,
    href: `${ROUTES.COMPANY_UNIVERSITIES}?status=pending`,
  },
])

const unreadAlertCount = computed(() => props.stats.unread_notifications_count)
const unreadAlert = computed(() => unreadAlertCount.value > 0)
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

      <div
        v-if="unreadAlert"
        class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="flex items-center gap-2">
          <IconBell class="h-4 w-4" aria-hidden="true" />
          <span>
            {{ t('company.dashboard.shortcuts.unreadApplications', { count: unreadAlertCount }) }}
          </span>
        </div>
        <Link :href="`${ROUTES.COMPANY_APPLICATIONS}?status=pending`" class="font-semibold underline underline-offset-4">
          {{ t('company.dashboard.shortcuts.checkIt') }}
        </Link>
      </div>

      <div class="flex flex-col items-start gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-semibold text-text text-sm">
            {{ t('company.dashboard.createOfferCard.title') }}
          </p>
          <p class="mt-0.5 text-additional text-xs">
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

      <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <Link
          v-for="card in statsCards"
          :key="card.label"
          :href="card.href"
          class="group flex items-center justify-between rounded-2xl border border-border bg-white p-5 shadow-sm transition hover:border-primary/40 hover:shadow-md"
        >
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-background">
              <component :is="card.icon" class="h-6 w-6" :class="card.accent" aria-hidden="true" />
            </div>
            <div>
              <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-additional">
                {{ card.label }}
              </p>
              <p class="mt-1 text-3xl font-semibold text-text leading-none">
                {{ card.value }}
              </p>
            </div>
          </div>
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-background text-additional transition group-hover:bg-primary/10 group-hover:text-primary">
            <IconArrowUpRight class="h-4 w-4" aria-hidden="true" />
          </span>
        </Link>
      </div>
    </div>
  </BaseLayout>
</template>
