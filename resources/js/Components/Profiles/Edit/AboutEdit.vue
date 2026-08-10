<script setup>
import { computed, ref, onMounted, watch, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const maxLength = 2500
const textareaRef = ref(null)

const description = computed({
  get: () => props.modelValue || '',
  set: (value) => emit('update:modelValue', value),
})

const autoResize = () => {
  const el = textareaRef.value
  if (!el) return
  
  el.style.height = 'auto'
  el.style.height = `${el.scrollHeight}px`
}

onMounted(() => {
  nextTick(() => {
    autoResize()
  })
})

watch(description, () => {
  nextTick(() => {
    autoResize()
  })
})
</script>

<template>
  <div class="flex flex-col gap-4">
    <h2 class="text-xl font-bold text-text">{{ t('common.profile.aboutUs') }}</h2>
    
    <div class="border border-border rounded-xl flex flex-col bg-white focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-shadow">
      <textarea
        ref="textareaRef"
        v-model="description"
        :aria-label="t('common.profile.aboutUs')"
        :maxlength="maxLength"
        rows="6"
        class="w-full p-4 text-sm sm:text-base text-gray-700 bg-transparent border-none focus:ring-0 resize-none overflow-hidden min-h-37.5 outline-none"
        @input="autoResize"
      />

      <div class="px-4 py-3 flex justify-end relative">
        <div class="absolute top-0 left-4 right-4 border-t border-gray-100" />
        <span class="text-xs text-gray-500 font-medium mt-1">
          {{ description.length }}/{{ maxLength }}
        </span>
      </div>
    </div>
  </div>
</template>
