<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconBriefcase, IconHeart, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OffersList from '@/Components/Offer/OffersList.vue'
import { ROUTES } from '@/Helpers/routes'
import { useStudentFavorites } from '@/composables/useStudentFavorites'

const props = defineProps({
  offers: { type: Array, default: () => [] },
})

const { t } = useI18n()
const { favoriteIds, toggleFavorite } = useStudentFavorites()

const offersCount = computed(() => props.offers.length)
const favoriteCount = computed(() => favoriteIds.value.length)

const navItems = computed(() => [
  { key: 'dashboard', label: t('student.nav.dashboard'), href: ROUTES.STUDENT_DASHBOARD, icon: IconHome },
  { key: 'profile', label: t('student.nav.profile'), href: ROUTES.STUDENT_PROFILE, icon: IconUser },
  { key: 'offers', label: t('student.nav.offers'), href: ROUTES.STUDENT_OFFERS, icon: IconBriefcase },
  { key: 'favorites', label: t('student.nav.favorites'), href: ROUTES.STUDENT_FAVORITES, icon: IconHeart },
])
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
  </BaseLayout>
</template>