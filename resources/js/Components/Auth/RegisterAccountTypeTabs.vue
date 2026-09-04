<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ROUTES } from '@/Helpers/routes'

const props = defineProps({
  activeTab: {
    type: String,
    required: true,
    validator: (value) => ['university', 'company', 'student'].includes(value),
  },
})

const { t } = useI18n()

const tabs = computed(() => [
  {
    key: 'university',
    label: t('auth.register.accountTypeTabs.university'),
    href: ROUTES.REGISTER_UNIVERSITY,
  },
  {
    key: 'company',
    label: t('auth.register.accountTypeTabs.company'),
    href: ROUTES.REGISTER_COMPANY,
  },
  {
    key: 'student',
    label: t('auth.register.accountTypeTabs.student'),
    href: ROUTES.REGISTER_STUDENT,

  },
])

function tabClass(key, disabled = false) {
  const isActive = props.activeTab === key
  const base =
    'block flex-1 px-2 py-3 text-center text-sm sm:text-base border-b-2 -mb-px transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40'

  if (disabled) {
    return [base, 'cursor-not-allowed border-transparent text-additional']
  }

  if (isActive) {
    return [base, 'border-secondary font-semibold text-secondary']
  }

  return [base, 'border-transparent text-additional hover:border-border hover:text-text']
}
</script>

<template>
  <div class="space-y-3">
    <p id="account-type-label" class="text-center text-base font-medium text-text sm:text-lg">
      {{ t('auth.register.accountTypeTabs.label') }}
    </p>

    <div role="tablist" class="flex border-b border-border" :aria-label="t('auth.register.accountTypeTabs.ariaLabel')"
         aria-labelledby="account-type-label"
    >
      <template v-for="tab in tabs" :key="tab.key">
        <button v-if="tab.disabled" type="button" role="tab" :class="tabClass(tab.key, true)" aria-disabled="true">
          {{ tab.label }}
        </button>

        <button v-else-if="activeTab === tab.key" type="button" role="tab" :class="tabClass(tab.key)"
                aria-selected="true" aria-current="page"
        >
          {{ tab.label }}
        </button>

        <Link v-else role="tab" :href="tab.href" :class="tabClass(tab.key)" aria-selected="false">
          {{ tab.label }}
        </Link>
      </template>
    </div>
  </div>
</template>
