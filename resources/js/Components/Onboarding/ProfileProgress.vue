<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconCircle, IconCircleCheckFilled, IconChevronDown } from '@tabler/icons-vue'

const { t } = useI18n()
const page = usePage()

const isExpanded = ref(false)

const steps = computed(() => page.props.onboarding?.steps ?? [])
const completedCount = computed(() => steps.value.filter(s => s.completed).length)
const total = computed(() => steps.value.length)
const percentage = computed(() => total.value === 0 ? 100 : Math.round((completedCount.value / total.value) * 100))
const isComplete = computed(() => completedCount.value === total.value)
const nextStep = computed(() => steps.value.find(s => !s.completed) ?? null)

const fillStyle = computed(() => {
  const style = { width: `${percentage.value}%` }
  if (!isComplete.value) {
    style.background = 'linear-gradient(90deg, color-mix(in srgb, var(--color-primary) 40%, white), var(--color-primary))'
  }
  return style
})

function toggleExpanded() {
  isExpanded.value = !isExpanded.value
}
</script>

<template>
  <div v-if="steps.length > 0" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
    <div class="flex items-center gap-3">
      <span class="font-medium text-text text-sm shrink-0">
        {{ t('onboarding.progress.title') }}
      </span>

      <div
        class="flex-1 bg-slate-200 rounded-full h-1.5 overflow-hidden"
        role="progressbar"
        :aria-valuenow="percentage"
        :aria-label="t('onboarding.progress.title')"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <div
          class="h-1.5 rounded-full transition-all duration-500"
          :class="isComplete ? 'bg-green-500' : ''"
          :style="fillStyle"
        />
      </div>

      <span class="text-xs font-semibold shrink-0" :class="isComplete ? 'text-green-500' : 'text-primary'">
        {{ completedCount }}/{{ total }}
      </span>

      <button
        v-if="!isComplete"
        type="button"
        class="flex items-center justify-center hover:bg-black/5 rounded-lg w-6 h-6 text-additional hover:text-text transition shrink-0"
        :aria-expanded="isExpanded"
        aria-controls="profile-progress-steps"
        :aria-label="isExpanded ? t('onboarding.progress.collapse') : t('onboarding.progress.expand')"
        @click="toggleExpanded"
      >
        <IconChevronDown
          class="w-4 h-4 transition-transform duration-300"
          :class="isExpanded ? 'rotate-180' : ''"
          aria-hidden="true"
        />
      </button>
    </div>

    <p class="mt-1.5 text-xs" :class="isComplete ? 'text-green-500' : 'text-additional'">
      {{ isComplete
        ? t('onboarding.progress.complete')
        : t('onboarding.progress.nextStep', { step: t(`onboarding.steps.${nextStep.key}`) }) }}
    </p>

    <div
      v-if="!isComplete"
      id="profile-progress-steps"
      class="grid transition-[grid-template-rows] duration-300 ease-out"
      :style="{ gridTemplateRows: isExpanded ? '1fr' : '0fr' }"
    >
      <div class="min-h-0 overflow-hidden">
        <span class="sr-only">{{ t('onboarding.progress.allSteps') }}</span>
        <div class="flex flex-wrap gap-2 pt-2.5">
          <span
            v-for="step in steps"
            :key="step.key"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium ring-1"
            :class="step.completed
              ? 'bg-green-50 text-green-700 ring-green-200'
              : 'bg-slate-100 text-slate-600 ring-slate-200'"
          >
            <IconCircleCheckFilled v-if="step.completed" class="w-3 h-3 shrink-0" aria-hidden="true" />
            <IconCircle v-else class="w-3 h-3 shrink-0" aria-hidden="true" />
            {{ t(`onboarding.steps.${step.key}`) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
