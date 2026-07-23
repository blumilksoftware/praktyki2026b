<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { MaskInput } from 'vue-3-mask'

const props = defineProps({
  id: { type: String, required: true },
  label: { type: String, required: true },
  mask: { type: String, required: true },
  type: { type: String, default: 'text' },
  error: { type: String, default: undefined },
  autocomplete: { type: String, default: undefined },
  inputmode: { type: String, default: undefined },
  required: { type: Boolean, default: false },
  placeholder: { type: String, default: '' },
})

const model = defineModel({ type: String, required: true })

const maskInputRef = ref(null)

const hasError = computed(() => Boolean(props.error))

function getInputElement() {
  const element = maskInputRef.value?.$el

  return element instanceof HTMLInputElement ? element : null
}

function syncInputValue(value) {
  const input = getInputElement()

  if (!input) {
    return
  }

  const nextValue = value ?? ''

  if (input.value !== nextValue) {
    input.value = nextValue
  }
}

watch(
  model,
  (value) => {
    nextTick(() => syncInputValue(value))
  },
  { immediate: true },
)

onMounted(() => {
  syncInputValue(model.value)
})

function handleUpdate(value) {
  model.value = value
}
</script>

<template>
  <div class="flex w-full flex-col gap-1.5">
    <label
      :for="id"
      class="mb-1 block text-base font-medium text-additional"
      :class="{ 'text-error': hasError }"
    >
      {{ label }}
    </label>

    <div class="relative">
      <MaskInput
        :id="id"
        ref="maskInputRef"
        :mask="mask"
        :type="type"
        :inputmode="inputmode"
        :autocomplete="autocomplete"
        :required="required"
        :placeholder="placeholder"
        class="w-full rounded-lg border border-border bg-white px-4 py-3 focus:border-text focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
        :class="[
          hasError ? 'border-error focus:border-error focus:ring-error/30' : '',
        ]"
        :aria-invalid="hasError ? true : undefined"
        :aria-describedby="error ? `${id}-error` : undefined"
        @update:model-value="handleUpdate"
      />
    </div>

    <p
      v-if="error"
      :id="`${id}-error`"
      class="text-sm text-error"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>