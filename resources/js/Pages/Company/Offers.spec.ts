import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import Offers from '@/Pages/Company/Offers.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const { routerPatch, routerGet, routerVisit } = vi.hoisted(() => ({ routerPatch: vi.fn(), routerGet: vi.fn(), routerVisit: vi.fn() }))
const pageProps = { props: { flash: {} } }
const usePage = vi.fn(() => pageProps)
const baseToastShow = vi.fn()

vi.mock('@inertiajs/vue3', async () => {
  const actual = await vi.importActual('@inertiajs/vue3')
  return {
    ...actual,
    Head: { template: '<div />' },
    router: { patch: routerPatch, get: routerGet, visit: routerVisit },
    usePage,
  }
})

const draftOffer = {
  id: '1',
  title: 'Backend Internship',
  status: 'draft',
  spots: 5,
  remaining_spots: 5,
  applications_count: 2,
}

const publishedOffer = {
  id: '2',
  title: 'Frontend Internship',
  status: 'published',
  spots: 10,
  remaining_spots: 3,
  applications_count: 7,
}

const paginate = (data: unknown[]) => ({
  data,
  links: [],
  last_page: 1,
  from: data.length > 0 ? 1 : null,
  to: data.length,
  total: data.length,
})

const mountOffers = (props = {}) => mount(Offers, {
  props: {
    offers: paginate([]),
    isCompanyVerified: true,
    search: '',
    status: '',
    statusCounts: {},
    ...props,
  },
  global: {
    plugins: [i18n],
    stubs: {
      BaseLayout: { template: '<div><slot /></div>' },
      CompanyOfferDeleteModal: {
        props: ['open', 'offerId', 'offerTitle'],
        template: '<div v-if="open" class="stub-delete-modal">{{ offerId }}:{{ offerTitle }}</div>',
      },
      CompanyOfferUnpublishModal: {
        props: ['open', 'offerId', 'offerTitle'],
        template: '<div v-if="open" class="stub-unpublish-modal">{{ offerId }}:{{ offerTitle }}</div>',
      },
      BaseToast: {
        template: '<div />',
        methods: { show: baseToastShow },
      },
    },
  },
})

beforeEach(() => {
  pageProps.props.flash = {}
  baseToastShow.mockClear()
  routerPatch.mockClear()
  routerGet.mockClear()
  routerVisit.mockClear()
})

