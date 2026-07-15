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
const errorMessage = ref('')

const MAX_FILE_SIZE_MB = 2
const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp']

const currentImage = computed(() => {
  if (previewUrl.value) return previewUrl.value
  if (props.logoUrl) return props.logoUrl.startsWith('/') ? props.logoUrl : '/' + props.logoUrl
  return null
})

const handleFile = (file) => {
  errorMessage.value = ''
  
  if (!file) return

  if (!ALLOWED_TYPES.includes(file.type)) {
    errorMessage.value = t('profiles.errors.invalidFormat')
    return
  }

  if (file.size > MAX_FILE_SIZE_BYTES) {
    errorMessage.value = t('profiles.errors.fileTooLarge', { maxSize: MAX_FILE_SIZE_MB })
    return
  }
  
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
  
  e.target.value = null 
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
        isDragging ? 'border-primary border-dashed bg-primary/5' : ''
      ]"
      @click="triggerFileInput"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
    >
      <input 
        ref="fileInput"
        :aria-label="t('profiles.uploadLogo')" 
        type="file" 
        class="hidden" 
        accept="image/jpeg, image/png, .jpg, .jpeg, .png"
        @change="onFileChange"
      >

      <img 
        v-if="currentImage"
        :src="currentImage" 
        alt="Logo firmy" 
        class="w-full h-full object-cover" 
      >
      
      <div 
        class="absolute inset-0 flex flex-col items-center justify-center transition-colors"
        :class="currentImage ? 'bg-black/40 text-white opacity-0 hover:opacity-100' : 'text-additional'"
      >
        <IconPlus stroke="1.5" class="w-8 h-8 sm:w-10 sm:h-10 mb-1" />
        <span class="text-[10px] sm:text-xs font-medium leading-tight text-center px-1">
          {{ t('profiles.uploadLogo') }}
        </span>
      </div>
    </div>

    <span v-if="errorMessage" class="text-error text-xs sm:text-sm font-semibold mt-3 text-center">
      {{ errorMessage }}
    </span>

    <h1 class="text-2xl sm:text-3xl font-bold text-text mt-4 text-center">
      {{ name }}
    </h1>
  </div>
</template>
