import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { reactive } from 'vue'
import { ROUTES } from '@/Helpers/routes'

const { routerVisit } = vi.hoisted(() => ({ routerVisit: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({
  router: { visit: routerVisit },
  useForm: (initialData: Record<string, unknown>) => reactive({
    ...initialData,
    errors: {},
    processing: false,
    isDirty: false,
    patch: vi.fn(),
    post: vi.fn(),
  }),
}))

import OfferForm from './OfferForm.vue'

describe('OfferForm', () => {
  const getWrapper = (props = {}) => mount(OfferForm, {
    props: {
      studyFields: [{ id: 1, name: 'Computer Science' }],
      universities: [{ id: 1, name: 'Wrocław University' }],
      isCompanyVerified: false,
      ...props,
    },
    global: {
      stubs: {
        BaseInput: true,
        BaseSelect: true,
        BaseModal: true,
        DateRangeField: true,
        CityAutocomplete: true,
        DynamicMultiSelect: true,
        BaseTextarea: true,
      },
    },
  })

  beforeEach(() => {
    routerVisit.mockClear()
  })

  it('prompts before navigating away when there are unsaved changes', async () => {
    const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true)
    const wrapper = getWrapper()

    wrapper.vm.form.isDirty = true

    const cancelButton = wrapper.findAll('button').find((button) => button.text() === 'Cancel')
    expect(cancelButton).toBeTruthy()

    await cancelButton!.trigger('click')

    expect(confirmSpy).toHaveBeenCalledWith('You have unsaved changes. Leave without saving?')
    expect(routerVisit).toHaveBeenCalledWith(ROUTES.COMPANY_OFFERS_INDEX)

    confirmSpy.mockRestore()
  })

  it('previews the spots left after the accepted candidates, not the full capacity', () => {
    const wrapper = getWrapper({
      isCompanyVerified: true,
      offer: {
        id: 123,
        title: 'Test offer',
        description: 'Test description',
        spots: 4,
        accepted_applications_count: 3,
        city: 'Test city',
        start_date: '2026-09-01',
        end_date: '2026-09-30',
        work_mode: 'remote',
        status: 'draft',
        is_paid: false,
        salary_min: null,
        salary_max: null,
        study_field_ids: [],
        university_ids: [],
      },
    })

    expect(wrapper.vm.previewOfferData.remaining_spots).toBe(1)

    wrapper.vm.form.spots = '2'

    expect(wrapper.vm.previewOfferData.remaining_spots).toBe(0)
  })
})
