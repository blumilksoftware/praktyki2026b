<script setup>
import { computed } from 'vue'
import { useApplicationStatus } from '@/composables/useApplicationStatus'

const props = defineProps({
  status: { type: String, required: true },
})

const { statusClass, statusLabel } = useApplicationStatus()

const label = computed(() => statusLabel(props.status))
const classes = computed(() => statusClass(props.status))
</script>

<template>
  <span
    class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold"
    :class="classes"
  >
    {{ label }}
  </span>
</template>
