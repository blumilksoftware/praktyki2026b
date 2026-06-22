<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import AdminGlassSection from '@/Components/Admin/AdminGlassSection.vue'
import AdminStatCard from '@/Components/Admin/AdminStatCard.vue'

const { t } = useI18n()

const students = ref(1245)
const approvedCompanies = ref(86)
const approvedUniversities = ref(12)
const activeOffers = ref(42)
const pendingVerifications = ref(7)

const stats = computed(() => [
  { label: t('admin.panel.stats.activeStudents'), value: students.value, accent: 'border-t-primary' },
  { label: t('admin.panel.stats.approvedCompanies'), value: approvedCompanies.value, accent: 'border-t-primary' },
  { label: t('admin.panel.stats.approvedUniversities'), value: approvedUniversities.value, accent: 'border-t-primary' },
  { label: t('admin.panel.stats.activeOffers'), value: activeOffers.value, accent: 'border-t-primary' },
])
</script>

<template>
  <Head :title="t('admin.layout.title')" />
  <BaseLayout active-page="dashboard">
    <AdminGlassSection class="px-4 md:px-8 py-5 md:py-6 text-center">
      <h1 class="font-semibold text-text text-2xl">
        {{ t('admin.panel.greeting') }}
      </h1>
      <p class="mx-auto mt-3 max-w-2xl text-slate-800 text-sm leading-relaxed">
        {{ t('admin.panel.description') }}
      </p>
      <a
        href="/admin/applications"
        class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 mt-5 px-4 py-2 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 focus-visible:ring-offset-2 font-medium text-white text-sm transition"
        aria-hidden="true"
        tabindex="-1"
      >
        {{ t('admin.panel.goToApplications') }}
      </a>
    </AdminGlassSection>

    <section class="gap-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
      <AdminStatCard
        v-for="stat in stats"
        :key="stat.label"
        :label="stat.label"
        :value="stat.value"
        :accent="stat.accent"
      />
    </section>

    <AdminGlassSection class="flex md:flex-row flex-col justify-between items-center gap-4 px-4 md:px-8 py-5 md:py-6">
      <div class="md:text-left text-center">
        <h2 class="font-medium text-slate-700 text-sm">{{ t('admin.panel.pendingVerifications') }}</h2>
        <h3 class="mt-1 font-bold text-primary text-3xl">
          {{ pendingVerifications }}
        </h3>
      </div>
      <div class="w-full md:max-w-sm">
        <div
          class="bg-white/40 rounded-full w-full h-2.5 overflow-hidden"
          role="progressbar"
          :aria-label="t('admin.panel.verificationProgressAriaLabel')"
          :aria-valuenow="Math.min((pendingVerifications / 20) * 100, 100)"
          aria-valuemin="0"
          aria-valuemax="100"
        >
          <div
            class="bg-primary rounded-full h-2.5 transition-all duration-500"
            :style="{ width: Math.min((pendingVerifications / 20) * 100, 100) + '%' }"
          />
        </div>
        <p class="mt-2 text-slate-700 text-xs text-center md:text-right">{{ t('admin.panel.verificationProgress') }}</p>
      </div>
    </AdminGlassSection>
  </BaseLayout>
</template>
