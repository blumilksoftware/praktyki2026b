<script setup>
defineProps({
  id: { type: String, required: true },
  label: { type: String, default: undefined },
})

const model = defineModel({ type: Boolean, required: true })
</script>

<template>
  <label
    :for="id"
    class="flex items-start gap-2 cursor-pointer text-sm text-text group"
  >
    <input
      :id="id"
      v-model="model"
      type="checkbox"
      class="mt-0.5 rounded border-border cursor-pointer text-primary focus:ring-primary/40"
    >
    <span class="leading-snug group-hover:text-primary transition-colors">
      <template v-if="label">{{ label }}</template>
      <slot v-else />
    </span>
  </label>
</template>
