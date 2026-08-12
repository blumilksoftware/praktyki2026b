<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentAccountSettingsSection from '@/Components/Student/StudentAccountSettingsSection.vue'
import AppLayout from "@/Components/Layouts/AppLayout.vue";

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
    <StudentAccountSettingsSection
      :email="email"
      :email-verified-at="emailVerifiedAt"
      :pending-email="pendingEmail"
    />
  </AppLayout>
</template>
