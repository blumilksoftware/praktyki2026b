<script setup>
import { computed, onUnmounted, ref, watch } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import TeamMemberCard from '@/Components/Organization/TeamMemberCard.vue'
import TeamInvitationCard from '@/Components/Organization/TeamInvitationCard.vue'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'
import { useUniversityPanelMenu } from '@/Composables/useUniversityPanelMenu'
import Pagination from '@/Components/Common/Pagination.vue'

const props = defineProps({
  organization: {
    type: Object,
    required: true,
  },
  members: {
    type: [Array, Object],
    default: () => [],
  },
  invitations: {
    type: [Array, Object],
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({
      member_search: '',
      invitation_search: '',
      page: 1,
      per_page: 10,
    }),
  },
})

const page = usePage()
const { t, locale } = useI18n()
const companyMenu = useCompanyPanelMenu('team')
const universityMenu = useUniversityPanelMenu('team')

const memberSearchQuery = ref(props.filters?.member_search ?? '')
const invitationSearchQuery = ref(props.filters?.invitation_search ?? '')
const perPage = ref(Number(props.filters?.per_page ?? 10))

let searchDebounceTimer = null

function normalizeCollection(collection) {
  if (Array.isArray(collection)) {
    return {
      data: collection,
      current_page: 1,
      last_page: 1,
      per_page: collection.length || 10,
      total: collection.length,
    }
  }

  return {
    data: Array.isArray(collection?.data) ? collection.data : [],
    current_page: Number(collection?.current_page ?? 1),
    last_page: Number(collection?.last_page ?? 1),
    per_page: Number(collection?.per_page ?? collection?.data?.length ?? 10),
    total: Number(collection?.total ?? collection?.data?.length ?? 0),
  }
}

const membersPage = computed(() => {
  if (Array.isArray(props.members)) {
    return normalizeCollection(props.members)
  }

  return {
    data: Array.isArray(props.members?.data) ? props.members.data : [],
    current_page: Number(props.members?.current_page ?? 1),
    last_page: Number(props.members?.last_page ?? 1),
    per_page: Number(props.members?.per_page ?? props.members?.data?.length ?? 10),
    total: Number(props.members?.total ?? props.members?.data?.length ?? 0),
    from: Number(props.members?.from ?? 1),
    to: Number(props.members?.to ?? props.members?.data?.length ?? 0),
    links: Array.isArray(props.members?.links) ? props.members.links : [],
  }
})

const invitationsPage = computed(() => {
  if (Array.isArray(props.invitations)) {
    return normalizeCollection(props.invitations)
  }

  return {
    data: Array.isArray(props.invitations?.data) ? props.invitations.data : [],
    current_page: Number(props.invitations?.current_page ?? 1),
    last_page: Number(props.invitations?.last_page ?? 1),
    per_page: Number(props.invitations?.per_page ?? props.invitations?.data?.length ?? 10),
    total: Number(props.invitations?.total ?? props.invitations?.data?.length ?? 0),
    from: Number(props.invitations?.from ?? 1),
    to: Number(props.invitations?.to ?? props.invitations?.data?.length ?? 0),
    links: Array.isArray(props.invitations?.links) ? props.invitations.links : [],
  }
})

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

const membersList = computed(() => membersPage.value.data.map((member) => ({
  ...member,
  roleLabel: t(`organization.team.roles.${member.role}`),
})))
const invitationList = computed(() => invitationsPage.value.data)
const membersPagination = computed(() => (!Array.isArray(props.members) && props.members?.last_page > 1 ? props.members : null))
const invitationsPagination = computed(() => (!Array.isArray(props.invitations) && props.invitations?.last_page > 1 ? props.invitations : null))

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

function applyMembersFilters() {
  router.get(window.location.pathname, {
    member_search: memberSearchQuery.value || undefined,
    member_page: 1,
    per_page: perPage.value || undefined,
  }, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  })
}

function applyInvitationFilters() {
  router.get(window.location.pathname, {
    invitation_search: invitationSearchQuery.value || undefined,
    invitation_page: 1,
    per_page: perPage.value || undefined,
  }, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  })
}

watch(memberSearchQuery, () => {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    applyMembersFilters()
  }, 350)
})

watch(invitationSearchQuery, () => {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    applyInvitationFilters()
  }, 350)
})

onUnmounted(() => {
  clearTimeout(searchDebounceTimer)
})

function paginationLabel(label) {
  const textarea = document.createElement('textarea')
  textarea.innerHTML = label

  return textarea.value
}

function goToPage(url) {
  if (!url) {
    return
  }

  router.get(url, {}, { preserveState: true, preserveScroll: true, replace: true })
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

          <div class="mb-4">
            <input
              v-model="memberSearchQuery"
              type="search"
              class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-text outline-none transition placeholder:text-additional focus:border-primary/50 focus:ring-2 focus:ring-primary/20"
              :placeholder="t('organization.team.searchMembersPlaceholder')"
              :aria-label="t('organization.team.searchMembersPlaceholder')"
            >
          </div>

          <div v-if="membersList.length === 0" class="rounded-xl border border-dashed border-border bg-background px-4 py-6 text-center text-sm text-additional">
            {{ t('organization.team.noMembers') }}
          </div>

          <div v-else class="space-y-3">
            <TeamMemberCard
              v-for="member in membersList"
              :key="member.id"
              :member="member"
              :role-label="member.roleLabel"
              :join-date-label="t('organization.team.joinDate', { date: formatDate(member.joinedAt) })"
              :remove-label="t('organization.team.remove')"
              :can-remove="canManageInvitations"
              @remove="openRemoveModal(member)"
            />
          </div>

          <div v-if="membersPagination && membersPagination.last_page > 1" class="mt-4 border-t border-border pt-4">
            <span class="text-xs text-additional">
              {{ t('organization.team.showingRange', {
                from: membersPagination.from || 1,
                to: membersPagination.to || membersList.length,
                total: membersPagination.total,
              }) }}
            </span>

            <Pagination :meta="membersPagination" />
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

          <div class="mb-4">
            <input
              v-model="invitationSearchQuery"
              type="search"
              class="w-full rounded-lg border border-border bg-white px-4 py-2.5 text-sm text-text outline-none transition placeholder:text-additional focus:border-primary/50 focus:ring-2 focus:ring-primary/20"
              :placeholder="t('organization.team.searchInvitationsPlaceholder')"
              :aria-label="t('organization.team.searchInvitationsPlaceholder')"
            >
          </div>

          <div v-if="invitationList.length === 0" class="rounded-xl border border-dashed border-border bg-background px-4 py-6 text-center text-sm text-additional">
            {{ t('organization.team.noInvitations') }}
          </div>

          <div v-else class="space-y-3">
            <TeamInvitationCard
              v-for="invitation in invitationList"
              :key="invitation.id"
              :invitation="invitation"
              :status-label="t('organization.team.pending')"
              :revoke-label="t('organization.team.revoke')"
              :can-revoke="canManageInvitations"
              @revoke="revokeInvitation(invitation)"
            />
          </div>

          <div v-if="invitationsPagination && invitationsPagination.last_page > 1" class="mt-4 border-t border-border pt-4">
            <span class="text-xs text-additional">
              {{ t('organization.team.showingRange', {
                from: invitationsPagination.from || 1,
                to: invitationsPagination.to || invitationList.length,
                total: invitationsPagination.total,
              }) }}
            </span>

            <Pagination :meta="invitationsPagination" />
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
