<script setup>
import { computed } from 'vue'

const props = defineProps({
  id: { type: String, required: true },
  label: { type: String, required: true },
  error: { type: String, default: undefined },
  required: { type: Boolean, default: false },
  maxlength: { type: [Number, String], default: undefined },
  rows: { type: [Number, String], default: 5 },
})

const model = defineModel({ type: String, required: true })

const hasError = computed(() => !!props.error)
const characterCount = computed(() => model.value?.length ?? 0)
</script>

<template>
  <div class="flex flex-col gap-1.5 w-full">
    <label :for="id" class="text-sm font-medium text-text">
      {{ label }}
      <span v-if="required" aria-hidden="true" class="text-error">*</span>
    </label>

    <textarea
      :id="id"
      v-model="model"
      :required="required"
      :maxlength="maxlength"
      :rows="rows"
      :aria-invalid="hasError ? true : undefined"
      :aria-describedby="error ? `${id}-error` : undefined"
      class="w-full resize-y rounded-lg border border-border bg-white px-4 py-3 text-base text-text focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
      :class="hasError ? 'border-error focus:border-error focus:ring-error/30' : ''"
    />

    <div class="flex items-center justify-between gap-2">
      <p
        v-if="error"
        :id="`${id}-error`"
        class="text-sm text-error"
        role="alert"
      >
        {{ error }}
      </p>
      <span v-else />
      <span v-if="maxlength" class="text-xs text-additional whitespace-nowrap">
        {{ characterCount }}/{{ maxlength }}
      </span>
    </div>
  </div>
</template>
