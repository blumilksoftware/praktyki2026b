import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import Offers from '@/Pages/Offers.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string, params?: Record<string, unknown>) => (params ? `${key} ${JSON.stringify(params)}` : key),
  }),
}))

vi.mock('@inertiajs/vue3', () => ({
  Head: { template: '<div />' },
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
}))

const studyFields = [
  { value: 'sf-1', label: 'Computer Science' },
  { value: 'sf-2', label: 'Mathematics' },
]

const offers = [
  {
    id: 1, title: 'Frontend Intern', city: 'Wrocław', work_mode: 'remote', has_applied: false,
    start_date: '2026-09-01', end_date: '2026-09-30', study_field_ids: ['sf-1'],
    company: { name: 'Acme', is_verified: true },
  },
  {
    id: 2, title: 'Backend Intern', city: 'Warszawa', work_mode: 'onSite', has_applied: true,
    start_date: '2026-10-01', end_date: '2026-10-31', study_field_ids: ['sf-2'],
    company: { name: 'Globex', is_verified: false },
  },
  {
    id: 3, title: 'Fullstack Intern', city: 'Wrocław', work_mode: 'hybrid', has_applied: false,
    start_date: '2026-11-01', end_date: '2026-11-30', study_field_ids: ['sf-1'],
    company: { name: 'Acme', is_verified: true },
  },
]

const stubs = {
  AppLayout: { template: '<div><slot /></div>' },
  OffersList: {
    props: ['offers', 'guest'],
    template: '<div class="stub-offers-list" :data-guest="guest"><div v-for="o in offers" :key="o.id" class="stub-offer">{{ o.title }}</div></div>',
  },
}

