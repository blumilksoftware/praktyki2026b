<script setup>
import { computed, onUnmounted, ref, watch } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BaseLayout from '@/Components/Layouts/BaseLayout.vue'
import BaseModal from '@/Components/Common/BaseModal.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import TeamMemberCard from '@/Components/Organization/TeamMemberCard.vue'
import TeamInvitationCard from '@/Components/Organization/TeamInvitationCard.vue'
import TeamMemberPreviewModal from '@/Components/Organization/TeamMemberPreviewModal.vue'
import TeamInviteModal from '@/Components/Organization/TeamInviteModal.vue'
import TeamRemoveModal from '@/Components/Organization/TeamRemoveModal.vue'
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

const currentUserId = computed(() => page.props.auth?.user?.id)

const canManageInvitations = computed(() => {
  const role = page.props.auth?.user?.role

  return role === 'companyAdmin' || role === 'universityAdmin'
})

const activeTab = ref('members')
const previewTarget = ref(null)
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

function openMemberPreview(member) {
  previewTarget.value = member
}

function closeMemberPreview() {
  previewTarget.value = null
}

function openRemoveModal(member) {
  if (member?.id === currentUserId.value) {
    return
  }

  removeTarget.value = member
}

function closeRemoveModal() {
  removeTarget.value = null
}

function confirmRemove() {
  if (!removeTarget.value || removeTarget.value.id === currentUserId.value) {
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

      <section class="min-w-0 rounded-2xl border border-border bg-white p-4 shadow-sm sm:p-6">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="inline-flex rounded-xl border border-border bg-background p-1">
            <button
              type="button"
              class="rounded-lg px-4 py-2 text-sm font-medium transition"
              :class="activeTab === 'members'
                ? 'bg-white text-text shadow-sm ring-1 ring-border'
                : 'text-additional hover:text-text'"
              @click="activeTab = 'members'"
            >
              {{ t('organization.team.membersTitle') }}
            </button>
            <button
              type="button"
              class="rounded-lg px-4 py-2 text-sm font-medium transition"
              :class="activeTab === 'invitations'
                ? 'bg-white text-text shadow-sm ring-1 ring-border'
                : 'text-additional hover:text-text'"
              @click="activeTab = 'invitations'"
            >
              {{ t('organization.team.invitationsTitle') }}
            </button>
          </div>

          <BaseButton
            v-if="activeTab === 'invitations' && canManageInvitations"
            type="button"
            class="w-full sm:w-auto"
            @click="openInviteModal"
          >
            {{ t('organization.team.inviteButton') }}
          </BaseButton>
        </div>

        <div v-if="activeTab === 'members'" class="min-w-0">
          <div class="mb-4 flex items-start justify-between gap-4">
            <div class="min-w-0">
              <h2 class="break-words font-semibold text-text text-lg">
                {{ t('organization.team.membersTitle') }}
              </h2>
              <p class="mt-1 break-words text-sm text-additional">
                {{ t('organization.team.membersDescription') }}
              </p>
            </div>
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
              :disable-remove="member.id === currentUserId"
              @preview="openMemberPreview(member)"
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
        </div>

        <div v-else class="min-w-0">
          <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
              <h2 class="break-words font-semibold text-text text-lg">
                {{ t('organization.team.invitationsTitle') }}
              </h2>
              <p class="mt-1 break-words text-sm text-additional">
                {{ t('organization.team.invitationsDescription') }}
              </p>
            </div>
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
        </div>
      </section>
    </div>
  </BaseLayout>

  <TeamMemberPreviewModal
    :open="Boolean(previewTarget)"
    :title="t('organization.team.memberPreview.title')"
    :member="previewTarget"
    @close="closeMemberPreview"
  />

  <TeamInviteModal
    v-if="canManageInvitations"
    :open="isInviteModalOpen"
    :title="t('organization.team.inviteModal.title')"
    :email="inviteForm.email"
    :email-error="inviteForm.errors.email"
    :processing="inviteForm.processing"
    @close="closeInviteModal"
    @submit="submitInvite"
    @update:email="inviteForm.email = $event"
  />

  <TeamRemoveModal
    :open="Boolean(removeTarget)"
    :title="t('organization.team.removeModal.title')"
    :member-name="removeTarget?.name ?? removeTarget?.email ?? ''"
    :processing="removeForm.processing"
    @close="closeRemoveModal"
    @confirm="confirmRemove"
  />
</template>
