<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import RegisterAccountTypeTabs from '@/Components/Auth/RegisterAccountTypeTabs.vue'
import { useValidationMessages } from '@/Composables/useValidationMessages'
import { ROUTES } from '@/Helpers/routes'
import { URL_PATTERN, validateForm } from '@/Helpers/validation'
import { validateNip } from '@/utils/validateNip'

const { t } = useI18n()
const { message: validationMessage } = useValidationMessages()

const form = useForm({
  company_name: '',
  nip: '',
  email: '',
  password: '',
  password_confirmation: '',
  street: '',
  building_number: '',
  postal_code: '',
  city: '',
  phone: '',
  website: '',
  terms: false,
})

const clientErrors = ref({})

const fieldRules = {
  company_name: [{ type: 'required' }],
  nip: [
    { type: 'required' },
    {
      type: 'custom',
      rule: 'nip',
      validate: (value) => typeof value === 'string' && validateNip(value),
    },
  ],
  email: [{ type: 'required' }, { type: 'email' }],
  password: [{ type: 'required' }],
  password_confirmation: [{ type: 'required' }, { type: 'confirmed', field: 'password' }],
  street: [{ type: 'required' }],
  building_number: [{ type: 'required' }],
  postal_code: [{ type: 'required' }],
  city: [{ type: 'required' }],
  phone: [{ type: 'required' }],
  website: [
    {
      type: 'custom',
      rule: 'url',
      validate: (value) => {
        if (typeof value !== 'string' || !value.trim()) {
          return true
        }

        return URL_PATTERN.test(value.trim())
      },
    },
  ],
  terms: [{ type: 'accepted' }],
}

const validate = () => {
  const errors = validateForm(form, fieldRules, validationMessage)
  clientErrors.value = errors
  return Object.keys(errors).length === 0
}

const fieldError = (field) => {
  return clientErrors.value[field] ?? form.errors[field]
}

const submit = () => {
  form.clearErrors()
  clientErrors.value = {}

  if (!validate()) {
    return
  }

  form.post(ROUTES.REGISTER_COMPANY, {
    preserveScroll: true,
  })
}

const hasTermsError = computed(() => Boolean(fieldError('terms')))
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar class="shrink-0" />
    <AuthLayout class="flex-1 min-h-0">
      <Head :title="t('auth.register.company.title')" />

      <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 px-2 sm:px-4">
        <RegisterAccountTypeTabs active-tab="company" />

        <div class="text-center">
          <h1 class="text-3xl font-normal text-text sm:text-4xl">
            {{ t('auth.register.heading') }}
          </h1>
        </div>

        <form class="flex flex-col gap-4" novalidate @submit.prevent="submit">
          <BaseInput
            id="company_name"
            v-model="form.company_name"
            :label="t('auth.register.company.companyName')"
            autocomplete="organization"
            required
            :error="fieldError('company_name')"
          />
          <BaseInput
            id="nip"
            v-model="form.nip"
            :label="t('auth.register.company.nip')"
            autocomplete="off"
            required
            :error="fieldError('nip')"
          />
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

          <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_2fr]">
            <BaseInput
              id="postal_code"
              v-model="form.postal_code"
              :label="t('auth.register.company.postalCode')"
              autocomplete="postal-code"
              required
              :error="fieldError('postal_code')"
            />
            <BaseInput
              id="city"
              v-model="form.city"
              :label="t('auth.register.company.city')"
              autocomplete="address-level2"
              required
              :error="fieldError('city')"
            />
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-[2fr_1fr]">
            <BaseInput
              id="street"
              v-model="form.street"
              :label="t('auth.register.company.street')"
              autocomplete="street-address"
              required
              :error="fieldError('street')"
            />
            <BaseInput
              id="building_number"
              v-model="form.building_number"
              :label="t('auth.register.company.buildingNumber')"
              autocomplete="off"
              required
              :error="fieldError('building_number')"
            />
          </div>

          <BaseInput
            id="phone"
            v-model="form.phone"
            :label="t('auth.register.company.phone')"
            type="tel"
            autocomplete="tel"
            required
            :error="fieldError('phone')"
          />
          <BaseInput
            id="website"
            v-model="form.website"
            :label="t('auth.register.company.website')"
            type="url"
            autocomplete="url"
            :error="fieldError('website')"
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
            class="mx-auto mt-1 w-fit px-12 py-3 text-base sm:text-lg font-medium"
            :disabled="form.processing"
          >
            {{ t('auth.register.submit') }}
          </BaseButton>
        </form>

        <div class="h-px bg-text/20" />

        <p class="text-center text-sm font-medium">
          {{ t('auth.register.hasAccount') }}
          <Link
            :href="ROUTES.LOGIN"
            class="text-link hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            {{ t('auth.register.loginLink') }}
          </Link>
        </p>
      </div>
    </AuthLayout>
  </div>
</template>
