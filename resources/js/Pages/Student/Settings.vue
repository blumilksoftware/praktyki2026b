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
  <Head :title="t('student.profile.account.title')" />
  <AppLayout active-page="settings">
    <AccountSettingsSection
      :email="email"
      :email-verified-at="emailVerifiedAt"
      :pending-email="pendingEmail"
      i18n-prefix="student.profile"
      :password-update-route="ROUTES.STUDENT_PASSWORD_UPDATE"
      :email-update-route="ROUTES.STUDENT_EMAIL_UPDATE"
      :account-delete-route="ROUTES.STUDENT_ACCOUNT_DELETE"
    />
  </AppLayout>
</template>
