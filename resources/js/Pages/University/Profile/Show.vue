<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { IconArrowLeft } from '@tabler/icons-vue'
import Header from '@/Components/Profiles/Header.vue'
import BaseNavbar from '@/Components/Navigation/BaseNavbar.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import ContactCard from '@/Components/Profiles/ContactCard.vue'
import Sidebar from '@/Components/Profiles/Sidebar.vue'
import { ROUTES } from '@/Helpers/routes'
import { useI18n } from 'vue-i18n'
import { IconUserCircle, IconBuildingCommunity } from '@tabler/icons-vue'

const { t } = useI18n()

const universityMenu = computed(() => [
  { label: t('profiles.profile'), href: ROUTES.PROFILE, icon: IconUserCircle, isActive: true },
])

const goBack = () => {
  window.history.back()
}

const goToEdit = () => {
  window.location.href = ROUTES.PROFILE_EDIT 
}

defineProps({
  university: { type: Object, default: () => ({}) },
})
</script>

<template>
  <Head :title="university.name" />
  <div class="min-h-screen flex flex-col bg-background">
    <BaseNavbar class="shrink-0" />

    <div class="flex-1 w-full max-w-screen-2xl mx-auto bg-background px-6 md:px-12 lg:px-16 py-6 md:py-8">
      <div class="flex flex-row justify-between items-center w-full mb-6">
        <div class="w-auto lg:w-72 shrink-0">
          <BaseButton
            class="bg-secondary hover:bg-secondary/90 text-white px-5 py-2 flex items-center justify-center lg:justify-start w-fit text-sm font-semibold rounded-xl shadow-sm transition-all"
            @click="goBack"
          >
            <IconArrowLeft stroke="2.5" class="w-4 h-4 mr-2" />
            {{ t('buttons.back') }}
          </BaseButton>
        </div>

        <div>
          <BaseButton
            class="bg-secondary hover:bg-secondary/90 text-white px-6 py-2 text-sm font-semibold rounded-xl shadow-sm transition-all"
            @click="goToEdit"
          >
            {{ t('buttons.editProfile') }}
          </BaseButton>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-8 items-start">
        <div class="w-full lg:w-72 shrink-0">
          <Sidebar :items="universityMenu" />
        </div>

        <div class="flex-1 w-full bg-white rounded-xl border border-secondary shadow-sm px-6 py-10 sm:px-12 sm:py-12 relative">
          <Header
            :name="university.name"
            :logo-url="university.logoUrl"
          />

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mt-12">
            <!-- LEWA KOLUMNA (Główna - tylko treść) -->
            <div class="lg:col-span-2 space-y-8">
              <section v-if="university.faculties && university.faculties.length > 0">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                  <IconBuildingCommunity class="w-6 h-6 text-primary" />
                  Wydziały i Kierunki
                </h3>
                <div class="space-y-4">
                  <div 
                    v-for="faculty in university.faculties" 
                    :key="faculty.id" 
                    class="p-5 border border-gray-200 rounded-xl bg-white shadow-sm"
                  >
                    <h4 class="font-bold text-lg text-secondary mb-3">{{ faculty.name }}</h4>
                    
                    <ul v-if="faculty.study_fields && faculty.study_fields.length > 0" class="flex flex-wrap gap-2">
                      <li 
                        v-for="field in faculty.study_fields" 
                        :key="field.id"
                        class="bg-blue-50 text-primary px-3 py-1.5 rounded-lg text-sm font-medium border border-blue-100"
                      >
                        {{ field.name }}
                      </li>
                    </ul>
                    <div v-else class="text-sm text-gray-400 italic">
                      Brak przypisanych kierunków.
                    </div>
                  </div>
                </div>
              </section>

              <section v-else class="text-gray-500 italic">
                {{ t('profiles.university.noFaculties') }}
              </section>
            </div>

            <div class="lg:col-span-1 lg:col-start-3 lg:row-span-2 flex flex-col gap-8">
              <ContactCard
                :email="university.email"
                :phone="university.phone"
                :website="university.website"
                :address="university.address"
              />

              <!-- Karta Informacji Systemowych / Rekrutacji -->
              <div v-if="university.domain || university.externalFormUrl" class="flex flex-col gap-5">
                <h2 class="text-xl font-bold text-text">Rekrutacja i system</h2>
                
                <div class="border border-gray-200 rounded-4xl p-6 sm:p-8 bg-gray-50 flex flex-col gap-6">
                  <div v-if="university.domain" class="flex flex-col gap-1">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                      Domena uczelni
                    </span>
                    <span class="text-gray-800 font-medium">
                      {{ university.domain }}
                    </span>
                  </div>

                  <div v-if="university.externalFormUrl" class="flex flex-col gap-1.5">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                      System rekrutacyjny
                    </span>
                    <a 
                      :href="university.externalFormUrl" 
                      target="_blank" 
                      rel="noopener noreferrer"
                      class="text-primary hover:text-primary/80 font-medium underline underline-offset-4 transition-colors wrap-break-word"
                    >
                      Przejdź do formularza
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
