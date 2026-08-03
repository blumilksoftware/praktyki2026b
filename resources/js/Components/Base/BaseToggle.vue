<script setup>
defineProps({
  id: { type: String, required: true },
  label: { type: String, default: undefined },
})

const model = defineModel({ type: Boolean, required: true })
</script>

<template>
  <div class="flex items-center gap-3">
    <button
      :id="id"
      type="button"
      role="switch"
      :aria-checked="model"
      class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer items-center rounded-full transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :class="model ? 'bg-secondary' : 'bg-border'"
      @click="model = !model"
    >
      <span
        class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
        :class="model ? 'translate-x-6' : 'translate-x-1'"
      />
    </button>
    <label v-if="label" :for="id" class="cursor-pointer text-sm font-medium text-text select-none" @click="model = !model">
      {{ label }}
    </label>
    <slot v-else />
  </div>
</template>
