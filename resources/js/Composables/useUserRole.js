export function useUserRole() {
  function roleClass(role) {
    switch (role) {
    case 'superAdmin':
      return 'bg-red-100 text-red-700'
    case 'companyAdmin':
    case 'universityAdmin':
      return 'bg-indigo-100 text-indigo-700'
    case 'companyMember':
    case 'universityMember':
      return 'bg-blue-100 text-blue-700'
    default:
      return 'bg-gray-100 text-gray-700'
    }
  }

  return {
    roleClass,
  }
}
