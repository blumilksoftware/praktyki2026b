<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLogo from '@/Components/Navigation/BaseLogo.vue'

const props = defineProps({
  status: { type: Number, required: true },
  role: { type: String, default: null },
})

const { t } = useI18n()

const homeUrl = computed(() => {
  if (props.role === 'superAdmin') return '/admin/dashboard'
  if (props.role === 'companyAdmin' || props.role === 'companyMember') return '/company/dashboard'
  if (props.role === 'universityAdmin' || props.role === 'universityMember') return '/university/dashboard'
  return '/'
})
</script>

<template>
  <Head :title="t(`errors.${status}.title`)" />
  <div class="flex flex-col min-h-screen bg-white text-text">
    <header class="bg-primary shadow-md">
      <div class="flex items-center px-4 md:px-6 py-4">
        <BaseLogo />
      </div>
    </header>

    <main class="flex flex-1 items-center justify-center px-4">
      <div class="text-center">
        <p class="font-bold text-primary text-8xl" aria-hidden="true">
          {{ status }}
        </p>
        <h1 class="mt-4 font-semibold text-text text-2xl">
          {{ t(`errors.${status}.title`) }}
        </h1>
        <p class="mt-2 text-additional text-sm">
          {{ t(`errors.${status}.description`) }}
        </p>
        <a
          :href="homeUrl"
          class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 mt-8 px-5 py-2.5 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60 focus-visible:ring-offset-2 font-medium text-white text-sm transition"
        >
          {{ t('errors.backToHome') }}
        </a>
      </div>
    </main>
  </div>
</template>
