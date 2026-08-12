<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import { ROUTES } from '@/Helpers/routes'

defineProps({
  admin: { type: Object, required: true },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('admin.profile.title')" />
  <AppLayout active-page="profile">
    <div class="mx-auto w-full max-w-2xl">
      <h1 class="font-semibold text-text text-2xl">{{ admin.firstName }} {{ admin.lastName }}</h1>
      <p class="mt-1 text-additional text-sm">{{ admin.email }}</p>

      <Link
        :href="ROUTES.PROFILE_EDIT"
        class="mt-6 inline-flex items-center rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90"
      >
        {{ t('buttons.editProfile') }}
      </Link>
    </div>
  </AppLayout>
</template>
