import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import Index from '@/Pages/University/Companies/Index.vue'

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

const stubs = {
  UniversityLayout: { template: '<div><slot /></div>' },
  CompanyCard: {
    props: ['company'],
    template: '<div class="stub-company-card">{{ company.name }}</div>',
  },
  Pagination: { props: ['meta'], template: '<div class="stub-pagination" />' },
  FilterSuggestField: {
    props: ['modelValue', 'options', 'icon', 'placeholder', 'ariaLabel'],
    emits: ['update:modelValue'],
    template: '<input :aria-label="ariaLabel" :placeholder="placeholder" :value="modelValue" :data-options="options.join(\',\')" @input="$emit(\'update:modelValue\', $event.target.value)">',
  },
}

const companies = {
  data: [
    { id: '1', name: 'Acme Corp', partnership_status: 'none' },
    { id: '2', name: 'Globex', partnership_status: 'active' },
  ],
  links: {},
  meta: {},
}

describe('University/Companies/Index.vue', () => {
  const mountIndex = (props = {}) => mount(Index, {
    props: { companies, filters: {}, ...props },
    global: { stubs },
  })

  beforeEach(() => {
    routerGet.mockClear()
  })

  it('renders a company card for every company', () => {
    const wrapper = mountIndex()

    const names = wrapper.findAll('.stub-company-card').map((el) => el.text())
    expect(names).toEqual(['Acme Corp', 'Globex'])
  })

  it('shows the empty state when there are no companies', () => {
    const wrapper = mountIndex({ companies: { data: [], links: {}, meta: {} } })

    expect(wrapper.findAll('.stub-company-card').length).toBe(0)
    expect(wrapper.text()).toContain('university.companies.empty.title')
  })

  it('requests filtered results when the name filter changes', async () => {
    const wrapper = mountIndex()

    await wrapper.find('[aria-label="university.companies.filters.name"]').setValue('Acme')

    expect(routerGet).toHaveBeenCalledTimes(1)
    const [url, params] = routerGet.mock.calls[0]
    expect(url).toBe('/university/companies')
    expect(params).toEqual({ name: 'Acme', city: '', tag: '' })
  })

  it('requests filtered results when the city filter changes', async () => {
    const wrapper = mountIndex()

    await wrapper.find('[aria-label="university.companies.filters.city"]').setValue('Wrocław')

    const params = routerGet.mock.calls[0][1]
    expect(params).toEqual({ name: '', city: 'Wrocław', tag: '' })
  })

  it('requests filtered results when the tag filter changes', async () => {
    const wrapper = mountIndex()

    await wrapper.find('[aria-label="university.companies.filters.tag"]').setValue('Laravel')

    const params = routerGet.mock.calls[0][1]
    expect(params).toEqual({ name: '', city: '', tag: 'Laravel' })
  })

  it('seeds the filter inputs from the filters prop', () => {
    const wrapper = mountIndex({ filters: { name: 'Acme', city: 'Wrocław', tag: 'IT' } })

    expect((wrapper.find('[aria-label="university.companies.filters.name"]').element as HTMLInputElement).value).toBe('Acme')
    expect((wrapper.find('[aria-label="university.companies.filters.city"]').element as HTMLInputElement).value).toBe('Wrocław')
    expect((wrapper.find('[aria-label="university.companies.filters.tag"]').element as HTMLInputElement).value).toBe('IT')
  })

  it('passes the companies paginator to the Pagination component', () => {
    const wrapper = mountIndex()

    expect(wrapper.find('.stub-pagination').exists()).toBe(true)
  })

  it('passes the cityOptions prop through to the city filter field', () => {
    const wrapper = mountIndex({ cityOptions: ['Legnica', 'Wrocław'] })

    expect(wrapper.find('[aria-label="university.companies.filters.city"]').attributes('data-options')).toBe('Legnica,Wrocław')
  })

  it('passes the tagOptions prop through to the tag filter field', () => {
    const wrapper = mountIndex({ tagOptions: ['Laravel', 'Vue'] })

    expect(wrapper.find('[aria-label="university.companies.filters.tag"]').attributes('data-options')).toBe('Laravel,Vue')
  })
})
