import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import CompanyCard from '@/Components/University/CompanyCard.vue'

const stubs = {
  PartnerCard: {
    props: ['partner', 'namespace', 'actionUrl', 'acceptUrl'],
    template: '<div class="stub-partner-card" />',
  },
}

const company = {
  id: 'abc-123',
  name: 'Acme Corp',
  city: 'Wrocław',
  partnership_status: 'none',
}

describe('CompanyCard.vue', () => {
  it('passes the company as the partner, with the university namespace and routes', () => {
    const wrapper = mount(CompanyCard, { props: { company }, global: { stubs } })

    const stub = wrapper.findComponent(stubs.PartnerCard)
    expect(stub.props('partner')).toEqual(company)
    expect(stub.props('namespace')).toBe('university.companies')
    expect(stub.props('actionUrl')).toBe('/university/companies/abc-123/partnership')
    expect(stub.props('acceptUrl')).toBe('/university/companies/abc-123/partnership/accept')
  })
})
