<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  startId: { type: String, required: true },
  endId: { type: String, required: true },
  startLabel: { type: String, required: true },
  endLabel: { type: String, required: true },
  startError: { type: String, default: undefined },
  endError: { type: String, default: undefined },
  required: { type: Boolean, default: false },
  stacked: { type: Boolean, default: true },
})

const start = defineModel('start', { type: String, required: true })
const end = defineModel('end', { type: String, required: true })

watch(start, (newStart) => {
  if (newStart && end.value && end.value < newStart) {
    end.value = newStart
  }
})

const startFocused = ref(false)
const endFocused = ref(false)

const startFloating = computed(() => startFocused.value || !!start.value)
const endFloating = computed(() => endFocused.value || !!end.value)

const startLabelClasses = computed(() => {
  const base = 'pointer-events-none absolute z-10 origin-left transition-all duration-200 font-medium'

  if (startFloating.value) {
    return `${base} -top-6 inset-s-0 translate-y-0 scale-90 text-sm ${props.startError ? 'text-error' : 'text-text'}`
  }

  return `${base} top-1/2 -translate-y-1/2 inset-s-4 scale-100 text-base ${props.startError ? 'text-error' : 'text-additional'}`
})

const endLabelClasses = computed(() => {
  const base = 'pointer-events-none absolute z-10 origin-left transition-all duration-200 font-medium'

  if (endFloating.value) {
    return `${base} -top-6 inset-s-0 translate-y-0 scale-90 text-sm ${props.endError ? 'text-error' : 'text-text'}`
  }

  return `${base} top-1/2 -translate-y-1/2 inset-s-4 scale-100 text-base ${props.endError ? 'text-error' : 'text-additional'}`
})
</script>

<template>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="flex w-full flex-col gap-1.5" :class="stacked ? '' : 'pt-6'">
      <label v-if="stacked" :for="startId" class="text-sm font-medium text-text">
        {{ startLabel }}
        <span v-if="required" aria-hidden="true" class="text-error">*</span>
      </label>

      <div class="relative">
        <input
          :id="startId"
          v-model="start"
          type="date"
          :required="required"
          :max="end || undefined"
          :aria-invalid="startError ? true : undefined"
          :aria-describedby="startError ? `${startId}-error` : undefined"
          class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
          :class="[
            startError ? 'border-error focus:border-error focus:ring-error/30' : '',
            !stacked && !startFloating ? 'text-transparent' : '',
          ]"
          @focus="startFocused = true"
          @blur="startFocused = false"
        >

        <label v-if="!stacked" :for="startId" :class="startLabelClasses">
          {{ startLabel }}
          <span v-if="required" aria-hidden="true" class="text-error">*</span>
        </label>
      </div>

      <p v-if="startError" :id="`${startId}-error`" class="text-sm text-error" role="alert">
        {{ startError }}
      </p>
    </div>

    <div class="flex w-full flex-col gap-1.5" :class="stacked ? '' : 'pt-6'">
      <label v-if="stacked" :for="endId" class="text-sm font-medium text-text">
        {{ endLabel }}
        <span v-if="required" aria-hidden="true" class="text-error">*</span>
      </label>

      <div class="relative">
        <input
          :id="endId"
          v-model="end"
          type="date"
          :required="required"
          :min="start || undefined"
          :aria-invalid="endError ? true : undefined"
          :aria-describedby="endError ? `${endId}-error` : undefined"
          class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
          :class="[
            endError ? 'border-error focus:border-error focus:ring-error/30' : '',
            !stacked && !endFloating ? 'text-transparent' : '',
          ]"
          @focus="endFocused = true"
          @blur="endFocused = false"
        >

        <label v-if="!stacked" :for="endId" :class="endLabelClasses">
          {{ endLabel }}
          <span v-if="required" aria-hidden="true" class="text-error">*</span>
        </label>
      </div>

      <p v-if="endError" :id="`${endId}-error`" class="text-sm text-error" role="alert">
        {{ endError }}
      </p>
    </div>
  </div>
</template>
