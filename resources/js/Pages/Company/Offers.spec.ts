import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { describe, expect, it, vi } from 'vitest'
import Offers from '@/Pages/Company/Offers.vue'
import en from '@/lang/en.json'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const { routerPatch } = vi.hoisted(() => ({ routerPatch: vi.fn() }))

vi.mock('@inertiajs/vue3', async () => {
  const actual = await vi.importActual('@inertiajs/vue3')
  return {
    ...actual,
    Head: { template: '<div />' },
    router: { patch: routerPatch },
  }
})

const draftOffer = {
  id: '1',
  title: 'Backend Internship',
  status: 'draft',
  spots: 5,
  applications_count: 2,
}

const publishedOffer = {
  id: '2',
  title: 'Frontend Internship',
  status: 'published',
  spots: 3,
  applications_count: 7,
}

const mountOffers = (props = {}) => mount(Offers, {
  props: { offers: [], isCompanyVerified: true, ...props },
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
    },
  },
})

describe('Company/Offers', () => {
  it('shows the empty state when there are no offers', () => {
    const wrapper = mountOffers()

    expect(wrapper.text()).toContain(en.company.offers.index.empty.title)
  })

  it('renders offer title, status, spots and application count', () => {
    const wrapper = mountOffers({ offers: [publishedOffer] })
    const text = wrapper.text()

    expect(text).toContain('Frontend Internship')
    expect(text).toContain(en.company.offers.index.status.published)
    expect(text).toContain('3')
    expect(text).toContain('7')
  })

  const openActionsMenu = async (wrapper: ReturnType<typeof mountOffers>, offerId: string) => {
    const row = wrapper.find(`[data-offer-menu="${offerId}"]`)
    await row.find('button').trigger('click')
    return row
  }

  it('links the edit action to the offer edit route', async () => {
    const wrapper = mountOffers({ offers: [publishedOffer] })

    const row = await openActionsMenu(wrapper, '2')
    const editLink = row.findAll('a').find((a) => a.text() === en.company.offers.index.editAction)
    expect(editLink!.attributes('href')).toBe('/company/offers/2/edit')
  })

  it('shows a publish action for a verified company\'s draft offer and publishes it', async () => {
    routerPatch.mockClear()
    const wrapper = mountOffers({ offers: [draftOffer], isCompanyVerified: true })

    const row = await openActionsMenu(wrapper, '1')
    const publishButton = row.findAll('button').find((btn) => btn.text() === en.company.offers.index.publishAction)
    expect(publishButton).toBeTruthy()

    await publishButton!.trigger('click')

    expect(routerPatch).toHaveBeenCalledTimes(1)
    expect(routerPatch.mock.calls[0][0]).toBe('/company/offers/1/publish')
  })

  it('hides the publish action and shows a verification hint for an unverified company\'s draft offer', async () => {
    const wrapper = mountOffers({ offers: [draftOffer], isCompanyVerified: false })

    const row = await openActionsMenu(wrapper, '1')
    expect(row.findAll('button').some((btn) => btn.text() === en.company.offers.index.publishAction)).toBe(false)
    expect(wrapper.text()).toContain(en.company.offers.index.verificationRequiredHint)
  })

  it('shows an unpublish action only for published offers and opens the confirmation modal with the offer details', async () => {
    const wrapper = mountOffers({ offers: [draftOffer, publishedOffer], isCompanyVerified: true })

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
    const wrapper = mountOffers({ offers: [publishedOffer], isCompanyVerified: true })

    const row = await openActionsMenu(wrapper, '2')
    const deleteButton = row.findAll('button').find((btn) => btn.text() === en.company.offers.index.deleteAction)
    await deleteButton!.trigger('click')

    const modal = wrapper.find('.stub-delete-modal')
    expect(modal.exists()).toBe(true)
    expect(modal.text()).toBe('2:Frontend Internship')
  })

  it('always offers the delete action regardless of offer status', async () => {
    const closedOffer = { ...publishedOffer, id: '3', status: 'closed' }
    const wrapper = mountOffers({ offers: [closedOffer], isCompanyVerified: true })

    const row = await openActionsMenu(wrapper, '3')
    expect(row.findAll('button').some((btn) => btn.text() === en.company.offers.index.deleteAction)).toBe(true)
  })

  it('closes the actions menu when clicking outside of it', async () => {
    const wrapper = mountOffers({ offers: [publishedOffer], isCompanyVerified: true })

    const row = await openActionsMenu(wrapper, '2')
    expect(row.find('[role="menu"]').exists()).toBe(true)

    document.body.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }))
    await wrapper.vm.$nextTick()

    expect(wrapper.find('[role="menu"]').exists()).toBe(false)
  })

  it('filters the offer list by search query', async () => {
    const wrapper = mountOffers({ offers: [draftOffer, publishedOffer], isCompanyVerified: true })

    await wrapper.find('input[type="search"]').setValue('front')

    expect(wrapper.text()).toContain('Frontend Internship')
    expect(wrapper.text()).not.toContain('Backend Internship')
  })

  it('shows a tab per status with counts and filters when a tab is selected', async () => {
    const wrapper = mountOffers({ offers: [draftOffer, publishedOffer], isCompanyVerified: true })

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

    expect(wrapper.text()).toContain('Frontend Internship')
    expect(wrapper.text()).not.toContain('Backend Internship')
  })

  it('shows a distinct no-results state when filters exclude every offer', async () => {
    const wrapper = mountOffers({ offers: [publishedOffer], isCompanyVerified: true })

    await wrapper.find('input[type="search"]').setValue('nonexistent offer')

    expect(wrapper.text()).toContain(en.company.offers.index.noResults.title)
    expect(wrapper.text()).not.toContain(en.company.offers.index.empty.title)
  })
})
