import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import ApplicationsCard from '@/Components/Profiles/ApplicationsCard.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key, locale: { value: 'en' } })
}))

vi.mock('@inertiajs/vue3', () => ({
  Link: { template: '<a><slot /></a>' }
}))

const baseApplication = {
  id: 'application-1',
  student_name: 'Dana Whitfield',
  university: 'Northgate Institute',
  offer_title: 'Backend Internship',
  application_date: '2026-05-04T09:15:00Z',
  status: 'pending',
  cv_url: null,
  profile_url: null,
}

const mountCard = (overrides = {}) => mount(ApplicationsCard, {
  props: { application: { ...baseApplication, ...overrides } },
  global: { stubs: { IconDownload: true } }
})

const cvSlotClasses = (wrapper: ReturnType<typeof mountCard>) =>
  wrapper.get('select').element.parentElement!.lastElementChild!.className

describe('ApplicationsCard', () => {
  it('opens the cv in a new tab without leaking the opener', () => {
    const wrapper = mountCard({ cv_url: 'https://files.example.test/cv.pdf' })

    expect(wrapper.get('a[target="_blank"]').attributes('rel')).toBe('noopener noreferrer')
  })

  it('keeps both cv states in the same fixed-width slot so the status select stays aligned', () => {
    const withCv = mountCard({ cv_url: 'https://files.example.test/cv.pdf' })
    const withoutCv = mountCard()

    expect(cvSlotClasses(withCv)).toBe(cvSlotClasses(withoutCv))
    expect(cvSlotClasses(withoutCv)).toContain('sm:w-44')
  })

  it('keeps the actions right aligned and lets them wrap on narrow screens', () => {
    const row = mountCard().get('select').element.parentElement!.className

    expect(row).toContain('flex-wrap')
    expect(row).toContain('justify-end')
  })

  it('emits the picked status together with the application id', async () => {
    const wrapper = mountCard()

    await wrapper.get('select').setValue('reviewed')

    expect(wrapper.emitted('update-status')).toEqual([['application-1', 'reviewed']])
  })
})
