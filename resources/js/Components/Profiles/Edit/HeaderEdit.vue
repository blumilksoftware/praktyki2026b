<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPlus } from '@tabler/icons-vue'

const props = defineProps({
  name: { type: String, default: '' },
  logoUrl: { type: String, default: null },
  serverError: { type: String, default: null },
})

const emit = defineEmits(['update:logo'])

const { t } = useI18n()

const fileInput = ref(null)
const isDragging = ref(false)
const previewUrl = ref(null)
const localError = ref(null)
const MAX_SIZE_MB = 2
const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024

const displayLogoUrl = computed(() => {
  if (previewUrl.value) return previewUrl.value
  if (props.logoUrl) return props.logoUrl.startsWith('/') ? props.logoUrl : '/' + props.logoUrl
  return null
})

const logoError = computed(() => localError.value || props.serverError)

function revokePreview() {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

function validateLogo(file) {
  const name = file.name.toLowerCase()
  const ok = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)
    || name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.png') || name.endsWith('.webp')

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

function setLogo(file) {
  localError.value = null
  if (!validateLogo(file)) return

  revokePreview()
  previewUrl.value = URL.createObjectURL(file)

  emit('update:logo', file)
}

function openFileDialog() {
  fileInput.value?.click()
}

function onDragOver() {
  isDragging.value = true
}

function onDragLeave() {
  isDragging.value = false
}

function onDrop(event) {
  isDragging.value = false
  const file = event.dataTransfer?.files?.[0]
  if (file) setLogo(file)
}

function onFileSelected(event) {
  const file = event.target.files?.[0]
  if (file) setLogo(file)
  event.target.value = ''
}

onBeforeUnmount(revokePreview)
</script>

<template>
  <div class="w-full flex flex-col items-center text-center">
    <p v-if="name" class="font-medium text-text text-sm">
      {{ name }}
    </p>

    <div
      class="cursor-pointer mt-3 w-24 h-24 rounded-xl border-2 flex items-center justify-center transition overflow-hidden shrink-0"
      :class="isDragging ? 'border-primary border-dashed bg-primary/5' : 'border-border bg-white'"
      role="button"
      tabindex="0"
      @click="openFileDialog"
      @keydown.enter="openFileDialog"
      @keydown.space.prevent="openFileDialog"
      @dragover.prevent="onDragOver"
      @dragenter.prevent="onDragOver"
      @dragleave="onDragLeave"
      @drop.prevent="onDrop"
    >
      <img
        v-if="displayLogoUrl"
        :src="displayLogoUrl"
        :alt="t('profiles.logoAlt')"
        class="w-full h-full object-cover"
      >
      <IconPlus v-else stroke="1.5" class="w-8 h-8 text-secondary" />
    </div>

    <button
      type="button"
      class="mt-2 text-sm font-semibold text-primary hover:text-primary/80 transition-colors"
      @click="openFileDialog"
    >
      {{ t('profiles.uploadLogo') }}
    </button>

    <input
      ref="fileInput"
      type="file"
      accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
      class="sr-only"
      :aria-label="t('profiles.uploadLogo')"
      @change="onFileSelected"
    >

    <p v-if="logoError" class="mt-2 text-error text-sm" role="alert">
      {{ logoError }}
    </p>
  </div>
</template>
