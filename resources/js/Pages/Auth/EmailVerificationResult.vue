<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
})

const { t } = useI18n()

const isSuccess = computed(() => props.status === 'success' || props.status === 'already_verified')

const heading = computed(() => t(`auth.verification.result.${props.status}.heading`))
const message = computed(() => t(`auth.verification.result.${props.status}.message`))
</script>

<template>
  <Head :title="t('auth.verification.result.title')" />

  <header class="top-4 right-4 z-10 absolute shadow p-2 rounded-lg">
    <LanguageSwitcher :mobile="true" variant="light" />
  </header>

  <main class="flex flex-col justify-center items-center bg-slate-50 px-6 py-16 min-h-screen">
    <section class="bg-white shadow-sm p-8 md:p-12 rounded-2xl w-full max-w-3xl">
      <div class="flex justify-center items-center p-4 rounded-lg w-full">
        <img src="/logo.svg" alt="Applikuj" class="mb-8 w-auto h-10 md:h-12 brightness-0">
      </div>

      <div class="space-y-8">
        <div class="flex justify-center">
          <div
            class="flex justify-center items-center rounded-full w-16 h-16"
            :class="isSuccess ? 'bg-green-100' : 'bg-red-100'"
          >
            <svg v-if="isSuccess" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-600" fill="none"
                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>

            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600" fill="none"
                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>

        <h1 class="font-semibold text-text text-4xl text-center">
          {{ heading }}
        </h1>

        <p class="text-text text-base text-center leading-7">
          {{ message }}
        </p>

        <hr class="border-slate-200">

        <div class="flex justify-center">
          <Link
            href="/login"
            class="bg-primary hover:bg-primary/90 px-4 py-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 w-fit font-semibold text-white text-sm text-center tracking-wide transition"
          >
            {{ t('auth.verification.result.backToLogin') }}
          </Link>
        </div>
      </div>
    </section>
  </main>
</template>
