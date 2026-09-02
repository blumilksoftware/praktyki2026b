<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import HeaderEdit from '@/Components/Profiles/Edit/HeaderEdit.vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import AboutEdit from '@/Components/Profiles/Edit/AboutEdit.vue'
import ContactCardEdit from '@/Components/Profiles/Edit/ContactCardEdit.vue'
import { useI18n } from 'vue-i18n'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const goBack = () => {
  window.history.back()
}

const props = defineProps({
  company: { type: Object, default: () => ({}) },
  availableTags: { type: Array, default: () => [] },
})

const form = useForm({
  logo: null,
  tags: props.company.tags || [],
  description: props.company.description || '',
  website: props.company.website || '',
  phone: props.company.phone || '',
  street: props.company.street || '',
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
  })).post(ROUTES.COMPANY_PROFILE, {
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

  <AppLayout active-page="profile">
    <div class="flex flex-col gap-6 w-full">
      <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 flex flex-col items-center text-center">
        <HeaderEdit
          :name="company.name"
          :logo-url="company.logoUrl"
          class="flex flex-col items-center w-full md:px-10"
          @update:logo="form.logo = $event"
        />

        <div class="mt-4 w-full max-w-md">
          <DynamicMultiSelect
            id="company-tags"
            v-model="form.tags"
            :label="t('profiles.activeTags')"
            :options="availableTags"
            :max="10"
            :error="form.errors.tags"
            allow-custom
          />
        </div>
      </div>

      <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6">
        <ContactCardEdit
          v-model:email="form.email"
          v-model:website="form.website"
          v-model:phone="form.phone"
          v-model:street="form.street"
          v-model:postal-code="form.postalCode"
          v-model:city="form.city"
          :errors="form.errors"
        />
      </div>

      <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 sm:p-8">
        <AboutEdit id="company-description" v-model="form.description" />
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
  </AppLayout>
</template>
