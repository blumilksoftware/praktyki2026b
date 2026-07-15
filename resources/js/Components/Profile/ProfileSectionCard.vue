<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: undefined },
})
</script>

<template>
  <section
    class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm"
    :aria-label="title"
  >
    <header class="mb-4 flex items-start justify-between gap-4">
      <div>
        <h2 class="font-semibold text-text text-base">
          {{ title }}
        </h2>
        <p
          v-if="description"
          class="mt-1 text-additional text-sm"
        >
          {{ description }}
        </p>
      </div>
      <slot name="actions" />
    </header>
    <slot />
  </section>
</template>
