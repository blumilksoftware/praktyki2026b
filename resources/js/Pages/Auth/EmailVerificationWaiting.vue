<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'

const { t } = useI18n()

const page = usePage()

const email = computed(() => (page.props.flash as any)?.email as string)

const cooldown = ref(0)
const isCooldownActive = computed(() => cooldown.value > 0)

const form = useForm({
  email: email.value,
})

const startCooldown = () => {
  cooldown.value = 60
  const interval = setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0) {
      clearInterval(interval)
    }
  }, 1000)
}

const resend = () => {
  form.post('/email/resend', {
    preserveScroll: true,
    onSuccess: () => {
      startCooldown()
    },
  })
}

onMounted(() => {
  startCooldown()
})
</script>

<template>
  <div class="flex flex-col bg-background min-h-screen">
    <BaseNavbar class="shrink-0" />

    <AuthLayout class="flex-1 min-h-0">
      <Head :title="t('auth.login.waiting.title')" />

      <div class="flex flex-col justify-center items-center sm:px-8 md:px-10 lg:px-12 2xl:px-20 xl:px-16 w-full">
        <h1 class="mb-8 md:mb-10 font-normal text-text text-5xl md:text-6xl text-center">
          {{ t('auth.login.waiting.heading') }}
        </h1>

        <div class="flex flex-col items-center space-y-6 sm:space-y-5 w-full max-w-2xl">
          <div class="space-y-4 w-full text-center">
            <p class="text-text text-lg md:text-xl">
              {{ t('auth.login.waiting.sentTo') }}
            </p>
            <p class="font-semibold text-primary text-xl md:text-2xl">
              {{ email }}
            </p>
          </div>

          <div class="w-full text-center">
            <p class="text-additional text-base md:text-lg">
              {{ t('auth.login.waiting.expiresInfo') }}
            </p>
          </div>

          <div class="flex sm:flex-row flex-col justify-center items-center gap-4 sm:gap-6 w-full">
            <BaseButton
              v-if="!isCooldownActive"
              type="button"
              variant="primary"
              :disabled="form.processing"
              @click="resend"
            >
              {{ t('auth.login.waiting.resend') }}
            </BaseButton>
            <BaseButton
              v-else
              type="button"
              variant="primary"
              disabled
            >
              {{ t('auth.login.waiting.resendCooldown', { seconds: cooldown }) }}
            </BaseButton>

            <Link
              href="/login"
              class="rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 font-medium text-link text-base sm:text-lg hover:underline whitespace-nowrap"
            >
              {{ t('auth.login.waiting.backToLogin') }}
            </Link>
          </div>

          <div v-if="form.recentlySuccessful" class="w-full text-center">
            <p class="text-green-600 text-base md:text-lg">
              {{ t('auth.login.waiting.resendSuccess') }}
            </p>
          </div>
        </div>
      </div>
    </AuthLayout>
  </div>
</template>
