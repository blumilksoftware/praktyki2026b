<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import ProfileProgress from '@/Components/Onboarding/ProfileProgress.vue'

const { t } = useI18n()

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/company/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/company/profile', icon: IconUser },
]
</script>

<template>
  <Head :title="t('company.profile.title')" />
  <BaseLayout active-page="profile" :nav-items="navItems">
    <ProfileProgress />
  </BaseLayout>
</template>
