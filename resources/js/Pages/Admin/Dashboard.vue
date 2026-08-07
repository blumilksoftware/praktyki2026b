<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'

const { t } = useI18n()

const students = ref(1245)
const approvedCompanies = ref(86)
const approvedUniversities = ref(12)
const activeOffers = ref(42)
const pendingVerifications = ref(7)

defineProps({
  companiesNeedingVerification: {
    type: Array,
    required: true,
  },
  universitiesNeedingVerification: {
    type: Array,
    required: true,
  },
})

const stats = computed(() => [
  { label: t('admin.panel.stats.activeStudents'), value: students.value },
  { label: t('admin.panel.stats.approvedCompanies'), value: approvedCompanies.value },
  { label: t('admin.panel.stats.approvedUniversities'), value: approvedUniversities.value },
  { label: t('admin.panel.stats.activeOffers'), value: activeOffers.value },
])
</script>

<template>
  <Head :title="t('admin.layout.title')" />
  <AdminLayout active-page="dashboard">
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="font-bold text-2xl text-gray-900 tracking-tight">
            {{ t('admin.panel.greeting') }}
          </h1>
          <p class="mt-1 text-slate-600 text-sm">
            {{ t('admin.panel.description') }}
          </p>
        </div>
        <a
          href="/admin/applications"
          class="inline-flex shrink-0 items-center gap-2 bg-primary hover:bg-primary/90 px-4 py-2.5 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 focus-visible:ring-offset-2 font-semibold text-white text-sm transition"
        >
          {{ t('admin.panel.goToApplications') }}
        </a>
      </div>

      <section class="gap-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <ProfilePageCard v-for="stat in stats" :key="stat.label" centered>
          <div class="font-medium text-additional text-sm">{{ stat.label }}</div>
          <div class="mt-2 font-bold text-text text-3xl">{{ stat.value }}</div>
        </ProfilePageCard>
      </section>

      <ProfilePageCard class="flex md:flex-row flex-col justify-between items-center gap-4">
        <div class="md:text-left text-center">
          <h2 class="font-medium text-slate-700 text-sm">{{ t('admin.panel.pendingVerifications') }}</h2>
          <h3 class="mt-1 font-bold text-primary text-3xl">
            {{ pendingVerifications }}
          </h3>
        </div>
        <div class="w-full md:max-w-sm">
          <div
            class="bg-gray-100 rounded-full ring-1 ring-border w-full h-2.5 overflow-hidden"
            role="progressbar"
            :aria-valuenow="Math.min((pendingVerifications / 20) * 100, 100)"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-describedby="verification-progress-label"
          >
            <div
              class="bg-primary rounded-full h-2.5 transition-all duration-500"
              :style="{ width: Math.min((pendingVerifications / 20) * 100, 100) + '%' }"
            />
          </div>
          <p id="verification-progress-label" class="mt-2 text-slate-700 text-xs text-center md:text-right">
            {{ t('admin.panel.verificationProgress') }}
          </p>
        </div>
      </ProfilePageCard>
    </div>
  </AdminLayout>
</template>
