<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const navItems = computed(() => [
  { key: 'dashboard', label: t('student.layout.nav.dashboard'), href: ROUTES.STUDENT_DASHBOARD, icon: IconHome },
  { key: 'profile', label: t('student.layout.nav.profile'), href: ROUTES.STUDENT_PROFILE, icon: IconUser },
])
</script>

<template>
  <Head :title="t('student.layout.title')" />
  <BaseLayout
    active-page="dashboard"
    :nav-items="navItems"
    :minimal-header="true"
    :show-background="false"
  >
    <OnboardingBanner />
  </BaseLayout>
</template>
