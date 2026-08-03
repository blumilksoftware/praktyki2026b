import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import DateRangeField from '@/Components/Offer/DateRangeField.vue'

describe('DateRangeField', () => {
  it('sets min/max attributes based on the other date', () => {
    const wrapper = mount(DateRangeField, {
      props: {
        startId: 'start_date',
        endId: 'end_date',
        startLabel: 'Start date',
        endLabel: 'End date',
        start: '2026-08-01',
        end: '2026-09-30',
      },
    })

    expect(wrapper.find('#start_date').attributes('max')).toBe('2026-09-30')
    expect(wrapper.find('#end_date').attributes('min')).toBe('2026-08-01')
  })

  it('pushes the end date forward when start date moves past it', async () => {
    const wrapper = mount(DateRangeField, {
      props: {
        startId: 'start_date',
        endId: 'end_date',
        startLabel: 'Start date',
        endLabel: 'End date',
        start: '2026-08-01',
        end: '2026-08-15',
        'onUpdate:end': (value: string) => wrapper.setProps({ end: value }),
      },
    })

    await wrapper.find('#start_date').setValue('2026-09-01')

    expect(wrapper.emitted('update:end')?.at(-1)).toEqual(['2026-09-01'])
  })
})
