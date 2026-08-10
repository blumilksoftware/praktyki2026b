import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { reactive } from 'vue'
import { ROUTES } from '@/Helpers/routes'

const routerVisit = vi.fn()

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

  it('shows a save confirmation modal after a successful edit and redirects after a short delay', async () => {
    const wrapper = getWrapper({
      isCompanyVerified: true,
      offer: {
        id: 123,
        title: 'Test offer',
        description: 'Test description',
        spots: 1,
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

    const patch = wrapper.vm.form.patch as ReturnType<typeof vi.fn>
    await wrapper.vm.submit()

    expect(patch).toHaveBeenCalled()
    const onSuccess = patch.mock.calls[0][1].onSuccess

    vi.useFakeTimers()
    try {
      onSuccess()
      await wrapper.vm.$nextTick()

      expect(wrapper.vm.showSuccessModal).toBe(true)
      expect(wrapper.text()).toContain('Your changes have been saved. You will be returned to the offer list shortly.')

      vi.advanceTimersByTime(2000)
      expect(routerVisit).toHaveBeenCalledWith(ROUTES.COMPANY_OFFERS_INDEX)
    } finally {
      vi.useRealTimers()
    }
  })
})
