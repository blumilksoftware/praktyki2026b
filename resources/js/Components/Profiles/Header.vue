<script setup>
import { IconUser } from '@tabler/icons-vue'

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  logoUrl: {
    type: String,
    default: undefined,
  },
})
</script>

<template>
  <div class="relative flex flex-col items-center">
    <div class="w-28 h-28 sm:w-32 sm:h-32 border-4 border-white bg-background shadow-md overflow-hidden flex items-center justify-center shrink-0 text-secondary">
      <img 
        v-if="logoUrl"
        :src="logoUrl.startsWith('/') ? logoUrl : '/' + logoUrl"
        alt=""
        class="w-full h-full object-cover"
      >
      
      <IconUser
        v-else
        stroke="1.5"
        class="w-12 h-12 sm:w-16 sm:h-16 text-additional"
        aria-hidden="true"
      />
    </div>

    <h1 class="text-2xl sm:text-3xl font-bold text-text mt-4 text-center">
      {{ name }}
    </h1>
  </div>
</template>
