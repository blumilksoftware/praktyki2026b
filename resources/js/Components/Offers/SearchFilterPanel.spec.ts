import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import SearchFilterPanel from '@/Components/Offers/SearchFilterPanel.vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import { ROUTES } from '@/Helpers/routes'
import en from '@/lang/en.json'

const routerGet = vi.fn()

vi.mock('@inertiajs/vue3', () => ({
  router: {
    get: (...args: unknown[]) => routerGet(...args),
  },
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const studyFieldId = '11111111-1111-1111-1111-111111111111'

const studyFieldOptions = [
  { value: studyFieldId, label: 'Computer Science' },
]

function mountPanel(filters = {}) {
  return mount(SearchFilterPanel, {
    props: {
      filters,
      studyFieldOptions,
    },
    global: {
      plugins: [i18n],
    },
  })
}

describe('SearchFilterPanel', () => {
  beforeEach(() => {
    routerGet.mockClear()
  })

  it('renders filter sections with i18n labels', () => {
    const wrapper = mountPanel()

    expect(wrapper.text()).toContain('Filters')
    expect(wrapper.text()).toContain('Fields of study')
    expect(wrapper.text()).toContain('Work mode')
    expect(wrapper.text()).toContain('Internship date range')
  })

  it('requests filtered results when work mode is selected', async () => {
    const wrapper = mountPanel()

    const remoteButton = wrapper.findAll('button').find((btn) => btn.text() === 'Remote')
    expect(remoteButton).toBeDefined()

    await remoteButton!.trigger('click')

    expect(routerGet).toHaveBeenCalledTimes(1)
    expect(routerGet).toHaveBeenCalledWith(
      ROUTES.OFFERS_SEARCH,
      { work_mode: 'remote' },
      expect.objectContaining({
        only: ['offers', 'mapPoints', 'filters'],
        preserveState: true,
        replace: true,
      }),
    )
  })

  it('requests filtered results when study fields change', async () => {
    const wrapper = mountPanel()
    const multiSelect = wrapper.findComponent(DynamicMultiSelect)

    await multiSelect.vm.$emit('update:modelValue', [studyFieldId])

    expect(routerGet).toHaveBeenCalledTimes(1)
    expect(routerGet).toHaveBeenCalledWith(
      ROUTES.OFFERS_SEARCH,
      { study_field_ids: [studyFieldId] },
      expect.objectContaining({ replace: true }),
    )
  })

  it('shows friendly date range error without submitting invalid range', async () => {
    const wrapper = mountPanel()

    await wrapper.find('#offer-search-date-from').setValue('2026-09-01')
    routerGet.mockClear()

    await wrapper.find('#offer-search-date-to').setValue('2026-08-01')

    expect(wrapper.text()).toContain('The end date must be on or after the start date.')
    expect(routerGet).not.toHaveBeenCalled()
  })

  it('submits valid date range filter', async () => {
    const wrapper = mountPanel()

    await wrapper.find('#offer-search-date-from').setValue('2026-08-01')
    await wrapper.find('#offer-search-date-to').setValue('2026-09-01')

    expect(routerGet).toHaveBeenCalledWith(
      ROUTES.OFFERS_SEARCH,
      { date_from: '2026-08-01', date_to: '2026-09-01' },
      expect.objectContaining({ replace: true }),
    )
  })

  it('clears filters and reloads unfiltered results', async () => {
    const wrapper = mountPanel({
      work_mode: 'remote',
      study_field_ids: [studyFieldId],
    })

    const clearButton = wrapper.findAll('button').find((btn) => btn.text().includes('Clear filters'))
    expect(clearButton).toBeDefined()

    await clearButton!.trigger('click')

    expect(routerGet).toHaveBeenCalledWith(
      ROUTES.OFFERS_SEARCH,
      {},
      expect.objectContaining({ replace: true }),
    )
  })

  it('does not request again when filters prop syncs from server', async () => {
    const wrapper = mountPanel({ work_mode: 'remote' })

    routerGet.mockClear()

    await wrapper.setProps({ filters: { work_mode: 'remote' } })

    expect(routerGet).not.toHaveBeenCalled()
  })
})
