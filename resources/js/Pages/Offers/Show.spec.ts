import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createI18n } from 'vue-i18n'
import OfferShow from '@/Pages/Offers/Show.vue'
import en from '@/lang/en.json'
import { offerShow, universityShow } from '@/Helpers/routes'

const i18n = createI18n({ legacy: false, locale: 'en', messages: { en } })

const { routerPost, routerVisit, routerDelete } = vi.hoisted(() => ({
  routerPost: vi.fn(),
  routerVisit: vi.fn(),
  routerDelete: vi.fn(),
}))

vi.mock('@inertiajs/vue3', () => ({
  Head: { template: '<div />' },
  Link: {
    props: ['href'],
    template: '<a :href="href"><slot /></a>',
  },
  router: { post: routerPost, visit: routerVisit, delete: routerDelete },
}))

const baseOffer = {
  id: 'offer-1',
  title: 'Frontend Intern',
  description: 'Build UI for internships.',
  city: 'Wrocław',
  work_mode: 'remote',
  start_date: '2026-09-01',
  end_date: '2026-12-01',
  spots: 5,
  remaining_spots: 3,
  status: 'published',
  is_paid: true,
  salary_min: 4000,
  salary_max: 5000,
  study_fields: [{ id: 'sf-1', name: 'Computer Science' }],
  preferred_universities: [{ id: 'uni-1', name: 'Test University' }],
  has_applied: false,
  applied_at: null,
  is_favorite: false,
  company: {
    id: 'company-1',
    name: 'Acme Corp',
    logo_path: null,
    is_verified: true,
  },
}

function mountShow(props = {}) {
  return mount(OfferShow, {
    props: {
      offer: baseOffer,
      similarOffers: [],
      hasCv: true,
      isGuest: false,
      canApply: true,
      ...props,
    },
    global: {
      plugins: [i18n],
      stubs: {
        AppLayout: { template: '<div><slot /></div>' },
        VerifiedBadge: {
          props: ['verified'],
          template: '<span v-if="verified" data-testid="verified-badge" />',
        },
        SimilarOfferCard: {
          props: ['offer'],
          template: '<a :href="`/offers/${offer.id}`" data-testid="similar-offer">{{ offer.title }}</a>',
        },
        BaseApplyButton: {
          props: ['hasCv', 'isApplied', 'appliedDate', 'isLoading'],
          emits: ['apply'],
          template: `
            <button type="button" data-testid="apply-button" @click="$emit('apply')">
              <span v-if="isApplied">Applied {{ appliedDate }}</span>
              <span v-else>Apply</span>
            </button>
          `,
        },
        BaseButton: { template: '<button type="button"><slot /></button>' },
        WithdrawApplicationModal: true,
      },
    },
  })
}

