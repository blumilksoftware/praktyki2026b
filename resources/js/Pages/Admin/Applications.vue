<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import AdminGlassSection from '@/Components/Admin/AdminGlassSection.vue'
import AdminStatCard from '@/Components/Admin/AdminStatCard.vue'
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
  { label: t('admin.verification.pending'), value: props.companyStats.pending + props.universityStats.pending, accent: 'border-t-primary' },
  { label: t('admin.verification.verified'), value: props.companyStats.verified + props.universityStats.verified, accent: 'border-t-primary' },
  { label: t('admin.verification.rejected'), value: props.companyStats.rejected + props.universityStats.rejected, accent: 'border-t-primary' },
])
</script>

<template>
  <Head :title="t('admin.applications.title')" />
  <AdminLayout active-page="applications">
    <h1 class="font-semibold text-text text-2xl">{{ t('admin.applications.title') }}</h1>
    <p class="mt-2 text-slate-600 text-sm">{{ t('admin.applications.description') }}</p>
    <AdminGlassSection class="px-4 md:px-8 py-5 md:py-6 text-center">
      <p class="mt-2 text-slate-600 text-sm m-4">{{ t('admin.applications.stats') }}</p>
      <section class="gap-4 grid grid-cols-1 sm:grid-cols-3">
        <AdminStatCard
          v-for="stat in stats"
          :key="stat.label"
          :label="stat.label"
          :value="stat.value"
          :accent="stat.accent"
        />
      </section>
    </AdminGlassSection>



    <AdminGlassSection class="px-4 md:px-6 py-5 md:py-6">
      <VerificationTable
        :companies="companies"
        :universities="universities"
        :filters="filters"
      />
    </AdminGlassSection>
  </AdminLayout>
</template>
