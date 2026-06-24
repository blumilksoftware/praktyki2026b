<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const { t } = useI18n()

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post('/login', {
    preserveScroll: true,
  })
}

const authError = computed(() => form.errors.email)

const showResendLink = computed(() => {
  if (!authError.value) {
    return false
  }
  return authError.value === t('auth.verification.notVerified')
})
</script>

<template>
  <Head :title="t('auth.login.title')" />

  <AuthLayout :title="t('auth.login.heading')">
    <form class="flex flex-col gap-6" @submit.prevent="submit">
      <div
        v-if="authError"
        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3
               text-sm text-red-800"
        role="alert"
      >
        <p>{{ authError }}</p>
        <Link
          v-if="showResendLink"
          href="/email/resend"
          method="post"
          as="button"
          type="button"
          class="mt-2 font-medium text-primary hover:underline
                 focus-visible:outline-none focus-visible:ring-2
                 focus-visible:ring-primary/40 rounded"
        >
          {{ t('auth.login.resendVerification') }}
        </Link>
      </div>

      <BaseInput
        id="login-email"
        v-model="form.email"
        type="email"
        autocomplete="email"
        required
        :label="t('auth.login.email')"
      />

      <BaseInput
        id="login-password"
        v-model="form.password"
        type="password"
        autocomplete="current-password"
        required
        :label="t('auth.login.password')"
      />

      <Link
        href="/forgot-password"
        class="text-sm text-primary hover:underline
               focus-visible:outline-none focus-visible:ring-2
               focus-visible:ring-primary/40 rounded"
      >
        {{ t('auth.login.forgotPassword') }}
      </Link>

      <BaseButton type="submit" :disabled="form.processing">
        {{ t('auth.login.submit') }}
      </BaseButton>
    </form>

    <div class="flex items-center gap-5 my-8">
      <div class="h-px flex-1 bg-border" />
      <span class="text-xs text-additional tracking-wide">
        {{ t('auth.login.or') }}
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
      {{ t('auth.login.google') }}
    </a>

    <p class="mt-8 text-center text-sm text-text">
      {{ t('auth.login.noAccount') }}
      <Link
        href="/register/student"
        class="font-medium text-primary hover:underline
               focus-visible:outline-none focus-visible:ring-2
               focus-visible:ring-primary/40 rounded"
      >
        {{ t('auth.login.registerLink') }}
      </Link>
    </p>
  </AuthLayout>
</template>
