<script setup>
import { computed } from 'vue'
import { IconHome, IconClipboard, IconUsers } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'
import BaseLayout from './BaseLayout.vue'
import { ROUTES } from '@/Helpers/routes'

defineProps({ activePage: { type: String, default: 'dashboard' } })
const { t } = useI18n()

const navItems = computed(() => [
  { key: 'dashboard', label: t('admin.layout.nav.dashboard'), href: ROUTES.ADMIN_DASHBOARD, icon: IconHome },
  { key: 'applications', label: t('admin.layout.nav.applications'), href: ROUTES.ADMIN_APPLICATIONS, icon: IconClipboard },
  { key: 'users', label: t('admin.layout.nav.users'), href: ROUTES.ADMIN_USERS, icon: IconUsers },
])
</script>

<template>
  <BaseLayout :active-page="activePage" :nav-items="navItems">
    <slot />
  </BaseLayout>
</template>
