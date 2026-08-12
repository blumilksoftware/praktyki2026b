<script setup>
import { ref, watch, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import AppLayout from "@/Components/Layouts/AppLayout.vue";
import { ROUTES } from '@/Helpers/routes'
import ApplicationsCard from '@/Components/Profiles/ApplicationsCard.vue'
import BaseSelect from '@/Components/Base/BaseSelect.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const props = defineProps({
  applications: {
    type: Object,
    default: () => ({ data: [], total: 0, current_page: 1, next_page_url: null }),
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

const goBack = () => {
  window.history.back()
}

const currentFilters = ref({
  offer: props.filters.offer ?? '',
  status: props.filters.status ?? '',
})

const displayedApplications = ref([...props.applications.data])
const nextPageUrl = ref(props.applications.next_page_url)
const isLoadingMore = ref(false)

watch(currentFilters, (value) => {
  router.get(
    ROUTES.COMPANY_APPLICATIONS,
    { offer: value.offer, status: value.status },
    { preserveState: true, preserveScroll: true, replace: true },
  )
}, { deep: true })

watch(() => props.applications, (newVal) => {
  displayedApplications.value = [...newVal.data]
  nextPageUrl.value = newVal.next_page_url
}, { deep: true })

const loadMore = async () => {
  if (!nextPageUrl.value || isLoadingMore.value) return

  isLoadingMore.value = true

  try {
    const response = await axios.get(nextPageUrl.value, {
      headers: {
        'Accept': 'application/json',
      },
    })

    displayedApplications.value.push(...response.data.data)

    nextPageUrl.value = response.data.next_page_url

  } catch (error) {
    console.error(error)
  } finally {
    isLoadingMore.value = false
  }
}

function updateStatus(applicationId, newStatus) {
  const url = ROUTES.COMPANY_APPLICATIONS_STATUS_UPDATE.replace('{application}', applicationId)

  const appIndex = displayedApplications.value.findIndex(a => a.id === applicationId)
  if (appIndex !== -1) {
    displayedApplications.value[appIndex].status = newStatus
  }

  router.patch(url, {
    status: newStatus,
  }, {
    preserveScroll: true,
    preserveState: true,
  })
}

const statusOptions = ['pending', 'reviewed', 'accepted', 'rejected']

const offerFilterOptions = computed(() => [
  { value: '', label: t('profiles.company.applications.filters.all_offers') },
  ...props.offers.map((offer) => ({ value: offer.id, label: offer.title })),
])

const statusFilterOptions = computed(() => [
  { value: '', label: t('profiles.company.applications.filters.all_statuses') },
  ...statusOptions.map((status) => ({ value: status, label: t(`profiles.company.applications.statuses.${status}`) })),
])
</script>

<template>
  <Head :title="t('profiles.company.applications.title')" />
  <AppLayout active-page="applications">
  <div class="min-h-screen flex flex-col bg-slate-50/50">
    <BaseNavbar show-hamburger :menu-items="companyMenu" show-navigation-buttons :navigation-buttons="companyMenu" navigation-variant="default" />

    <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <div class="flex flex-col gap-6">
        <h1 class="text-3xl font-bold text-text">
          {{ t('profiles.company.applications.title') }}
          <span v-if="applications.total">({{ applications.total }})</span>
          <span v-else>({{ displayedApplications.length }})</span>
        </h1>

        <div class="flex flex-col sm:flex-row gap-4">
          <BaseSelect
            id="applications-filter-offer"
            v-model="currentFilters.offer"
            :label="t('profiles.company.applications.filters.offer')"
            :options="offerFilterOptions"
            :stacked="false"
            class="w-full sm:w-64"
          />

          <BaseSelect
            id="applications-filter-status"
            v-model="currentFilters.status"
            :label="t('profiles.company.applications.filters.status')"
            :options="statusFilterOptions"
            :stacked="false"
            class="w-full sm:w-64"
          />
        </div>

        <div class="flex flex-col gap-4 mt-2">
          <div v-if="!displayedApplications.length" class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-500 shadow-sm">
            {{ t('profiles.company.applications.empty') }}
          </div>

          <ApplicationsCard
            v-for="application in displayedApplications"
            :key="application.id"
            :application="application"
            @update-status="updateStatus"
          />

          <BaseButton
            v-if="nextPageUrl"
            variant="secondary"
            class="w-full justify-center mt-4"
            :disabled="isLoadingMore"
            @click="loadMore"
          >
            {{ isLoadingMore ? t('buttons.loading') : t('buttons.load_more') }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
  </AppLayout>
</template>
