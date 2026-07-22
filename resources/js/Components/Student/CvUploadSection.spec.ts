import { mount } from "@vue/test-utils"
import type { VueWrapper } from "@vue/test-utils"
import { nextTick } from "vue"
import { beforeEach, describe, expect, it, vi } from "vitest"

const mocks = vi.hoisted(() => ({
  post: vi.fn(),
  deleteRequest: vi.fn(),
  reset: vi.fn(),
  reload: vi.fn(),
}))

import CvUploadSection from "@/Components/Student/CvUploadSection.vue"

const mockFormState = {
  errors: {} as Record<string, string>,
  processing: false,
}

vi.mock("@inertiajs/vue3", async () => {
  const actual = await vi.importActual<typeof import("@inertiajs/vue3")>("@inertiajs/vue3")

  return {
    ...actual,
    useForm: () => ({
      cv: null,
      get errors() {
        return mockFormState.errors
      },
      get processing() {
        return mockFormState.processing
      },
      post: mocks.post,
      delete: mocks.deleteRequest,
      reset: mocks.reset,
    }),
    router: {
      reload: mocks.reload,
    },
  }
})

function mountComponent(props: Record<string, unknown> = {}) {
  return mount(CvUploadSection, {
    props: {
      cvPath: null,
      ...props,
    },
  })
}

async function selectFile(wrapper: VueWrapper, file: File) {
  const input = wrapper.find('input[type="file"]')

  Object.defineProperty(input.element, "files", {
    value: [file],
    configurable: true,
  })

  await input.trigger("change")
}

describe("CvUploadSection", () => {
  beforeEach(() => {
    Object.defineProperty(URL, "createObjectURL", {
      value: vi.fn(() => "blob:cv-preview"),
      configurable: true,
    })
    Object.defineProperty(URL, "revokeObjectURL", {
      value: vi.fn(),
      configurable: true,
    })
    document.body.innerHTML = ""
    mockFormState.errors = {}
    mockFormState.processing = false
    mocks.post.mockReset()
    mocks.deleteRequest.mockReset()
    mocks.reset.mockReset()
    mocks.reload.mockReset()
  })

  it("shows accepted format and size limit before file selection", () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain("Accepted format: PDF")
    expect(wrapper.text()).toContain("Maximum size: 5 MB")
    expect(wrapper.text()).toContain("Choose PDF file")
  })

  it("shows filename and replace option after upload", async () => {
    mocks.post.mockImplementation((_url: string, options: { onSuccess: () => void }) => options.onSuccess())
    const wrapper = mountComponent()
    const file = new File(["cv"], "Jan_Kowalski_CV.pdf", { type: "application/pdf" })

    await selectFile(wrapper, file)
    await nextTick()

    expect(mocks.post).toHaveBeenCalledOnce()
    expect(wrapper.text()).toContain("Jan_Kowalski_CV.pdf")
    expect(wrapper.text()).toContain("Preview")
    expect(wrapper.text()).toContain("Replace")
    expect(mocks.reload).toHaveBeenCalledWith({
      only: ["user", "auth", "onboarding"],
      preserveScroll: true,
      preserveState: true,
    })
  })

  it("uploads a dropped PDF instead of letting the browser open it", async () => {
    mocks.post.mockImplementation((_url: string, options: { onSuccess: () => void }) => options.onSuccess())
    const wrapper = mountComponent()
    const file = new File(["cv"], "Dropped_CV.pdf", { type: "application/pdf" })

    await wrapper.findAll("button").find(button => button.text().includes("Choose PDF file"))?.trigger("drop", {
      dataTransfer: { files: [file] },
    })
    await nextTick()

    expect(mocks.post).toHaveBeenCalledOnce()
    expect(wrapper.text()).toContain("Dropped_CV.pdf")
  })

  it("opens uploaded CV preview in a new tab", async () => {
    mocks.post.mockImplementation((_url: string, options: { onSuccess: () => void }) => options.onSuccess())
    const wrapper = mountComponent()
    const file = new File(["cv"], "Jan_Kowalski_CV.pdf", { type: "application/pdf" })

    await selectFile(wrapper, file)
    await nextTick()

    const previewLink = wrapper.findAll("a").find(link => link.text().includes("Preview"))

    expect(previewLink?.attributes("target")).toBe("_blank")
    expect(previewLink?.attributes("href")).toBe("blob:cv-preview")
    expect(previewLink?.attributes("rel")).toBe("noopener noreferrer")
  })

  it("links existing server CV preview in a new tab", () => {
    const wrapper = mountComponent({ cvPath: "cvs/existing.pdf" })
    const previewLink = wrapper.findAll("a").find(link => link.text().includes("Preview"))

    expect(previewLink?.attributes("target")).toBe("_blank")
    expect(previewLink?.attributes("href")).toBe("/student/cv")
  })

  it("shows upload progress while processing", () => {
    mockFormState.processing = true
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain("Uploading")
    expect(wrapper.find('[role="progressbar"]').exists()).toBe(true)
  })

  it("rejects non-pdf files before upload", async () => {
    const wrapper = mountComponent()
    const file = new File(["image"], "avatar.png", { type: "image/png" })

    await selectFile(wrapper, file)

    expect(mocks.post).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain("The file must be a PDF.")
  })

  it("rejects files larger than 5 MB before upload", async () => {
    const wrapper = mountComponent()
    const file = new File([new ArrayBuffer((5 * 1024 * 1024) + 1)], "large.pdf", { type: "application/pdf" })

    await selectFile(wrapper, file)

    expect(mocks.post).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain("The file must not be larger than 5 MB.")
  })

  it("shows server-side error", () => {
    mockFormState.errors = { cv: "The cv field must be a file of type: pdf." }
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain("The cv field must be a file of type: pdf.")
  })

  it("sends delete request when delete action is clicked", async () => {
    mocks.deleteRequest.mockImplementation((_url: string, options: { onSuccess: () => void }) => options.onSuccess())
    const wrapper = mountComponent({ cvPath: "cvs/existing.pdf" })
    const deleteButton = wrapper.findAll("button").find(button => button.text() === "Delete file")

    await deleteButton?.trigger("click")
    await nextTick()

    expect(mocks.deleteRequest).toHaveBeenCalledOnce()
    expect(mocks.reload).toHaveBeenCalledWith({
      only: ["user", "auth", "onboarding"],
      preserveScroll: true,
      preserveState: true,
    })
    expect(wrapper.text()).not.toContain("CV.pdf")
  })
})
