<script setup>
const { id, label, disabled } = defineProps({
  id: { type: String, required: true },
  label: { type: String, default: undefined },
  disabled: { type: Boolean, default: false },
})

const model = defineModel({ type: Boolean, required: true })

function toggle() {
  if (!disabled) model.value = !model.value
}
</script>

<template>
  <div class="flex items-center gap-3" :aria-disabled="disabled">
    <button
      :id="id"
      type="button"
      role="switch"
      :aria-checked="model"
      :aria-label="label"
      :aria-disabled="disabled"
      :disabled="disabled"
      class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :class="[
        model ? 'bg-secondary' : 'bg-border',
        disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer',
      ]"
      @click="toggle"
    >
      <span
        class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
        :class="model ? 'translate-x-6' : 'translate-x-1'"
      />
    </button>
    <label
      v-if="label"
      :for="id"
      class="text-sm font-medium text-text select-none"
      :class="disabled ? 'cursor-not-allowed' : 'cursor-pointer'"
      @click="toggle"
    >
      {{ label }}
    </label>
    <slot v-else />
  </div>
</template>
