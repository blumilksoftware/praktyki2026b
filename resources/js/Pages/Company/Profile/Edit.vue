<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { IconArrowLeft } from '@tabler/icons-vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import HeaderEdit from '@/Components/Profiles/Edit/HeaderEdit.vue'
import TagsEdit from '@/Components/Profiles/Edit/TagsEdit.vue'
import AboutEdit from '@/Components/Profiles/Edit/AboutEdit.vue'
import ContactCardEdit from '@/Components/Profiles/Edit/ContactCardEdit.vue'
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
      statusMessage.value = t('profiles.edit.successMessage')
      
      setTimeout(() => {
        statusMessage.value = null
      }, 5000)
    },
  })
}
</script>

<template>
  <Head :title="company.name" />
  
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar show-hamburger :menu-items="companyMenu" />
  
    <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-row justify-between items-center w-full mb-6">
        <BaseButton
          variant="primary"
          class="flex items-center gap-2 text-sm font-semibold transition-all px-4 py-2 rounded-xl"
          @click="goBack"
        >
          <IconArrowLeft stroke="2.5" class="w-4 h-4 mr-2" />
          {{ t('buttons.back') }}
        </BaseButton>
        <div>
          <Menu :items="companyMenu" />
        </div>
      </div>

      <div class="flex flex-col gap-6 w-full">
        <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 flex flex-col items-center text-center">
          <HeaderEdit
            :name="company.name"
            :logo-url="company.logoUrl"
            class="flex flex-col items-center w-full"
            @update:logo="form.logo = $event"
          />

          <div class="text-sm text-slate-500 mt-2 flex items-center gap-2 w-full justify-center">
            <TagsEdit v-model="form.tags" :max-tags="10" />
          </div>
        </div>

        <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6">
          <ContactCardEdit
            v-model:email="form.email"
            v-model:website="form.website"
            v-model:phone="form.phone"
            v-model:street="form.street"
            v-model:building-number="form.buildingNumber"
            v-model:postal-code="form.postalCode"
            v-model:city="form.city"
            :nip="company.nip"
            :errors="form.errors"
          />
        </div>

        <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
          <AboutEdit v-model="form.description" />
        </div>

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

          <div class="flex flex-wrap justify-center items-center gap-4 w-full">
            <BaseButton
              variant="secondary"
              @click="goBack"
            >
              {{ t('buttons.cancel') }}
            </BaseButton>
              
            <BaseButton
              class="bg-primary hover:bg-primary/90 text-white px-10 py-2.5 text-sm font-semibold rounded-xl shadow-sm transition-all"
              :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
              :disabled="form.processing"
              @click="submit"
            >
              {{ form.processing ? t('buttons.saving') : t('buttons.save') }}
            </BaseButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
