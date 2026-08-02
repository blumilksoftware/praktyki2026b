import { ref } from 'vue'

export const useTogglePassword = () => {
  const showPassword = ref(false)

  const togglePassword = () => {
    showPassword.value = !showPassword.value
  }

  return { showPassword, togglePassword }
}
