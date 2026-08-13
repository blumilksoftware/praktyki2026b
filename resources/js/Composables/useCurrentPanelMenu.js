import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useAdminPanelMenu } from '@/Composables/useAdminPanelMenu'
import { useCompanyPanelMenu } from '@/Composables/useCompanyPanelMenu'
import { useStudentPanelMenu } from '@/Composables/useStudentPanelMenu'
import { useUniversityPanelMenu } from '@/Composables/useUniversityPanelMenu'

export function useCurrentPanelMenu(activePage) {
  const page = usePage()

  const studentMenu = useStudentPanelMenu(activePage)
  const companyMenu = useCompanyPanelMenu(activePage)
  const universityMenu = useUniversityPanelMenu(activePage)
  const adminMenu = useAdminPanelMenu(activePage)

  return computed(() => {
    switch (page.props?.auth?.user?.role) {
      case 'student':
        return studentMenu.value
      case 'companyAdmin':
      case 'companyMember':
        return companyMenu.value
      case 'universityAdmin':
      case 'universityMember':
        return universityMenu.value
      case 'superAdmin':
        return adminMenu.value
      default:
        return []
    }
  })
}
