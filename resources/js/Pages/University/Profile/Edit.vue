<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { IconArrowLeft, IconUserCircle } from '@tabler/icons-vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import HeaderEdit from '@/Components/Profiles/Edit/HeaderEdit.vue'
import ContactCardEdit from '@/Components/Profiles/Edit/ContactCardEdit.vue'
import InfoEdit from '@/Components/Profiles/Edit/InfoEdit.vue'
import AboutEdit from '@/Components/Profiles/Edit/AboutEdit.vue'
import { ROUTES } from '@/Helpers/routes'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

const { t } = useI18n()

const goBack = () => {
  window.history.back()
}

const props = defineProps({
  university: { type: Object, required: true },
})

const isDomainLocked = computed(() => {
  return !!props.university.domain
})

const form = useForm({
  logo: null,
  description: props.university.description || '',
  website: props.university.website || '',
  phone: props.university.phone || '',
  street: props.university.street || '',
  postalCode: props.university.postalCode || '',
  city: props.university.city || '',
  domain: props.university.domain || '',
  external_form_url: props.university.externalFormUrl || '',
})

const statusMessage = ref(null)

const submit = () => {
  statusMessage.value = null

  form.transform((data) => ({
    ...data,
    _method: 'patch',
  })).post(ROUTES.UNIVERSITY_PROFILE, {
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
  <Head :title="university.name" />

  <div class="min-h-screen flex flex-col bg-background">
    <AppLayout active-page="profile">
      <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col gap-6 w-full">
      <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 flex flex-col items-center text-center">
        <HeaderEdit
          :name="university.name"
          :logo-url="university.logoUrl"
          class="flex flex-col items-center w-full md:px-10"
          @update:logo="form.logo = $event"
        />
      </div>

      <InfoEdit
        v-model:domain="form.domain"
        v-model:external-form-url="form.external_form_url"
        :is-domain-locked="isDomainLocked"
        :errors="form.errors"
      />

      <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6">
        <ContactCardEdit
          v-model:website="form.website"
          v-model:phone="form.phone"
          v-model:street="form.street"
          v-model:postal-code="form.postalCode"
          v-model:city="form.city"
          :errors="form.errors"
        />
      </div>

      <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
        <AboutEdit id="university-description" v-model="form.description" />
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

        <div class="flex flex-wrap justify-end items-center gap-4 w-full">
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
    </AppLayout>
  </div>
</template>
