<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PersonPreviewModal from '@/Components/Common/PersonPreviewModal.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  user: { type: Object, default: null },
  companies: { type: Array, default: () => [] },
  universities: { type: Array, default: () => [] },
})
const emit = defineEmits(['close'])
const { t } = useI18n()

const companyMap = computed(() => new Map(props.companies.map(c => [c.id, c.name])))
const universityMap = computed(() => new Map(props.universities.map(u => [u.id, u.name])))

function organizationName(user) {
  if (['companyAdmin', 'companyMember'].includes(user.role)) return companyMap.value.get(user.organization_id) ?? null
  if (['universityAdmin', 'universityMember'].includes(user.role)) return universityMap.value.get(user.organization_id) ?? null
  if (user.role === 'student') return user.university ?? null
  return null
}
function fullName(user) {
  return [user.first_name, user.last_name].filter(Boolean).join(' ') || user.email
}
function formattedDate(date) {
  if (!date) return ''
  return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(date))
}

const details = computed(() => {
  if (!props.user) return []
  const items = [
    { label: t('admin.users.email'), value: props.user.email },
  ]
  const org = organizationName(props.user)
  if (org) items.push({ label: t('admin.users.organization'), value: org })
  items.push({ label: t('admin.users.status'), value: t(`admin.users.statuses.${props.user.status}`) })
  items.push({ label: t('admin.users.createdAt'), value: formattedDate(props.user.created_at) })
  return items
})
</script>

<template>
  <PersonPreviewModal
    :open="open"
    :title="user ? fullName(user) : ''"
    :name="user ? fullName(user) : null"
    :subtitle="user ? t(`admin.users.roles.${user.role}`) : null"
    :details="details"
    :close-label="t('admin.users.close')"
    @close="emit('close')"
  />
</template>
