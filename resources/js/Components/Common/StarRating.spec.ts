import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import StarRating from '@/Components/Common/StarRating.vue'

describe('StarRating', () => {
  it('renders five stars with the correct label', () => {
    const wrapper = mount(StarRating, { props: { rating: 3 } })

    expect(wrapper.attributes('aria-label')).toBe('3 / 5')
    expect(wrapper.findAll('svg').length).toBe(5)
  })

  it('renders spans and does not emit when not interactive', async () => {
    const wrapper = mount(StarRating, { props: { rating: 2 } })

    expect(wrapper.findAll('button').length).toBe(0)
  })

  it('renders buttons and emits update:rating when interactive', async () => {
    const wrapper = mount(StarRating, { props: { rating: 0, interactive: true } })

    const buttons = wrapper.findAll('button')
    expect(buttons.length).toBe(5)

    await buttons[3].trigger('click')

    expect(wrapper.emitted('update:rating')?.[0]).toEqual([4])
  })
})
