<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconArrowLeft } from '@tabler/icons-vue'
import Header from '@/Components/Profiles/Header.vue'
import Tags from '@/Components/Profiles/Tags.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import About from '@/Components/Profiles/About.vue'
import ContactCard from '@/Components/Profiles/ContactCard.vue'
import Offers from '@/Components/Profiles/Offers.vue'
import Partners from '@/Components/Profiles/Partners.vue'
import VerifiedBadge from '@/Components/Common/VerifiedBadge.vue'

const props = defineProps({
  company: { type: Object, required: true },
})

const { t } = useI18n()
const page = usePage()

const goBack = () => {
  window.history.back()
}

const isStudent = computed(() => page.props.auth?.user?.role === 'student')  
</script>

<template>
  <Head :title="company.name" />
  
  <div class="min-h-screen flex flex-col bg-slate-50/50">
    <BaseNavbar />
  
    <div class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-row justify-between items-center w-full mb-6">
        <a class="inline-flex items-center gap-2 text-additional text-sm transition hover:text-text cursor-pointer"
           @click="goBack"
        >
          <IconArrowLeft stroke="2.5" class="w-4 h-4" />
          {{ t('buttons.back') }}
        </a>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6">
          <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col items-center text-center">
            <Header
              :name="company.name"
              :logo-url="company.logoUrl"
              class="flex flex-col items-center w-full"
            />

            <div class="mt-3 flex items-center justify-center gap-1.5">
              <VerifiedBadge :verified="true" size="md" />
              <span class="text-sm font-medium text-text">
                {{ t('profiles.company.verified') }}
              </span>
            </div>

            <div v-if="company.tags?.length" class="text-sm text-additional mt-3 flex items-center gap-2">
              <Tags :tags="company.tags" />
            </div>
          </div>

          <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <ContactCard
              :email="company.email"
              :phone="company.phone"
              :website="company.website"
              :street="company.street"
              :postal-code="company.postalCode"
              :city="company.city"
              :nip="company.nip"
            />
          </div>

          <div v-if="company.partners?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <Partners :partners="company.partners" />
          </div>
        </div>

        <div class="flex flex-col gap-6 lg:col-span-2">
          <div v-if="company.description" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <About :description="company.description" />
          </div>

          <div v-if="company.offers?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <Offers :offers="company.offers" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
