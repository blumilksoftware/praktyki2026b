import { onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'

export function useFlashToast() {
  const page = usePage()
  const { toastSuccess, toastError } = useToast()

  const showStatus = () => {
    const status = page.props?.flash?.status

    if (status) {
      toastSuccess(status)
    }
  }

  const showError = () => {
    const error = page.props?.flash?.error

    if (error) {
      toastError(error)
    }
  }

  onMounted(() => {
    showStatus()
    showError()

    watch(() => page.props?.flash?.status, showStatus)
    watch(() => page.props?.flash?.error, showError)
  })
}
