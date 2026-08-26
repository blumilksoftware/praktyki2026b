import { mount } from '@vue/test-utils'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { router } from '@inertiajs/vue3'
import Pagination from '@/Components/Common/Pagination.vue'

vi.mock('@inertiajs/vue3', () => ({
  router: { visit: vi.fn() },
}))

function buildLinks(lastPage, currentPage) {
  const links = [
    { url: currentPage > 1 ? `/items?page=${currentPage - 1}` : null, label: '&laquo; Previous', active: false },
  ]

  for (let page = 1; page <= lastPage; page++) {
    links.push({ url: `/items?page=${page}`, label: `${page}`, active: page === currentPage })
  }

  links.push({ url: currentPage < lastPage ? `/items?page=${currentPage + 1}` : null, label: 'Next &raquo;', active: false })

  return links
}

function buildMeta(lastPage, currentPage) {
  return {
    current_page: currentPage,
    last_page: lastPage,
    links: buildLinks(lastPage, currentPage),
  }
}

function mountPagination(lastPage, currentPage) {
  return mount(Pagination, {
    props: { meta: buildMeta(lastPage, currentPage) },
  })
}

function desktopButtons(wrapper) {
  return wrapper.find('div.hidden.sm\\:flex').findAll('button')
}

function mobileButtons(wrapper) {
  return wrapper.find('div.sm\\:hidden').findAll('button')
}

function desktopItemLabels(wrapper) {
  return wrapper.find('div.hidden.sm\\:flex').findAll('button, span').map(item => item.text())
}

function mobileItemLabels(wrapper) {
  return wrapper.find('div.sm\\:hidden').findAll('button, span').map(item => item.text())
}

describe('Pagination', () => {
  beforeEach(() => {
    vi.mocked(router.visit).mockClear()
  })

  it('renders nothing when there is only one page', () => {
    const wrapper = mountPagination(1, 1)

    expect(wrapper.find('button').exists()).toBe(false)
  })

  it('decodes html entities in the previous/next labels', () => {
    const wrapper = mountPagination(3, 2)
    const buttons = desktopButtons(wrapper)

    expect(buttons[0].text()).toBe('« Previous')
    expect(buttons[buttons.length - 1].text()).toBe('Next »')
  })

  it('shows every page when the total fits without collapsing', () => {
    const wrapper = mountPagination(5, 3)
    const labels = desktopButtons(wrapper).map(button => button.text())

    expect(labels).toEqual(['« Previous', '1', '2', '3', '4', '5', 'Next »'])
  })

  it('collapses the desktop window with an ellipsis around the current page', () => {
    const wrapper = mountPagination(20, 10)

    expect(desktopItemLabels(wrapper)).toEqual(['« Previous', '1', '…', '8', '9', '10', '11', '12', '…', '20', 'Next »'])
  })

  it('uses a narrower window on small screens than on desktop', () => {
    const wrapper = mountPagination(20, 10)

    expect(mobileItemLabels(wrapper)).toEqual(['« Previous', '1', '…', '9', '10', '11', '…', '20', 'Next »'])
  })

  it('highlights the active page and disables links without a url', () => {
    const wrapper = mountPagination(3, 1)
    const buttons = desktopButtons(wrapper)

    expect(buttons[0].attributes('disabled')).toBeDefined()
    expect(buttons[1].classes()).toContain('bg-primary')
    expect(buttons[2].attributes('disabled')).toBeUndefined()
  })

  it('navigates to the clicked page while preserving state and scroll', async () => {
    const wrapper = mountPagination(3, 1)

    await desktopButtons(wrapper)[2].trigger('click')

    expect(router.visit).toHaveBeenCalledWith('/items?page=2', {
      preserveState: true,
      preserveScroll: true,
    })
  })

  it('does not navigate when the link has no url', async () => {
    const wrapper = mountPagination(3, 1)

    await desktopButtons(wrapper)[0].trigger('click')

    expect(router.visit).not.toHaveBeenCalled()
  })
})
