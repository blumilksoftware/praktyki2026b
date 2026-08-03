<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconPlus } from '@tabler/icons-vue'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import { ROUTES } from '@/Helpers/routes'

defineProps({
  canCreateDraftOffer: { type: Boolean, default: false },
})

const { t } = useI18n()

function logout() {
  router.post('/logout')
}
</script>

<template>
  <Head :title="t('pending.title')" />

  <header class="top-4 right-4 z-10 absolute shadow p-2 rounded-lg" aria-label="Language selection">
    <LanguageSwitcher :mobile="true" variant="light" />
  </header>

  <main class="flex flex-col justify-center items-center bg-slate-50 px-6 py-16 min-h-screen" role="main">
    <section class="bg-white shadow-sm p-8 md:p-12 rounded-2xl w-full max-w-3xl" aria-labelledby="pending-title">
      <div class="flex justify-center items-centerp-4 rounded-lg w-full">
        <Link :href="ROUTES.DASHBOARD" aria-label="Go to dashboard">
          <img src="/logo.svg" alt="Applikuj" class="mb-8 w-auto h-10 md:h-12 cursor-pointer brightness-0">
        </Link>
      </div>
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

        <div v-if="canCreateDraftOffer" class="flex flex-col items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-center sm:flex-row sm:justify-between sm:text-left">
          <div>
            <p class="font-semibold text-text text-sm">
              {{ t('pending.createDraftOffer.title') }}
            </p>
            <p class="mt-0.5 text-slate-500 text-xs">
              {{ t('pending.createDraftOffer.description') }}
            </p>
          </div>

          <Link
            :href="ROUTES.COMPANY_OFFERS_CREATE"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
          >
            <IconPlus class="h-4 w-4" aria-hidden="true" />
            {{ t('pending.createDraftOffer.action') }}
          </Link>
        </div>

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


