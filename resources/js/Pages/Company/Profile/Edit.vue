<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { IconArrowLeft } from '@tabler/icons-vue'
import HeaderEdit from '@/Components/Profiles/Edit/HeaderEdit.vue'
import Tags from '@/Components/Profiles/Tags.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import About from '@/Components/Profiles/About.vue'
import ContactCard from '@/Components/Profiles/ContactCard.vue'
import Offers from '@/Components/Profiles/Offers.vue'
import Sidebar from '@/Components/Profiles/Sidebar.vue'
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
    <BaseNavbar class="shrink-0" />

    <div class="flex-1 w-full max-w-screen-2xl mx-auto bg-background px-6 md:px-12 lg:px-16 py-6 md:py-8">
      <div class="flex flex-row justify-between items-center w-full mb-6">
        <div class="w-auto lg:w-72 shrink-0">
          <BaseButton
            class="bg-secondary hover:bg-secondary/90 text-white px-5 py-2 flex items-center justify-center lg:justify-start w-fit text-sm font-semibold rounded-xl shadow-sm transition-all"
            @click="goBack"
          >
            <IconArrowLeft stroke="2.5" class="w-4 h-4 mr-2" />
            {{ t('buttons.back') }}
          </BaseButton>
        </div>

        <div>
          <BaseButton
            class="bg-secondary hover:bg-secondary/90 text-white px-6 py-2 text-sm font-semibold rounded-xl shadow-sm transition-all"
            @click="goToEdit"
          >
            {{ t('buttons.editProfile') }}
          </BaseButton>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-8 items-start">
        <div class="w-full lg:w-72 shrink-0">
          <Sidebar :items="companyMenu" />
        </div>

        <div class="flex-1 w-full bg-white rounded-xl border border-secondary shadow-sm px-6 py-10 sm:px-12 sm:py-12 relative">
          <HeaderEdit
            :name="company.name"
            :logo-url="company.logoUrl"
          />

            TAGI
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mt-12">
            <div class="lg:col-span-2">
              <About
                :description="company.description"
                :nip="company.nip"
              />
            </div>

            <div class="lg:col-span-1 lg:col-start-3 lg:row-span-2">
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

            <div class="lg:col-span-2 flex flex-col gap-10">
              <hr class="border-border/60">
              <Offers :offers="company.offers" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
