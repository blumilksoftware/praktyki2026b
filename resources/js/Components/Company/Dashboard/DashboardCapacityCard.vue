<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconUsers } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  totalSpots: { type: Number, default: 0 },
  remainingSpots: { type: Number, default: 0 },
})

const filledSpots = computed(() => Math.max(0, props.totalSpots - props.remainingSpots))
const fillPercent = computed(() => (
  props.totalSpots > 0 ? Math.min(100, Math.round((filledSpots.value / props.totalSpots) * 100)) : 0
))
</script>

<template>
  <section class="rounded-2xl border border-border bg-white p-5 shadow-sm">
    <div class="flex items-center gap-3">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
        <IconUsers class="h-5 w-5 text-emerald-600" aria-hidden="true" />
      </div>
      <div class="min-w-0">
        <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-additional">
          {{ t('company.dashboard.stats.capacity.title') }}
        </p>
        <p class="mt-0.5 text-sm text-text">
          {{ t('company.dashboard.stats.capacity.summary', { filled: filledSpots, total: totalSpots }) }}
        </p>
      </div>
    </div>

    <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-background">
      <div
        class="h-full rounded-full bg-emerald-500 transition-all"
        :style="{ width: `${fillPercent}%` }"
      />
    </div>

    <p class="mt-2 text-xs text-additional">
      {{ t('company.dashboard.stats.capacity.remaining', { count: remainingSpots }) }}
    </p>
  </section>
</template>
