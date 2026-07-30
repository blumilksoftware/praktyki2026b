<script setup>
import { computed } from 'vue'
import { IconHome, IconClipboard, IconUser } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'
import BaseLayout from './BaseLayout.vue'
import { ROUTES } from '@/Helpers/routes'

defineProps({ activePage: { type: String, default: 'dashboard' } })
const { t } = useI18n()

const navItems = computed(() =>[
  { key: 'dashboard', label: t('company.layout.nav.dashboard'), href: ROUTES.COMPANY_DASHBOARD, icon: IconHome },
  { key: 'profile', label: t('company.layout.nav.profile'), href: ROUTES.COMPANY_PROFILE, icon: IconUser },
])
</script>

<template>
  <BaseLayout :active-page="activePage" :nav-items="navItems">
    <slot />
  </BaseLayout>
</template>
