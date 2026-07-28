import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import Offers from '@/Pages/Student/Offers.vue'

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

const offers = [
  { id: 1, title: 'Frontend Intern', city: 'Wrocław', work_mode: 'remote', company: { name: 'Acme', is_verified: true } },
  { id: 2, title: 'Backend Intern', city: 'Warszawa', work_mode: 'onSite', company: { name: 'Globex', is_verified: false } },
  { id: 3, title: 'Fullstack Intern', city: 'Wrocław', work_mode: 'hybrid', company: { name: 'Acme', is_verified: true } },
]

describe('Student/Offers.vue', () => {
  const mountOffers = () => mount(Offers, {
    props: { offers },
    global: {
      stubs: {
        StudentPanelLayout: { template: '<div><slot /></div>' },
        OffersList: {
          props: ['offers'],
          template: '<div class="stub-offers-list"><div v-for="o in offers" :key="o.id" class="stub-offer">{{ o.title }}</div></div>',
        },
      },
    },
  })

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

  it('shows no offers when filters combine to exclude everything', async () => {
    const wrapper = mountOffers()

    await wrapper.find('input[type="search"]').setValue('backend')
    await wrapper.find('input[type="checkbox"]').setValue(true)

    expect(wrapper.findAll('.stub-offer').length).toBe(0)
    expect(wrapper.text()).toContain('student.offers.results.count {"count":0}')
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
})
