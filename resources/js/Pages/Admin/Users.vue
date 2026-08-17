<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import AdminUsersTable from '@/Components/Admin/AdminUsersTable.vue'

const { t } = useI18n()

defineProps({
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
</script>

<template>
  <Head :title="t('admin.users.title')" />
  <AdminLayout active-page="users">
    <div class="space-y-6">
      <div>
        <h1 class="font-semibold text-text text-2xl">{{ t('admin.users.title') }}</h1>
        <p class="mt-2 text-slate-600 text-sm">{{ t('admin.users.description') }}</p>
      </div>

      <ProfilePageCard>
        <AdminUsersTable
          :users="users"
          :filters="filters"
          :roles="roles"
          :companies="companies"
          :universities="universities"
        />
      </ProfilePageCard>
    </div>
  </AdminLayout>
</template>
