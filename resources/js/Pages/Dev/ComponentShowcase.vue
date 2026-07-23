<script setup>
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseApplyButton from '@/Components/Base/BaseApplyButton.vue'
import BaseCheckbox from '@/Components/Base/BaseCheckbox.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import BaseLogo from '@/Components/Navigation/BaseLogo.vue'
import LanguageSwitcher from '@/Components/Navigation/LanguageSwitcher.vue'
import DynamicMultiSelect from '@/Components/Common/DynamicMultiSelect.vue'
import Menu from '@/Components/Profiles/Menu.vue'
import { IconSearch, IconClipboardText, IconUserCircle, IconUsersGroup } from '@tabler/icons-vue'
import { useI18n } from 'vue-i18n'
import { ROUTES } from '@/Helpers/routes'

const { t } = useI18n()

const email = ref('student@example.com')
const password = ref('secret')
const inputWithError = ref('')
const terms = ref(false)

const companyMenu = computed(() => [
  { label: t('profiles.company.myOffers'), href: ROUTES.OFFERS, icon: IconSearch },
  { label: t('profiles.company.candidateApplications'), href: ROUTES.APPLICATIONS, icon: IconClipboardText },
  { label: t('profiles.profile'), href: ROUTES.PROFILE, icon: IconUserCircle, isActive: true },
  { label: t('profiles.company.teamAndPermissions'), href: ROUTES.TEAM, icon: IconUsersGroup },
])

const selectedTags = ref(['Vue'])
const availableTags = ref([
  'JavaScript',
  'TypeScript',
  'Vue',
  'React',
  'Angular',
  'Svelte',
  'Node.js',
  'Laravel',
  'PHP',
  'Python',
  'Django',
  'Ruby',
  'Rails',
  'Java',
  'Spring Boot',
  'C#',
  '.NET',
  'Go',
  'Rust',
  'Kotlin',
  'Swift',
  'Flutter',
  'Dart',
  'React Native',
  'Docker',
  'Kubernetes',
  'AWS',
  'Azure',
  'Google Cloud',
  'Firebase',
  'PostgreSQL',
  'MySQL',
  'MongoDB',
  'Redis',
  'GraphQL',
  'REST API',
  'Git',
  'GitHub',
  'CI/CD',
  'DevOps',
  'Linux',
  'UI/UX',
  'Tailwind CSS',
  'Bootstrap',
  'Figma',
  'Testing',
  'Jest',
  'Cypress',
  'Machine Learning',
  'Artificial Intelligence',
])

const currentDate = new Date().toISOString()
</script>

<template>
  <Head title="Components" />
  <div class="min-h-screen bg-background px-4 py-10">
    <div class="mx-auto w-full max-w-4xl flex flex-col gap-10">
      <header class="text-center">
        <h1 class="text-3xl md:text-4xl font-normal text-text">
          Components preview
        </h1>
        <p class="mt-3 text-sm text-additional">
          Developer page for previewing reusable components.
        </p>
      </header>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          BaseNavbar
        </h2>
        <div class="rounded-lg border border-border bg-white p-6 flex flex-col gap-6">
          <BaseNavbar show-hamburger :menu-items="companyMenu" />
          <BaseLogo />
          <div><LanguageSwitcher current-locale="pl" /></div>
        </div>
      </section>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          Menu
        </h2>
        <div class="rounded-lg border border-border bg-white p-6 flex flex-col gap-6">
          <Menu :items="companyMenu" />
          <p>moves to navbar on mobile</p>
        </div>
      </section>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          BaseInput
        </h2>
        <div class="rounded-lg border border-border bg-white p-6 flex flex-col gap-6">
          <BaseInput
            id="showcase-email"
            v-model="email"
            type="email"
            autocomplete="email"
            label="E-mail"
          />
          <BaseInput
            id="showcase-password"
            v-model="password"
            type="password"
            autocomplete="current-password"
            label="Password"
          />
          <BaseInput
            id="showcase-error"
            v-model="inputWithError"
            type="text"
            label="E-mail"
            error="Sample validation error"
          />
        </div>
      </section>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          BaseButton
        </h2>
        <div class="rounded-lg border border-border bg-white p-6 flex flex-col gap-4">
          <BaseButton type="button">
            Primary button
          </BaseButton>
          <BaseButton type="button" variant="outline">
            Outline button
          </BaseButton>
          <BaseButton type="button" disabled>
            Disabled button
          </BaseButton>
        </div>
      </section>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          BaseApplyButton
        </h2>
        <div class="rounded-lg border border-border bg-white p-6 flex flex-col gap-4">
          <BaseApplyButton
            :has-cv="false"
          />
          <BaseApplyButton
            :has-cv="true"
            :is-applied="false"
            :is-loading="false"
          />
          <BaseApplyButton
            :has-cv="true"
            :is-applied="true"
            :applied-date="currentDate"
          />
          <BaseApplyButton
            :has-cv="true"
            :is-applied="false"
            :is-loading="true"
          />
        </div>
      </section>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          BaseCheckbox
        </h2>
        <div class="rounded-lg border border-border bg-white p-6">
          <BaseCheckbox id="showcase-terms" v-model="terms">
            I accept the terms
          </BaseCheckbox>
        </div>
      </section>
      <section class="flex flex-col gap-4">
        <h2 class="text-xl font-medium text-text">
          DynamicMultiSelect
        </h2>
        <div class="rounded-lg border border-border bg-white p-6">
          <DynamicMultiSelect 
            v-model="selectedTags" 
            :options="availableTags"
            :max="30"
            :allow-custom="false"
          />
        </div>
      </section>
    </div>
  </div>
</template>
