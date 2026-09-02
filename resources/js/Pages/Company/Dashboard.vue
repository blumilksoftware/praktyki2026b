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
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import { ROUTES } from '@/Helpers/routes'
import DashboardStatSection from '@/Components/Company/Dashboard/DashboardStatSection.vue'
import DashboardStatTile from '@/Components/Company/Dashboard/DashboardStatTile.vue'
import DashboardCapacityCard from '@/Components/Company/Dashboard/DashboardCapacityCard.vue'

const { t, locale } = useI18n()
const page = usePage()

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalOffers: 0,
      publishedOffers: 0,
      draftOffers: 0,
      offersClosingSoon: 0,
      totalSpots: 0,
      remainingSpots: 0,
      applicationsCount: 0,
      pendingApplicationsCount: 0,
      acceptedApplicationsCount: 0,
      teamSize: 0,
      pendingInvitationsCount: 0,
      acceptedInvitationsCount: 0,
      universityPartnershipsCount: 0,
      openPartnershipRequestsCount: 0,
      unreadNotificationsCount: 0,
    }),
  },
})

const unreadAlertCount = computed(() => props.stats.unreadNotificationsCount)
const unreadAlert = computed(() => unreadAlertCount.value > 0)

const companyName = computed(() => page.props.auth?.user?.company?.name ?? '')
const todayLabel = computed(() => new Intl.DateTimeFormat(locale.value, {
  weekday: 'long',
  day: 'numeric',
  month: 'long',
}).format(new Date()))
</script>

<template>
  <Head :title="t('company.layout.title')" />
  <AppLayout active-page="dashboard">
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
        v-if="stats.totalSpots > 0"
        :total-spots="stats.totalSpots"
        :remaining-spots="stats.remainingSpots"
      />

      <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <DashboardStatSection
          :title="t('company.dashboard.stats.sections.offers')"
          :primary-label="t('company.dashboard.stats.totalOffers')"
          :primary-value="stats.totalOffers"
          :primary-href="ROUTES.COMPANY_OFFERS_INDEX"
          :icon="IconBriefcase"
        >
          <DashboardStatTile
            :label="t('company.dashboard.stats.publishedOffers')"
            :value="stats.publishedOffers"
            :href="`${ROUTES.COMPANY_OFFERS_INDEX}?status=published`"
            :icon="IconFileText"
          />
          <DashboardStatTile
            :label="t('company.dashboard.stats.draftOffers')"
            :value="stats.draftOffers"
            :href="`${ROUTES.COMPANY_OFFERS_INDEX}?status=draft`"
            :icon="IconFileText"
          />
          <DashboardStatTile
            :label="t('company.dashboard.stats.closingSoon')"
            :value="stats.offersClosingSoon"
            :href="`${ROUTES.COMPANY_OFFERS_INDEX}?closing_soon=true`"
            :icon="IconClock"
          />
        </DashboardStatSection>

        <DashboardStatSection
          :title="t('company.dashboard.stats.sections.applications')"
          :primary-label="t('company.dashboard.stats.applications')"
          :primary-value="stats.applicationsCount"
          :primary-href="ROUTES.COMPANY_APPLICATIONS"
          :icon="IconArrowDownLeftCircle"
          accent="text-sky-600"
          accent-bg="bg-sky-50"
        >
          <DashboardStatTile
            :label="t('company.dashboard.stats.pendingApplications')"
            :value="stats.pendingApplicationsCount"
            :href="`${ROUTES.COMPANY_APPLICATIONS}?status=pending`"
            :icon="IconArrowDownLeftCircle"
          />
        </DashboardStatSection>

        <DashboardStatSection
          :title="t('company.dashboard.stats.sections.team')"
          :primary-label="t('company.dashboard.stats.teamSize')"
          :primary-value="stats.teamSize"
          :primary-href="ROUTES.TEAM_MEMBERS"
          :icon="IconUsers"
          accent="text-indigo-600"
          accent-bg="bg-indigo-50"
        >
          <DashboardStatTile
            :label="t('company.dashboard.stats.pendingInvitations')"
            :value="stats.pendingInvitationsCount"
            :href="ROUTES.TEAM_INVITATIONS"
            :icon="IconMailForward"
          />
        </DashboardStatSection>

        <DashboardStatSection
          :title="t('company.dashboard.stats.sections.partnerships')"
          :primary-label="t('company.dashboard.stats.universityPartnerships')"
          :primary-value="stats.universityPartnershipsCount"
          :primary-href="`${ROUTES.COMPANY_UNIVERSITIES}?status=active `"
          :icon="IconBuildingBank"
        >
          <DashboardStatTile
            :label="t('company.dashboard.stats.openPartnershipRequests')"
            :value="stats.openPartnershipRequestsCount"
            :href="`${ROUTES.COMPANY_UNIVERSITIES}?status=pending_incoming`"
            :icon="IconBuildingBank"
          />
        </DashboardStatSection>
      </div>
    </div>
  </AppLayout>
</template>
