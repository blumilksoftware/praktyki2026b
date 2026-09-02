import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import OfferCard from '@/Components/Offer/OfferCard.vue'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string, params?: Record<string, unknown>) => (params ? `${key}:${JSON.stringify(params)}` : key),
  }),
}))

const { routerPost, routerDelete } = vi.hoisted(() => ({
  routerPost: vi.fn(),
  routerDelete: vi.fn(),
}))

const { toastSuccess, toastError } = vi.hoisted(() => ({
  toastSuccess: vi.fn(),
  toastError: vi.fn(),
}))

vi.mock('@/Composables/useToast', () => ({
  useToast: () => ({ toastSuccess, toastError }),
}))

vi.mock('@inertiajs/vue3', () => ({
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
  router: { post: routerPost, delete: routerDelete },
}))

const baseOffer = {
  id: 42,
  title: 'Frontend Developer Intern',
  city: 'Wrocław',
  work_mode: 'remote',
  start_date: '2026-09-01',
  end_date: '2026-12-01',
  spots: 5,
  remaining_spots: 3,
  company: {
    id: 'company-1',
    name: 'Acme Corp',
    logo_path: null,
    is_verified: true,
  },
}

describe('OfferCard.vue', () => {
  const createWrapper = (props = {}) => mount(OfferCard, {
    props: { offer: baseOffer, canApply: true, ...props },
  })

  beforeEach(() => {
    routerPost.mockClear()
    routerDelete.mockClear()
    toastSuccess.mockClear()
    toastError.mockClear()
  })

  it('renders company name, title, city, date range and remaining spots', () => {
    const wrapper = createWrapper()
    const text = wrapper.text()

    expect(text).toContain('Acme Corp')
    expect(text).toContain('Frontend Developer Intern')
    expect(text).toContain('Wrocław')
    expect(text).toContain('01.09.2026 - 01.12.2026')
    expect(text).toContain('3')
  })

  it('shows the translated work mode label', () => {
    const wrapper = createWrapper()

    expect(wrapper.text()).toContain('student.workModes.remote')
  })

  it('shows the verified badge when the company is verified', () => {
    const wrapper = createWrapper()

    expect(wrapper.text()).toContain('student.offers.card.verified')
  })

  it('hides the verified badge when the company is not verified', () => {
    const wrapper = createWrapper({
      offer: { ...baseOffer, company: { ...baseOffer.company, is_verified: false } },
    })

    expect(wrapper.text()).not.toContain('student.offers.card.verified')
  })

  it('shows the company logo image when a logo path is present', () => {
    const wrapper = createWrapper({
      offer: { ...baseOffer, company: { ...baseOffer.company, logo_path: '/logos/acme.png' } },
    })

    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('/logos/acme.png')
  })

  it('falls back to the company initial when there is no logo path', () => {
    const wrapper = createWrapper()

    expect(wrapper.find('img').exists()).toBe(false)
    expect(wrapper.text()).toContain('A')
  })

  it('posts to the offer favourite endpoint when marking an offer as favorite', async () => {
    const wrapper = createWrapper()

    const favoriteButton = wrapper.findAll('button').find((btn) => btn.attributes('aria-pressed') !== undefined)
    await favoriteButton!.trigger('click')

    expect(routerPost).toHaveBeenCalledTimes(1)
    const [url, data] = routerPost.mock.calls[0]
    expect(url).toBe('/student/offers/42/favourite')
    expect(data).toEqual({})
  })

  it('deletes the offer favourite endpoint when unmarking an already favorited offer', async () => {
    const wrapper = createWrapper({ offer: { ...baseOffer, is_favorite: true } })

    const favoriteButton = wrapper.findAll('button').find((btn) => btn.attributes('aria-pressed') !== undefined)
    await favoriteButton!.trigger('click')

    expect(routerDelete).toHaveBeenCalledTimes(1)
    expect(routerDelete.mock.calls[0][0]).toBe('/student/offers/42/favourite')
  })

  it('reflects the offer.is_favorite prop on the favorite button', () => {
    const wrapper = createWrapper({ offer: { ...baseOffer, is_favorite: true } })

    const favoriteButton = wrapper.findAll('button').find((btn) => btn.attributes('aria-pressed') !== undefined)
    expect(favoriteButton!.attributes('aria-pressed')).toBe('true')
  })

  it('blocks the apply button without hiding it when the student has no CV', () => {
    const wrapper = createWrapper({ hasCv: false })

    const applyButton = wrapper.findAll('button').find((btn) => btn.text() === 'buttons.apply.applyNow')
    expect(applyButton!.attributes('aria-disabled')).toBe('true')
    expect(applyButton!.attributes('disabled')).toBeUndefined()
  })

  it('posts to the offer apply endpoint when applying', async () => {
    const wrapper = createWrapper()

    const applyButton = wrapper.findAll('button').find((btn) => btn.text() === 'buttons.apply.applyNow')
    await applyButton!.trigger('click')

    expect(routerPost).toHaveBeenCalledTimes(1)
    const [url, data] = routerPost.mock.calls[0]
    expect(url).toBe('/student/offers/42/apply')
    expect(data).toEqual({})
  })

  it('shows the applied confirmation once the apply request succeeds', async () => {
    const wrapper = createWrapper()

    const applyButton = wrapper.findAll('button').find((btn) => btn.text() === 'buttons.apply.applyNow')
    await applyButton!.trigger('click')

    const options = routerPost.mock.calls[0][2]
    options.onSuccess()
    options.onFinish()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('buttons.apply.appliedOn')
  })

  it('shows a persisted applied state when the offer already has an application', () => {
    const wrapper = createWrapper({
      offer: { ...baseOffer, has_applied: true, applied_at: '2026-07-20' },
    })

    expect(wrapper.text()).toContain('buttons.apply.appliedOn')
  })

  it('raises a toast with the server validation error when the apply request fails', async () => {
    const wrapper = createWrapper()

    const applyButton = wrapper.findAll('button').find((btn) => btn.text() === 'buttons.apply.applyNow')
    await applyButton!.trigger('click')

    const options = routerPost.mock.calls[0][2]
    options.onError({ offer: 'You have already applied to this offer.' })
    await wrapper.vm.$nextTick()

    expect(toastError).toHaveBeenCalledWith('You have already applied to this offer.')
  })

  it('shows neither the apply button nor the login link to a signed in user who cannot apply', () => {
    const wrapper = createWrapper({ canApply: false })

    expect(wrapper.findAll('button').some((btn) => btn.text() === 'buttons.apply.applyNow')).toBe(false)
    expect(wrapper.find('a[href="/login"]').exists()).toBe(false)
  })

  it('shows the login link instead of the apply button to a guest', () => {
    const wrapper = createWrapper({ canApply: false, guest: true })

    expect(wrapper.findAll('button').some((btn) => btn.text() === 'buttons.apply.applyNow')).toBe(false)
    expect(wrapper.find('a[href="/login"]').text()).toBe('student.offers.card.loginToApply')
  })

  it('hides the favourite button from users who cannot apply', () => {
    const wrapper = createWrapper({ canApply: false })

    expect(wrapper.findAll('button').some((btn) => btn.attributes('aria-pressed') !== undefined)).toBe(false)
  })

  it('does not show a withdraw button when the student has not applied', () => {
    const wrapper = createWrapper()

    expect(wrapper.findAll('button').some((btn) => btn.text() === 'student.applications.withdraw.action')).toBe(false)
  })

  it('withdraws the application after confirming in the modal', async () => {
    const wrapper = mount(OfferCard, {
      props: { offer: { ...baseOffer, has_applied: true, applied_at: '2026-07-20' }, canApply: true },
      global: {
        stubs: {
          WithdrawApplicationModal: {
            props: ['open', 'offerTitle', 'processing'],
            template: '<div v-if="open" class="stub-withdraw-modal"><button @click="$emit(\'confirm\')">confirm-withdraw</button></div>',
          },
        },
      },
    })

    const withdrawButton = wrapper.findAll('button').find((btn) => btn.text() === 'student.applications.withdraw.action')
    await withdrawButton!.trigger('click')

    const modal = wrapper.find('.stub-withdraw-modal')
    expect(modal.exists()).toBe(true)

    await modal.find('button').trigger('click')

    expect(routerPost).toHaveBeenCalledTimes(1)
    const [url, data, options] = routerPost.mock.calls[0]
    expect(url).toBe('/student/offers/42/withdraw')
    expect(data).toEqual({})

    options.onSuccess()
    options.onFinish()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('buttons.apply.applyNow')
    expect(wrapper.findAll('button').some((btn) => btn.text() === 'student.applications.withdraw.action')).toBe(false)
  })

  it('keeps the disabled "show on map" button focusable and explained via aria-disabled instead of the native disabled attribute', () => {
    const wrapper = createWrapper()

    const mapButton = wrapper.findAll('button').find((btn) => btn.text() === 'student.offers.card.showOnMap')
    expect(mapButton!.attributes('disabled')).toBeUndefined()
    expect(mapButton!.attributes('aria-disabled')).toBe('true')
    expect(mapButton!.attributes('title')).toBe('student.offers.card.mapComingSoon')
  })

  it('links the offer title to the offer detail page', () => {
    const wrapper = createWrapper()
    const titleLink = wrapper.find('a[href="/offers/42"]')

    expect(titleLink.exists()).toBe(true)
    expect(titleLink.text()).toContain('Frontend Developer Intern')
  })

  it('shows the study field tags when the offer has any', () => {
    const wrapper = createWrapper({
      offer: { ...baseOffer, study_fields: [{ id: 'sf-1', name: 'Informatyka' }, { id: 'sf-2', name: 'Matematyka' }] },
    })

    expect(wrapper.text()).toContain('Informatyka')
    expect(wrapper.text()).toContain('Matematyka')
  })

  it('shows the closing soon badge when the offer is closing soon', () => {
    const wrapper = createWrapper({
      offer: { ...baseOffer, closing_soon: true },
    })

    expect(wrapper.text()).toContain('student.offers.card.closingSoon')
  })

  it('hides the closing soon badge when the offer is not closing soon', () => {
    const wrapper = createWrapper({
      offer: { ...baseOffer, closing_soon: false },
    })

    expect(wrapper.text()).not.toContain('student.offers.card.closingSoon')
  })
})
