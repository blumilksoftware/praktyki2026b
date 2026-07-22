<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPhoto } from '@tabler/icons-vue'
import ProfileAvatar from '@/Components/Student/ProfileAvatar.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const props = defineProps({
  photoUrl: { type: String, default: null },
  firstName: { type: String, default: '' },
  lastName: { type: String, default: '' },
  serverError: { type: String, default: null },
})

const pendingFile = defineModel('pendingFile', { type: Object, default: null })

const { t } = useI18n()

const fileInput = ref(null)
const isDragging = ref(false)
const previewUrl = ref(null)
const localError = ref(null)
const MAX_SIZE_BYTES = 2 * 1024 * 1024

const displayPhotoUrl = computed(() => previewUrl.value ?? props.photoUrl)
const hasPhotoPending = computed(() => Boolean(pendingFile.value))
const photoError = computed(() => localError.value || props.serverError)

function revokePreview() {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

function validatePhoto(file) {
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

function setPendingPhoto(file) {
  localError.value = null
  if (!validatePhoto(file)) return
  revokePreview()
  pendingFile.value = file
  previewUrl.value = URL.createObjectURL(file)
}

function onDrop(event) {
  isDragging.value = false
  const file = event.dataTransfer?.files?.[0]
  if (file) setPendingPhoto(file)
}

function onFileSelected(event) {
  const file = event.target.files?.[0]
  if (file) setPendingPhoto(file)
  event.target.value = ''
}

function clearPending() {
  pendingFile.value = null
  revokePreview()
  localError.value = null
}

watch(pendingFile, (file) => {
  if (!file) revokePreview()
})

onBeforeUnmount(revokePreview)

defineExpose({ clearPending })
</script>

<template>
  <div class="flex w-full flex-col items-start text-left">
    <div class="flex items-center gap-4">
      <ProfileAvatar
        :photo-url="displayPhotoUrl"
        :first-name="firstName"
        :last-name="lastName"
        size-class="w-20 h-20 text-xl"
      />
      <div>
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
      class="mt-4 w-full rounded-xl border-2 border-dashed px-6 py-8 text-center transition"
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
          class="font-semibold text-primary hover:text-primary/80"
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

    <p v-if="hasPhotoPending" class="mt-2 text-additional text-xs">
      {{ t('student.profile.photo.previewHint') }}
    </p>
    <p v-if="photoError" class="mt-2 text-error text-sm" role="alert">{{ photoError }}</p>
  </div>
</template>
