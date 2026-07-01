<script setup>
import { Head } from '@inertiajs/vue3'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/company/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/company/profile', icon: IconUser },
]
</script>

<template>
  <Head title="Company" />
  <BaseLayout active-page="dashboard" :nav-items="navItems">
    <OnboardingBanner />
  </BaseLayout>
</template>
