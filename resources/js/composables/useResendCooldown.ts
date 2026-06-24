import { computed, onUnmounted, ref } from 'vue'

const COOLDOWN_SECONDS = 60

export const useResendCooldown = () => {
  const remaining = ref(0)
  let intervalId: ReturnType<typeof setInterval> | null = null

  const clearTimer = () => {
    if (intervalId !== null) {
      clearInterval(intervalId)
      intervalId = null
    }
  }

  const startCooldown = () => {
    clearTimer()
    remaining.value = COOLDOWN_SECONDS

    intervalId = setInterval(() => {
      remaining.value -= 1
      if (remaining.value <= 0) {
        clearTimer()
        remaining.value = 0
      }
    }, 1000)
  }

  const canResend = computed(() => remaining.value === 0)

  onUnmounted(clearTimer)

  return { remaining, canResend, startCooldown }
}
