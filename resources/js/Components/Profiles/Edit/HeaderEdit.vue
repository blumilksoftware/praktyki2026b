<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPlus } from '@tabler/icons-vue'

const { t } = useI18n()

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  logoUrl: {
    type: String,
    default: undefined,
  },
})

const emit = defineEmits(['update:logo'])

const fileInput = ref(null)
const isDragging = ref(false)
const previewUrl = ref(null)

const currentImage = computed(() => {
  if (previewUrl.value) return previewUrl.value
  if (props.logoUrl) return props.logoUrl.startsWith('/') ? props.logoUrl : '/' + props.logoUrl
  return null
})

const handleFile = (file) => {
  if (!file || !file.type.startsWith('image/')) return
  
  previewUrl.value = URL.createObjectURL(file)
  emit('update:logo', file)
}

const onDrop = (e) => {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  handleFile(file)
}

const onFileChange = (e) => {
  const file = e.target.files[0]
  handleFile(file)
}

const triggerFileInput = () => {
  fileInput.value.click()
}
</script>

<template>
  <div class="relative flex flex-col items-center">
    <div 
      :class="[
        'w-28 h-28 sm:w-32 sm:h-32 border-4 border-white bg-background shadow-md overflow-hidden flex items-center justify-center shrink-0 text-secondary cursor-pointer relative',
        isDragging ? 'border-primary border-dashed' : ''
      ]"
      @click="triggerFileInput"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
    >
      <input 
        ref="fileInput" 
        type="file" 
        class="hidden" 
        accept="image/*"
        @change="onFileChange"
      >

      <img 
        v-if="currentImage"
        :src="currentImage" 
        alt="Logo firmy" 
        class="w-full h-full object-cover" 
      >
      
      <div 
        class="absolute inset-0 flex flex-col items-center justify-center"
        :class="currentImage ? 'bg-black/40 text-white' : 'text-additional'"
      >
        <IconPlus stroke="1.5" class="w-8 h-8 sm:w-10 sm:h-10 mb-1" />
        <span class="text-[10px] sm:text-xs font-medium leading-tight text-center px-1">
          {{ t('profiles.uploadLogo') }}
        </span>
      </div>
    </div>

    <h1 class="text-2xl sm:text-3xl font-bold text-text mt-4 text-center">
      {{ name }}
    </h1>
  </div>
</template>
