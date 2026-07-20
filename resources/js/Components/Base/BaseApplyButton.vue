<script setup>
import { useI18n } from 'vue-i18n'
import { IconLoader2, IconCheck } from '@tabler/icons-vue'

const { t } = useI18n()

defineProps({
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
</script>

<template>
  <div v-if="!hasCv" class="justify-content items-center flex flex-col gap-2 p-4 border border-border rounded-xl bg-gray-50/50 text-sm w-fit">
    <span class="text-text text-center font-medium">{{ t('buttons.apply.noCvMessage') }}</span>
    <button
      type="button"
      class="text-link hover:text-link/80 font-bold underline underline-offset-4 text-left w-fit transition-colors"
      @click="$emit('uploadCv')"
    >
      {{ t('buttons.apply.uploadCvPrompt') }}
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
      {{ t('buttons.apply.loading') }}
    </template>

    <template v-else-if="isApplied">
      <IconCheck class="w-5 h-5" />
      {{ t('buttons.apply.appliedOn', { date: appliedDate }) }}
    </template>

    <template v-else>
      {{ t('buttons.apply.applyNow') }}
    </template>
  </button>
</template>
