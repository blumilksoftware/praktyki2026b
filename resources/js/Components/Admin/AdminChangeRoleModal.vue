<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseSelect from '@/Components/Base/BaseSelect.vue'
import { ROUTES } from '@/Helpers/routes'

const COMPANY_ROLES = ['companyAdmin', 'companyMember']
const UNIVERSITY_ROLES = ['universityAdmin', 'universityMember']

const props = defineProps({
  userId: { type: String, default: null },
  userName: { type: String, default: '' },
  currentRole: { type: String, default: '' },
  currentOrganizationId: { type: String, default: '' },
  roles: { type: Array, default: () => [] },
  companies: { type: Array, default: () => [] },
  universities: { type: Array, default: () => [] },
  open: { type: Boolean, required: true },
})

const emit = defineEmits(['close'])
const { t } = useI18n()

const form = useForm({ role: props.currentRole, organization_id: props.currentOrganizationId })

const roleOptions = computed(() =>
  props.roles.map(role => ({ value: role, label: t(`admin.users.roles.${role}`) })),
)

const needsCompany = computed(() => COMPANY_ROLES.includes(form.role))
const needsUniversity = computed(() => UNIVERSITY_ROLES.includes(form.role))
const needsOrganization = computed(() => needsCompany.value || needsUniversity.value)

const organizationOptions = computed(() => {
  const organizations = needsCompany.value ? props.companies : props.universities

  return organizations.map(organization => ({ value: organization.id, label: organization.name }))
})

const organizationLabel = computed(() =>
  needsCompany.value ? t('admin.users.modal.company') : t('admin.users.modal.university'),
)

watch(() => form.role, () => {
  form.organization_id = form.role === props.currentRole ? props.currentOrganizationId : ''
})

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
      <BaseSelect
        v-if="needsOrganization"
        id="userOrganization"
        v-model="form.organization_id"
        :label="organizationLabel"
        :options="organizationOptions"
        :placeholder="t('admin.users.modal.organizationPlaceholder')"
        :error="form.errors.organization_id"
        required
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
