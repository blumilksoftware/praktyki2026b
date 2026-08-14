<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { IconSearch } from '@tabler/icons-vue'
import DataTable from '@/Components/Common/DataTable.vue'
import Pagination from '@/Components/Common/Pagination.vue'
import FilterDropdown from '@/Components/Common/FilterDropdown.vue'
import AdminChangeRoleModal from '@/Components/Admin/AdminChangeRoleModal.vue'
import { useUserRole } from '@/Composables/useUserRole'

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
})

const { t } = useI18n()
const page = usePage()
const { roleClass } = useUserRole()

const roleFilter = ref(props.filters.role || 'all')
const searchQuery = ref(props.filters.search || '')

const roleFilterOptions = computed(() => [
  { value: 'all', label: t('admin.users.all') },
  ...props.roles.map(role => ({ value: role, label: t(`admin.users.roles.${role}`) })),
])

const columns = [
  { key: 'name', label: t('admin.users.name') },
  { key: 'email', label: t('admin.users.email') },
  { key: 'role', label: t('admin.users.role') },
  { key: 'actions', label: '', srLabel: t('admin.users.actions'), align: 'right' },
]

const userToChangeRole = ref(null)

function openChangeRoleModal(user) {
  userToChangeRole.value = user
}

function closeChangeRoleModal() {
  userToChangeRole.value = null
}

function isCurrentAdmin(user) {
  return user.id === page.props.auth?.user?.id
}

watch([roleFilter, searchQuery], ([newRole, newSearch]) => {
  router.get('/admin/users', {
    role: newRole,
    search: newSearch,
  }, {
    preserveState: true,
    replace: true,
  })
}, { debounce: 300 })
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
    >
      <template #cell-name="{ item }">
        {{ [item.first_name, item.last_name].filter(Boolean).join(' ') || '-' }}
      </template>
      <template #cell-email="{ item }">
        <a :href="`mailto:${item.email}`" class="text-primary hover:underline">{{ item.email }}</a>
      </template>
      <template #cell-role="{ item }">
        <span :class="['inline-flex px-2.5 py-1 rounded-full font-medium text-xs', roleClass(item.role)]">
          {{ t(`admin.users.roles.${item.role}`) }}
        </span>
      </template>
      <template #cell-actions="{ item }">
        <button
          v-if="!isCurrentAdmin(item)"
          class="bg-background hover:bg-background/60 px-3 py-1.5 border border-border rounded-lg font-medium text-text text-sm transition cursor-pointer"
          @click="openChangeRoleModal(item)"
        >
          {{ t('admin.users.changeRole') }}
        </button>
      </template>
    </DataTable>

    <Pagination :meta="users" />

    <div v-if="users.data.length === 0" class="py-12 text-slate-500 text-center">
      {{ t('table.noData') }}
    </div>

    <AdminChangeRoleModal
      :open="!!userToChangeRole"
      :user-id="userToChangeRole?.id"
      :user-name="userToChangeRole ? [userToChangeRole.first_name, userToChangeRole.last_name].filter(Boolean).join(' ') || userToChangeRole.email : ''"
      :current-role="userToChangeRole?.role"
      :roles="roles"
      @close="closeChangeRoleModal"
    />
  </div>
</template>
