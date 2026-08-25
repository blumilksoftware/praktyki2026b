<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const { t } = useI18n()

const page = usePage()

const email = ref('')

const cooldown = ref(0)
const isCooldownActive = computed(() => cooldown.value > 0)

const form = useForm({
  email: '',
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
  const flash = page.props.flash
  if (flash && typeof flash === 'object' && 'email' in flash && flash.email) {
    form.email = String(flash.email)
  }
  startCooldown()
})
</script>

<template>
  <Head :title="t('auth.login.waiting.title')" />

  <header class="top-4 right-4 z-10 absolute shadow p-2 rounded-lg">
    <LanguageSwitcher :mobile="true" variant="light" />
  </header>

  <main class="flex flex-col justify-center items-center bg-slate-50 px-6 py-16 min-h-screen">
    <section class="bg-white shadow-sm p-8 md:p-12 rounded-2xl w-full max-w-3xl">
      <div class="flex justify-center items-center p-4 rounded-lg w-full">
        <img src="/logo.svg" alt="Applikuj" class="mb-8 w-auto h-10 md:h-12 brightness-0">
      </div>

      <div class="space-y-8">
        <h1 class="font-semibold text-text text-4xl text-center">
          {{ t('auth.login.waiting.heading') }}
        </h1>

        <div class="space-y-4">
          <p class="text-text text-base text-center leading-7">
            {{ t('auth.login.waiting.sentTo') }}
          </p>

          <p class="font-semibold text-primary text-xl text-center">
            {{ form.email }}
          </p>
        </div>

        <hr class="border-slate-200">

        <p class="text-additional text-base text-center leading-7">
          {{ t('auth.login.waiting.expiresInfo') }}
        </p>

        <div class="flex sm:flex-row flex-col justify-center items-center gap-4">
          <Link href="/login" class="font-medium text-link text-base hover:underline">
            {{ t('auth.login.waiting.backToLogin') }}
          </Link>

          <BaseButton v-if="!isCooldownActive" type="button" variant="primary" :disabled="form.processing"
                      @click="resend"
          >
            {{ t('auth.login.waiting.resend') }}
          </BaseButton>

          <BaseButton v-else type="button" variant="primary" disabled>
            {{ t('auth.login.waiting.resendCooldown', { seconds: cooldown }) }}
          </BaseButton>
        </div>

        <p v-if="form.recentlySuccessful" class="text-green-600 text-base text-center">
          {{ t('auth.login.waiting.resendSuccess') }}
        </p>
      </div>
    </section>
  </main>
</template>
