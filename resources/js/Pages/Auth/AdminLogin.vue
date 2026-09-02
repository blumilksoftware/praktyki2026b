<script setup lang="ts">
import { computed, watch } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
import AuthErrorDisplay from '@/Components/Auth/AuthErrorDisplay.vue'
import GoogleSvg from '@/Components/Common/GoogleSvg.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const page = usePage()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

watch(
  // @ts-ignore
  () => page.props.status || page.props.flash?.status,
  (status) => {
    if (status) {
      form.reset()
      form.clearErrors()
    }
  },
)

const submit = () => {
  if (document.activeElement instanceof HTMLElement) {
    document.activeElement.blur()
  }
  form.post(ROUTES.LOGIN_ADMIN,
    {
      preserveScroll: true,
    })
}

const authError = computed(() => form.errors.email)
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <BaseNavbar />

    <AuthLayout class="flex-1 min-h-0">
      <Head :title="t('auth.login.title')" />

      <div class="flex flex-col items-center justify-center w-full sm:px-8 md:px-10 lg:px-12 xl:px-16 2xl:px-20 ">
        <h1 class="text-5xl md:text-6xl font-normal mb-8 md:mb-10 text-center text-text">
          {{ t('auth.login.adminTitle') }}
        </h1>

        <div class="flex flex-col w-full">
          <AuthErrorDisplay class="w-full mb-6 sm:mb-5" :error="authError" :email="form.email" />

          <form class="flex flex-col items-center space-y-6 sm:space-y-5 w-full" @submit.prevent="submit">
            <div class="w-full">
              <BaseInput
                id="email"
                v-model="form.email"
                :label="t('auth.login.email')"
                type="email"
                autocomplete="email"
                :invalid="!!authError"
                required
                floating
              />
            </div>

            <div class="w-full">
              <BaseInput
                id="password"
                v-model="form.password"
                :label="t('auth.login.password')"
                type="password"
                autocomplete="current-password"
                :invalid="!!authError"
                required
                floating
              />
            </div>

            <div class="flex items-center justify-between mt-2 sm:mt-4 w-full">
              <BaseCheckbox
                id="remember"
                v-model="form.remember"
                :label="t('auth.login.rememberMe')"
              />

              <Link
                :href="ROUTES.FORGOT_PASSWORD"
                class="text-link hover:underline text-sm sm:text-base font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded-base whitespace-nowrap"
              >
                {{ t('auth.login.forgotPassword') }}
              </Link>
            </div>

            <BaseButton
              class="w-fit px-12 py-3 sm:py-2.5 text-base sm:text-lg font-medium text-white bg-primary hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded shadow-sm transition-colors disabled:opacity-50"
              type="submit"
              :disabled="form.processing"
            >
              {{ t('auth.login.submit') }}
            </BaseButton>
          </form>

          <div class="flex items-center gap-4 sm:gap-5 my-6 sm:my-8 w-full">
            <div class="h-px flex-1 bg-text/20" />
            <span class="text-base sm:text-lg text-additional tracking-wide">
              {{ t('auth.login.or') }}
            </span>
            <div class="h-px flex-1 bg-text/20" />
          </div>

          <a
            :href="ROUTES.GOOGLE_AUTH"
            class="mx-auto flex justify-center items-center gap-2 w-fit rounded-lg border border-text/20 bg-white px-12 py-3 sm:py-2.5 text-base sm:text-lg font-medium text-text hover:bg-background transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 shadow-sm"
          >
            <GoogleSvg />
            {{ t('auth.login.google') }}
          </a>

          <div class="h-px bg-text/20 my-6 sm:my-8" />
        </div>
      </div>
    </AuthLayout>
  </div>
</template>
