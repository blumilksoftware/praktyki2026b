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
    <div v-else class="xl:hidden gap-4 grid grid-cols-1 md:grid-cols-2">
      <article
        v-for="item in props.items"
        :key="`mobile-${item[props.rowKey]}`"
        class="bg-white/40 p-4 rounded-xl ring-1 ring-black/5"
      >
        <div class="flex justify-between items-center gap-3">
          <p class="font-semibold text-text text-sm">{{ item.name || item[props.rowKey] }}</p>
          <span v-if="item.status" class="inline-flex px-2.5 py-1 rounded-full font-medium text-xs">
            <slot :name="`cell-status`" :item="item">{{ item.status }}</slot>
          </span>
        </div>
        <dl class="space-y-2 mt-3 text-sm">
          <div
            v-for="col in props.columns"
            :key="col.key"
            class="flex sm:flex-row flex-col sm:justify-between gap-1 sm:gap-2"
          >
            <dt class="text-slate-500 shrink-0">{{ col.label }}</dt>
            <dd class="overflow-hidden text-slate-800 sm:text-right break-words">
              <slot :name="`cell-${col.key}`" :item="item">{{ item[col.key] }}</slot>
            </dd>
          </div>
        </dl>
      </article>
    </div>

    <div v-if="props.items.length > 0" class="hidden xl:block bg-white/35 rounded-xl ring-1 ring-black/5 overflow-x-auto">
      <table class="min-w-full text-sm">
        <caption class="sr-only">{{ props.caption || 'Data table' }}</caption>
        <thead class="bg-white/50 font-semibold text-slate-600 text-xs uppercase tracking-wide">
          <tr>
            <th
              v-for="col in props.columns"
              :key="col.key"
              :class="['px-4 py-3', col.align === 'right' ? 'text-right' : 'text-left']"
            >
              <span v-if="col.srLabel" class="sr-only">{{ col.srLabel }}</span>
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
