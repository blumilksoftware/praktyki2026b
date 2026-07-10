<script setup>
import { computed } from 'vue'

const props = defineProps({
  id: { type: String, required: true },
  label: { type: String, required: true },
  options: {
    type: Array,
    required: true,
    // Array of { value, label }
  },
  error: { type: String, default: undefined },
  required: { type: Boolean, default: false },
  placeholder: { type: String, default: undefined },
})

const model = defineModel({ type: String, required: true })

const hasError = computed(() => !!props.error)
</script>

<template>
  <div class="flex flex-col gap-1.5 w-full">
    <label :for="id" class="text-sm font-medium text-text">
      {{ label }}
      <span v-if="required" aria-hidden="true" class="text-error">*</span>
    </label>

    <select
      :id="id"
      v-model="model"
      :required="required"
      :aria-invalid="hasError ? true : undefined"
      :aria-describedby="error ? `${id}-error` : undefined"
      class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
      :class="hasError ? 'border-error focus:border-error focus:ring-error/30' : ''"
    >
      <option v-if="placeholder" value="" disabled>
        {{ placeholder }}
      </option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>

    <p
      v-if="error"
      :id="`${id}-error`"
      class="text-sm text-error"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>
