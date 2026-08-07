<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import VerificationTable from '@/Components/Admin/VerificationTable.vue'

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

const stats = computed(() => [
  { label: t('admin.verification.pending'), value: props.companyStats.pending + props.universityStats.pending },
  { label: t('admin.verification.verified'), value: props.companyStats.verified + props.universityStats.verified },
  { label: t('admin.verification.rejected'), value: props.companyStats.rejected + props.universityStats.rejected },
])
</script>

<template>
  <Head :title="t('admin.applications.title')" />
  <AdminLayout active-page="applications">
    <div class="space-y-6">
      <div>
        <h1 class="font-semibold text-text text-2xl">{{ t('admin.applications.title') }}</h1>
        <p class="mt-2 text-slate-600 text-sm">{{ t('admin.applications.description') }}</p>
      </div>

      <div>
        <p class="mb-3 text-slate-600 text-sm">{{ t('admin.applications.stats') }}</p>
        <section class="gap-4 grid grid-cols-1 sm:grid-cols-3">
          <ProfilePageCard v-for="stat in stats" :key="stat.label" centered>
            <div class="font-medium text-additional text-sm">{{ stat.label }}</div>
            <div class="mt-2 font-bold text-text text-3xl">{{ stat.value }}</div>
          </ProfilePageCard>
        </section>
      </div>

      <ProfilePageCard>
        <VerificationTable
          :companies="companies"
          :universities="universities"
          :filters="filters"
        />
      </ProfilePageCard>
    </div>
  </AdminLayout>
</template>
