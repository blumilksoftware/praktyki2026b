<script setup>
import { useI18n } from 'vue-i18n'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import AdminGlassSection from '@/Components/Admin/AdminGlassSection.vue'
import AdminApplicationsTable from '@/Components/Admin/AdminApplicationsTable.vue'

const { t } = useI18n()
</script>

<template>
  <BaseLayout active-page="applications">
    <AdminGlassSection class="px-4 py-5 md:px-8 md:py-6 text-center">
      <h1 class="text-2xl font-semibold text-text">{{ t('admin.applications.title') }}</h1>
      <p class="text-sm text-slate-600 mt-2">{{ t('admin.applications.description') }}</p>
    </AdminGlassSection>

    <AdminGlassSection class="px-4 py-5 md:px-6 md:py-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h2 class="text-xl font-semibold text-text">{{ t('admin.applications.heading') }}</h2>
        <a
          href="/admin"
          class="inline-flex items-center justify-center rounded-xl border border-primary/20 bg-white/40 px-4 py-2 text-sm font-medium text-primary transition hover:bg-white/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 focus-visible:ring-offset-2"
        >
          {{ t('admin.applications.backToDashboard') }}
        </a>
      </div>
    </AdminGlassSection>
    <p>{{ t('admin.applications.placeholder') }}</p>
  </BaseLayout>
</template>
