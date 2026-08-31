<script setup>
defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' },
  disabled: { type: Boolean, default: false },
  widthClass: { type: String, default: 'w-fit' },
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="rounded-lg px-4 py-3 text-sm font-semibold cursor-pointer tracking-wide transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-60"
    :class="[
      widthClass,
      variant === 'primary'
        ? 'bg-primary text-white hover:bg-primary/90'
        : 'border border-border bg-white text-text hover:bg-background',
    ]"
  >
    <slot />
  </button>
</template>
