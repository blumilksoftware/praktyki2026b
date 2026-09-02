<script setup>
import { computed } from 'vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  user: {
    type: Object,
    default: null,
  },
  companies: {
    type: Array,
    default: () => [],
  },
  universities: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close'])

const companyMap = computed(() =>
  new Map(props.companies.map(company => [company.id, company.name])),
)
const universityMap = computed(() =>
  new Map(props.universities.map(university => [university.id, university.name])),
)

function organizationName(user) {
  if (['companyAdmin', 'companyMember'].includes(user.role)) {
    return companyMap.value.get(user.organization_id) ?? null
  }
  if (['universityAdmin', 'universityMember'].includes(user.role)) {
    return universityMap.value.get(user.organization_id) ?? null
  }
  if (user.role === 'student') {
    return user.university ?? null
  }
  return null
}

function fullName(user) {
  return [user.first_name, user.last_name].filter(Boolean).join(' ') || user.email
}

function formattedDate(date) {
  if (!date) return ''
  return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(date))
}
</script>

<template>
  <BaseModal
    :open="open"
    :title="user ? fullName(user) : ''"
    @close="emit('close')"
  >
    <div v-if="user" class="flex flex-col gap-5">
      <div class="flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-background text-lg font-semibold text-additional">
          {{ fullName(user).charAt(0) || 'U' }}
        </div>
        <div class="min-w-0">
          <p class="truncate text-lg font-semibold text-text">
            {{ fullName(user) }}
          </p>
          <p class="text-sm text-additional">
            {{ $t(`admin.users.roles.${user.role}`) }}
          </p>
        </div>
      </div>
      <dl class="space-y-3">
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('admin.users.email') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ user.email }}
          </dd>
        </div>
        <div v-if="organizationName(user)">
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('admin.users.organization') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ organizationName(user) }}
          </dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('admin.users.status') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ $t(`admin.users.statuses.${user.status}`) }}
          </dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-additional">
            {{ $t('admin.users.createdAt') }}
          </dt>
          <dd class="mt-1 text-sm text-text">
            {{ formattedDate(user.created_at) }}
          </dd>
        </div>
      </dl>
      <div class="flex justify-end">
        <BaseButton type="button" variant="secondary" @click="emit('close')">
          {{ $t('admin.users.close') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
