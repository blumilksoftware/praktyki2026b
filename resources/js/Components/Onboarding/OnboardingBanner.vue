<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconX, IconUserCheck } from '@tabler/icons-vue'

const { t } = useI18n()
const page = usePage()

const onboarding = computed(() => page.props.onboarding)
const role = computed(() => page.props.auth?.user?.role)

const profileUrl = computed(() => {
  if (role.value === 'companyAdmin') return '/company/profile'
  if (role.value === 'universityAdmin') return '/university/profile'
  return null
})

function dismiss() {
  router.post('/onboarding/dismiss')
}
</script>

<template>
  <div
    v-if="onboarding?.show"
    class="flex items-start gap-4 bg-white/60 backdrop-blur-sm rounded-2xl ring-1 ring-primary/20 shadow-sm px-5 py-4"
    role="status"
  >
    <div class="flex items-center justify-center bg-primary/10 rounded-xl w-10 h-10 shrink-0">
      <IconUserCheck class="w-5 h-5 text-primary" aria-hidden="true" />
    </div>

    <div class="flex-1 min-w-0">
      <p class="font-semibold text-text text-sm">
        {{ t('onboarding.banner.title') }}
      </p>
      <p class="mt-0.5 text-slate-500 text-xs">
        {{ t('onboarding.banner.description') }}
      </p>
      <a
        v-if="profileUrl"
        :href="profileUrl"
        class="inline-flex items-center mt-2 text-primary hover:text-primary/80 font-medium text-xs transition"
      >
        {{ t('onboarding.banner.action') }}
      </a>
    </div>

    <button
      class="flex items-center justify-center hover:bg-black/5 rounded-lg w-8 h-8 text-slate-400 hover:text-slate-600 transition shrink-0"
      :aria-label="t('onboarding.banner.dismiss')"
      @click="dismiss"
    >
      <IconX class="w-4 h-4" aria-hidden="true" />
    </button>
  </div>
</template>
