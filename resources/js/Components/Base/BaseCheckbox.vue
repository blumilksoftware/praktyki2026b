<script setup>
defineProps({
  id: { type: String, required: true },
})

const model = defineModel({ type: Boolean, required: true })
</script>

<template>
  <label
    :for="id"
    class="flex items-start gap-2 cursor-pointer text-sm text-text"
  >
    <input
      :id="id"
      v-model="model"
      type="checkbox"
      class="mt-0.5 rounded border-border cursor-pointer text-primary focus:ring-primary/40"
    >
    <span class="leading-snug">
      <slot />
    </span>
  </label>
</template>
