<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import HeaderEdit from '@/Components/Profiles/Edit/HeaderEdit.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const props = defineProps({
  admin: { type: Object, required: true },
})

const { t } = useI18n()

const form = useForm({ photo: null })

const submit = () => {
  form.post('/admin/profile/photo', {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('admin.profile.editTitle')" />
  <AppLayout active-page="profile">
    <div class="mx-auto w-full max-w-2xl">
      <div class="bg-white rounded-xl border border-secondary/20 shadow-sm p-6 flex flex-col items-center text-center">
        <HeaderEdit
          :name="`${admin.firstName} ${admin.lastName}`"
          :logo-url="admin.photoUrl"
          class="flex flex-col items-center w-full md:px-10"
          @update:logo="form.photo = $event"
        />
      </div>

      <BaseButton class="mt-6 w-full justify-center" :disabled="form.processing || !form.photo" @click="submit">
        {{ form.processing ? t('buttons.saving') : t('buttons.save') }}
      </BaseButton>
    </div>
  </AppLayout>
</template>
