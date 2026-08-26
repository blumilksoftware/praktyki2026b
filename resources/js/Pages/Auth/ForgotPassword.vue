<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import AuthErrorDisplay from '@/Components/Auth/AuthErrorDisplay.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(ROUTES.FORGOT_PASSWORD, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}

const authError = computed(() => form.errors.email)
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar />

    <AuthLayout class="flex-1 min-h-0">
      <Head :title="t('auth.forgotPassword.title')" />

      <div class="mx-auto flex w-full max-w-md flex-col gap-6 px-2 sm:px-4">
        <div class="text-center">
          <h1 class="text-3xl font-normal text-text sm:text-4xl">
            {{ t('auth.forgotPassword.heading') }}
          </h1>
          <p class="mt-3 text-base text-additional">
            {{ t('auth.forgotPassword.description') }}
          </p>
        </div>

        <AuthErrorDisplay :error="authError" :email="form.email" />

        <form class="flex flex-col gap-4" novalidate @submit.prevent="submit">
          <BaseInput
            id="email"
            v-model="form.email"
            :label="t('auth.forgotPassword.email')"
            type="email"
            autocomplete="email"
            required
            :error="authError"
          />

          <BaseButton
            type="submit"
            class="mx-auto mt-1 w-fit px-12 py-3 text-base sm:text-lg font-medium"
            :disabled="form.processing"
          >
            {{ t('auth.forgotPassword.submit') }}
          </BaseButton>
        </form>

        <div class="h-px bg-text/20" />

        <p class="w-full text-center">
          <Link
            :href="ROUTES.LOGIN"
            class="inline-block text-base sm:text-lg font-medium text-link hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded whitespace-nowrap"
          >
            {{ t('auth.login.waiting.backToLogin') }}
          </Link>
        </p>
      </div>
    </AuthLayout>
  </div>
</template>
