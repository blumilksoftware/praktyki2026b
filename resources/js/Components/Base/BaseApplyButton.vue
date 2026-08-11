<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconLoader2, IconCheck } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  hasCv: {
    type: Boolean,
    required: true,
  },
  isApplied: {
    type: Boolean,
    default: false,
  },
  appliedDate: {
    type: String,
    default: null,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['apply'])

const isBlocked = computed(() => props.disabled || props.isLoading || props.isApplied || !props.hasCv)

const formattedAppliedDate = computed(() => {
  if (!props.appliedDate) return ''
  const [year, month, day] = props.appliedDate.split('-')
  return `${day}.${month}.${year}`
})

function apply() {
  if (isBlocked.value) return
  emit('apply')
}
</script>

<template>
  <span class="inline-flex w-fit">
    <button
      type="button"
      :aria-disabled="isBlocked || undefined"
      :aria-describedby="hasCv ? undefined : `${id}-reason`"
      :title="hasCv ? undefined : t('buttons.apply.noCvMessage')"
      class="w-fit flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold tracking-wide transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :class="[
        isApplied ? 'bg-success text-white' : 'bg-primary text-white',
        isBlocked ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:bg-primary/90',
      ]"
      @click="apply"
    >
      <template v-if="isLoading">
        <IconLoader2 class="w-5 h-5 animate-spin" />
        {{ t('buttons.apply.loading') }}
      </template>

      <template v-else-if="isApplied">
        <IconCheck class="w-5 h-5" />
        {{ t('buttons.apply.appliedOn', { date: formattedAppliedDate }) }}
      </template>

      <template v-else>
        {{ t('buttons.apply.applyNow') }}
      </template>
    </button>

    <span v-if="!hasCv" :id="`${id}-reason`" class="sr-only">{{ t('buttons.apply.noCvMessage') }}</span>
  </span>
</template>
