<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconCheck, IconCircle } from '@tabler/icons-vue'

const { t } = useI18n()
const page = usePage()

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
</script>

<template>
  <div v-if="steps.length > 0" class="bg-white/60 backdrop-blur-sm rounded-2xl ring-1 ring-black/5 shadow-sm px-5 py-3">
    <div class="flex items-center gap-3">
      <span class="font-medium text-text text-sm shrink-0">
        {{ t('onboarding.progress.title') }}
      </span>

      <div
        class="flex-1 bg-slate-200 rounded-full h-1.5 overflow-hidden"
        role="progressbar"
        :aria-valuenow="percentage"
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
    </div>

    <p class="mt-1.5 text-xs" :class="isComplete ? 'text-green-500' : 'text-slate-500'">
      {{ isComplete
        ? t('onboarding.progress.complete')
        : t('onboarding.progress.nextStep', { step: t(`onboarding.steps.${nextStep.key}`) }) }}
    </p>

    <div class="flex flex-wrap gap-2 mt-2.5">
      <span
        v-for="step in steps"
        :key="step.key"
        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
        :class="step.completed
          ? 'bg-primary/10 text-primary line-through'
          : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200'"
      >
        <IconCheck v-if="step.completed" class="w-3 h-3 shrink-0" aria-hidden="true" />
        <IconCircle v-else class="w-3 h-3 shrink-0" aria-hidden="true" />
        {{ t(`onboarding.steps.${step.key}`) }}
      </span>
    </div>
  </div>
</template>
