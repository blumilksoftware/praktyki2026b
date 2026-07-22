<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'

const { t } = useI18n()

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/company/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/company/profile', icon: IconUser },
]
</script>

<template>
  <Head :title="t('company.layout.title')" />
  <BaseLayout active-page="dashboard" :nav-items="navItems" :show-background="false">
    <OnboardingBanner />
  </BaseLayout>
</template>
