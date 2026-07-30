import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest'
import { router } from '@inertiajs/vue3'
import Dashboard from '@/Pages/Company/Dashboard.vue'
import OffersTable from '@/Components/Company/Offers/OffersTable.vue'
import OffersCards from '@/Components/Company/Offers/OffersCards.vue'
import OffersToolbar from '@/Components/Company/Offers/OffersToolbar.vue'
import OffersPagination from '@/Components/Company/Offers/OffersPagination.vue'
import OfferActionsMenu from '@/Components/Company/Offers/OfferActionsMenu.vue'
import en from '@/lang/en.json'

vi.mock('@inertiajs/vue3', () => ({
  router: {
    get: vi.fn(),
    visit: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
  Head: { props: ['title'], template: '<div />' },
}))

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const offers = [
  { id: 'o1', title: 'Frontend Intern', status: 'published', spots: 3, applications_count: 7 },
  { id: 'o2', title: 'Backend Intern', status: 'draft', spots: 1, applications_count: 0 },
]

const paginator = {
  data: offers,
  from: 1,
  to: 2,
  total: 2,
  last_page: 1,
  links: [],
}

function mountDashboard(props = {}, options = {}) {
  return mount(Dashboard, {
    props: {
      offers: paginator,
      ...props,
    },
    global: {
      plugins: [i18n],
      stubs: {
        BaseLayout: { template: '<div><slot /></div>' },
        OnboardingBanner: true,
      },
    },
    ...options,
  })
}

// The dashboard renders the table (desktop) and the cards (mobile) side by side,
// so every offer owns two action menus — one per layout.
const menusFor = (wrapper: ReturnType<typeof mountDashboard>, component: typeof OffersTable | typeof OffersCards) =>
  wrapper.findComponent(component).findAllComponents(OfferActionsMenu)

describe('Company/Dashboard', () => {
  beforeEach(() => {
    vi.mocked(router.get).mockClear()
    vi.mocked(router.visit).mockClear()
    vi.mocked(router.patch).mockClear()
    vi.mocked(router.delete).mockClear()
  })

  afterEach(() => {
    vi.useRealTimers()
    vi.restoreAllMocks()
  })

  describe('rendering', () => {
    it('renders the page headings', () => {
      const wrapper = mountDashboard()

      expect(wrapper.text()).toContain('Manage your job postings and applications')
      expect(wrapper.text()).toContain('Offers')
    })

    it('renders the toolbar, both offer layouts and the paginator', () => {
      const wrapper = mountDashboard()

      expect(wrapper.findComponent(OffersToolbar).exists()).toBe(true)
      expect(wrapper.findComponent(OffersTable).props('offers')).toEqual(offers)
      expect(wrapper.findComponent(OffersCards).props('offers')).toEqual(offers)
      expect(wrapper.findComponent(OffersPagination).props('offers')).toEqual(paginator)
    })

    it('shows the empty state instead of the offer layouts when there are no offers', () => {
      const wrapper = mountDashboard({ offers: { ...paginator, data: [], total: 0 } })

      expect(wrapper.text()).toContain('No offers yet')
      expect(wrapper.findComponent(OffersTable).exists()).toBe(false)
      expect(wrapper.findComponent(OffersCards).exists()).toBe(false)
    })

    it('seeds the toolbar from the server side filters', () => {
      const wrapper = mountDashboard({ search: 'intern', status: 'draft' })
      const toolbar = wrapper.findComponent(OffersToolbar)

      expect(toolbar.props('search')).toBe('intern')
      expect(toolbar.props('status')).toBe('draft')
    })
  })

  describe('filtering', () => {
    it('reloads with a debounced search query and resets the page', async () => {
      vi.useFakeTimers()
      const wrapper = mountDashboard({ status: 'draft' })

      await wrapper.find('input').setValue('intern')
      expect(router.get).not.toHaveBeenCalled()

      vi.advanceTimersByTime(350)

      expect(router.get).toHaveBeenCalledTimes(1)
      expect(router.get).toHaveBeenCalledWith(
        window.location.pathname,
        { sort: 'created_at', direction: 'desc', search: 'intern', status: 'draft', page: undefined },
        { preserveScroll: true, replace: true },
      )
    })

    it('collapses rapid typing into a single request', async () => {
      vi.useFakeTimers()
      const wrapper = mountDashboard()
      const input = wrapper.find('input')

      await input.setValue('in')
      vi.advanceTimersByTime(200)
      await input.setValue('intern')
      vi.advanceTimersByTime(350)

      expect(router.get).toHaveBeenCalledTimes(1)
      expect(vi.mocked(router.get).mock.calls[0][1]).toMatchObject({ search: 'intern' })
    })

    it('drops an empty search from the query string', async () => {
      vi.useFakeTimers()
      const wrapper = mountDashboard({ search: 'intern' })

      await wrapper.find('input').setValue('')
      vi.advanceTimersByTime(350)

      expect(vi.mocked(router.get).mock.calls[0][1]).toMatchObject({ search: undefined })
    })

    it('cancels a pending search request when the page is torn down', async () => {
      vi.useFakeTimers()
      const wrapper = mountDashboard()

      await wrapper.find('input').setValue('intern')
      wrapper.unmount()
      vi.advanceTimersByTime(350)

      expect(router.get).not.toHaveBeenCalled()
    })

    it('reloads immediately when the status filter changes', async () => {
      const wrapper = mountDashboard()

      await wrapper.find('select').setValue('closed')

      expect(router.get).toHaveBeenCalledWith(
        window.location.pathname,
        { sort: 'created_at', direction: 'desc', search: undefined, status: 'closed', page: undefined },
        { preserveScroll: true, replace: true },
      )
    })
  })

  describe('sorting', () => {
    it('sorts ascending on a new column', async () => {
      const wrapper = mountDashboard()

      await wrapper.findAll('th button')[0].trigger('click')

      expect(vi.mocked(router.get).mock.calls[0][1]).toMatchObject({ sort: 'title', direction: 'asc', page: undefined })
    })

    it('flips the direction when the same column is clicked twice', async () => {
      const wrapper = mountDashboard()
      const titleHeader = wrapper.findAll('th button')[0]

      await titleHeader.trigger('click')
      await titleHeader.trigger('click')

      expect(vi.mocked(router.get).mock.calls[1][1]).toMatchObject({ sort: 'title', direction: 'desc' })
    })

    it('restarts at ascending when a different column is picked', async () => {
      const wrapper = mountDashboard({ sort: 'title', direction: 'asc' })

      await wrapper.findAll('th button')[1].trigger('click')

      expect(vi.mocked(router.get).mock.calls[0][1]).toMatchObject({ sort: 'spots', direction: 'asc' })
    })

    it('marks the sorted column with a direction icon and the others as sortable', async () => {
      const wrapper = mountDashboard()
      const sortIcon = wrapper.findComponent(OffersTable).props('sortIcon')

      expect(sortIcon('title')).toBe(sortIcon('spots'))

      await wrapper.findAll('th button')[0].trigger('click')

      expect(sortIcon('title')).not.toBe(sortIcon('spots'))
    })
  })

  describe('actions menu', () => {
    it('opens the menu of a single offer in both layouts', async () => {
      const wrapper = mountDashboard()

      await menusFor(wrapper, OffersTable)[0].find('button').trigger('click')

      expect(menusFor(wrapper, OffersTable).map((menu) => menu.props('isOpen'))).toEqual([true, false])
      expect(menusFor(wrapper, OffersCards).map((menu) => menu.props('isOpen'))).toEqual([true, false])
    })

    it('closes the menu when its own trigger is clicked again', async () => {
      const wrapper = mountDashboard()
      const trigger = menusFor(wrapper, OffersTable)[0].find('button')

      await trigger.trigger('click')
      await trigger.trigger('click')

      expect(menusFor(wrapper, OffersTable)[0].props('isOpen')).toBe(false)
    })

    it('moves the open menu when another offer is clicked', async () => {
      const wrapper = mountDashboard()

      await menusFor(wrapper, OffersTable)[0].find('button').trigger('click')
      await menusFor(wrapper, OffersTable)[1].find('button').trigger('click')

      expect(menusFor(wrapper, OffersTable).map((menu) => menu.props('isOpen'))).toEqual([false, true])
    })

    it('closes the menu on a click outside of it', async () => {
      const wrapper = mountDashboard({}, { attachTo: document.body })

      await menusFor(wrapper, OffersTable)[0].find('button').trigger('click')
      expect(menusFor(wrapper, OffersTable)[0].props('isOpen')).toBe(true)

      document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }))
      await wrapper.vm.$nextTick()

      expect(menusFor(wrapper, OffersTable)[0].props('isOpen')).toBe(false)

      wrapper.unmount()
    })

    it('keeps the menu open while clicking inside of it', async () => {
      const wrapper = mountDashboard({}, { attachTo: document.body })
      const menu = menusFor(wrapper, OffersTable)[0]

      await menu.find('button').trigger('click')
      await menu.findAll('button')[1].trigger('click')

      expect(router.visit).toHaveBeenCalled()

      wrapper.unmount()
    })

    it('stops listening for outside clicks after unmount', async () => {
      const wrapper = mountDashboard({}, { attachTo: document.body })
      const removeListener = vi.spyOn(document, 'removeEventListener')

      wrapper.unmount()

      expect(removeListener).toHaveBeenCalledWith('click', expect.any(Function))
    })
  })

  describe('offer actions', () => {
    async function openMenu(wrapper: ReturnType<typeof mountDashboard>, index = 0) {
      const menu = menusFor(wrapper, OffersTable)[index]
      await menu.find('button').trigger('click')
      return menu.findAll('button').slice(1)
    }

    it('navigates to the edit page and closes the menu', async () => {
      const wrapper = mountDashboard()
      const items = await openMenu(wrapper)

      await items[0].trigger('click')

      expect(router.visit).toHaveBeenCalledWith('/company/offers/o1/edit')
      expect(menusFor(wrapper, OffersTable)[0].props('isOpen')).toBe(false)
    })

    it('deactivates a published offer', async () => {
      const wrapper = mountDashboard()
      const items = await openMenu(wrapper)

      await items[1].trigger('click')

      expect(router.patch).toHaveBeenCalledWith('/company/offers/o1/deactivate', {}, { preserveScroll: true })
    })

    it('publishes a draft offer', async () => {
      const wrapper = mountDashboard()
      const items = await openMenu(wrapper, 1)

      await items[1].trigger('click')

      expect(router.patch).toHaveBeenCalledWith('/company/offers/o2/publish', {}, { preserveScroll: true })
    })

    it('does not change the status of a closed offer', async () => {
      const wrapper = mountDashboard({
        offers: { ...paginator, data: [{ ...offers[0], status: 'closed' }] },
      })
      const items = await openMenu(wrapper)

      await items[1].trigger('click')

      expect(router.patch).not.toHaveBeenCalled()
    })

    it('deletes the offer after the confirmation is accepted', async () => {
      const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true)
      const wrapper = mountDashboard()
      const items = await openMenu(wrapper)

      await items[2].trigger('click')

      expect(confirmSpy).toHaveBeenCalledWith('Are you sure you want to delete this offer?')
      expect(router.delete).toHaveBeenCalledWith('/company/offers/o1', { preserveScroll: true })
    })

    it('keeps the offer when the confirmation is dismissed', async () => {
      vi.spyOn(window, 'confirm').mockReturnValue(false)
      const wrapper = mountDashboard()
      const items = await openMenu(wrapper)

      await items[2].trigger('click')

      expect(router.delete).not.toHaveBeenCalled()
      expect(menusFor(wrapper, OffersTable)[0].props('isOpen')).toBe(false)
    })
  })

  describe('applications link', () => {
    it('visits the filtered applications list through inertia instead of a full reload', () => {
      const wrapper = mountDashboard()
      const link = wrapper.findComponent(OffersTable).findAll('tbody a')[1]
      const event = new MouseEvent('click', { bubbles: true, cancelable: true })

      link.element.dispatchEvent(event)

      expect(event.defaultPrevented).toBe(true)
      expect(router.visit).toHaveBeenCalledWith('/company/applications?offer=o1')
    })

    it('works the same from the mobile cards', () => {
      const wrapper = mountDashboard()
      const link = wrapper.findComponent(OffersCards).findAll('a')[3]

      link.element.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }))

      expect(router.visit).toHaveBeenCalledWith('/company/applications?offer=o2')
    })
  })
})
