<script setup>
import { computed, ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import TeamMemberCard from '@/Components/Organization/TeamMemberCard.vue'
import TeamInvitationCard from '@/Components/Organization/TeamInvitationCard.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'
import { useUniversityPanelMenu } from '@/Composables/useUniversityPanelMenu'

const props = defineProps({
  organization: {
    type: Object,
    required: true,
  },
  members: {
    type: Array,
    default: () => [],
  },
  invitations: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const { t, locale } = useI18n()
const companyMenu = useCompanyPanelMenu('team')
const universityMenu = useUniversityPanelMenu('team')

const canManageInvitations = computed(() => {
  const role = page.props.auth?.user?.role

  return role === 'companyAdmin' || role === 'universityAdmin'
})

const removeTarget = ref(null)
const removeForm = useForm({})
const revokeForm = useForm({})
const inviteForm = useForm({ email: '' })
const isInviteModalOpen = ref(false)

const organizationPath = computed(() => props.organization?.type === 'company' ? '/company/team' : '/university/team')
const panelMenu = computed(() => props.organization?.type === 'company' ? companyMenu.value : universityMenu.value)

const formattedMembers = computed(() => props.members.map((member) => ({
  ...member,
  roleLabel: t(`organization.team.roles.${member.role}`),
})))

function formatDate(value) {
  if (!value) {
    return ''
  }

  const date = new Date(value)

  return new Intl.DateTimeFormat(locale.value, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date)
}

function openRemoveModal(member) {
  removeTarget.value = member
}

function closeRemoveModal() {
  removeTarget.value = null
}

function confirmRemove() {
  if (!removeTarget.value) {
    return
  }

  removeForm.delete(`${organizationPath.value}/members/${removeTarget.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      closeRemoveModal()
    },
  })
}

function revokeInvitation(invitation) {
  revokeForm.delete(`${organizationPath.value}/invitations/${invitation.id}`, {
    preserveScroll: true,
  })
}

function openInviteModal() {
  inviteForm.clearErrors()
  inviteForm.reset('email')
  isInviteModalOpen.value = true
}

function closeInviteModal() {
  inviteForm.clearErrors()
  inviteForm.reset('email')
  isInviteModalOpen.value = false
}

function submitInvite() {
  inviteForm.post(`${organizationPath.value}/invitations`, {
    preserveScroll: true,
    onSuccess: () => {
      closeInviteModal()
    },
  })
}
</script>

<template>
  <Head :title="t('organization.team.title')" />

  <BaseLayout
    active-page="team"
    :nav-items="panelMenu"
    :navigation-buttons="panelMenu"
    navigation-variant="default"
  >
    <div class="min-h-0 space-y-6">
      <section class="shrink-0 rounded-2xl border border-border bg-white p-6 shadow-sm">
        <div class="min-w-0 flex flex-col gap-2">
          <h1 class="break-words font-semibold text-text text-2xl">
            {{ t('organization.team.title') }}
          </h1>
          <p class="break-words text-sm text-additional">
            {{ t('organization.team.description', { organization: props.organization.name }) }}
          </p>
        </div>
      </section>

      <div class="grid min-w-0 gap-6 lg:grid-cols-2">
        <section class="min-w-0 rounded-2xl border border-border bg-white p-6 shadow-sm">
          <div class="mb-4 flex items-start justify-between gap-4">
            <h2 class="break-words font-semibold text-text text-lg">
              {{ t('organization.team.membersTitle') }}
            </h2>
            <p class="mt-1 break-words text-sm text-additional">
              {{ t('organization.team.membersDescription') }}
            </p>
          </div>

          <div v-if="formattedMembers.length === 0" class="rounded-xl border border-dashed border-border bg-background px-4 py-6 text-center text-sm text-additional">
            {{ t('organization.team.noMembers') }}
          </div>

          <div v-else class="space-y-3">
            <TeamMemberCard
              v-for="member in formattedMembers"
              :key="member.id"
              :member="member"
              :role-label="member.roleLabel"
              :join-date-label="t('organization.team.joinDate', { date: formatDate(member.joinedAt) })"
              :remove-label="t('organization.team.remove')"
              :can-remove="canManageInvitations"
              @remove="openRemoveModal(member)"
            />
          </div>
        </section>

        <section class="min-w-0 rounded-2xl border border-border bg-white p-6 shadow-sm">
          <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
              <h2 class="break-words font-semibold text-text text-lg">
                {{ t('organization.team.invitationsTitle') }}
              </h2>
              <p class="mt-1 break-words text-sm text-additional">
                {{ t('organization.team.invitationsDescription') }}
              </p>
            </div>

            <BaseButton
              v-if="canManageInvitations"
              type="button"
              class="w-full sm:w-auto"
              @click="openInviteModal"
            >
              {{ t('organization.team.inviteButton') }}
            </BaseButton>
          </div>

          <div v-if="invitations.length === 0" class="rounded-xl border border-dashed border-border bg-background px-4 py-6 text-center text-sm text-additional">
            {{ t('organization.team.noInvitations') }}
          </div>

          <div v-else class="space-y-3">
            <TeamInvitationCard
              v-for="invitation in invitations"
              :key="invitation.id"
              :invitation="invitation"
              :status-label="t('organization.team.pending')"
              :revoke-label="t('organization.team.revoke')"
              :can-revoke="canManageInvitations"
              @revoke="revokeInvitation(invitation)"
            />
          </div>
        </section>
      </div>
    </div>
  </BaseLayout>

  <BaseModal
    v-if="canManageInvitations"
    :open="isInviteModalOpen"
    :title="t('organization.team.inviteModal.title')"
    @close="closeInviteModal"
  >
    <form class="flex flex-col gap-6" novalidate @submit.prevent="submitInvite">
      <div class="flex flex-col gap-2">
        <label class="text-sm font-medium text-text" for="team-invite-email">
          {{ t('organization.team.inviteModal.emailLabel') }}
        </label>
        <input
          id="team-invite-email"
          v-model="inviteForm.email"
          type="email"
          autocomplete="email"
          class="w-full rounded-lg border border-border bg-white px-4 py-3 text-sm text-text outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
          :placeholder="t('organization.team.inviteModal.emailPlaceholder')"
        >
        <p v-if="inviteForm.errors.email" class="text-sm text-error">
          {{ inviteForm.errors.email }}
        </p>
      </div>

      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="inviteForm.processing" @click="closeInviteModal">
          {{ t('organization.team.inviteModal.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="inviteForm.processing">
          {{ t('organization.team.inviteModal.submit') }}
        </BaseButton>
      </div>
    </form>
  </BaseModal>

  <BaseModal
    :open="Boolean(removeTarget)"
    :title="t('organization.team.removeModal.title')"
    @close="closeRemoveModal"
  >
    <div class="flex flex-col gap-6">
      <p class="text-sm leading-relaxed text-additional">
        {{ t('organization.team.removeModal.description', { name: removeTarget?.name ?? '' }) }}
      </p>

      <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <BaseButton type="button" variant="secondary" :disabled="removeForm.processing" @click="closeRemoveModal">
          {{ t('organization.team.removeModal.cancel') }}
        </BaseButton>
        <BaseButton type="button" :disabled="removeForm.processing" @click="confirmRemove">
          {{ t('organization.team.removeModal.confirm') }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
