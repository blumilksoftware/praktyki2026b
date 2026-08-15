<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from "@/Components/Layouts/AppLayout.vue";

const { t } = useI18n()

defineProps({
  companies: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

</script>

<template>
  <Head :title="t('university.companies.title')" />
  <AppLayout active-page="Company">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ t('university.companies.title') }}</h1>
  </AppLayout>
</template>
