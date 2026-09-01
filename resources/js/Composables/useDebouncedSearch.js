import { onUnmounted } from 'vue'

export function useDebouncedSearch(callback, delay = 300) {
  let timer = null

  onUnmounted(() => clearTimeout(timer))

  return (...args) => {
    clearTimeout(timer)
    timer = setTimeout(() => callback(...args), delay)
  }
}
