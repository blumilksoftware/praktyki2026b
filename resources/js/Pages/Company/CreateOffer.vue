<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconHome, IconUser } from '@tabler/icons-vue'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import OfferForm from '@/Components/Offer/OfferForm.vue'

defineProps({
  studyFields: { type: Array, required: true },
  universities: { type: Array, required: true },
})

const { t } = useI18n()

const navItems = [
  { key: 'dashboard', label: 'Dashboard', href: '/company/dashboard', icon: IconHome },
  { key: 'profile', label: 'Profile', href: '/company/profile', icon: IconUser },
]
</script>

<template>
  <Head :title="t('company.offers.create.title')" />
  <BaseLayout active-page="dashboard" :nav-items="navItems">
    <div class="mx-auto px-4 py-8 w-full max-w-6xl">
      <div class="bg-white shadow-sm mx-auto border border-border rounded-3xl w-full overflow-visible">
        <div class="px-6 sm:px-8 py-6 border-border border-b">
          <h1 class="font-semibold text-text text-2xl">
            {{ t('company.offers.create.heading') }}
          </h1>
        </div>

        <div class="px-4 sm:px-8 py-8">
          <OfferForm :study-fields="studyFields" :universities="universities" />
        </div>
      </div>
    </div>
  </BaseLayout>
</template>
