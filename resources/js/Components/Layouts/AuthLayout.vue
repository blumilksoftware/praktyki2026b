<script setup>
defineProps({
  title: { type: String, default: undefined },
})
</script>

<template>
  <div class="min-h-screen bg-background flex justify-center px-4 py-10 md:py-16">
    <div
      class="w-full max-w-4xl bg-white border border-border shadow-md rounded-sm px-6 py-12 md:px-12 md:pt-16 md:pb-14"
    >
      <img
        src="/logo.svg"
        alt="Applikuj logo"
        class="mx-auto mb-8 h-10 w-auto md:h-12"
      >
      <h1
        v-if="title"
        class="text-center text-3xl md:text-4xl font-normal text-text mb-8 md:mb-10"
      >
        {{ title }}
      </h1>
      <slot />
    </div>
  </div>
</template>
