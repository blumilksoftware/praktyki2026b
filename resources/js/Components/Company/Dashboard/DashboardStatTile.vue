<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({
  label: { type: String, required: true },
  value: { type: [Number, String], required: true },
  href: { type: String, required: true },
  icon: { type: [Object, Function], required: true },
})
</script>

<template>
  <Link
    :href="href"
    class="group flex items-start gap-3 rounded-xl border border-border px-3 py-2.5 shadow-sm transition hover:bg-background hover:shadow"
  >
    <component :is="icon" class="mt-0.5 h-4 w-4 shrink-0 text-additional transition group-hover:text-primary" aria-hidden="true" />
    <span class="min-w-0 flex-1 line-clamp-2 text-sm leading-snug text-additional transition group-hover:text-text">
      {{ label }}
    </span>
    <span class="shrink-0 text-sm font-semibold text-text">
      {{ value }}
    </span>
  </Link>
</template>
