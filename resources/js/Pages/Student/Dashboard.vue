<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import OnboardingBanner from '@/Components/Onboarding/OnboardingBanner.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'

const { t } = useI18n()
</script>

<template>
  <Head :title="t('student.layout.title')" />
  <StudentPanelLayout active-page="dashboard">
    <div class="flex flex-col gap-6">
      <OnboardingBanner />

      <ProfilePageCard :aria-label="t('student.dashboard.comingSoon.title')">
        <h1 class="font-semibold text-text text-lg">
          {{ t('student.dashboard.comingSoon.title') }}
        </h1>
        <p class="mt-3 text-additional text-sm">
          {{ t('student.dashboard.comingSoon.applications') }}
        </p>
        <p class="mt-2 text-additional text-sm">
          {{ t('student.dashboard.comingSoon.offersAndFavourites') }}
        </p>
      </ProfilePageCard>
    </div>
  </StudentPanelLayout>
</template>
