<script setup>
defineProps({
  title: { type: String, default: undefined },
})
</script>

<template>
  <div class="w-full h-full bg-background flex flex-col px-4 py-4 md:py-6">
    <div class="m-auto w-full max-w-6xl bg-white border border-border shadow-lg rounded-xl px-4 py-8 md:px-16 md:py-16">
      <h1 v-if="title" class="text-center text-4xl md:text-5xl font-normal text-text mb-8">
        {{ title }}
      </h1>
      <slot />
    </div>
  </div>
</template>
