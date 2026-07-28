<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentPanelLayout from '@/Components/Student/StudentPanelLayout.vue'
import ApplicationHistoryList from '@/Components/Student/ApplicationHistoryList.vue'
import { studentOfferWithdraw } from '@/Helpers/routes'

defineProps({
  applications: { type: Array, default: () => [] },
})

const { t } = useI18n()
const isWithdrawing = ref(false)

function onWithdraw(application) {
  if (!application?.offer_id || isWithdrawing.value) {
    return
  }

  isWithdrawing.value = true
  router.post(studentOfferWithdraw(application.offer_id), {}, {
    preserveScroll: true,
    onFinish: () => {
      isWithdrawing.value = false
    },
  })
}
</script>

<template>
  <Head :title="t('student.applications.title')" />
  <StudentPanelLayout active-page="applications">
    <header class="mb-6">
      <h1 class="font-semibold text-text text-2xl">
        {{ t('student.applications.title') }}
      </h1>
      <p class="mt-1 text-additional text-sm">
        {{ t('student.applications.subtitle') }}
      </p>
    </header>

    <ApplicationHistoryList
      :applications="applications"
      :processing="isWithdrawing"
      @withdraw="onWithdraw"
    />
  </StudentPanelLayout>
</template>
