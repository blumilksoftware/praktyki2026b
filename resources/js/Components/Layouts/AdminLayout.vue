<script setup>
import { computed } from 'vue'
import { IconHome, IconClipboard } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'
import BaseLayout from './BaseLayout.vue'

const props = defineProps({ activePage: { type: String, default: 'dashboard' } })
const { t } = useI18n()

const navItems = computed(() => [
  { key: 'dashboard',    label: t('admin.layout.nav.dashboard'),    href: '/admin',              icon: IconHome      },
  { key: 'applications', label: t('admin.layout.nav.applications'), href: '/admin/applications', icon: IconClipboard },
])
</script>

<template>
  <BaseLayout :active-page="activePage" :nav-items="navItems">
    <slot />
  </BaseLayout>
</template>
