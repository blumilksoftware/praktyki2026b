<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Base/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  userId: { type: String, default: null },
  userName: { type: String, default: '' },
  currentStatus: { type: String, default: '' },
  open: { type: Boolean, required: true },
})
const emit = defineEmits(['close'])
const { t } = useI18n()
const form = useForm()

const isLastOrganizationMember = ref(false)
const impactLoading = ref(false)

watch(() => props.open, async (open) => {
  if (!open || !props.userId) return
  isLastOrganizationMember.value = false
  impactLoading.value = true
  try {
    const { data } = await axios.get(ROUTES.ADMIN_USER_DELETION_IMPACT(props.userId))
    isLastOrganizationMember.value = data.isLastOrganizationMember
  } finally {
    impactLoading.value = false
  }
}, { immediate: true })

function submit() {
  form.delete(ROUTES.ADMIN_DELETE_USER(props.userId), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('admin.users.deleteModal.deleteTitle')"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="submit">
      <p class="text-additional text-sm leading-relaxed">
        {{ isLastOrganizationMember
          ? t('admin.users.deleteModal.deleteDescriptionLastMember', { name: userName })
          : t('admin.users.deleteModal.deleteDescription', { name: userName }) }}
      </p>
      <p
        v-if="isLastOrganizationMember"
        class="rounded-md bg-red-50 px-3 py-2 text-sm leading-relaxed text-red-700"
      >
        {{ t('admin.users.deleteModal.lastMemberWarning') }}
      </p>
      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="form.processing" @click="emit('close')">
          {{ t('admin.users.deleteModal.cancel') }}
        </BaseButton>
        <BaseButton
          type="submit"
          :variant="'primary'"
          :disabled="form.processing || impactLoading"
        >
          {{ isLastOrganizationMember
            ? t('admin.users.deleteModal.confirmDeleteWithOrganization')
            : t('admin.users.deleteModal.confirmDelete') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
