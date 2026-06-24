import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { useResendCooldown } from '@/composables/useResendCooldown'

describe('useResendCooldown', () => {
  beforeEach(() => {
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  it('allows resend initially', () => {
    const { canResend } = useResendCooldown()
    expect(canResend.value).toBe(true)
  })

  it('blocks resend during cooldown', () => {
    const { canResend, startCooldown } = useResendCooldown()

    startCooldown()
    expect(canResend.value).toBe(false)
  })

  it('unblocks after 60 seconds', () => {
    const { canResend, remaining, startCooldown } = useResendCooldown()

    startCooldown()
    expect(remaining.value).toBe(60)

    vi.advanceTimersByTime(60_000)
    expect(canResend.value).toBe(true)
    expect(remaining.value).toBe(0)
  })
})
