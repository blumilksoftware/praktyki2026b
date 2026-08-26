<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  token: { type: String, default: '' },
})

const { t } = useI18n()

const form = useForm({
  first_name: '',
  last_name: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const fieldError = (field) => form.errors[field]
const hasTermsError = computed(() => Boolean(fieldError('terms')))
const isSubmitDisabled = computed(() => !props.token || form.processing)

const submit = () => {
  form.post(`/invitations/${props.token}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar/>
    <AuthLayout class="flex-1 min-h-0">
      <Head :title="t('auth.invitation.title')" />

      <div class="mx-auto flex w-full max-w-xl flex-col gap-6 px-2 sm:px-4">
        <div class="text-center">
          <h1 class="text-3xl font-normal text-text sm:text-4xl">
            {{ t('auth.invitation.title') }}
          </h1>
        </div>

        <form class="flex flex-col gap-4" novalidate @submit.prevent="submit">
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
            :disabled="isSubmitDisabled"
          >
            {{ t('auth.invitation.submit') }}
          </BaseButton>
        </form>

        <div class="h-px bg-text/20" />

        <p class="w-full text-center text-base sm:text-lg font-medium">
          {{ t('auth.invitation.hasAccount') }}
          <Link
            :href="ROUTES.LOGIN"
            class="inline-block text-base sm:text-lg font-medium text-link hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded whitespace-nowrap"
          >
            {{ t('auth.invitation.loginLink') }}
          </Link>
        </p>
      </div>
    </AuthLayout>
  </div>
</template>
