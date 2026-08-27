<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  title: { type: String, required: true },
  primaryLabel: { type: String, required: true },
  primaryValue: { type: [Number, String], required: true },
  primaryHref: { type: String, required: true },
  icon: { type: [Object, Function], required: true },
  accent: { type: String, default: 'text-primary' },
  accentBg: { type: String, default: 'bg-primary/10' },
  cardBg: { type: String, default: 'bg-white' },
})
</script>

<template>
  <section class="rounded-2xl border border-border p-5 shadow-sm" :class="cardBg">
    <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-additional">
      {{ title }}
    </p>

    <Link
      :href="primaryHref"
      class="group mt-3 flex items-center gap-3 rounded-xl transition hover:opacity-90"
    >
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" :class="accentBg">
        <component :is="icon" class="h-5 w-5" :class="accent" aria-hidden="true" />
      </div>
      <div class="min-w-0">
        <p class="text-2xl font-semibold leading-none text-text">
          {{ primaryValue }}
        </p>
        <p class="mt-1 truncate text-xs text-additional group-hover:text-primary">
          {{ primaryLabel }}
        </p>
      </div>
    </Link>
    <div class="mt-3 flex flex-col border-t border-border">
      <slot />
    </div>
  </section>
</template>
