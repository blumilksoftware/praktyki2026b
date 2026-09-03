<script setup>
import { computed } from 'vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'
import ProfileAvatar from '@/Components/Student/ProfileAvatar.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    default: null,
  },
  photoUrl: {
    type: String,
    default: null,
  },
  subtitle: {
    type: String,
    default: null,
  },
  details: {
    type: Array,
    default: () => [],
  },
  closeLabel: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['close'])

const fullNameParts = computed(() => (props.name ?? '').trim().split(/\s+/).filter(Boolean))
const firstName = computed(() => fullNameParts.value[0] ?? '')
const lastName = computed(() => fullNameParts.value.slice(1).join(' '))
</script>

<template>
  <BaseModal
    :open="open"
    :title="title"
    @close="emit('close')"
  >
    <div v-if="name" class="flex flex-col gap-5">
      <div class="flex items-center gap-3">
        <ProfileAvatar
          :photo-url="photoUrl"
          :first-name="firstName"
          :last-name="lastName"
          size-class="h-12 w-12 text-lg"
        />
        <div class="min-w-0">
          <p class="truncate text-xl font-semibold text-text">
            {{ name }}
          </p>
          <p v-if="subtitle" class="text-sm text-additional">
            {{ subtitle }}
          </p>
        </div>
      </div>

      <dl class="space-y-3">
        <div v-for="detail in details" :key="detail.label" class="min-w-0">
          <dt class="text-[13px] font-medium uppercase tracking-[0.08em] text-additional">
            {{ detail.label }}
          </dt>
          <dd v-if="detail.badge" class="mt-1.5">
            <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium', detail.badgeClass]">
              {{ detail.value }}
            </span>
          </dd>
          <dd v-else class="mt-1.5 text-sm text-text">
            {{ detail.value }}
          </dd>
        </div>
      </dl>

      <div class="flex justify-end pt-1">
        <BaseButton type="button" variant="secondary" @click="emit('close')">
          {{ closeLabel }}
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>
