<script setup>
import { useI18n } from 'vue-i18n'

/** @typedef {{key: string, label: string, align?: 'left' | 'right'}} Column */

const props = defineProps({
  items: { type: Array, default: () => [] },
  columns: { type: Array, default: () => [] },
  rowKey: { type: String, default: '' },
  caption: { type: String, default: '' },
})

const { t } = useI18n()
</script>

<template>
  <div class="space-y-3">
    <div v-if="props.items.length === 0" class="py-8 text-slate-500 text-center">
      {{ t('table.noData') }}
    </div>
    <div v-else class="sm:hidden space-y-3">
      <article
        v-for="item in props.items"
        :key="`mobile-${item[props.rowKey]}`"
        class="bg-white/40 p-4 rounded-xl ring-1 ring-black/5"
      >
        <div class="flex justify-between items-center gap-3">
          <p class="font-semibold text-text text-sm">{{ item[props.rowKey] }}</p>
          <span v-if="item.status" class="inline-flex px-2.5 py-1 rounded-full font-medium text-xs">
            <slot :name="`cell-status`" :item="item">{{ item.status }}</slot>
          </span>
        </div>
        <dl class="space-y-1 mt-3 text-sm">
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

    <div v-if="props.items.length > 0" class="hidden sm:block bg-white/35 rounded-xl ring-1 ring-black/5 overflow-x-auto">
      <table class="min-w-full text-sm">
        <caption class="sr-only">{{ props.caption || 'Data table' }}</caption>
        <thead class="bg-white/50 font-semibold text-slate-600 text-xs uppercase tracking-wide">
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
          <tr v-for="item in props.items" :key="item[props.rowKey]" class="border-white/60 border-t">
            <td
              v-for="col in props.columns"
              :key="col.key"
              :class="['px-4 py-3', col.align === 'right' ? 'text-right' : 'text-left']"
            >
              <span v-if="col.key === 'status'" class="inline-flex px-2.5 py-1 rounded-full font-medium text-xs">
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
