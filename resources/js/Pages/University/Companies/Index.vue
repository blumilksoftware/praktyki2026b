<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'

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

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/university/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/university/profile', icon: IconUser },
]
</script>

<template>
  <Head :title="t('common.titles.companySearch')" />
  <BaseLayout active-page="companies" :nav-items="navItems">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ t('common.titles.companySearch') }}</h1>
  </BaseLayout>
</template>
