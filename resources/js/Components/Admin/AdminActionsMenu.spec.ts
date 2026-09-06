import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import AdminActionsMenu from '@/Components/Admin/AdminActionsMenu.vue'

function mountMenu() {
  return mount(AdminActionsMenu, {
    props: { label: 'Actions for John' },
    slots: { default: '<button type="button" role="menuitem">Do thing</button>' },
    attachTo: document.body,
  })
}

describe('AdminActionsMenu', () => {
  it('renders only the trigger while closed', () => {
    const wrapper = mountMenu()

    expect(wrapper.findAll('button')).toHaveLength(1)
    expect(wrapper.find('button').attributes('aria-label')).toBe('Actions for John')
    expect(wrapper.find('button').attributes('aria-expanded')).toBe('false')
  })

  it('opens the menu when the trigger is clicked', async () => {
    const wrapper = mountMenu()

    await wrapper.find('button').trigger('click')

    expect(wrapper.find('button').attributes('aria-expanded')).toBe('true')
    expect(wrapper.findAll('button')).toHaveLength(2)
    expect(wrapper.text()).toContain('Do thing')
  })

  it('closes the menu when clicking outside', async () => {
    const wrapper = mountMenu()

    await wrapper.find('button').trigger('click')
    expect(wrapper.findAll('button')).toHaveLength(2)

    document.body.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }))
    await wrapper.vm.$nextTick()

    expect(wrapper.findAll('button')).toHaveLength(1)
    wrapper.unmount()
  })

  it('closes the menu on escape', async () => {
    const wrapper = mountMenu()

    await wrapper.find('button').trigger('click')
    expect(wrapper.findAll('button')).toHaveLength(2)

    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }))
    await wrapper.vm.$nextTick()

    expect(wrapper.findAll('button')).toHaveLength(1)
    wrapper.unmount()
  })

  it('closes the menu when a slotted action is clicked', async () => {
    const wrapper = mountMenu()

    await wrapper.find('button').trigger('click')
    await wrapper.findAll('button')[1].trigger('click')

    expect(wrapper.findAll('button')).toHaveLength(1)
    wrapper.unmount()
  })
})