describe('Offers/Show', () => {
  beforeEach(() => {
    routerPost.mockClear()
    routerVisit.mockClear()
    routerDelete.mockClear()
  })

  it('renders title, description and preferred universities', () => {
    const wrapper = mountShow()

    expect(wrapper.text()).toContain('Frontend Intern')
    expect(wrapper.text()).toContain('Build UI for internships.')
    expect(wrapper.text()).toContain('Test University')
    expect(wrapper.text()).toContain('Computer Science')
  })

  it('links to the profile of a preferred university only once it is verified', () => {
    expect(mountShow().find(`a[href="${universityShow('uni-1')}"]`).exists()).toBe(false)

    const wrapper = mountShow({
      offer: {
        ...baseOffer,
        preferred_universities: [{ id: 'uni-1', name: 'Test University', is_verified: true }],
      },
    })
    const link = wrapper.find(`a[href="${universityShow('uni-1')}"]`)

    expect(link.exists()).toBe(true)
    expect(link.text()).toBe('Test University')
  })

  it('shows readable internship period, available spots and salary range', () => {
    const wrapper = mountShow()
    const text = wrapper.text()

    expect(text).toContain('Internship period')
    expect(text).toContain('from 01.09.2026 to 01.12.2026')
    expect(text).toContain('Available spots')
    expect(text).toContain('3 of 5 still open')
    expect(text).toContain('from 4000 to 5000 PLN')
    expect(text).not.toContain('Paid —')
  })

  it('shows a clear message when no spots are left', () => {
    const wrapper = mountShow({
      offer: { ...baseOffer, remaining_spots: 0, spots: 8 },
    })

    expect(wrapper.text()).toContain('No spots left (8 filled)')
  })

  it('links verified company to public profile and shows badge', () => {
    const wrapper = mountShow()
    const companyLink = wrapper.find(`a[href="/companies/company-1"]`)

    expect(companyLink.exists()).toBe(true)
    expect(companyLink.text()).toContain('Acme Corp')
    expect(wrapper.find('[data-testid="verified-badge"]').exists()).toBe(true)
  })

  it('renders unverified company name without profile link', () => {
    const wrapper = mountShow({
      offer: {
        ...baseOffer,
        company: { ...baseOffer.company, is_verified: false },
      },
    })

    expect(wrapper.find(`a[href="/companies/company-1"]`).exists()).toBe(false)
    expect(wrapper.text()).toContain('Acme Corp')
    expect(wrapper.find('[data-testid="verified-badge"]').exists()).toBe(false)
  })

  it('shows closed message and hides apply for closed offers', () => {
    const wrapper = mountShow({
      offer: { ...baseOffer, status: 'closed' },
    })

    expect(wrapper.text()).toContain('No longer accepting applications')
    expect(wrapper.find('[data-testid="apply-button"]').exists()).toBe(false)
  })

  it('shows apply and favourite for published student view', () => {
    const wrapper = mountShow()

    expect(wrapper.find('[data-testid="apply-button"]').exists()).toBe(true)
    expect(wrapper.find('button[aria-pressed]').exists()).toBe(true)
  })

  it('shows login CTA for guests', () => {
    const wrapper = mountShow({ isGuest: true, canApply: false })

    expect(wrapper.text()).toContain('Log in to apply')
    expect(wrapper.find('[data-testid="apply-button"]').exists()).toBe(false)
    expect(wrapper.find('button[aria-pressed]').exists()).toBe(false)
  })

  it('hides apply, favourite and login CTA from signed in users who cannot apply', () => {
    const wrapper = mountShow({ canApply: false })

    expect(wrapper.text()).not.toContain('Log in to apply')
    expect(wrapper.find('[data-testid="apply-button"]').exists()).toBe(false)
    expect(wrapper.find('button[aria-pressed]').exists()).toBe(false)
  })

  it('shows applied date when already applied', () => {
    const wrapper = mountShow({
      offer: {
        ...baseOffer,
        has_applied: true,
        applied_at: '2026-08-01',
      },
    })

    expect(wrapper.text()).toContain('Applied 2026-08-01')
  })

  it('renders similar offers sidebar when related offers are provided', () => {
    const wrapper = mountShow({
      similarOffers: [
        {
          id: 'similar-1',
          title: 'Related Backend Intern',
          city: 'Wrocław',
          work_mode: 'remote',
          company: {
            id: 'company-2',
            name: 'Other Corp',
            logo_path: null,
            is_verified: false,
          },
        },
      ],
    })

    expect(wrapper.text()).toContain('Similar offers')
    expect(wrapper.find('[data-testid="similar-offer"]').text()).toContain('Related Backend Intern')
  })

  it('hides similar offers section when the list is empty', () => {
    const wrapper = mountShow({ similarOffers: [] })

    expect(wrapper.text()).not.toContain('Similar offers')
  })
})

describe('offerShow helper', () => {
  it('builds detail url', () => {
    expect(offerShow('abc-123')).toBe('/offers/abc-123')
  })
})
