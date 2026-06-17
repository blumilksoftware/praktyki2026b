<script setup>
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Components/Admin/AdminLayout.vue'
import AdminGlassSection from '@/Components/Admin/AdminGlassSection.vue'

const { t } = useI18n()
</script>

<template>
  <AdminLayout active-page="applications">
    <AdminGlassSection class="px-4 md:px-8 py-5 md:py-6 text-center">
      <h1 class="font-semibold text-text text-2xl">{{ t('admin.applications.title') }}</h1>
      <p class="mt-2 text-slate-800 text-sm">{{ t('admin.applications.description') }}</p>
    </AdminGlassSection>

    <AdminGlassSection class="px-4 md:px-6 py-5 md:py-6">
      <div class="flex sm:flex-row flex-col sm:justify-between sm:items-center gap-3 mb-4">
        <h2 class="font-semibold text-text text-xl">{{ t('admin.applications.heading') }}</h2>
        <a
          href="/admin"
          class="inline-flex justify-center items-center bg-white/40 hover:bg-white/70 px-4 py-2 border border-primary/20 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 focus-visible:ring-offset-2 font-medium text-primary text-sm transition"
        >
          {{ t('admin.applications.backToDashboard') }}
        </a>
      </div>
    </AdminGlassSection>
    <p>{{ t('admin.applications.placeholder') }}</p>
  </AdminLayout>
</template>
