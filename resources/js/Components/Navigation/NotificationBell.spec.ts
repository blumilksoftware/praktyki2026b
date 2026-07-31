import { mount } from "@vue/test-utils"
import { describe, expect, it, vi, beforeEach } from "vitest"
import NotificationBell from "@/Components/Navigation/NotificationBell.vue"

interface NotificationItem {
  id: string
  type: string
  data: Record<string, string>
  read_at: string | null
  created_at: string
}

const { mockReload, mockPatch, pageProps } = vi.hoisted(() => ({
  mockReload: vi.fn(),
  mockPatch: vi.fn(),
  pageProps: {
    notificationsUnreadCount: 0,
    notifications: [] as NotificationItem[],
  },
}))

vi.mock("@inertiajs/vue3", () => ({
  usePage: () => ({ props: pageProps }),
  router: { reload: mockReload, patch: mockPatch },
  Link: {
    props: ["href"],
    template: "<button :href=\"href\" @click=\"$emit('click')\"><slot /></button>",
  },
}))

const globalStubs = { IconBell: true }

function mountBell() {
  return mount(NotificationBell, { global: { stubs: globalStubs } })
}

describe("NotificationBell", () => {
  beforeEach(() => {
    mockReload.mockClear()
    mockPatch.mockClear()
    pageProps.notificationsUnreadCount = 0
    pageProps.notifications = []
  })

  it("does not show a badge when there are no unread notifications", () => {
    const wrapper = mountBell()

    expect(wrapper.find(".bg-error").exists()).toBe(false)
  })

  it("shows the unread count badge", () => {
    pageProps.notificationsUnreadCount = 3
    const wrapper = mountBell()

    expect(wrapper.find(".bg-error").text()).toBe("3")
  })

  it("caps the badge at 9+", () => {
    pageProps.notificationsUnreadCount = 42
    const wrapper = mountBell()

    expect(wrapper.find(".bg-error").text()).toBe("9+")
  })

  it("loads notifications via a partial reload when opened", async () => {
    const wrapper = mountBell()

    await wrapper.find("button").trigger("click")

    expect(mockReload).toHaveBeenCalledTimes(1)
    expect(mockReload.mock.calls[0][0]).toMatchObject({ only: ["notifications"] })
  })

  it("shows an empty state when there are no notifications", async () => {
    const wrapper = mountBell()

    await wrapper.find("button").trigger("click")

    expect(wrapper.text()).toContain("You have no notifications yet.")
  })

  it("renders a label for each notification type", async () => {
    pageProps.notifications = [
      {
        id: "n1",
        type: "verification_request",
        data: { entity_name: "Acme Corp" },
        read_at: null,
        created_at: "2026-07-01T10:00:00.000000Z",
      },
      {
        id: "n2",
        type: "new_application",
        data: { student_name: "Jan Kowalski", offer_title: "Frontend Intern" },
        read_at: null,
        created_at: "2026-07-02T10:00:00.000000Z",
      },
      {
        id: "n3",
        type: "application_status_changed",
        data: { offer_title: "Backend Intern", status: "accepted" },
        read_at: "2026-07-03T10:00:00.000000Z",
        created_at: "2026-07-03T09:00:00.000000Z",
      },
    ]
    const wrapper = mountBell()

    await wrapper.find("button").trigger("click")

    expect(wrapper.text()).toContain("New verification request from Acme Corp")
    expect(wrapper.text()).toContain("Jan Kowalski applied to Frontend Intern")
    expect(wrapper.text()).toContain("Your application to Backend Intern is now Accepted")
  })

  it("calls mark-all-as-read when the button is clicked", async () => {
    pageProps.notificationsUnreadCount = 2
    const wrapper = mountBell()

    await wrapper.find("button").trigger("click")
    const markAllButton = wrapper.findAll("button").find((button) => button.text().includes("Mark all as read"))
    await markAllButton!.trigger("click")

    expect(mockPatch).toHaveBeenCalledTimes(1)
    expect(mockPatch.mock.calls[0][0]).toBe("/notifications/read-all")
  })

  it("links each notification to its mark-as-read endpoint", async () => {
    pageProps.notifications = [
      {
        id: "n1",
        type: "verification_request",
        data: { entity_name: "Acme Corp" },
        read_at: null,
        created_at: "2026-07-01T10:00:00.000000Z",
      },
    ]
    const wrapper = mountBell()

    await wrapper.find("button").trigger("click")

    const notificationLink = wrapper.findAll("button").find((button) => button.attributes("href") === "/notifications/n1/read")
    expect(notificationLink).toBeTruthy()
  })
})