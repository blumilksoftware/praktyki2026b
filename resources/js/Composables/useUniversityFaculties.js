import { ref } from 'vue'
import { ROUTES } from '@/Helpers/routes'

export function useUniversityFaculties(initialFaculties = []) {
  const faculties = ref([...initialFaculties])

  const loadFaculties = async (universityId) => {
    if (!universityId) {
      faculties.value = []
      return
    }

    try {
      const response = await fetch(ROUTES.STUDENT_UNIVERSITY_FACULTIES(universityId), {
        headers: { Accept: 'application/json' },
      })
      const data = response.ok ? await response.json() : { faculties: [] }
      faculties.value = data.faculties
    } catch {
      faculties.value = []
    }
  }

  return { faculties, loadFaculties }
}
