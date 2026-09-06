<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AccountSettingsSection from '@/Components/Profile/AccountSettingsSection.vue'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import { ROUTES } from '@/Helpers/routes'

defineProps({
  email: { type: String, required: true },
  emailVerifiedAt: { type: String, default: null },
  pendingEmail: { type: String, default: null },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('admin.profile.account.title')" />
  <AppLayout active-page="settings">
    <AccountSettingsSection
      :email="email"
      :email-verified-at="emailVerifiedAt"
      :pending-email="pendingEmail"
      i18n-prefix="admin.profile"
      :password-update-route="ROUTES.ADMIN_PASSWORD_UPDATE"
      :email-update-route="ROUTES.ADMIN_EMAIL_UPDATE"
      :show-delete-account="false"
    />
  </AppLayout>
</template>
