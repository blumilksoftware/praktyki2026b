<script setup lang = "ts">
defineProps < { id: string } > ()

const model = defineModel<boolean>({ required: true })
</script>

<template>
  <label :for="id"
         class="flex items-start gap-2 cursor-pointer text-sm text-text"
  >
    <input
      :id="id"
      v-model="model"
      type="checkbox"
      class="mt-0.5 rounded border-border text-primary
                    focus:ring-primary/40"
    >
    <span class="leading-snug">
      <slot />
    </span>
  </label>
</template>
