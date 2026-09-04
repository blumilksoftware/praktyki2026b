import { router, usePage } from '@inertiajs/vue3'
import { ROUTES } from '@/Helpers/routes'

const ROLE_FALLBACK = {
  student: ROUTES.STUDENT_DASHBOARD,
  universityAdmin: ROUTES.UNIVERSITY_COMPANIES,
  universityMember: ROUTES.UNIVERSITY_COMPANIES,
  companyAdmin: ROUTES.COMPANY_UNIVERSITIES,
  companyMember: ROUTES.COMPANY_UNIVERSITIES,
  superAdmin: ROUTES.ADMIN_APPLICATIONS,
}

export function useProfileBack(backUrl) {
  const page = usePage()

  function goBack() {
    if (backUrl) {
      router.visit(backUrl)
      return
    }
    const role = page.props.auth?.user?.role
    router.visit(ROLE_FALLBACK[role] ?? ROUTES.OFFERS)
  }

  return { goBack }
}
