<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
import AuthErrorDisplay from '@/Components/Auth/AuthErrorDisplay.vue'
import GoogleSvg from '@/Components/Common/GoogleSvg.vue'

const { t } = useI18n()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    preserveScroll: true,
  })
}

const authError = computed(() => form.errors.email)
</script>

<template>
  <AuthLayout>
    <Head :title="t('auth.login.title')" />

    <div class="flex flex-col items-center justify-center w-full px-8 sm:px-10 md:px-12 lg:px-16 xl:px-20 2xl:px-24">
      <h1 class="text-3xl md:text-4xl font-normal mb-8 md:mb-10 text-center text-text">
        {{ t('auth.login.heading') }}
      </h1>

      <div class="flex flex-col w-full">
        <form class="flex flex-col items-center space-y-4 sm:space-y-5 w-full" @submit.prevent="submit">
          <div class="w-full">
            <BaseInput
              id="email"
              v-model="form.email"
              :label="t('auth.login.email')"
              type="email"
              autocomplete="email"
              required
              floating
            />
          </div>

          <div class="w-full">
            <div class="relative w-full">
              <BaseInput
                id="password"
                v-model="form.password"
                :label="t('auth.login.password')"
                type="password"
                autocomplete="current-password"
                required
                floating
              />
            </div>
          </div>

          <div class="flex items-center justify-between mt-2 sm:mt-4 w-full">
            <div class="flex items-center gap-2">
              <BaseCheckbox
                id="remember"
                v-model="form.remember"
              />
              <label 
                for="remember" 
                class="text-xs sm:text-sm font-medium text-text cursor-pointer select-none whitespace-nowrap"
              >
                {{ t('auth.login.rememberMe') }}
              </label>
            </div>

            <Link
              href="/forgot-password"
              class="text-link hover:underline text-xs sm:text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded-base"
            >
              {{ t('auth.login.forgotPassword') }}
            </Link>
          </div>

          <AuthErrorDisplay class="w-full justify-center" :error="authError" :email="form.email" />

          <BaseButton
            class="w-fit px-8 sm:px-10 md:px-20 py-3 sm:py-3.5 text-sm sm:text-base mt-2 sm:mt-4"
            type="submit"
            :disabled="form.processing"
          >
            {{ t('auth.login.submit') }}
          </BaseButton>
        </form>
        
        <div class="flex items-center gap-4 sm:gap-5 my-6 sm:my-8 w-full">
          <div class="h-[1px] flex-1 bg-text/20" />
          <span class="text-xs sm:text-sm text-additional tracking-wide">
            {{ t('auth.login.or') }}
          </span>
          <div class="h-[1px] flex-1 bg-text/20" />
        </div>
        
        <div class="flex flex-col items-center gap-4 sm:gap-5 w-full">
          <a
            href="/auth/google"
            class="flex gap-2 w-fit rounded-full border border-text/20 bg-white px-4 py-3 sm:py-2.5 text-xs font-medium text-text hover:bg-background transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 shadow-sm"
          >
            <GoogleSvg />

            {{ t('auth.login.google') }}
          </a>
        </div>

        <div class="h-[1px] bg-text/20 my-6 sm:my-8" />

        <div class="w-full text-center md:text-left">
          <Link
            href="/register/student"
            class="inline-block text-xs sm:text-sm font-medium text-link hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded whitespace-nowrap"
          >
            {{ t('auth.login.noAccount') }}
          </Link>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>
