<script setup>
import { Head } from '@inertiajs/vue3'
import Header from '@/Components/Profiles/Header.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import Tags from '@/Components/Profiles/Tags.vue'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import About from '@/Components/Profiles/About.vue'
import ContactCard from '@/Components/Profiles/ContactCard.vue'
import Offers from '@/Components/Profiles/Offers.vue'
import ReviewList from '@/Components/Profiles/ReviewList.vue'
import VerifiedBadge from '@/Components/Common/VerifiedBadge.vue'
import { ROUTES } from '@/Helpers/routes'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const goToEdit = () => {
  window.location.href = ROUTES.PROFILE_EDIT
}

defineProps({
  company: {
    type: Object,
    default: () => ({}),
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <Head :title="company.name" />
  <AppLayout active-page="profile">
    <div class="min-h-screen flex flex-col bg-background">
      <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="flex flex-col gap-6">
            <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 flex flex-col items-center text-center">
              <Header
                :name="company.name"
                :logo-url="company.logoUrl"
                class="flex flex-col items-center w-full"
              />

              <div v-if="company.verification_status === 'verified'" class="mt-3 flex items-center justify-center gap-1.5">
                <VerifiedBadge :verified="true" size="md" />
                <span class="text-sm font-medium text-slate-700">{{ t('profiles.company.verified') }}</span>
              </div>

              <div class="text-sm text-slate-500 mt-3 flex items-center gap-2">
                <Tags :tags="company.tags" />
              </div>

              <BaseButton
                v-if="canEdit"
                class="w-full bg-[#0f172a] hover:bg-slate-800 text-white py-2.5 mt-6 text-sm font-semibold rounded-lg transition-all"
                @click="goToEdit"
              >
                {{ t('buttons.editProfile') }}
              </BaseButton>
            </div>

            <div class="h-full bg-white rounded-xl border border-secondary/20 shadow-sm p-6">
              <ContactCard
                :email="company.email"
                :phone="company.phone"
                :website="company.website"
                :street="company.street"
                :postal-code="company.postalCode"
                :city="company.city"
                :nip="company.nip"
              />
            </div>
          </div>

          <div class="flex flex-col gap-6 lg:col-span-2">
            <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
              <About
                :description="company.description"
                :empty-message="t('profiles.company.noDescription')"
              />
            </div>

            <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
              <Offers :offers="company.offers" />
            </div>

            <div v-if="company.reviews" class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
              <ReviewList :reviews="company.reviews" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
