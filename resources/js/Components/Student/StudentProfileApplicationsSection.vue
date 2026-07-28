<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ProfileSectionCard from '@/Components/Profile/ProfileSectionCard.vue'
import { ROUTES } from '@/Helpers/routes'

defineProps({
  applications: { type: Array, default: () => [] },
})
const { t } = useI18n()
</script>

<template>
  <ProfileSectionCard :title="t('student.profile.applications.title')">
    <p class="text-additional text-sm">
      {{ t('student.profile.applications.historyHint') }}
    </p>
    <Link
      :href="ROUTES.STUDENT_APPLICATIONS"
      class="mt-2 inline-flex text-primary text-sm font-medium hover:underline"
    >
      {{ t('student.applications.openHistory') }}
    </Link>
  </ProfileSectionCard>
</template>
