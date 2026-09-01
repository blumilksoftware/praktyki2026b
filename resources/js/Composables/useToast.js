import { ref } from 'vue'

const toastRef = ref(null)

export function useToast() {
  return {
    toastRef,
    toastSuccess: (message) => toastRef.value?.show(message, 3000, 'success'),
    toastError: (message) => toastRef.value?.show(message, 5000, 'fail'),
    toastInfo: (message) => toastRef.value?.show(message, 4000, 'info'),
  }
}
