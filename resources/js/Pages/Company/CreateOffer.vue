<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import OfferForm from '@/Components/Offer/OfferForm.vue'

defineProps({
  studyFields: { type: Array, required: true },
  universities: { type: Array, required: true },
  isCompanyVerified: { type: Boolean, default: false },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('company.offers.create.title')" />
  <AppLayout active-page="createOffer">
    <div class="mx-auto w-full max-w-6xl">
      <div class="bg-white shadow-sm mx-auto border border-border rounded-3xl w-full overflow-visible">
        <div class="px-6 sm:px-8 py-6 border-border border-b">
          <h1 class="font-semibold text-text text-2xl">
            {{ t('company.offers.create.heading') }}
          </h1>
        </div>

        <div class="px-4 sm:px-8 py-8">
          <OfferForm :study-fields="studyFields" :universities="universities" :is-company-verified="isCompanyVerified" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
