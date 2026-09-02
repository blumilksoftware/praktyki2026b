import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'

export function useAuthUser() {
  const page = usePage()

  const user = computed(() => page.props?.auth?.user ?? null)

  const fullName = computed(() => (
    [user.value?.first_name, user.value?.last_name].filter(Boolean).join(' ')
  ))

  const emailName = computed(() => user.value?.email?.split('@')[0] ?? '')

  const organizationName = computed(() => (
    user.value?.company?.name ?? user.value?.university_organization?.name ?? ''
  ))

  const displayName = computed(() => fullName.value || organizationName.value || emailName.value)

  const avatarUrl = computed(() => {
    if (!user.value) {
      return null
    }

    if (user.value.photo_path) {
      return ROUTES.STUDENT_PROFILE_PHOTO
    }

    return user.value.company?.logo_path ?? user.value.university_organization?.logo_path ?? null
  })

  return { user, fullName, displayName, emailName, avatarUrl }
}
