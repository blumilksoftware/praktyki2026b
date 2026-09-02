<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import ProfilePageCard from '@/Components/Profile/ProfilePageCard.vue'
import AdminOffersTable from '@/Components/Admin/AdminOffersTable.vue'

const { t } = useI18n()

defineProps({
  offers: {
    type: Object,
    default: () => ({ data: [], links: {}, meta: {} }),
  },
  filters: {
    type: Object,
    default: () => ({ status: 'all', search: '' }),
  },
  statuses: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <Head :title="t('admin.offers.title')" />
  <AppLayout active-page="offers">
    <div class="space-y-6">
      <div>
        <h1 class="font-semibold text-text text-2xl">{{ t('admin.offers.title') }}</h1>
        <p class="mt-2 text-slate-600 text-sm">{{ t('admin.offers.description') }}</p>
      </div>

      <ProfilePageCard>
        <AdminOffersTable
          :offers="offers"
          :filters="filters"
          :statuses="statuses"
        />
      </ProfilePageCard>
    </div>
  </AppLayout>
</template>
