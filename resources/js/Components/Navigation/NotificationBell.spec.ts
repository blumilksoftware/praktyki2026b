import { mount } from "@vue/test-utils"
import { describe, expect, it, vi, beforeEach, afterEach } from "vitest"
import NotificationBell from "@/Components/Navigation/NotificationBell.vue"
import { useToast } from "@/Composables/useToast"

interface NotificationItem {
  id: string
  type: string
  data: Record<string, string>
  read_at: string | null
  created_at: string
}

const { mockReload, mockPatch, pageProps } = vi.hoisted(() => {
  const { reactive } = require("vue")

  return {
    mockReload: vi.fn(),
    mockPatch: vi.fn(),
    pageProps: reactive({
      notificationsUnreadCount: 0,
      notifications: [] as NotificationItem[],
    }),
  }
})

vi.mock("@inertiajs/vue3", () => ({
  usePage: () => ({ props: pageProps }),
  router: { reload: mockReload, patch: mockPatch },
  Link: {
    props: ["href"],
    template: "<button :href=\"href\" @click=\"$emit('click')\"><slot /></button>",
  },
}))

const globalStubs = { IconBell: true }

let activeWrapper: ReturnType<typeof mount> | null = null

function mountBell() {
  activeWrapper = mount(NotificationBell, { global: { stubs: globalStubs } })
  return activeWrapper
}

describe("NotificationBell", () => {
  const { toastRef } = useToast()
  const toastShow = vi.fn()

  beforeEach(() => {
    mockReload.mockClear()
    mockPatch.mockClear()
    toastShow.mockClear()
    pageProps.notificationsUnreadCount = 0
    pageProps.notifications = []
    toastRef.value = { show: toastShow }
  })

  afterEach(() => {
    activeWrapper?.unmount()
    activeWrapper = null
  })

  it("does not show a badge when there are no unread notifications", () => {
    const wrapper = mountBell()

    expect(wrapper.find(".bg-secondary").exists()).toBe(false)
  })

  it("shows the unread count badge", () => {
    pageProps.notificationsUnreadCount = 3
    const wrapper = mountBell()

    expect(wrapper.find(".bg-secondary").text()).toBe("3")
  })

  it("caps the badge at 9+", () => {
    pageProps.notificationsUnreadCount = 42
    const wrapper = mountBell()

    expect(wrapper.find(".bg-secondary").text()).toBe("9+")
  })

  it("loads notifications via a partial reload when opened", async () => {
    const wrapper = mountBell()

    await wrapper.find("button").trigger("click")

    expect(mockReload).toHaveBeenCalledTimes(1)
    expect(mockReload.mock.calls[0][0]).toMatchObject({ only: ["notifications", "notificationsUnreadCount"] })
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
      {
        id: "n4",
        type: "partnership_requested",
        data: { proposer_name: "Politechnika Testowa" },
        read_at: null,
        created_at: "2026-07-04T10:00:00.000000Z",
      },
      {
        id: "n5",
        type: "partnership_accepted",
        data: { acceptor_name: "Acme Corp" },
        read_at: null,
        created_at: "2026-07-05T10:00:00.000000Z",
      },
      {
        id: "n6",
        type: "partnership_cancelled",
        data: { canceller_name: "Acme Corp" },
        read_at: null,
        created_at: "2026-07-06T10:00:00.000000Z",
      },
      {
        id: "n7",
        type: "partnership_declined",
        data: { decliner_name: "Politechnika Testowa" },
        read_at: null,
        created_at: "2026-07-07T10:00:00.000000Z",
      },
      {
        id: "n8",
        type: "partnership_ended",
        data: { ender_name: "Acme Corp" },
        read_at: null,
        created_at: "2026-07-08T10:00:00.000000Z",
      },
    ]
    const wrapper = mountBell()

    await wrapper.find("button").trigger("click")

    expect(wrapper.text()).toContain("New verification request from Acme Corp")
    expect(wrapper.text()).toContain("Jan Kowalski applied to Frontend Intern")
    expect(wrapper.text()).toContain("Your application to Backend Intern is now Accepted")
    expect(wrapper.text()).toContain("Politechnika Testowa proposed a partnership with you")
    expect(wrapper.text()).toContain("Acme Corp accepted your partnership proposal")
    expect(wrapper.text()).toContain("Acme Corp cancelled their partnership proposal")
    expect(wrapper.text()).toContain("Politechnika Testowa declined your partnership proposal")
    expect(wrapper.text()).toContain("Acme Corp ended the partnership with you")
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

  describe("polling for updates", () => {
    beforeEach(() => {
      vi.useFakeTimers()
    })

    afterEach(() => {
      vi.useRealTimers()
    })

    it("polls the unread count on an interval without navigating", () => {
      mountBell()

      vi.advanceTimersByTime(30000)

      expect(mockReload).toHaveBeenCalledTimes(1)
      expect(mockReload.mock.calls[0][0]).toMatchObject({ only: ["notificationsUnreadCount"] })
    })

    it("stops polling while the tab is hidden and resumes when visible again", () => {
      mountBell()

      Object.defineProperty(document, "hidden", { value: true, configurable: true })
      document.dispatchEvent(new Event("visibilitychange"))
      vi.advanceTimersByTime(60000)

      expect(mockReload).not.toHaveBeenCalled()

      Object.defineProperty(document, "hidden", { value: false, configurable: true })
      document.dispatchEvent(new Event("visibilitychange"))

      expect(mockReload).toHaveBeenCalledTimes(1)
    })

    it("shows a toast when the unread count increases", async () => {
      const wrapper = mountBell()

      pageProps.notificationsUnreadCount = 2
      await wrapper.vm.$nextTick()

      expect(toastShow).toHaveBeenCalledWith("You have 2 new notification(s).", 4000, "info")
    })

    it("does not show a toast when the unread count drops", async () => {
      pageProps.notificationsUnreadCount = 3
      const wrapper = mountBell()

      pageProps.notificationsUnreadCount = 1
      await wrapper.vm.$nextTick()

      expect(toastShow).not.toHaveBeenCalled()
    })
  })
})
