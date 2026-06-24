<script setup>
defineProps({
  title: { type: String, default: undefined },
})
</script>

<template>
  <div class="min-h-screen bg-secondary flex justify-center px-4 py-10 md:py-16">
    <div
      class="w-full max-w-4xl bg-white border border-text shadow-md rounded-sm px-6 py-12 md:px-12 md:pt-20 md:pb-16"
    >
      <h1
        v-if="title"
        class="text-center text-4xl md:text-6xl font-normal text-text mb-10 md:mb-12"
      >
        {{ title }}
      </h1>
      <slot />
    </div>
  </div>
</template>
