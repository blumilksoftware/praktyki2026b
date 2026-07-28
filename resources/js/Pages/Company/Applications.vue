<script setup>
import { ref, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconArrowLeft, IconSearch, IconClipboardText, IconUserCircle, IconUsersGroup } from '@tabler/icons-vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import Menu from '@/Components/Profiles/Menu.vue'
import { ROUTES } from '@/Helpers/routes'
import ApplicationsCard from '@/Components/Profiles/ApplicationsCard.vue'

const props = defineProps({
  applications: {
    type: Object,
    default: () => ({ data: [], total: 0 }),
  },
  offers: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({ offer: null, status: null }),
  },
})

const { t } = useI18n()

const companyMenu = computed(() => [
  { label: t('profiles.company.myOffers'), href: ROUTES.OFFERS, icon: IconSearch },
  { label: t('profiles.company.candidateApplications'), href: ROUTES.COMPANY_APPLICATIONS, icon: IconClipboardText, isActive: true },
  { label: t('profiles.profile'), href: ROUTES.PROFILE, icon: IconUserCircle },
  { label: t('profiles.company.teamAndPermissions'), href: ROUTES.TEAM, icon: IconUsersGroup },
])

const goBack = () => {
  window.history.back()
}

const currentFilters = ref({
  offer: props.filters.offer ?? '',
  status: props.filters.status ?? '',
})

watch(currentFilters, (value) => {
  router.get(
    ROUTES.COMPANY_APPLICATIONS, 
    { offer: value.offer, status: value.status },
    { preserveState: true, preserveScroll: true, replace: true },
  )
}, { deep: true })

function updateStatus(applicationId, newStatus) {
  const url = ROUTES.COMPANY_APPLICATIONS_STATUS_UPDATE.replace('{application}', applicationId)
  
  router.patch(url, {
    status: newStatus,
  }, {
    preserveScroll: true,
    preserveState: true,
  })
}

const statusOptions = ['pending', 'reviewed', 'accepted', 'rejected']
</script>

<template>
  <Head :title="t('profiles.company.applications.title')" />

  <div class="min-h-screen flex flex-col bg-slate-50/50">
    <BaseNavbar show-hamburger :menu-items="companyMenu" />

    <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-row justify-between items-center w-full mb-6">
        <a class="inline-flex items-center gap-2 text-slate-500 text-sm transition hover:text-slate-800 cursor-pointer"
           @click="goBack"
        >
          <IconArrowLeft stroke="2.5" class="w-4 h-4" />
          {{ t('buttons.back') }}
        </a>
        <div>
          <Menu :items="companyMenu" />
        </div>
      </div>

      <div class="flex flex-col gap-6">
        <h1 class="text-3xl font-bold text-text">
          {{ t('profiles.company.applications.title') }} 
          <span v-if="applications.total">({{ applications.total }})</span>
          <span v-else>({{ applications.data.length }})</span>
        </h1>

        <div class="flex flex-col sm:flex-row gap-4">
          <select
            v-model="currentFilters.offer"
            class="w-full sm:w-64 rounded-xl border-slate-200 text-slate-700 shadow-sm focus:border-primary focus:ring focus:ring-primary/50 bg-white py-2.5"
          >
            <option value="">{{ t('profiles.company.applications.filters.all_offers') }}</option>
            <option v-for="offer in offers" :key="offer.id" :value="offer.id">
              {{ offer.title }}
            </option>
          </select>

          <select
            v-model="currentFilters.status"
            class="w-full sm:w-64 rounded-xl border-slate-200 text-slate-700 shadow-sm focus:border-primary focus:ring focus:ring-primary/50 bg-white py-2.5"
          >
            <option value="">{{ t('profiles.company.applications.filters.all_statuses') }}</option>
            <option v-for="status in statusOptions" :key="status" :value="status">
              {{ t(`profiles.company.applications.statuses.${status}`) }}
            </option>
          </select>
        </div>
        <div class="flex flex-col gap-4 mt-2">
          <div v-if="!applications.data.length" class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-500 shadow-sm">
            {{ t('profiles.company.applications.empty') }}
          </div>

          <ApplicationsCard 
            v-for="application in applications.data" 
            :key="application.id" 
            :application="application"
            @update-status="updateStatus"
          />
        </div>
      </div>
    </div>
  </div>
</template>