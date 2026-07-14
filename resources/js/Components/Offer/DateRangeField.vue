<script setup>
import { watch } from 'vue'

const props = defineProps({
  startId: { type: String, required: true },
  endId: { type: String, required: true },
  startLabel: { type: String, required: true },
  endLabel: { type: String, required: true },
  startError: { type: String, default: undefined },
  endError: { type: String, default: undefined },
  required: { type: Boolean, default: false },
})

const start = defineModel('start', { type: String, required: true })
const end = defineModel('end', { type: String, required: true })

watch(start, (newStart) => {
  if (newStart && end.value && end.value < newStart) {
    end.value = newStart
  }
})
</script>

<template>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="flex flex-col gap-1.5 w-full">
      <label :for="startId" class="text-sm font-medium text-text">
        {{ startLabel }}
        <span v-if="required" aria-hidden="true" class="text-error">*</span>
      </label>
      <input
        :id="startId"
        v-model="start"
        type="date"
        :required="required"
        :max="end || undefined"
        :aria-invalid="startError ? true : undefined"
        :aria-describedby="startError ? `${startId}-error` : undefined"
        class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
        :class="startError ? 'border-error focus:border-error focus:ring-error/30' : ''"
      >
      <p v-if="startError" :id="`${startId}-error`" class="text-sm text-error" role="alert">
        {{ startError }}
      </p>
    </div>

    <div class="flex flex-col gap-1.5 w-full">
      <label :for="endId" class="text-sm font-medium text-text">
        {{ endLabel }}
        <span v-if="required" aria-hidden="true" class="text-error">*</span>
      </label>
      <input
        :id="endId"
        v-model="end"
        type="date"
        :required="required"
        :min="start || undefined"
        :aria-invalid="endError ? true : undefined"
        :aria-describedby="endError ? `${endId}-error` : undefined"
        class="w-full rounded-lg border border-border bg-white px-4 py-3 text-base text-text focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
        :class="endError ? 'border-error focus:border-error focus:ring-error/30' : ''"
      >
      <p v-if="endError" :id="`${endId}-error`" class="text-sm text-error" role="alert">
        {{ endError }}
      </p>
    </div>
  </div>
</template>
