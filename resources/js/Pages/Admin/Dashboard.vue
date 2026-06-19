<script setup>
defineProps({
  companiesNeedingVerification: {
    type: Array,
    required: true,
  },
  universitiesNeedingVerification: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <div>Admin Dashboard</div>
</template>
