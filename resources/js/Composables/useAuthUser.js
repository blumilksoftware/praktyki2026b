import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'

export function useAuthUser() {
  const page = usePage()

  const user = computed(() => page.props?.auth?.user ?? null)

  const avatarUrl = computed(() => {
    if (!user.value) {
      return null
    }

    if (user.value.photo_path) {
      return ROUTES.STUDENT_PROFILE_PHOTO
    }

    return user.value.company?.logo_path ?? user.value.university_organization?.logo_path ?? null
  })

  return { user, avatarUrl }
}
