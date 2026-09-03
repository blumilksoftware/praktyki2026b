<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconArrowLeft, IconExternalLink } from '@tabler/icons-vue'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import Header from '@/Components/Profiles/Header.vue'
import About from '@/Components/Profiles/About.vue'
import ContactCard from '@/Components/Profiles/ContactCard.vue'
import Faculties from '@/Components/Profiles/Faculties.vue'
import VerifiedBadge from '@/Components/Common/VerifiedBadge.vue'
import { useProfileBack } from '@/Composables/useProfileBack.js'

const props = defineProps({
  university: { type: Object, required: true },
  backUrl: { type: String, default: null },
})

const { t } = useI18n()

const { goBack } = useProfileBack(props.backUrl)

</script>

<template>
  <Head :title="university.name" />

  <AppLayout active-page="applications">
    <div class="mb-6">
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded text-additional text-sm transition hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        @click="goBack"
      >
        <IconArrowLeft stroke="2.5" class="h-4 w-4" aria-hidden="true" />
        {{ t('buttons.back') }}
      </button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="flex flex-col gap-6 self-start">
        <ProfilePageCard centered>
          <Header
            :name="university.name"
            :logo-url="university.logoUrl"
            class="w-full"
          />

          <div class="mt-3 flex items-center justify-center gap-1.5">
            <VerifiedBadge :verified="true" size="md" />
            <span class="font-medium text-text text-sm">
              {{ t('profiles.university.verified') }}
            </span>
          </div>
        </ProfilePageCard>

        <ProfilePageCard compact>
          <ContactCard
            :email="university.email"
            :phone="university.phone"
            :website="university.website"
            :street="university.street"
            :postal-code="university.postalCode"
            :city="university.city"
          />
        </ProfilePageCard>

        <ProfilePageCard v-if="university.externalFormUrl">
          <h2 class="font-bold text-text text-xl">
            {{ t('profiles.university.applicationForm') }}
          </h2>
          <p class="mt-2 text-additional text-sm">
            {{ t('profiles.university.applicationFormHint') }}
          </p>

          <a
            :href="university.externalFormUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-4 inline-flex w-fit items-center gap-2 rounded-lg bg-primary px-4 py-3 font-semibold text-sm text-white tracking-wide transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            {{ t('profiles.university.goToApplicationForm') }}
            <IconExternalLink stroke="2" class="h-4 w-4" aria-hidden="true" />
          </a>
        </ProfilePageCard>
      </div>

      <div class="flex flex-col gap-6 lg:col-span-2">
        <ProfilePageCard v-if="university.description">
          <About :description="university.description" />
        </ProfilePageCard>

        <ProfilePageCard>
          <h2 class="mb-6 font-bold text-text text-xl">
            {{ t('profiles.university.facultiesAndStudyFields') }}
          </h2>

          <Faculties :faculties="university.faculties" :limit="4" />
        </ProfilePageCard>
      </div>
    </div>
  </AppLayout>
</template>
