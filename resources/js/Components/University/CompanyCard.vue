<script setup>
import PartnerCard from '@/Components/Partnership/PartnerCard.vue'
import { universityCompanyPartnership, universityCompanyPartnershipAccept } from '@/Helpers/routes'

const props = defineProps({
  company: { type: Object, required: true },
})
</script>

<template>
  <PartnerCard
    :partner="company"
    namespace="university.companies"
    :action-url="universityCompanyPartnership(company.id)"
    :accept-url="universityCompanyPartnershipAccept(company.id)"
  />
</template>
