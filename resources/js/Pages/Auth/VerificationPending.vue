<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageDropdown from '@/Components/Common/LanguageDropdown.vue'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

function logout() {
  router.post('/logout')
}
</script>

<template>
  <Head :title="t('pending.title')" />

  <header
    class="top-4 left-4 z-10 absolute bg-radial from-slate-600/45 to-slate-600/25 shadow-slate-900/40 shadow-xl px-10 py-4 rounded-full"
  >
    <img src="/logo_text.svg" alt="Applikuj" class="w-auto h-8 md:h-10">
  </header>
  <header class="top-4 right-4 z-10 absolute shadow p-2 rounded-lg" aria-label="Language selection">
    <LanguageDropdown :mobile="true" variant="light" />
  </header>

  <main class="flex justify-center items-center bg-slate-50 px-6 min-h-screen" role="main">
    <section class="bg-white shadow-sm p-8 md:p-12 rounded-2xl w-full max-w-3xl" aria-labelledby="pending-title">
      <div class="space-y-8">
        <h1 id="pending-title" class="font-semibold text-slate-900 text-4xl text-center">
          {{ t('pending.title') }}
        </h1>

        <p class="text-slate-700 text-base text-center leading-7">
          {{ t('pending.description') }}
        </p>

        <hr class="border-slate-200" aria-hidden="true">

        <p class="text-slate-500 text-base text-center leading-7">
          {{ t('pending.status', { time: '2-15' }) }}
        </p>

        <p class="text-slate-500 text-base text-center leading-7">
          {{ t('pending.contact', { email: $page.props.support_email }) }}
        </p>

        <button
          type="button"
          class="bg-slate-900 hover:bg-slate-800 px-4 py-3 rounded-lg w-full font-medium text-white transition"
          aria-label="Log out"
          @click="logout"
        >
          {{ t('pending.log_out') }}
        </button>
      </div>
    </section>
  </main>
</template>


