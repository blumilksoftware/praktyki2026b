<script setup>
const props = defineProps({
  applications: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <div class="space-y-3">
    <div class="sm:hidden space-y-3">
      <article
        v-for="application in props.applications"
        :key="`mobile-${application.id}`"
        class="rounded-xl bg-white/40 p-4 ring-1 ring-black/5"
      >
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-semibold text-text">{{ application.id }}</p>
          <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClass(application.status)">
            {{ application.status }}
          </span>
        </div>
        <dl class="mt-3 space-y-1 text-sm">
          <div class="flex justify-between gap-2">
            <dt class="text-slate-500">Student</dt>
            <dd class="text-slate-800">{{ application.student }}</dd>
          </div>
          <div class="flex justify-between gap-2">
            <dt class="text-slate-500">Firma</dt>
            <dd class="text-slate-800">{{ application.company }}</dd>
          </div>
        </dl>
      </article>
    </div>

    <div class="hidden sm:block overflow-x-auto rounded-xl ring-1 ring-black/5 bg-white/35">
      <table class="min-w-full text-sm">
        <caption class="sr-only">Lista zgloszen kandydatow</caption>
        <thead class="bg-white/50 text-xs font-semibold uppercase tracking-wide text-slate-600">
          <tr>
            <th scope="col" class="px-4 py-3 text-left">ID</th>
            <th scope="col" class="px-4 py-3 text-left">Student</th>
            <th scope="col" class="px-4 py-3 text-left">Firma</th>
            <th scope="col" class="px-4 py-3 text-right">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="application in props.applications"
            :key="application.id"
            class="border-t border-white/60"
          >
            <th scope="row" class="px-4 py-3 text-left font-medium text-text">{{ application.id }}</th>
            <td class="px-4 py-3 text-slate-700">{{ application.student }}</td>
            <td class="px-4 py-3 text-slate-700">{{ application.company }}</td>
            <td class="px-4 py-3 text-right">
              <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium">
                {{ application.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
