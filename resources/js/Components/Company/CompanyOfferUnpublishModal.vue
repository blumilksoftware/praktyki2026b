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
  form.patch(ROUTES.COMPANY_OFFERS_DEACTIVATE(offerId), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('company.offer.unpublish.modalTitle')"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="submit">
      <p class="text-additional text-sm leading-relaxed">
        {{ t('company.offer.unpublish.confirmation', { title: offerTitle }) }}
      </p>
      <p class="rounded-lg border border-error/40 bg-error/10 px-3 py-2 text-error text-sm font-medium leading-relaxed">
        {{ t('company.offer.unpublish.irreversibleNotice') }}
      </p>
      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="form.processing" @click="emit('close')">
          {{ t('common.actions.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="form.processing">
          {{ t('common.actions.unpublish') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
