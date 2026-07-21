<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { IconArrowLeft } from '@tabler/icons-vue'
import Header from '@/Components/Profiles/Header.vue'
import Tags from '@/Components/Profiles/Tags.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import About from '@/Components/Profiles/About.vue'
import ContactCard from '@/Components/Profiles/ContactCard.vue'
import Offers from '@/Components/Profiles/Offers.vue'
import Menu from '@/Components/Profiles/Menu.vue'
import { ROUTES } from '@/Helpers/routes'
import { useI18n } from 'vue-i18n'
import { IconSearch, IconClipboardText, IconUserCircle, IconUsersGroup } from '@tabler/icons-vue'

const { t } = useI18n()

const companyMenu = computed(() => [
  { label: t('profiles.company.myOffers'), href: ROUTES.OFFERS, icon: IconSearch },
  { label: t('profiles.company.candidateApplications'), href: ROUTES.APPLICATIONS, icon: IconClipboardText },
  { label: t('profiles.profile'), href: ROUTES.PROFILE, icon: IconUserCircle, isActive: true },
  { label: t('profiles.company.teamAndPermissions'), href: ROUTES.TEAM, icon: IconUsersGroup },
])

const goBack = () => {
  window.history.back()
}

const goToEdit = () => {
  window.location.href = ROUTES.PROFILE_EDIT
}

defineProps({
  company: { type: Object, default: () => ({}) },
})
</script>

<template>
  <Head :title="company.name" />
  
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar show-hamburger :menu-items="companyMenu" />
  
    <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-row justify-between items-center w-full mb-6">
        <a class="inline-flex items-center gap-2 text-additional text-sm transition hover:text-text cursor-pointer"
           @click="goBack"
        >
          <IconArrowLeft stroke="2.5" class="w-4 h-4" />
          {{ t('buttons.back') }}
        </a>
        <div>
          <Menu :items="companyMenu" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6">
          <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 flex flex-col items-center text-center">
            <Header
              :name="company.name"
              :logo-url="company.logoUrl"
              class="flex flex-col items-center w-full"
            />

            <div class="text-sm text-slate-500 mt-2 flex items-center gap-2">
              <Tags :tags="company.tags" />
            </div>

            <BaseButton
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
              :building-number="company.buildingNumber"
              :postal-code="company.postalCode"
              :city="company.city"
              :nip="company.nip"
            />
          </div>
        </div>

        <div class="flex flex-col gap-6 lg:col-span-2">
          <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
            <About :description="company.description" />
          </div>

          <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
            <Offers :offers="company.offers" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
