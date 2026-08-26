<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import GoogleSvg from '@/Components/Common/GoogleSvg.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import RegisterAccountTypeTabs from '@/Components/Auth/RegisterAccountTypeTabs.vue'
import { ROUTES } from '@/Helpers/routes'

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

const fieldError = (field) => form.errors[field]

const submit = () => {
  form.post(ROUTES.REGISTER_STUDENT, {
    preserveScroll: true,
  })
}

const hasTermsError = computed(() => Boolean(fieldError('terms')))
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar />
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
            :label="t('auth.register.optionalUniversity')"
            autocomplete="organization"
            :error="fieldError('university')"
          />

          <div>
            <BaseCheckbox id="terms" v-model="form.terms" required>
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
            class="mx-auto mt-1 w-fit px-12 py-3 text-base sm:text-lg font-medium"
            :disabled="form.processing"
          >
            {{ t('auth.register.submit') }}
          </BaseButton>
        </form>

        <div class="flex items-center gap-4">
          <div class="h-px flex-1 bg-text/20" />
          <span class="text-base sm:text-lg text-additional tracking-wide">
            {{ t('auth.register.or') }}
          </span>
          <div class="h-px flex-1 bg-text/20" />
        </div>

        <a
          :href="ROUTES.GOOGLE_AUTH"
          class="mx-auto flex justify-center items-center gap-2 w-fit rounded-lg border border-text/20 bg-white px-12 py-3 sm:py-2.5 text-base sm:text-lg font-medium text-text hover:bg-background transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 shadow-sm"
        >
          <GoogleSvg />
          {{ t('auth.register.google') }}
        </a>

        <div class="h-px bg-text/20" />

        <p class="w-full text-center text-base sm:text-lg font-medium">
          {{ t('auth.register.hasAccount') }}
          <Link
            :href="ROUTES.LOGIN"
            class="inline-block text-base sm:text-lg font-medium text-link hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded whitespace-nowrap"
          >
            {{ t('auth.register.loginLink') }}
          </Link>
        </p>
      </div>
    </AuthLayout>
  </div>
</template>
