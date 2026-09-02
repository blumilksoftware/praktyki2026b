import { ref } from 'vue'

export function useMobileMenu() {
  const isMobileMenuOpen = ref(false)
  const toggle = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
  }

  const close = () => {
    isMobileMenuOpen.value = false
  }

  return {
    isMobileMenuOpen,
    toggle,
    close,
  }
}
