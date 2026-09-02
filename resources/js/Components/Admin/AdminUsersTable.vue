<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconSearch } from '@tabler/icons-vue'
import DataTable from '@/Components/Common/DataTable.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import FilterDropdown from '@/Components/Common/FilterDropdown.vue'
import AdminChangeRoleModal from '@/Components/Admin/AdminChangeRoleModal.vue'
import AdminBlockUserModal from '@/Components/Admin/AdminBlockUserModal.vue'
import AdminUserActionsMenu from '@/Components/Admin/AdminUserActionsMenu.vue'
import AdminPreviewUserModal from '@/Components/Admin/AdminPreviewUserModal.vue'
import { useUserRole } from '@/Composables/useUserRole'
import { useUserStatus } from '@/Composables/useUserStatus'
import { useDebouncedSearch } from '@/Composables/useDebouncedSearch'
import AdminDeleteUserModal from '@/Components/Admin/AdminDeleteUserModal.vue'

const props = defineProps({
  users: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  filters: {
    type: Object,
    default: () => ({ role: 'all', search: '' }),
  },
  roles: {
    type: Array,
    default: () => [],
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

const { t } = useI18n()
const page = usePage()
const { roleClass } = useUserRole()
const { statusClass } = useUserStatus()

const roleFilter = ref(props.filters.role || 'all')
const searchQuery = ref(props.filters.search || '')

const roleFilterOptions = computed(() => [
  { value: 'all', label: t('admin.users.all') },
  ...props.roles.map(role => ({ value: role, label: t(`admin.users.roles.${role}`) })),
])

const sortKey = ref(props.filters.sort_key || 'created_at')
const sortDir = ref(props.filters.sort_dir || 'desc')

const columns = [
  { key: 'name', label: t('admin.users.name'), sortable: true },
  { key: 'email', label: t('admin.users.email'), sortable: true },
  { key: 'role', label: t('admin.users.role'), sortable: true },
  { key: 'status', label: t('admin.users.status'), sortable: true },
  { key: 'actions', label: '', srLabel: t('admin.users.actions'), align: 'right' },
]

const userToChangeRole = ref(null)
const userToBlock = ref(null)
const userToDelete = ref(null)
const userToPreview = ref(null)

function openChangeRoleModal(user) {
  userToChangeRole.value = user
}

function closeChangeRoleModal() {
  userToChangeRole.value = null
}

function openBlockModal(user) {
  userToBlock.value = user
}

function closeBlockModal() {
  userToBlock.value = null
}

function openDeleteModal(user) {
  userToDelete.value = user
}

function closeDeleteModal() {
  userToDelete.value = null
}

function openUserPreview(user) {
  userToPreview.value = user
}

function closeUserPreview() {
  userToPreview.value = null
}

function isCurrentAdmin(user) {
  return user.id === page.props.auth?.user?.id
}

function userLabel(user) {
  if (user.status === 'deleted') return t('admin.users.deletedAccount')
  return [user.first_name, user.last_name].filter(Boolean).join(' ') || user.email
}

function applyQuery() {
  router.get('/admin/users', {
    role: roleFilter.value,
    search: searchQuery.value,
    sort_key: sortKey.value,
    sort_dir: sortDir.value,
  }, {
    preserveState: true,
    replace: true,
  })
}

function handleSort({ key, dir }) {
  sortKey.value = key
  sortDir.value = dir
  applyQuery()
}

watch([roleFilter, searchQuery], useDebouncedSearch(applyQuery))
</script>

<template>
  <div class="space-y-6">
    <div class="flex lg:flex-row flex-col lg:justify-between lg:items-center gap-4">
      <FilterDropdown
        v-model="roleFilter"
        :options="roleFilterOptions"
        :aria-label="t('admin.users.filterByRoleAriaLabel')"
      />

      <div class="relative">
        <div class="left-3 absolute inset-y-0 flex items-center pointer-events-none">
          <IconSearch class="w-4 h-4 text-slate-400" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('admin.users.search')"
          :aria-label="t('admin.users.searchAriaLabel')"
          class="bg-white px-4 py-2 pr-10 pl-9 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/60 w-full text-slate-700 text-sm"
        >
      </div>
    </div>

    <DataTable
      v-if="users.data.length > 0"
      :items="users.data"
      :columns="columns"
      row-key="id"
      :caption="t('admin.users.title')"
      :sort-key="sortKey"
      :sort-dir="sortDir"
      @sort="handleSort"
    >
      <template #cell-name="{ item }">
        <button type="button" class="text-primary hover:underline cursor-pointer" @click="openUserPreview(item)">
          {{ userLabel(item) }}
        </button>
      </template>
      <template #cell-email="{ item }">
        <a :href="`mailto:${item.email}`" class="text-primary hover:underline">{{ item.email }}</a>
      </template>
      <template #cell-role="{ item }">
        <span :class="['inline-flex px-2.5 py-1 rounded-full font-medium text-xs whitespace-nowrap', roleClass(item.role)]">
          {{ t(`admin.users.roles.${item.role}`) }}
        </span>
      </template>
      <template #cell-status="{ item }">
        <span :class="['inline-flex px-2.5 py-1 rounded-full font-medium text-xs', statusClass(item.status)]">
          {{ t(`admin.users.statuses.${item.status}`) }}
        </span>
      </template>
      <template #cell-actions="{ item }">
        <AdminUserActionsMenu
          v-if="!isCurrentAdmin(item)"
          :user="item"
          @change-role="openChangeRoleModal(item)"
          @toggle-block="openBlockModal(item)"
          @delete-user="openDeleteModal(item)"
        />
      </template>
    </DataTable>

    <Pagination :meta="users" />

    <div v-if="users.data.length === 0" class="py-12 text-slate-500 text-center">
      {{ t('table.noData') }}
    </div>

    <AdminChangeRoleModal
      :key="userToChangeRole?.id"
      :open="!!userToChangeRole"
      :user-id="userToChangeRole?.id"
      :user-name="userToChangeRole ? userLabel(userToChangeRole) : ''"
      :current-role="userToChangeRole?.role"
      :current-organization-id="userToChangeRole?.organization_id ?? ''"
      :roles="roles"
      :companies="companies"
      :universities="universities"
      @close="closeChangeRoleModal"
    />

    <AdminBlockUserModal
      :key="`block-${userToBlock?.id}`"
      :open="!!userToBlock"
      :user-id="userToBlock?.id"
      :user-name="userToBlock ? userLabel(userToBlock) : ''"
      :current-status="userToBlock?.status"
      @close="closeBlockModal"
    />

    <AdminPreviewUserModal
      :key="`preview-${userToPreview?.id}`"
      :open="!!userToPreview"
      :user="userToPreview"
      :companies="companies"
      :universities="universities"
      @close="closeUserPreview"
    />

    <AdminDeleteUserModal
      :key="`delete-${userToDelete?.id}`"
      :open="!!userToDelete"
      :user-id="userToDelete?.id"
      :user-name="userToDelete ? userLabel(userToDelete) : ''"
      :current-status="userToDelete?.status"
      @close="closeDeleteModal"
    />
  </div>
</template>
