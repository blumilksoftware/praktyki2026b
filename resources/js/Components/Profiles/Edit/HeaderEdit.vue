<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPhoto } from '@tabler/icons-vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const props = defineProps({
  logoUrl: { type: String, default: null },
  serverError: { type: String, default: null },
})

const emit = defineEmits(['update:logo'])

const pendingFile = ref(null)

const { t } = useI18n()

const fileInput = ref(null)
const isDragging = ref(false)
const previewUrl = ref(null)
const localError = ref(null)
const MAX_SIZE_MB = 2
const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024

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
  
  if (!ok) {
    localError.value = t('student.profile.photo.errors.invalidType')
    return false
  }
  if (file.size > MAX_SIZE_BYTES) {
    localError.value = t('student.profile.photo.errors.tooLarge')
    return false
  }
  return true
}

function setPendingLogo(file) {
  localError.value = null
  if (!validateLogo(file)) return
  
  revokePreview()
  pendingFile.value = file
  previewUrl.value = URL.createObjectURL(file)
  
  emit('update:logo', file)
}

function onDrop(event) {
  isDragging.value = false
  const file = event.dataTransfer?.files?.[0]
  if (file) setPendingLogo(file)
}

function onFileSelected(event) {
  const file = event.target.files?.[0]
  if (file) setPendingLogo(file)
  event.target.value = ''
}

function clearPending() {
  pendingFile.value = null
  revokePreview()
  localError.value = null
}

onBeforeUnmount(revokePreview)

defineExpose({ clearPending })
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
        accept="image/jpeg, image/png, image/webp"
        @change="onFileChange"
      >

      <img 
        v-if="currentImage"
        :src="currentImage" 
        alt="Logo" 
        class="w-full h-full object-cover" 
      >
      
      <div class="flex flex-col items-start">
        <p class="font-medium text-text text-sm">
          {{ t('student.profile.photo.label') }}
        </p>
        <BaseButton
          type="button"
          variant="secondary"
          class="mt-2"
          @click="fileInput?.click()"
        >
          {{ t('student.profile.photo.changeButton') }}
        </BaseButton>
      </div>
    </div>

    <input
      ref="fileInput"
      type="file"
      accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
      class="sr-only"
      :aria-label="t('student.profile.photo.dropzone')"
      @change="onFileSelected"
    >
    <div
      class="mt-6 w-full rounded-xl border-2 border-dashed px-6 py-8 flex flex-col items-center justify-center transition"
      :class="isDragging ? 'border-primary bg-primary/5' : 'border-border bg-white'"
      @dragover.prevent
      @dragenter.prevent="isDragging = true"
      @dragleave="isDragging = false"
      @drop.prevent="onDrop"
    >
      <IconPhoto class="mx-auto h-10 w-10 text-additional" aria-hidden="true" />
      <p class="mt-3 text-sm text-text">
        <button
          type="button"
          class="font-semibold text-primary hover:text-primary/80 transition-colors"
          @click="fileInput?.click()"
        >
          {{ t('student.profile.photo.uploadAction') }}
        </button>
        {{ t('student.profile.photo.orDragDrop') }}
      </p>
      <p class="mt-1 text-additional text-xs">
        {{ t('student.profile.photo.formatHint') }}
      </p>
    </div>
    <p v-if="hasLogoPending" class="mt-2 text-additional text-xs font-medium">
      {{ t('student.profile.photo.previewHint') }}
    </p>
    <p v-if="logoError" class="mt-2 text-error text-sm" role="alert">
      {{ logoError }}
    </p>
  </div>
</template> 
