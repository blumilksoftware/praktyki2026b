<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconLoader2, IconCheck } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
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

defineEmits(['apply', 'uploadCv'])

const formattedAppliedDate = computed(() => {
  if (!props.appliedDate) return ''
  const [year, month, day] = props.appliedDate.split('-')
  return `${day}.${month}.${year}`
})
</script>

<template>
  <div v-if="!hasCv" class="justify-content items-center flex flex-col gap-2 p-4 border border-border rounded-xl bg-gray-50/50 text-sm w-fit">
    <span class="text-text text-center font-medium">{{ t('common.actions.apply.noCvMessage') }}</span>
    <button
      type="button"
      class="text-link hover:text-link/80 font-bold underline underline-offset-4 text-left w-fit transition-colors hover:cursor-pointer"
      @click="$emit('uploadCv')"
    >
      {{ t('common.actions.apply.uploadCvPrompt') }}
    </button>
  </div>

  <button
    v-else
    type="button"
    :disabled="disabled || isLoading || isApplied"
    class="w-fit flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold tracking-wide transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
    :class="[
      isApplied
        ? 'bg-success text-white'
        : 'bg-primary text-white hover:bg-primary/90 cursor-pointer'
    ]"
    @click="$emit('apply')"
  >
    <template v-if="isLoading">
      <IconLoader2 class="w-5 h-5 animate-spin" />
      {{ t('common.actions.apply.loading') }}
    </template>

    <template v-else-if="isApplied">
      <IconCheck class="w-5 h-5" />
      {{ t('common.actions.apply.appliedOn', { date: formattedAppliedDate }) }}
    </template>

    <template v-else>
      {{ t('common.actions.apply.applyNow') }}
    </template>
  </button>
</template>
