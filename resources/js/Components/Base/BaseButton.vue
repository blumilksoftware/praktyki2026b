<script setup lang = "ts">
withDefaults(
  defineProps <{
        type: 'button' | 'submit' | 'reset'
        variant: 'primary' | 'outline'
        disabled: boolean
    }>(),
  {
    type: 'button',
    variant: 'primary',
    disabled: false,
  },
)
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="w-full rounded-lg px-4 py-3 text-sm font-semibold
                 uppercase tracking-wide transition
                focus-visible:outline-none focus-visible:ring-2
                focus-visible:ring-primary/40
                disabled:cursor-not-allowed diasbled:opacity-60"
    :class="variant === 'primary'
      ? 'bg-primary text-white hover:bg-primary/90'
      : 'border border-border bg-white text-text hover:bg-secondary'"
  >
    <slot />
  </button>
</template>
