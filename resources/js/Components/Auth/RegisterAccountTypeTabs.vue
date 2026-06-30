<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

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
    href: null,
    disabled: true,
  },
  {
    key: 'company',
    label: t('auth.register.accountTypeTabs.company'),
    href: '/register/company',
  },
  {
    key: 'student',
    label: t('auth.register.accountTypeTabs.student'),
    href: '/register/student',
  },
])

function tabClass(key, index) {
  const isActive = props.activeTab === key
  const isFirst = index === 0
  const isLast = index === tabs.value.length - 1
  const isMiddle = !isFirst && !isLast

  const base = 'block w-full px-2 py-3 text-center'

  if (isActive) {
    return [
      base,
      'bg-white font-semibold text-secondary ring-1 ring-inset ring-secondary',
      isFirst && 'rounded-l-lg',
      isLast && 'rounded-r-lg',
      isMiddle && 'border-x border-border',
    ].filter(Boolean)
  }

  return [
    base,
    'bg-background text-text transition hover:bg-background/80',
    isMiddle && 'border-x border-border',
  ].filter(Boolean)
}
</script>

<template>
  <div class="space-y-3">
    <p
      id="account-type-label"
      class="text-center text-sm font-medium text-additional"
    >
      {{ t('auth.register.accountTypeTabs.label') }}
    </p>

    <div
      role="tablist"
      class="grid grid-cols-3 overflow-hidden rounded-lg border border-border text-sm sm:text-base"
      :aria-label="t('auth.register.accountTypeTabs.ariaLabel')"
      aria-labelledby="account-type-label"
    >
      <template v-for="(tab, index) in tabs" :key="tab.key">
        <button
          v-if="tab.disabled"
          type="button"
          role="tab"
          :class="tabClass(tab.key, index)"
          aria-disabled="true"
        >
          {{ tab.label }}
        </button>

        <button
          v-else-if="activeTab === tab.key"
          type="button"
          role="tab"
          :class="tabClass(tab.key, index)"
          aria-selected="true"
          aria-current="page"
        >
          {{ tab.label }}
        </button>

        <Link
          v-else
          role="tab"
          :href="tab.href"
          :class="tabClass(tab.key, index)"
          aria-selected="false"
        >
          {{ tab.label }}
        </Link>
      </template>
    </div>
  </div>
</template>
