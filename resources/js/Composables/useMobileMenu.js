import { ref } from 'vue'
const isMobileMenuOpen = ref(false)

export function useMobileMenu() {
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
