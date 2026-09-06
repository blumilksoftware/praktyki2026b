<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import Header from '@/Components/Profiles/Header.vue'

defineProps({
  admin: { type: Object, required: true },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('admin.profile.title')" />
  <AppLayout active-page="profile">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="flex flex-col gap-6">
        <ProfilePageCard centered>
          <Header :name="`${admin.firstName} ${admin.lastName}`" />
          <p class="mt-2 text-additional text-sm">{{ admin.email }}</p>
        </ProfilePageCard>
      </div>

      <div class="flex flex-col gap-6 lg:col-span-2">
        <ProfilePageCard>
          <h2 class="font-semibold text-text text-xl">{{ t('admin.profile.administratorHeading') }}</h2>
          <p class="mt-3 text-additional text-sm leading-6">
            {{ t('admin.profile.administratorDescription') }}
          </p>
        </ProfilePageCard>
      </div>
    </div>
  </AppLayout>
</template>
