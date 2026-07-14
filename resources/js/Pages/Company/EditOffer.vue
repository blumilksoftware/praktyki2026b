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
    <div class="mx-auto w-full max-w-4xl px-4 py-8">
      <div class="overflow-visible rounded-3xl border border-border bg-white shadow-sm w-full mx-auto">
        <div class="border-b border-border px-6 py-6 sm:px-8">
          <h1 class="text-2xl font-semibold text-text">
            {{ t('company.offers.edit.title') }}
          </h1>
        </div>

        <div class="px-4 py-8 sm:px-8">
          <OfferForm :study-fields="studyFields" :universities="universities" :offer="offer" />
        </div>
      </div>
    </div>
  </BaseLayout>
</template>
