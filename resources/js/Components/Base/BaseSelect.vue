<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <select
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
    class="rounded-xl border-border text-text shadow-sm focus:border-primary focus:ring focus:ring-primary/50 bg-white py-2.5 cursor-pointer transition-all disabled:cursor-not-allowed disabled:opacity-75 w-full"
  >
    <slot />
  </select>
</template>