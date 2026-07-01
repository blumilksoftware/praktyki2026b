<script setup>
import { Head } from '@inertiajs/vue3'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import ProfileProgress from '@/Components/Onboarding/ProfileProgress.vue'

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/university/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/university/profile', icon: IconUser },
]
</script>

<template>
  <Head title="Profile" />
  <BaseLayout active-page="profile" :nav-items="navItems">
    <ProfileProgress />
  </BaseLayout>
</template>
