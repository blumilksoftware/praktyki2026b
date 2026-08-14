<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseSelect from '@/Components/Base/BaseSelect.vue'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  userId: { type: String, default: null },
  userName: { type: String, default: '' },
  currentRole: { type: String, default: '' },
  roles: { type: Array, default: () => [] },
  open: { type: Boolean, required: true },
})

const emit = defineEmits(['close'])
const { t } = useI18n()

const form = useForm({ role: props.currentRole })

const roleOptions = computed(() =>
  props.roles.map(role => ({ value: role, label: t(`admin.users.roles.${role}`) })),
)

function submit() {
  form.patch(ROUTES.ADMIN_USERS_UPDATE_ROLE(props.userId), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="t('admin.users.modal.title')"
    max-width-class="max-w-lg"
    @close="emit('close')"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="submit">
      <p class="text-additional text-sm leading-relaxed">
        {{ t('admin.users.modal.description', { name: userName }) }}
      </p>
      <BaseSelect
        id="userRole"
        v-model="form.role"
        :label="t('admin.users.role')"
        :options="roleOptions"
        :error="form.errors.role"
      />
      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="form.processing" @click="emit('close')">
          {{ t('admin.users.modal.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="form.processing">
          {{ t('admin.users.modal.confirm') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
