<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PersonPreviewModal from '@/Components/Common/PersonPreviewModal.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, required: true },
  member: { type: Object, default: null },
})
const emit = defineEmits(['close'])
const { t } = useI18n()

const details = computed(() => props.member ? [
  { label: t('organization.team.memberPreview.email'), value: props.member.email },
  {
    label: t('organization.team.memberPreview.joined'),
    value: t('organization.team.joinDate', {
      date: props.member.joinedAt
        ? new Intl.DateTimeFormat('pl-PL', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(props.member.joinedAt))
        : '',
    }),
  },
] : [])
</script>

<template>
  <PersonPreviewModal
    :open="open"
    :title="title"
    :name="member?.name"
    :subtitle="member ? t(`organization.team.roles.${member.role}`) : null"
    :details="details"
    :close-label="t('organization.team.memberPreview.close')"
    @close="emit('close')"
  />
</template>
