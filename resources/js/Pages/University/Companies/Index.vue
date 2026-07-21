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
  <Head title="Wyszukiwarka firm" />
  <BaseLayout active-page="companies" :nav-items="navItems">
    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Wyszukiwarka firm</h1>
    </div>
  </BaseLayout>
</template>
