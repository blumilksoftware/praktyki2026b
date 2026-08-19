<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseMaskedInput from '@/Components/Base/BaseMaskedInput.vue'
import CityAutocomplete from '@/Components/Common/CityAutocomplete.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import RegisterAccountTypeTabs from '@/Components/Auth/RegisterAccountTypeTabs.vue'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const form = useForm({
  company_name: '',
  nip: '',
  email: '',
  password: '',
  password_confirmation: '',
  street: '',
  postal_code: '',
  city: '',
  phone: '',
  website: '',
  terms: false,
})

const fieldError = (field) => form.errors[field]

const submit = () => {
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
            <BaseMaskedInput
              id="postal_code"
              v-model="form.postal_code"
              mask="##-###"
              inputmode="numeric"
              autocomplete="postal-code"
              :label="t('auth.register.company.postalCode')"
              required
              :error="fieldError('postal_code')"
            />
            <CityAutocomplete
              id="city"
              v-model="form.city"
              :label="t('auth.register.company.city')"
              required
              :error="fieldError('city')"
            />
          </div>

          <BaseInput
            id="street"
            v-model="form.street"
            :label="t('auth.register.company.street')"
            autocomplete="street-address"
            required
            :error="fieldError('street')"
          />

          <BaseMaskedInput
            id="phone"
            v-model="form.phone"
            mask="+48 ### ### ###"
            type="tel"
            inputmode="numeric"
            autocomplete="tel"
            :label="t('auth.register.company.phone')"
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
          <p class="-mt-2 text-sm text-additional">
            {{ t('auth.register.company.websiteHint') }}
          </p>

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
