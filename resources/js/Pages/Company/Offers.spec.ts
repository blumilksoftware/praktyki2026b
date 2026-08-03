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

  it('links the edit action to the offer edit route', () => {
    const wrapper = mountOffers({ offers: [publishedOffer] })

    const editLink = wrapper.findAll('a').find((a) => a.text() === en.company.offers.index.editAction)
    expect(editLink!.attributes('href')).toBe('/company/offers/2/edit')
  })

  it('shows a publish button for a verified company\'s draft offer and publishes it', async () => {
    routerPatch.mockClear()
    const wrapper = mountOffers({ offers: [draftOffer], isCompanyVerified: true })

    const publishButton = wrapper.findAll('button').find((btn) => btn.text() === en.company.offers.index.publishAction)
    expect(publishButton).toBeTruthy()

    await publishButton!.trigger('click')

    expect(routerPatch).toHaveBeenCalledTimes(1)
    expect(routerPatch.mock.calls[0][0]).toBe('/company/offers/1/publish')
  })

  it('hides the publish button and shows a verification hint for an unverified company\'s draft offer', () => {
    const wrapper = mountOffers({ offers: [draftOffer], isCompanyVerified: false })

    expect(wrapper.findAll('button').some((btn) => btn.text() === en.company.offers.index.publishAction)).toBe(false)
    expect(wrapper.text()).toContain(en.company.offers.index.verificationRequiredHint)
  })

  it('shows an unpublish button only for published offers and opens the confirmation modal with the offer details', async () => {
    const wrapper = mountOffers({ offers: [draftOffer, publishedOffer], isCompanyVerified: true })

    const unpublishButtons = wrapper.findAll('button').filter((btn) => btn.text() === en.company.offers.index.unpublishAction)
    expect(unpublishButtons).toHaveLength(1)

    await unpublishButtons[0].trigger('click')

    const modal = wrapper.find('.stub-unpublish-modal')
    expect(modal.exists()).toBe(true)
    expect(modal.text()).toBe('2:Frontend Internship')
  })

  it('opens the delete confirmation modal with the offer details', async () => {
    const wrapper = mountOffers({ offers: [publishedOffer], isCompanyVerified: true })

    const deleteButton = wrapper.findAll('button').find((btn) => btn.text() === en.company.offers.index.deleteAction)
    await deleteButton!.trigger('click')

    const modal = wrapper.find('.stub-delete-modal')
    expect(modal.exists()).toBe(true)
    expect(modal.text()).toBe('2:Frontend Internship')
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
