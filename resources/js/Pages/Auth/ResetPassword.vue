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

const props = defineProps({
  token: { type: String, required: true },
  email: { type: String, default: '' },
  valid: { type: Boolean, default: true },
})

const { t } = useI18n()

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(ROUTES.RESET_PASSWORD_STORE, {
    preserveScroll: true,
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}

const authError = computed(() => form.errors.email)
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar />

    <AuthLayout class="flex-1 min-h-0">
      <Head :title="t('auth.resetPassword.title')" />

      <div class="mx-auto flex w-full max-w-md flex-col gap-6 px-2 sm:px-4">
        <div class="text-center">
          <h1 class="text-3xl font-normal text-text sm:text-4xl">
            {{ valid ? t('auth.resetPassword.heading') : t('auth.resetPassword.expired.heading') }}
          </h1>
        </div>

        <template v-if="!valid">
          <p class="text-center text-base text-text">
            {{ t('auth.resetPassword.expired.message') }}
          </p>

          <Link
            :href="ROUTES.FORGOT_PASSWORD"
            class="mx-auto mt-1 w-fit rounded-lg bg-primary px-12 py-3 text-base sm:text-lg font-medium text-white tracking-wide transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            {{ t('auth.resetPassword.expired.requestNew') }}
          </Link>
        </template>

        <AuthErrorDisplay v-if="valid" :error="authError" :email="form.email" />

        <form v-if="valid" class="flex flex-col gap-4" novalidate @submit.prevent="submit">
          <BaseInput
            id="email"
            v-model="form.email"
            :label="t('auth.resetPassword.email')"
            type="email"
            autocomplete="email"
            required
            :invalid="Boolean(authError)"
          />
          <BaseInput
            id="password"
            v-model="form.password"
            :label="t('auth.resetPassword.password')"
            type="password"
            autocomplete="new-password"
            required
            :error="form.errors.password"
          />
          <BaseInput
            id="password_confirmation"
            v-model="form.password_confirmation"
            :label="t('auth.resetPassword.passwordConfirmation')"
            type="password"
            autocomplete="new-password"
            required
            :error="form.errors.password_confirmation"
          />

          <BaseButton
            type="submit"
            class="mx-auto mt-1 w-fit px-12 py-3 text-base sm:text-lg font-medium"
            :disabled="form.processing"
          >
            {{ t('auth.resetPassword.submit') }}
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