describe('Company/Offers', () => {
  it('shows the empty state when there are no offers', () => {
    const wrapper = mountOffers()

    expect(wrapper.text()).toContain(en.company.offers.index.empty.title)
  })

  it('shows a toast message when the page has a flash status', async () => {
    pageProps.props.flash = { status: 'Your offer changes were saved successfully.' }

    const wrapper = mountOffers()
    await wrapper.vm.$nextTick()

    expect(baseToastShow).toHaveBeenCalledWith('Your offer changes were saved successfully.')
  })

  it('renders offer title, status, spots and application count', () => {
    const wrapper = mountOffers({ offers: paginate([publishedOffer]), statusCounts: { published: 1 } })
    const text = wrapper.text()

    expect(text).toContain('Frontend Internship')
    expect(text).toContain(en.company.offers.index.status.published)
    expect(text).toContain('3')
    expect(text).toContain('7')
  })

  it('shows remaining spots rather than the total number of spots', () => {
    const wrapper = mountOffers({ offers: paginate([publishedOffer]), statusCounts: { published: 1 } })

    expect(wrapper.text()).toContain(en.company.offers.index.spotsLabel.replace('{count}', '3'))
    expect(wrapper.text()).not.toContain(en.company.offers.index.spotsLabel.replace('{count}', '10'))
  })

  const openActionsMenu = async (wrapper: ReturnType<typeof mountOffers>, offerId: string) => {
    const row = wrapper.find(`[data-offer-menu="${offerId}"]`)
    await row.find('button').trigger('click')
    return row
  }

  it('navigates to the edit route when the edit action is clicked', async () => {
    const wrapper = mountOffers({ offers: paginate([publishedOffer]), statusCounts: { published: 1 } })

    const row = await openActionsMenu(wrapper, '2')
    const editButton = row.findAll('button').find((btn) => btn.text() === en.company.offers.index.editAction)
    await editButton!.trigger('click')

    expect(routerVisit).toHaveBeenCalledWith('/company/offers/2/edit')
  })

  it.each(['closed', 'expired'])('disables the edit action for a %s offer', async (status) => {
    const offer = { ...publishedOffer, id: '3', status }
    const wrapper = mountOffers({ offers: paginate([offer]), statusCounts: { [status]: 1 }, isCompanyVerified: true })
    const row = await openActionsMenu(wrapper, '3')

    const editButton = row.findAll('button').find((btn) => btn.text() === en.company.offers.index.editAction)
    expect(editButton).toBeTruthy()
    expect(editButton!.attributes('disabled')).toBeDefined()
    expect(editButton!.classes()).toContain('cursor-not-allowed')
  })

  it('shows a publish action for a verified company\'s draft offer and publishes it', async () => {
    routerPatch.mockClear()
    const wrapper = mountOffers({ offers: paginate([draftOffer]), statusCounts: { draft: 1 }, isCompanyVerified: true })

    const row = await openActionsMenu(wrapper, '1')
    const publishButton = row.findAll('button').find((btn) => btn.text() === en.company.offers.index.publishAction)
    expect(publishButton).toBeTruthy()

    await publishButton!.trigger('click')

    expect(routerPatch).toHaveBeenCalledTimes(1)
    expect(routerPatch.mock.calls[0][0]).toBe('/company/offers/1/publish')
  })

  it('hides the publish action and shows a verification hint for an unverified company\'s draft offer', async () => {
    const wrapper = mountOffers({ offers: paginate([draftOffer]), statusCounts: { draft: 1 }, isCompanyVerified: false })

    const row = await openActionsMenu(wrapper, '1')
    expect(row.findAll('button').some((btn) => btn.text() === en.company.offers.index.publishAction)).toBe(false)
    expect(wrapper.text()).toContain(en.company.offers.index.verificationRequiredHint)
  })

  it('shows an unpublish action only for published offers and opens the confirmation modal with the offer details', async () => {
    const wrapper = mountOffers({
      offers: paginate([draftOffer, publishedOffer]),
      statusCounts: { draft: 1, published: 1 },
      isCompanyVerified: true,
    })

    const draftRow = await openActionsMenu(wrapper, '1')
    expect(draftRow.findAll('button').some((btn) => btn.text() === en.company.offers.index.unpublishAction)).toBe(false)

    const publishedRow = await openActionsMenu(wrapper, '2')
    const unpublishButton = publishedRow.findAll('button').find((btn) => btn.text() === en.company.offers.index.unpublishAction)
    await unpublishButton!.trigger('click')

    const modal = wrapper.find('.stub-unpublish-modal')
    expect(modal.exists()).toBe(true)
    expect(modal.text()).toBe('2:Frontend Internship')
  })

  it('opens the delete confirmation modal with the offer details', async () => {
    const wrapper = mountOffers({ offers: paginate([publishedOffer]), statusCounts: { published: 1 }, isCompanyVerified: true })

    const row = await openActionsMenu(wrapper, '2')
    const deleteButton = row.findAll('button').find((btn) => btn.text() === en.company.offers.index.deleteAction)
    await deleteButton!.trigger('click')

    const modal = wrapper.find('.stub-delete-modal')
    expect(modal.exists()).toBe(true)
    expect(modal.text()).toBe('2:Frontend Internship')
  })

  it('always offers the delete action regardless of offer status', async () => {
    const closedOffer = { ...publishedOffer, id: '3', status: 'closed' }
    const wrapper = mountOffers({ offers: paginate([closedOffer]), statusCounts: { closed: 1 }, isCompanyVerified: true })

    const row = await openActionsMenu(wrapper, '3')
    expect(row.findAll('button').some((btn) => btn.text() === en.company.offers.index.deleteAction)).toBe(true)
  })

  it('closes the actions menu when clicking outside of it', async () => {
    const wrapper = mountOffers({ offers: paginate([publishedOffer]), statusCounts: { published: 1 }, isCompanyVerified: true })

    const row = await openActionsMenu(wrapper, '2')
    expect(row.find('[role="menu"]').exists()).toBe(true)

    document.body.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }))
    await wrapper.vm.$nextTick()

    expect(wrapper.find('[role="menu"]').exists()).toBe(false)
  })

  it('requests filtered offers from the server when searching', async () => {
    vi.useFakeTimers()
    routerGet.mockClear()
    const wrapper = mountOffers({ offers: paginate([publishedOffer]), statusCounts: { published: 1 } })

    await wrapper.find('input[type="search"]').setValue('front')
    vi.advanceTimersByTime(400)
    await wrapper.vm.$nextTick()

    expect(routerGet).toHaveBeenCalledTimes(1)
    expect(routerGet.mock.calls[0][0]).toBe('/company/offers')
    expect(routerGet.mock.calls[0][1]).toEqual({ search: 'front', status: undefined })
    vi.useRealTimers()
  })

  it('shows a tab per status with counts and requests the filtered status when a tab is selected', async () => {
    routerGet.mockClear()
    const wrapper = mountOffers({
      offers: paginate([draftOffer, publishedOffer]),
      statusCounts: { draft: 1, published: 1 },
    })

    const tabs = wrapper.findAll('[role="tab"]')
    expect(tabs.map((tab) => tab.text())).toEqual([
      `${en.company.offers.index.tabs.all} (2)`,
      `${en.company.offers.index.status.draft} (1)`,
      `${en.company.offers.index.status.published} (1)`,
      `${en.company.offers.index.status.closed} (0)`,
      `${en.company.offers.index.status.expired} (0)`,
    ])

    const publishedTab = tabs.find((tab) => tab.text().startsWith(en.company.offers.index.status.published))
    await publishedTab!.trigger('click')

    expect(routerGet).toHaveBeenCalledTimes(1)
    expect(routerGet.mock.calls[0][1]).toEqual({ search: undefined, status: 'published' })
  })

  it('shows a distinct no-results state when the current filters exclude every offer', () => {
    const wrapper = mountOffers({ offers: paginate([]), statusCounts: { published: 1 }, search: 'nonexistent offer' })

    expect(wrapper.text()).toContain(en.company.offers.index.noResults.title)
    expect(wrapper.text()).not.toContain(en.company.offers.index.empty.title)
  })

  it('shows pagination controls when there is more than one page', async () => {
    const wrapper = mountOffers({
      offers: {
        data: [publishedOffer],
        links: [
          { url: null, label: '&laquo; Previous', active: false },
          { url: '/company/offers?page=1', label: '1', active: true },
          { url: '/company/offers?page=2', label: '2', active: false },
          { url: '/company/offers?page=2', label: 'Next &raquo;', active: false },
        ],
        last_page: 2,
        from: 1,
        to: 1,
        total: 2,
      },
      statusCounts: { published: 2 },
    })

    const pageTwo = wrapper.findAll('button').find((btn) => btn.text() === '2')
    expect(pageTwo).toBeTruthy()

    await pageTwo!.trigger('click')

    expect(routerGet).toHaveBeenCalledWith('/company/offers?page=2', {}, { preserveState: true, preserveScroll: true, replace: true })
  })
})
