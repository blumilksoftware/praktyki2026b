<script setup>
const props = defineProps({
  label: { type: String, required: true },
  variant: { type: String, default: 'default' },
})

const variantClasses = {
  default: 'rounded-full border border-slate-400 bg-slate-100 px-4 py-1.5 text-sm font-medium text-text',
  profile: 'rounded-full border border-slate-400 bg-slate-100 px-4 py-1.5 text-sm font-semibold text-text',
}
</script>

<template>
  <span
    class="inline-flex items-center"
    :class="variantClasses[props.variant] ?? variantClasses.default"
  >
    {{ label }}
  </span>
</template>
