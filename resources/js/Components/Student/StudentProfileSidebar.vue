<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconMapPin, IconSchool, IconMail, IconFile, IconCircleCheckFilled } from '@tabler/icons-vue'
import ProfileAvatar from '@/Components/Student/ProfileAvatar.vue'
import ProfileTag from '@/Components/Profile/ProfileTag.vue'
import ProfileProgress from '@/Components/Onboarding/ProfileProgress.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'

const props = defineProps({
  user: { type: Object, required: true },
})

const emit = defineEmits(['edit'])

const { t } = useI18n()

const fullName = computed(() => `${props.user.first_name ?? ''} ${props.user.last_name ?? ''}`.trim())
const ageLabel = computed(() => {
  if (!props.user.age) return null
  return t('student.profile.sidebar.age', { count: props.user.age })
})
</script>

<template>
  <aside class="self-start rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col items-center text-center">
      <ProfileAvatar
        :photo-url="user.photo_url"
        :first-name="user.first_name"
        :last-name="user.last_name"
        size-class="w-28 h-28 text-3xl"
      />
      <h1 class="mt-4 font-semibold text-text text-xl">
        {{ fullName }}
      </h1>
      <p v-if="ageLabel" class="mt-1 text-additional text-sm">
        {{ ageLabel }}
      </p>

      <div v-if="user.university" class="mt-3 flex items-center justify-center gap-2 text-sm">
        <span class="font-medium text-text">{{ user.university }}</span>
        <span
          v-if="user.university_verified"
          class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-white text-xs"
          :aria-label="t('student.profile.sidebar.verified')"
        >
          ✓
        </span>
      </div>

      <div v-if="user.study_field || user.study_year" class="mt-3 flex flex-wrap justify-center gap-2">
        <ProfileTag v-if="user.study_field" :label="user.study_field" variant="profile" />
        <ProfileTag v-if="user.study_year" :label="t('student.profile.sidebar.yearTag', { year: user.study_year })" variant="profile" />
      </div>
    </div>

    <ul class="mt-6 space-y-3 text-base">
      <li v-if="user.location" class="flex items-start gap-2 text-text">
        <IconMapPin class="mt-0.5 h-5 w-5 shrink-0 text-additional" aria-hidden="true" />
        <span>{{ user.location }}</span>
      </li>
      <li v-if="user.specialization" class="flex items-start gap-2 text-text">
        <IconSchool class="mt-0.5 h-5 w-5 shrink-0 text-additional" aria-hidden="true" />
        <span>{{ user.specialization }}</span>
      </li>
      <li class="flex items-start gap-2 text-text">
        <IconMail class="mt-0.5 h-5 w-5 shrink-0 text-additional" aria-hidden="true" />
        <span class="break-all">{{ user.email }}</span>
      </li>
    </ul>

    <div
      v-if="user.cv_path"
      class="mt-4 flex items-center gap-2 rounded-xl border border-border bg-background px-3 py-2 text-sm"
    >
      <IconFile class="h-4 w-4 text-primary" aria-hidden="true" />
      <span class="min-w-0 flex-1 truncate font-medium text-text">
        {{ t('student.cv.defaultFileName') }}
      </span>
      <IconCircleCheckFilled
        class="h-4 w-4 shrink-0 text-green-500"
        :aria-label="t('student.cv.uploaded')"
      />
    </div>

    <div class="mt-5">
      <ProfileProgress />
    </div>

    <BaseButton
      type="button"
      class="mt-5 w-full justify-center"
      @click="emit('edit')"
    >
      {{ t('student.profile.sidebar.editProfile') }}
    </BaseButton>
  </aside>
</template>
