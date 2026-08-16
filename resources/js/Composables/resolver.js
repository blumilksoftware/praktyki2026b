import { computed } from 'vue'
import { useCompanyPanelMenu } from './companyMenu.js'
import { useUniversityPanelMenu } from './universityMenu.js'
import { useStudentPanelMenu } from './studentMenu.js'
import { useAdminPanelMenu } from './adminMenu.js'

const resolvers = {
  student: useStudentPanelMenu,
  companyAdmin: useCompanyPanelMenu,
  companyMember: useCompanyPanelMenu,
  universityAdmin: useUniversityPanelMenu,
  superAdmin: useAdminPanelMenu,
}

export function resolvePanelMenu(role, activePage) {
  if (isPending){
    if (role === 'companyAdmin' || role === 'companyMember') return usePendingCompanyMenu()
    return computed(() => [])
  }
  const resolver = resolvers[role]
  return resolver ? resolver(activePage) : computed(() => [])
}
