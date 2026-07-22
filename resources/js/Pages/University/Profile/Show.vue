<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { IconArrowLeft, IconUserCircle } from '@tabler/icons-vue'
import Header from '@/Components/Profiles/Header.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import Faculties from '@/Components/Profiles/Faculties.vue'
import ContactCard from '@/Components/Profiles/ContactCard.vue'
import Menu from '@/Components/Profiles/Menu.vue'
import Info from '@/Components/Profiles/Info.vue'
import { ROUTES } from '@/Helpers/routes'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const universityMenu = computed(() => [
  { label: t('profiles.profile'), href: ROUTES.PROFILE, icon: IconUserCircle, isActive: true },
])

const goBack = () => {
  window.history.back()
}

const goToEdit = () => {
  window.location.href = ROUTES.PROFILE_EDIT 
}

defineProps({
  university: { type: Object, default: () => ({}) },
})
</script>

<template>
  <Head :title="university.name" />
  
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar show-hamburger :menu-items="universityMenu" />
  
    <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-row justify-between items-center w-full mb-6">
        <a class="inline-flex items-center gap-2 text-additional text-sm transition hover:text-text cursor-pointer"
           @click="goBack"
        >
          <IconArrowLeft stroke="2.5" class="w-4 h-4" />
          {{ t('buttons.back') }}
        </a>
        <div>
          <Menu :items="universityMenu" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6">
          <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 flex flex-col items-center text-center">
            <Header
              :name="university.name"
              :logo-url="university.logoUrl"
              class="flex flex-col items-center w-full"
            />

            <BaseButton
              class="w-full bg-[#0f172a] hover:bg-slate-800 text-white py-2.5 mt-6 text-sm font-semibold rounded-lg transition-all"
              @click="goToEdit"
            >
              {{ t('buttons.editProfile') }}
            </BaseButton>
          </div>

          <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6">
            <ContactCard
              :email="university.email"
              :phone="university.phone"
              :website="university.website"
              :street="university.street"
              :postal-code="university.postalCode"
              :city="university.city"
            />
          </div>

          <div v-if="university.domain || university.externalFormUrl" class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-5">
              {{ t('profiles.university.recruitmentAndSystem') }}
            </h3>
            
            <Info 
              :domain="university.domain" 
              :external-form-url="university.externalFormUrl" 
            />
          </div>
        </div>

        <div class="flex flex-col gap-6 lg:col-span-2">
          <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8 h-fit">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
              {{ t('profiles.university.facultiesAndStudyFields') }}
            </h3>
            
            <Faculties :faculties="university.faculties" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
