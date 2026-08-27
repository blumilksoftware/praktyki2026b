<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OfferForm from '@/Components/Offer/OfferForm.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'

defineProps({
  studyFields: { type: Array, required: true },
  universities: { type: Array, required: true },
  offer: { type: Object, required: true },
  isCompanyVerified: { type: Boolean, default: false },
  cities: { type: Array, default: () => [] },
})

const { t } = useI18n()
const companyMenu = useCompanyPanelMenu('offers')
</script>

<template>
  <Head :title="t('company.offers.edit.title')" />
  <BaseLayout
    active-page="offers"
    :nav-items="companyMenu"
    :navigation-buttons="companyMenu"
    navigation-variant="default"
  >
    <div class="mx-auto w-full max-w-6xl">
      <div class="bg-white shadow-sm mx-auto border border-border rounded-3xl w-full overflow-visible">
        <div class="px-6 sm:px-8 py-6 border-border border-b">
          <h1 class="font-semibold text-text text-2xl">
            {{ t('company.offers.edit.title') }}
          </h1>
        </div>

        <div class="px-4 sm:px-8 py-8">
          <OfferForm :study-fields="studyFields" :universities="universities" :offer="offer" :is-company-verified="isCompanyVerified" :cities="cities" />
        </div>
      </div>
    </div>
  </BaseLayout>
</template>
