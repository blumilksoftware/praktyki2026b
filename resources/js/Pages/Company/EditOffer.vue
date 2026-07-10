<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OfferForm from '@/Components/Offer/OfferForm.vue'

defineProps({
  studyFields: { type: Array, required: true },
  universities: { type: Array, required: true },
  offer: { type: Object, required: true },
})

const { t } = useI18n()

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/company/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/company/profile', icon: IconUser },
]
</script>

<template>
  <Head :title="t('company.offers.edit.title')" />
  <BaseLayout active-page="dashboard" :nav-items="navItems">
    <div class="mx-auto w-full max-w-2xl px-4 py-8">
      <h1 class="mb-6 text-2xl font-semibold text-text">
        {{ t('company.offers.edit.heading', { title: offer.title }) }}
      </h1>
      <OfferForm :study-fields="studyFields" :universities="universities" :offer="offer" />
    </div>
  </BaseLayout>
</template>
