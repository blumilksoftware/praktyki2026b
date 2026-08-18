<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  organizationId: { type: String, default: null },
  organizationName: { type: String, default: '' },
  entityType: { type: String, default: 'university' },
  open: { type: Boolean, required: true },
})

const emit = defineEmits(['close'])
const { t } = useI18n()

const form = useForm({})

const deleteUrl = computed(() => (props.entityType === 'company'
  ? ROUTES.ADMIN_COMPANY_DELETE(props.organizationId)
  : ROUTES.ADMIN_UNIVERSITY_DELETE(props.organizationId)))

function submit() {
  form.delete(deleteUrl.value, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('admin.verification.deleteTitle')"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="submit">
      <p class="text-additional text-sm leading-relaxed">
        {{ t('admin.verification.deleteDescription', { name: organizationName }) }}
      </p>
      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="form.processing" @click="emit('close')">
          {{ t('admin.verification.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="form.processing">
          {{ t('admin.verification.confirmDelete') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
