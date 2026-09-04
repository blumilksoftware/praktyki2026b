<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'
</script>

<template>
  <Link :href="ROUTES.HOME" aria-label="Applikuj" class="flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded-sm">
    <img
      src="/logo.svg"
      alt=""
      class="block h-7 w-auto md:hidden"
    >

    <img
      src="/logo_text.svg"
      alt=""
      class="hidden h-8 w-auto md:block"
    >
  </Link>
</template>
