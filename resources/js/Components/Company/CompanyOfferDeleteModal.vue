<script setup>
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import { ROUTES } from '@/Helpers/routes'

const { offerId, offerTitle, open } = defineProps({
  offerId: { type: String, default: null },
  offerTitle: { type: String, default: '' },
  open: { type: Boolean, required: true },
})

const emit = defineEmits(['close'])
const { t } = useI18n()

const form = useForm({})

function submit() {
  form.delete(ROUTES.COMPANY_OFFERS_DELETE(offerId), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('company.offer.delete.modalTitle')"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="submit">
      <p class="text-additional text-sm leading-relaxed">
        {{ t('company.offer.delete.confirmation', { title: offerTitle }) }}
      </p>
      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="form.processing" @click="emit('close')">
          {{ t('company.offer.delete.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="form.processing">
          {{ t('company.offer.delete.confirm') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
