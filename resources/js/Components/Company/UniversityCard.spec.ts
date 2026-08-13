import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import UniversityCard from '@/Components/Company/UniversityCard.vue'

const stubs = {
  PartnerCard: {
    props: ['partner', 'namespace', 'actionUrl', 'acceptUrl'],
    template: '<div class="stub-partner-card" />',
  },
}

const university = {
  id: 'uni-1',
  name: 'Politechnika Testowa',
  city: 'Legnica',
  partnership_status: 'none',
}

describe('UniversityCard.vue', () => {
  it('passes the university as the partner, with the company namespace and routes', () => {
    const wrapper = mount(UniversityCard, { props: { university }, global: { stubs } })

    const stub = wrapper.findComponent(stubs.PartnerCard)
    expect(stub.props('partner')).toEqual(university)
    expect(stub.props('namespace')).toBe('company.universities')
    expect(stub.props('actionUrl')).toBe('/company/universities/uni-1/partnership')
    expect(stub.props('acceptUrl')).toBe('/company/universities/uni-1/partnership/accept')
  })
})
