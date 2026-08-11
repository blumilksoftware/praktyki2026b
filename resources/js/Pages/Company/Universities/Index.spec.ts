import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import Index from '@/Pages/Company/Universities/Index.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string) => key,
  }),
}))

const { routerGet } = vi.hoisted(() => ({
  routerGet: vi.fn(),
}))

vi.mock('@inertiajs/vue3', () => ({
  Head: { template: '<div />' },
  router: { get: routerGet },
}))

vi.mock('@/Composables/useCompanyPanelMenu', () => ({
  useCompanyPanelMenu: () => ({ value: [] }),
}))

const stubs = {
  BaseLayout: { template: '<div><slot /></div>' },
  UniversityCard: {
    props: ['university'],
    template: '<div class="stub-university-card">{{ university.name }}</div>',
  },
  Pagination: { props: ['meta'], template: '<div class="stub-pagination" />' },
  FilterSuggestField: {
    props: ['modelValue', 'options', 'icon', 'placeholder', 'ariaLabel'],
    emits: ['update:modelValue'],
    template: '<input :aria-label="ariaLabel" :value="modelValue" :data-options="options.join(\',\')" @input="$emit(\'update:modelValue\', $event.target.value)">',
  },
}

const universities = {
  data: [
    { id: '1', name: 'Politechnika Testowa', partnership_status: 'none' },
    { id: '2', name: 'Akademia Przykładowa', partnership_status: 'active' },
  ],
  links: {},
  meta: {},
}

describe('Company/Universities/Index.vue', () => {
  const mountIndex = (props = {}) => mount(Index, {
    props: { universities, filters: {}, ...props },
    global: { stubs },
  })

  beforeEach(() => {
    routerGet.mockClear()
  })

  it('renders a university card for every university', () => {
    const names = mountIndex().findAll('.stub-university-card').map((el) => el.text())

    expect(names).toEqual(['Politechnika Testowa', 'Akademia Przykładowa'])
  })

  it('shows the empty state when there are no universities', () => {
    const wrapper = mountIndex({ universities: { data: [], links: {}, meta: {} } })

    expect(wrapper.findAll('.stub-university-card').length).toBe(0)
    expect(wrapper.text()).toContain('company.universities.empty.title')
  })

  it('requests filtered results when the name filter changes', async () => {
    const wrapper = mountIndex()

    await wrapper.find('[aria-label="company.universities.filters.name"]').setValue('Politechnika')

    expect(routerGet).toHaveBeenCalledTimes(1)
    const [url, params] = routerGet.mock.calls[0]
    expect(url).toBe('/company/universities')
    expect(params).toEqual({ name: 'Politechnika', city: '' })
  })

  it('requests filtered results when the city filter changes', async () => {
    const wrapper = mountIndex()

    await wrapper.find('[aria-label="company.universities.filters.city"]').setValue('Legnica')

    expect(routerGet.mock.calls[0][1]).toEqual({ name: '', city: 'Legnica' })
  })

  it('seeds the filter inputs from the filters prop', () => {
    const wrapper = mountIndex({ filters: { name: 'Politechnika', city: 'Legnica' } })

    expect((wrapper.find('[aria-label="company.universities.filters.name"]').element as HTMLInputElement).value).toBe('Politechnika')
    expect((wrapper.find('[aria-label="company.universities.filters.city"]').element as HTMLInputElement).value).toBe('Legnica')
  })

  it('passes the cityOptions prop through to the city filter field', () => {
    const wrapper = mountIndex({ cityOptions: ['Legnica', 'Wrocław'] })

    expect(wrapper.find('[aria-label="company.universities.filters.city"]').attributes('data-options')).toBe('Legnica,Wrocław')
  })

  it('passes the universities paginator to the Pagination component', () => {
    expect(mountIndex().find('.stub-pagination').exists()).toBe(true)
  })
})
