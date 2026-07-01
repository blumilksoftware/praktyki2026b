<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import GoogleSvg from '@/Components/Common/GoogleSvg.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import RegisterAccountTypeTabs from '@/Components/Auth/RegisterAccountTypeTabs.vue'

const { t } = useI18n()

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  university: '',
  terms: false,
})
const clientErrors = ref({})
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const validate = () => {
  const errors = {}
  if (!form.first_name.trim()) {
    errors.first_name = t('auth.register.validation.firstNameRequired')
  }
  if (!form.last_name.trim()) {
    errors.last_name = t('auth.register.validation.lastNameRequired')
  }
  if (!form.email.trim()) {
    errors.email = t('auth.register.validation.emailRequired')
  } else if (!emailPattern.test(form.email)) {
    errors.email = t('auth.register.validation.emailInvalid')
  }
  if (!form.password) {
    errors.password = t('auth.register.validation.passwordRequired')
  }
  if (!form.password_confirmation) {
    errors.password_confirmation = t('auth.register.validation.passwordConfirmationRequired')
  } else if (form.password !== form.password_confirmation) {
    errors.password_confirmation = t('auth.register.validation.passwordConfirmationMismatch')
  }
  if (!form.terms) {
    errors.terms = t('auth.register.validation.termsRequired')
  }
  clientErrors.value = errors
  return Object.keys(errors).length === 0
}
const fieldError = (field) => {
  return clientErrors.value[field] ?? form.errors[field]
}
const submit = () => {
  form.clearErrors()
  if (!validate()) {
    return
  }
  form.post('/register/student', {
    preserveScroll: true,
  })
}
const hasTermsError = computed(() => Boolean(fieldError('terms')))
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar class="shrink-0" />
    <AuthLayout class="flex-1 min-h-0">
      <Head :title="t('auth.register.title')" />

      <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 px-2 sm:px-4">
        <RegisterAccountTypeTabs active-tab="student" />

        <div class="text-center">
          <h1 class="text-3xl font-normal text-text sm:text-4xl">
            {{ t('auth.register.heading') }}
          </h1>
        </div>

        <form class="flex flex-col gap-4" novalidate @submit.prevent="submit">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <BaseInput
              id="first_name"
              v-model="form.first_name"
              :label="t('auth.register.firstName')"
              autocomplete="given-name"
              required
              :error="fieldError('first_name')"
            />
            <BaseInput
              id="last_name"
              v-model="form.last_name"
              :label="t('auth.register.lastName')"
              autocomplete="family-name"
              required
              :error="fieldError('last_name')"
            />
          </div>
          <BaseInput
            id="email"
            v-model="form.email"
            :label="t('auth.register.email')"
            type="email"
            autocomplete="email"
            required
            :error="fieldError('email')"
          />
          <BaseInput
            id="password"
            v-model="form.password"
            :label="t('auth.register.password')"
            type="password"
            autocomplete="new-password"
            required
            :error="fieldError('password')"
          />
          <BaseInput
            id="password_confirmation"
            v-model="form.password_confirmation"
            :label="t('auth.register.passwordConfirmation')"
            type="password"
            autocomplete="new-password"
            required
            :error="fieldError('password_confirmation')"
          />
          <BaseInput
            id="university"
            v-model="form.university"
            :label="t('auth.register.university')"
            autocomplete="organization"
            :error="fieldError('university')"
          />

          <div>
            <BaseCheckbox id="terms" v-model="form.terms">
              <span>
                {{ t('auth.register.termsPrefix') }}
                <a
                  href="#"
                  class="font-medium text-link hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                  @click.stop
                >
                  {{ t('auth.register.termsLink') }}
                </a>
              </span>
            </BaseCheckbox>
            <p
              v-if="hasTermsError"
              class="mt-1 text-sm text-error"
              role="alert"
            >
              {{ fieldError('terms') }}
            </p>
          </div>

          <BaseButton
            type="submit"
            class="mx-auto mt-1 w-fit px-12 py-3 text-sm sm:text-base"
            :disabled="form.processing"
          >
            {{ t('auth.register.submit') }}
          </BaseButton>
        </form>

        <div class="flex items-center gap-4">
          <div class="h-px flex-1 bg-text/20" />
          <span class="text-sm text-additional">
            {{ t('auth.register.or') }}
          </span>
          <div class="h-px flex-1 bg-text/20" />
        </div>

        <a
          href="/auth/google/redirect"
          class="mx-auto inline-flex items-center justify-center gap-2 rounded-full border border-border bg-white px-5 py-2.5 text-sm font-medium text-text shadow-sm transition hover:bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        >
          <GoogleSvg />
          {{ t('auth.register.google') }}
        </a>

        <div class="h-px bg-text/20" />

        <p class="text-center text-sm font-medium">
          {{ t('auth.register.hasAccount') }}
          <Link
            href="/login"
            class="text-link hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            {{ t('auth.register.loginLink') }}
          </Link>
        </p>
      </div>
    </AuthLayout>
  </div>
</template>
