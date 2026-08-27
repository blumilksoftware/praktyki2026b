<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import {
  IconPlus,
  IconBriefcase,
  IconFileText,
  IconBell,
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
import DashboardStatSection from '@/Components/Company/Dashboard/DashboardStatSection.vue'
import DashboardStatTile from '@/Components/Company/Dashboard/DashboardStatTile.vue'
import DashboardCapacityCard from '@/Components/Company/Dashboard/DashboardCapacityCard.vue'

const { t, locale } = useI18n()
const page = usePage()

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

const unreadAlertCount = computed(() => props.stats.unread_notifications_count)
const unreadAlert = computed(() => unreadAlertCount.value > 0)
const companyMenu = useCompanyPanelMenu('dashboard')

const companyName = computed(() => page.props.auth?.user?.company?.name ?? '')
const todayLabel = computed(() => new Intl.DateTimeFormat(locale.value, {
  weekday: 'long',
  day: 'numeric',
  month: 'long',
}).format(new Date()))
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
      <div>
        <h1 class="font-semibold text-text text-2xl">
          {{ t('company.dashboard.greeting', { company: companyName }) }}
        </h1>
        <p class="mt-1 text-sm text-additional">
          {{ todayLabel }}
        </p>
      </div>

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

      <DashboardCapacityCard
        v-if="stats.total_spots > 0"
        :total-spots="stats.total_spots"
        :remaining-spots="stats.remaining_spots"
      />

      <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <DashboardStatSection
          :title="t('company.dashboard.stats.sections.offers')"
          :primary-label="t('company.dashboard.stats.totalOffers')"
          :primary-value="stats.total_offers"
          :primary-href="ROUTES.COMPANY_OFFERS_INDEX"
          :icon="IconBriefcase"
        >
          <DashboardStatTile
            :label="t('company.dashboard.stats.publishedOffers')"
            :value="stats.published_offers"
            :href="`${ROUTES.COMPANY_OFFERS_INDEX}?status=published`"
            :icon="IconFileText"
          />
          <DashboardStatTile
            :label="t('company.dashboard.stats.draftOffers')"
            :value="stats.draft_offers"
            :href="`${ROUTES.COMPANY_OFFERS_INDEX}?status=draft`"
            :icon="IconFileText"
          />
          <DashboardStatTile
            :label="t('company.dashboard.stats.closingSoon')"
            :value="stats.offers_closing_soon"
            :href="`${ROUTES.COMPANY_OFFERS_INDEX}?closing_soon=true`"
            :icon="IconClock"
          />
        </DashboardStatSection>

        <DashboardStatSection
          :title="t('company.dashboard.stats.sections.applications')"
          :primary-label="t('company.dashboard.stats.applications')"
          :primary-value="stats.applications_count"
          :primary-href="ROUTES.COMPANY_APPLICATIONS"
          :icon="IconArrowDownLeftCircle"
          accent="text-sky-600"
          accent-bg="bg-sky-50"
        >
          <DashboardStatTile
            :label="t('company.dashboard.stats.pendingApplications')"
            :value="stats.pending_applications_count"
            :href="`${ROUTES.COMPANY_APPLICATIONS}?status=pending`"
            :icon="IconArrowDownLeftCircle"
          />
        </DashboardStatSection>

        <DashboardStatSection
          :title="t('company.dashboard.stats.sections.team')"
          :primary-label="t('company.dashboard.stats.teamSize')"
          :primary-value="stats.team_size"
          :primary-href="ROUTES.TEAM_MEMBERS"
          :icon="IconUsers"
          accent="text-indigo-600"
          accent-bg="bg-indigo-50"
        >
          <DashboardStatTile
            :label="t('company.dashboard.stats.pendingInvitations')"
            :value="stats.pending_invitations_count"
            :href="ROUTES.TEAM_INVITATIONS"
            :icon="IconMailForward"
          />
        </DashboardStatSection>

        <DashboardStatSection
          :title="t('company.dashboard.stats.sections.partnerships')"
          :primary-label="t('company.dashboard.stats.universityPartnerships')"
          :primary-value="stats.university_partnerships_count"
          :primary-href="`${ROUTES.COMPANY_UNIVERSITIES}?status=active `"
          :icon="IconBuildingBank"
          accent="text-teal-600"
          accent-bg="bg-teal-50"
        >
          <DashboardStatTile
            :label="t('company.dashboard.stats.openPartnershipRequests')"
            :value="stats.open_partnership_requests_count"
            :href="`${ROUTES.COMPANY_UNIVERSITIES}?status=pending_incoming`"
            :icon="IconBuildingBank"
          />
        </DashboardStatSection>
      </div>
    </div>
  </BaseLayout>
</template>
