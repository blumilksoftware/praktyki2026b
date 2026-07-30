<script setup>
import { useI18n } from 'vue-i18n'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'

defineProps({
  linkedStudents: {
    type: Number,
    default: 0,
  },
  applicationsSubmitted: {
    type: Number,
    default: 0,
  },
  acceptedPlacements: {
    type: Number,
    default: 0,
  },
})

const { t } = useI18n()
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <ProfilePageCard>
      <div class="p-5">
        <div class="text-sm font-medium text-additional">
          {{ t('university.dashboard.stats.linkedStudents') }}
        </div>
        <div class="text-3xl font-bold text-text mt-2">
          {{ linkedStudents }}
        </div>
      </div>
    </ProfilePageCard>

    <ProfilePageCard>
      <div class="p-5">
        <div class="text-sm font-medium text-additional">
          {{ t('university.dashboard.stats.applicationsSubmitted') }}
        </div>
        <div class="text-3xl font-bold text-text mt-2">
          {{ applicationsSubmitted }}
        </div>
      </div>
    </ProfilePageCard>

    <ProfilePageCard>
      <div class="p-5">
        <div class="text-sm font-medium text-additional">
          {{ t('university.dashboard.stats.acceptedPlacements') }}
        </div>
        <div class="text-3xl font-bold text-primary mt-2">
          {{ acceptedPlacements }}
        </div>
      </div>
    </ProfilePageCard>
  </div>
</template>
