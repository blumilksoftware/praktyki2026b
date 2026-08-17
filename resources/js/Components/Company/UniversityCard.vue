<script setup>
import PartnerCard from '@/Components/Partnership/PartnerCard.vue'
import { companyUniversityPartnership, companyUniversityPartnershipAccept } from '@/Helpers/routes'

const props = defineProps({
  university: { type: Object, required: true },
})
</script>

<template>
  <PartnerCard
    :partner="university"
    namespace="company.universities"
    :action-url="companyUniversityPartnership(university.id)"
    :accept-url="companyUniversityPartnershipAccept(university.id)"
  />
</template>
