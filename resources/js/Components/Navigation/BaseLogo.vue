<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'
</script>

<template>
  <Link :href="ROUTES.HOME" class="flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded-sm">
    <img
      src="/logo.svg"
      alt="Applikuj"
      class="block h-8 w-auto md:hidden"
    >

    <img
      src="/logo_text.svg"
      alt="Applikuj"
      class="hidden h-10 w-auto md:block"
    >
  </Link>
</template>
