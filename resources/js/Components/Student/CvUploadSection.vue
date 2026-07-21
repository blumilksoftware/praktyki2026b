<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconEye, IconFile, IconUpload } from '@tabler/icons-vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  cvPath: { type: String, default: null },
})

const { t } = useI18n()

const fileInput = ref(null)
const isDragging = ref(false)
const uploadProgress = ref(0)
const uploadedFileName = ref(null)
const hasLocalCv = ref(false)
const isDeletedLocally = ref(false)
const localError = ref(null)
const previewObjectUrl = ref(null)
const MAX_SIZE_BYTES = 5 * 1024 * 1024

const form = useForm({
  cv: null,
})

watch(() => props.cvPath, () => {
  isDeletedLocally.value = false
})

const hasCv = computed(() => (Boolean(props.cvPath) && !isDeletedLocally.value) || hasLocalCv.value)
const serverPreviewUrl = computed(() => props.cvPath && !isDeletedLocally.value ? ROUTES.STUDENT_CV_PREVIEW : null)
const previewUrl = computed(() => previewObjectUrl.value || serverPreviewUrl.value)
const displayFileName = computed(() => {
  if (uploadedFileName.value) {
    return uploadedFileName.value
  }

  if (hasCv.value) {
    return t('student.cv.defaultFileName')
  }

  return null
})
const displayError = computed(() => localError.value || form.errors.cv)

function revokePreviewObjectUrl() {
  if (previewObjectUrl.value) {
    URL.revokeObjectURL(previewObjectUrl.value)
    previewObjectUrl.value = null
  }
}

function openFilePicker() {
  localError.value = null
  fileInput.value?.click()
}

function refreshProfileState() {
  router.reload({
    only: ['user', 'auth', 'onboarding'],
    preserveScroll: true,
    preserveState: true,
  })
}

function validateFile(file) {
  const fileName = file.name.toLowerCase()
  const isPdf = file.type === 'application/pdf' || fileName.endsWith('.pdf')

  if (!isPdf) {
    localError.value = t('student.cv.errors.invalidType')
    return false
  }

  if (file.size > MAX_SIZE_BYTES) {
    localError.value = t('student.cv.errors.tooLarge')
    return false
  }

  return true
}

function updateProgress(progressEvent) {
  if (typeof progressEvent?.percentage === 'number') {
    uploadProgress.value = progressEvent.percentage
    return
  }

  if (progressEvent?.total) {
    uploadProgress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100)
  }
}

function uploadFile(file) {
  form.cv = file
  uploadProgress.value = 0
  const objectUrl = URL.createObjectURL(file)

  form.post(ROUTES.STUDENT_CV_UPLOAD, {
    forceFormData: true,
    preserveScroll: true,
    onProgress: updateProgress,
    onSuccess: () => {
      revokePreviewObjectUrl()
      previewObjectUrl.value = objectUrl
      uploadedFileName.value = file.name
      hasLocalCv.value = true
      isDeletedLocally.value = false
      form.reset()
      uploadProgress.value = 0
      refreshProfileState()
    },
    onError: () => URL.revokeObjectURL(objectUrl),
  })
}

function handleFile(file) {
  if (!file) {
    return
  }

  localError.value = null

  if (validateFile(file)) {
    uploadFile(file)
  }
}

function onFileSelected(event) {
  handleFile(event.target.files?.[0])

  event.target.value = ''
}

function onDrop(event) {
  isDragging.value = false
  handleFile(event.dataTransfer?.files?.[0])
}

function deleteCv() {
  localError.value = null

  form.delete(ROUTES.STUDENT_CV_DELETE, {
    preserveScroll: true,
    onSuccess: () => {
      uploadedFileName.value = null
      hasLocalCv.value = false
      isDeletedLocally.value = true
      revokePreviewObjectUrl()
      form.reset()
      uploadProgress.value = 0
      refreshProfileState()
    },
  })
}

onBeforeUnmount(revokePreviewObjectUrl)
</script>

<template>
  <section id="student-cv-section">
    <h3 class="mb-1 font-medium text-text text-sm">
      {{ t('student.cv.title') }}
    </h3>
    <p class="mb-3 text-additional text-xs">
      {{ t('student.cv.hint') }}
    </p>

    <input
      ref="fileInput"
      type="file"
      accept=".pdf,application/pdf"
      class="sr-only"
      :aria-label="t('student.cv.selectFile')"
      @change="onFileSelected"
    >

    <div
      v-if="form.processing"
      class="rounded-xl border border-border bg-background px-4 py-3"
      aria-live="polite"
    >
      <p class="text-text text-sm">
        {{ t('student.cv.uploading', { progress: uploadProgress }) }}
      </p>
      <div
        class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200"
        role="progressbar"
        :aria-valuenow="uploadProgress"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <div
          class="h-2 rounded-full bg-primary transition-all duration-300"
          :style="{ width: `${uploadProgress}%` }"
        />
      </div>
    </div>

    <div
      v-else-if="hasCv && displayFileName"
      class="rounded-xl border border-border bg-background px-4 py-4"
    >
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
        <div class="flex min-w-0 flex-1 items-center gap-2">
          <IconFile class="h-5 w-5 shrink-0 text-primary" aria-hidden="true" />
          <span class="truncate text-sm font-medium text-text">
            {{ displayFileName }}
          </span>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:shrink-0 sm:items-center sm:gap-3">
          <div class="flex items-center gap-4">
            <a
              v-if="previewUrl && !form.processing"
              :href="previewUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex cursor-pointer items-center gap-1 text-primary text-sm font-medium hover:underline"
            >
              <IconEye class="h-4 w-4 shrink-0" aria-hidden="true" />
              {{ t('student.cv.preview') }}
            </a>
            <span
              v-else
              class="inline-flex items-center gap-1 text-primary text-sm font-medium opacity-60"
              aria-hidden="true"
            >
              <IconEye class="h-4 w-4 shrink-0" aria-hidden="true" />
              {{ t('student.cv.preview') }}
            </span>
            <button
              type="button"
              class="inline-flex cursor-pointer items-center text-red-600 text-sm font-medium hover:text-red-700 hover:underline disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="form.processing"
              @click="deleteCv"
            >
              {{ t('student.cv.delete') }}
            </button>
          </div>
          <BaseButton
            type="button"
            variant="secondary"
            class="w-full justify-center sm:w-auto"
            :disabled="form.processing"
            @click="openFilePicker"
          >
            {{ t('student.cv.replace') }}
          </BaseButton>
        </div>
      </div>
    </div>

    <button
      v-else
      type="button"
      class="flex w-full cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 py-8 text-additional transition hover:border-primary/50 hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
      :class="isDragging ? 'border-primary bg-primary/5 text-text' : 'border-border'"
      @dragover.prevent
      @dragenter.prevent="isDragging = true"
      @dragleave="isDragging = false"
      @drop.prevent="onDrop"
      @click="openFilePicker"
    >
      <IconUpload class="h-8 w-8" aria-hidden="true" />
      <span class="text-sm font-medium">
        {{ t('student.cv.selectFile') }}
      </span>
      <span class="text-xs">
        {{ t('student.cv.dropzone') }}
      </span>
    </button>

    <p
      v-if="displayError"
      class="mt-2 text-error text-sm"
      role="alert"
    >
      {{ displayError }}
    </p>
  </section>
</template>
