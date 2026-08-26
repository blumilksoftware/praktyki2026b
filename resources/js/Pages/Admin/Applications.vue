<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import VerificationTable from '@/Components/Admin/VerificationTable.vue'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

const { t } = useI18n()

const props = defineProps({
  companies: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  universities: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  companyStats: {
    type: Object,
    default: () => ({ pending: 0, verified: 0, rejected: 0 }),
  },
  universityStats: {
    type: Object,
    default: () => ({ pending: 0, verified: 0, rejected: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({ status: 'all', search: '' }),
  },
})
</script>

<template>
  <Head :title="t('admin.applications.title')" />
  <AppLayout active-page="applications">
    <div class="space-y-6">
      <div>
        <h1 class="font-semibold text-text text-2xl">{{ t('admin.applications.title') }}</h1>
        <p class="mt-2 text-additional text-sm">{{ t('admin.applications.description') }}</p>
      </div>

      <ProfilePageCard>
        <VerificationTable
          :companies="companies"
          :universities="universities"
          :company-stats="companyStats"
          :university-stats="universityStats"
          :filters="filters"
        />
      </ProfilePageCard>
    </div>
  </AppLayout>
</template>
