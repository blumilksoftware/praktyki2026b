<script setup>
import { computed } from 'vue'
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

const isBlocked = computed(() => props.currentStatus === 'blocked')

const form = useForm({ status: '' })

function submit() {
  form.status = isBlocked.value ? 'active' : 'blocked'
  form.patch(ROUTES.ADMIN_USERS_UPDATE_STATUS(props.userId), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="isBlocked ? t('admin.users.blockModal.unblockTitle') : t('admin.users.blockModal.blockTitle')"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="submit">
      <p class="text-additional text-sm leading-relaxed">
        {{ isBlocked
          ? t('admin.users.blockModal.unblockDescription', { name: userName })
          : t('admin.users.blockModal.blockDescription', { name: userName }) }}
      </p>
      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="form.processing" @click="emit('close')">
          {{ t('admin.users.blockModal.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="form.processing">
          {{ isBlocked ? t('admin.users.blockModal.confirmUnblock') : t('admin.users.blockModal.confirmBlock') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