describe('Offers.vue', () => {
  const mountOffers = (props = {}) => mount(Offers, {
    props: { offers, studyFields, ...props },
    global: { stubs },
  })

  describe('student mode', () => {
    it('shows every offer and the correct count by default', () => {
      const wrapper = mountOffers()

      expect(wrapper.findAll('.stub-offer').length).toBe(3)
      expect(wrapper.text()).toContain('student.offers.results.count {"count":3}')
    })

    it('filters offers by search query across title, company and city', async () => {
      const wrapper = mountOffers()

      await wrapper.find('input[type="search"]').setValue('backend')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Backend Intern'])
    })

    it('filters offers by selected city', async () => {
      const wrapper = mountOffers()

      await wrapper.find('#offers-filter-city').setValue('Wrocław')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Frontend Intern', 'Fullstack Intern'])
    })

    it('filters offers by selected work mode', async () => {
      const wrapper = mountOffers()

      await wrapper.find('#offers-filter-work-mode').setValue('hybrid')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Fullstack Intern'])
    })

    it('filters offers to only verified companies', async () => {
      const wrapper = mountOffers()

      await wrapper.find('input[type="checkbox"]').setValue(true)

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Frontend Intern', 'Fullstack Intern'])
    })

    it('filters offers by date range', async () => {
      const wrapper = mountOffers()

      await wrapper.find('#offers-filter-date-from').setValue('2026-10-15')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Backend Intern', 'Fullstack Intern'])
    })

    it('shows an error and ignores the date filter when the range is invalid', async () => {
      const wrapper = mountOffers()

      await wrapper.find('#offers-filter-date-from').setValue('2026-11-01')
      await wrapper.find('#offers-filter-date-to').setValue('2026-09-01')

      expect(wrapper.text()).toContain('student.offers.filters.dateRangeInvalid')
      expect(wrapper.findAll('.stub-offer').length).toBe(3)
    })

    it('resets all filters back to showing every offer', async () => {
      const wrapper = mountOffers()

      await wrapper.find('input[type="search"]').setValue('backend')
      await wrapper.find('input[type="checkbox"]').setValue(true)
      expect(wrapper.findAll('.stub-offer').length).toBe(0)

      await wrapper.find('button[aria-label="student.offers.filters.reset"]').trigger('click')

      expect(wrapper.findAll('.stub-offer').length).toBe(3)
      expect((wrapper.find('input[type="search"]').element as HTMLInputElement).value).toBe('')
      expect((wrapper.find('input[type="checkbox"]').element as HTMLInputElement).checked).toBe(false)
    })

    it('switches to the "applied" tab and shows only offers the student applied to', async () => {
      const wrapper = mountOffers()

      const tabs = wrapper.findAll('[role="tab"]')
      const appliedTab = tabs.find((tab) => tab.text() === 'student.offers.tabs.applied')
      await appliedTab!.trigger('click')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Backend Intern'])
      expect(appliedTab!.attributes('aria-selected')).toBe('true')
    })

    it('switches to the "not applied" tab and shows only offers the student has not applied to', async () => {
      const wrapper = mountOffers()

      const tabs = wrapper.findAll('[role="tab"]')
      const notAppliedTab = tabs.find((tab) => tab.text() === 'student.offers.tabs.notApplied')
      await notAppliedTab!.trigger('click')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Frontend Intern', 'Fullstack Intern'])
    })

    it('does not render the OffersList in guest mode', () => {
      const wrapper = mountOffers()

      expect(wrapper.find('.stub-offers-list').attributes('data-guest')).toBe('false')
    })
  })

  describe('guest mode', () => {
    it('shows every offer without applied/not-applied tabs', () => {
      const wrapper = mountOffers({ isGuest: true })

      expect(wrapper.findAll('.stub-offer').length).toBe(3)
      expect(wrapper.find('[role="tablist"]').exists()).toBe(false)
    })

    it('passes guest mode down to OffersList', () => {
      const wrapper = mountOffers({ isGuest: true })

      expect(wrapper.find('.stub-offers-list').attributes('data-guest')).toBe('true')
    })

    it('filters offers by study field', async () => {
      const wrapper = mountOffers({ isGuest: true })

      const multiSelectInput = wrapper.find('#offers-filter-study-fields')
      await multiSelectInput.setValue('Computer Science')
      await multiSelectInput.trigger('keydown.enter')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Frontend Intern', 'Fullstack Intern'])
    })
  })

  describe('pagination', () => {
    const manyOffers = Array.from({ length: 8 }, (_, index) => ({
      id: index + 1,
      title: `Intern ${index + 1}`,
      city: 'Wrocław',
      work_mode: 'remote',
      has_applied: false,
      start_date: '2026-09-01',
      end_date: '2026-09-30',
      study_field_ids: [],
      company: { name: 'Acme', is_verified: true },
    }))

    it('shows only the first page of results and hides the rest', () => {
      const wrapper = mountOffers({ offers: manyOffers })

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Intern 1', 'Intern 2', 'Intern 3', 'Intern 4', 'Intern 5', 'Intern 6'])
      expect(wrapper.text()).toContain('student.offers.results.count {"count":8}')
    })

    it('navigates to the next page and shows the remaining offers', async () => {
      const wrapper = mountOffers({ offers: manyOffers })

      const pageButtons = wrapper.findAll('button').filter((btn) => btn.text() === '2')
      await pageButtons[0].trigger('click')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Intern 7', 'Intern 8'])
    })

    it('resets back to the first page when a filter changes', async () => {
      const wrapper = mountOffers({ offers: manyOffers })

      const pageButtons = wrapper.findAll('button').filter((btn) => btn.text() === '2')
      await pageButtons[0].trigger('click')
      expect(wrapper.findAll('.stub-offer').map((el) => el.text())).toEqual(['Intern 7', 'Intern 8'])

      await wrapper.find('input[type="search"]').setValue('Intern 1')

      const titles = wrapper.findAll('.stub-offer').map((el) => el.text())
      expect(titles).toEqual(['Intern 1'])
    })

    it('does not render pagination controls when everything fits on one page', () => {
      const wrapper = mountOffers()

      expect(wrapper.findAll('button').some((btn) => btn.text() === '2')).toBe(false)
    })
  })
})
