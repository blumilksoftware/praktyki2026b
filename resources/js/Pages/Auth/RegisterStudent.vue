<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'

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

const submit = () => {
  form.post('/register/student', {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('auth.register.title')" />

  <AuthLayout :title="t('auth.register.heading')">
    <form class="flex flex-col gap-6" @submit.prevent="submit">
      <BaseInput
        id="register-first-name"
        v-model="form.first_name"
        type="text"
        autocomplete="given-name"
        required
        :label="t('auth.register.firstName')"
        :error="form.errors.first_name"
      />

      <BaseInput
        id="register-last-name"
        v-model="form.last_name"
        type="text"
        autocomplete="family-name"
        required
        :label="t('auth.register.lastName')"
        :error="form.errors.last_name"
      />

      <BaseInput
        id="register-email"
        v-model="form.email"
        type="email"
        autocomplete="email"
        required
        :label="t('auth.register.email')"
        :error="form.errors.email"
      />

      <BaseInput
        id="register-password"
        v-model="form.password"
        type="password"
        autocomplete="new-password"
        required
        :label="t('auth.register.password')"
        :error="form.errors.password"
      />

      <BaseInput
        id="register-password-confirmation"
        v-model="form.password_confirmation"
        type="password"
        autocomplete="new-password"
        required
        :label="t('auth.register.passwordConfirmation')"
        :error="form.errors.password_confirmation"
      />

      <BaseInput
        id="register-university"
        v-model="form.university"
        type="text"
        autocomplete="organization"
        :label="t('auth.register.university')"
        :error="form.errors.university"
      />

      <div class="flex flex-col gap-1.5">
        <BaseCheckbox id="register-terms" v-model="form.terms">
          {{ t('auth.register.termsPrefix') }}
          <a
            href="/regulamin"
            class="font-medium text-primary hover:underline
                   focus-visible:outline-none focus-visible:ring-2
                   focus-visible:ring-primary/40 rounded"
          >
            {{ t('auth.register.termsLink') }}
          </a>
        </BaseCheckbox>

        <p
          v-if="form.errors.terms"
          class="text-sm text-red-600"
          role="alert"
        >
          {{ form.errors.terms }}
        </p>
      </div>

      <BaseButton type="submit" :disabled="form.processing">
        {{ t('auth.register.submit') }}
      </BaseButton>
    </form>

    <div class="flex items-center gap-5 my-8">
      <div class="h-px flex-1 bg-border" />
      <span class="text-xs text-additional tracking-wide">
        {{ t('auth.register.or') }}
      </span>
      <div class="h-px flex-1 bg-border" />
    </div>

    <a
      href="/auth/google"
      class="flex w-full items-center justify-center gap-2 rounded-full
             border border-border bg-white px-4 py-2.5 text-sm text-text
             hover:bg-secondary transition
             focus-visible:outline-none focus-visible:ring-2
             focus-visible:ring-primary/40"
    >
      {{ t('auth.register.google') }}
    </a>

    <p class="mt-8 text-center text-sm text-text">
      {{ t('auth.register.hasAccount') }}
      <Link
        href="/login"
        class="font-medium text-primary hover:underline
               focus-visible:outline-none focus-visible:ring-2
               focus-visible:ring-primary/40 rounded"
      >
        {{ t('auth.register.loginLink') }}
      </Link>
    </p>
  </AuthLayout>
</template>
