<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { IconArrowLeft } from '@tabler/icons-vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import HeaderEdit from '@/Components/Profiles/Edit/HeaderEdit.vue'
import TagsEdit from '@/Components/Profiles/Edit/TagsEdit.vue'
import AboutEdit from '@/Components/Profiles/Edit/AboutEdit.vue'
import OffersEdit from '@/Components/Profiles/Edit/OffersEdit.vue'
import ContactCardEdit from '@/Components/Profiles/Edit/ContactCardEdit.vue'
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

const props = defineProps({
  company: { type: Object, default: () => ({}) },
})

const form = useForm({
  logo: null,
  tags: props.company.tags || [],
  description: props.company.description || '',
  website: props.company.website || '',
  phone: props.company.phone || '',
  street: props.company.street || '',
  buildingNumber: props.company.buildingNumber || '',
  postalCode: props.company.postalCode || '',
  city: props.company.city || '',
  nip: props.company.nip || '',
})

const statusMessage = ref(null)

const submit = () => {
  statusMessage.value = null

  form.transform((data) => ({
    ...data,
    _method: 'patch',
  })).post('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      statusMessage.value = t('profiles.edit.success_message')
      
      setTimeout(() => {
        statusMessage.value = null
      }, 5000)
    },
  })
}

const saveAndGoToOffers = () => {
  statusMessage.value = null

  form.transform((data) => ({
    ...data,
    _method: 'patch',
  })).post('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      router.get(ROUTES.OFFERS)
    },
  })
}
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
      </div>

      <div class="flex flex-col lg:flex-row gap-8 items-start">
        <div class="w-full lg:w-72 shrink-0">
          <Sidebar :items="companyMenu" />
        </div>

        <div class="flex-1 w-full bg-white rounded-xl border border-secondary shadow-sm px-6 py-10 sm:px-12 sm:py-12 relative">
          <HeaderEdit
            :name="company.name"
            :logo-url="company.logoUrl"
            @update:logo="form.logo = $event" 
          />
          <TagsEdit
            v-model="form.tags"
            :available-tags-pool="company.availableTagsPool"
            :max-tags="20"
          />
          
          <div class="flex flex-col gap-10 mt-12">
            <AboutEdit v-model="form.description" />

            <ContactCardEdit
              v-model:website="form.website"
              v-model:phone="form.phone"
              v-model:street="form.street"
              v-model:building-number="form.buildingNumber"
              v-model:postal-code="form.postalCode"
              v-model:city="form.city"
              :nip="company.nip"    
              :errors="form.errors" 
            />
            
            <hr class="border-border/60">
            
            <OffersEdit :offers="company.offers" />

            <div class="flex flex-col items-center gap-5 pt-4 pb-4 mt-2">
              <div v-if="form.hasErrors || statusMessage" class="flex flex-col items-center w-full min-h-6">
                <div
                  v-if="form.hasErrors"
                  class="bg-error/10 border border-error w-fit rounded-lg px-6 py-3 flex flex-col items-center justify-center shadow-sm gap-1.5"
                >
                  <span class="text-error text-sm sm:text-base font-medium text-center">
                    {{ t('validation.fillRequiredFields') }}
                  </span>
                </div>

                <div
                  v-else-if="statusMessage"
                  class="w-full max-w-md bg-success/10 border border-success/40 rounded-lg px-4 py-3 flex items-center justify-center shadow-sm"
                >
                  <span class="text-success text-sm sm:text-base font-medium text-center leading-snug">
                    {{ statusMessage }}
                  </span>
                </div>
              </div>

              <!-- Sekcja przycisków -->
              <div class="flex flex-col gap-6 w-full mt-4">
                <!-- Przycisk Górny -->
                <div class="flex justify-center w-full">
                  <BaseButton
                    variant="primary"
                    class="bg-gray-50 hover:bg-gray-100 text-secondary border border-gray-200 px-6 py-2.5 text-sm font-semibold rounded-xl shadow-sm transition-all"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                    @click="saveAndGoToOffers"
                  >
                    {{ t('buttons.saveAndGoToOffers') }}
                  </BaseButton>
                </div>

                <hr class="border-border/60 w-full">

                <div class="flex flex-wrap justify-center items-center gap-4 w-full">
                  <BaseButton
                    class="bg-primary hover:bg-primary/90 text-white px-10 py-2.5 text-sm font-semibold rounded-xl shadow-sm transition-all"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                    @click="submit"
                  >
                    {{ form.processing ? t('buttons.saving') : t('buttons.save') }}
                  </BaseButton>

                  <BaseButton
                    variant="secondary"
                    @click="goBack"
                  >
                    {{ t('buttons.cancel') }}
                  </BaseButton>
                </div>
              </div>              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
