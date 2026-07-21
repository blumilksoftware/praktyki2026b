<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'
</script>

<template>
  <Link :href="ROUTES.HOME" class="flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 rounded-sm">
    <img src="/logo.svg" alt="Applikuj" class="block md:hidden h-8 w-auto">
    
    <img src="/logo_text.svg" alt="Applikuj" class="hidden md:block h-10 w-auto">
  </Link>
</template>
