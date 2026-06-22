<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import AdminGlassSection from '@/Components/Admin/AdminGlassSection.vue'

const { t } = useI18n()
</script>

<template>
  <Head :title="t('admin.applications.title')" />
  <AdminLayout active-page="applications">
    <AdminGlassSection class="px-4 md:px-8 py-5 md:py-6 text-center">
      <h1 class="font-semibold text-text text-2xl">{{ t('admin.applications.title') }}</h1>
      <p class="mt-2 text-slate-600 text-sm">{{ t('admin.applications.description') }}</p>
    </AdminGlassSection>

    <AdminGlassSection class="px-4 md:px-6 py-5 md:py-6">
      <div class="flex sm:flex-row flex-col sm:justify-between sm:items-center gap-3 mb-4">
        <h2 class="font-semibold text-text text-xl">{{ t('admin.applications.heading') }}</h2>
      </div>
    </AdminGlassSection>
    <p>{{ t('admin.applications.placeholder') }}</p>
  </AdminLayout>
</template>
