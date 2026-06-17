<script setup>
const props = defineProps({
  items: { type: Array, required: true },
  columns: { type: Array, required: true },
  rowKey: { type: String, default: 'id' },
  caption: { type: String, default: '' },
})
</script>

<template>
  <div class="space-y-3">
    <div class="sm:hidden space-y-3">
      <article
        v-for="item in props.items"
        :key="`mobile-${item[props.rowKey]}`"
        class="rounded-xl bg-white/40 p-4 ring-1 ring-black/5"
      >
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-semibold text-text">{{ item[props.rowKey] }}</p>
          <span v-if="item.status" class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium">
            <slot :name="`cell-status`" :item="item">{{ item.status }}</slot>
          </span>
        </div>
        <dl class="mt-3 space-y-1 text-sm">
          <div
            v-for="col in props.columns"
            :key="col.key"
            class="flex justify-between gap-2"
          >
            <dt class="text-slate-500">{{ col.label }}</dt>
            <dd class="text-slate-800">
              <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
            </dd>
          </div>
        </dl>
      </article>
    </div>

    <div class="hidden sm:block overflow-x-auto rounded-xl ring-1 ring-black/5 bg-white/35">
      <table class="min-w-full text-sm">
        <caption class="sr-only">{{ props.caption || 'Data table' }}</caption>
        <thead class="bg-white/50 text-xs font-semibold uppercase tracking-wide text-slate-600">
          <tr>
            <th
              v-for="col in props.columns"
              :key="col.key"
              :class="['px-4 py-3', col.align === 'right' ? 'text-right' : 'text-left']"
            >
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in props.items" :key="item[props.rowKey]" class="border-t border-white/60">
            <td
              v-for="col in props.columns"
              :key="col.key"
              :class="['px-4 py-3', col.align === 'right' ? 'text-right' : 'text-left']"
            >
              <span v-if="col.key === 'status'" class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium">
                <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
              </span>
              <template v-else>
                <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
