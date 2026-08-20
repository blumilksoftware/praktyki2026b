<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import DateRangeFilter from '@/Components/University/DateRangeFilter.vue'
import StatsOverview from '@/Components/University/StatsOverview.vue'
import BreakdownTabs from '@/Components/University/BreakdownTabs.vue'
import UniversityLayout from '@/Components/Layouts/UniversityLayout.vue'

defineProps({
  data: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('university.dashboard.title')" />

  <UniversityLayout active-page="dashboard">
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-semibold tracking-tight text-text">
          {{ t('university.dashboard.title') }}
        </h1>

        <DateRangeFilter :filters="filters" />
      </div>

      <StatsOverview
        :linked-students="data.linkedStudents"
        :applications-submitted="data.applicationsSubmitted"
        :accepted-placements="data.acceptedPlacements"
      />

      <BreakdownTabs
        :faculties="data.breakdownByFaculty"
        :fields="data.breakdownByField"
        :filters="filters"
      />
    </div>
  </UniversityLayout>
</template>
