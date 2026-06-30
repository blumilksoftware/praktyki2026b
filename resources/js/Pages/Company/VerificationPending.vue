<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import LanguageDropdown from "@/Components/Common/LanguageDropdown.vue";

const { t } = useI18n();
</script>

<template>
    <Head :title="t('pending.title')" />

<div class="top-4 right-4 z-10 absolute shadow p-2 rounded-lg">
  <LanguageDropdown :mobile="true" variant="light" />
</div>

    <div class="flex justify-center items-center bg-slate-50 px-6 min-h-screen">
        <div class="bg-white shadow-sm p-8 md:p-12 rounded-2xl w-full max-w-3xl">
            <div class="space-y-8">
                <h1 class="font-semibold text-slate-900 text-4xl text-center">
                    {{ t('pending.title') }}
                </h1>

                <p class="text-slate-700 text-base text-center leading-7">
                    {{ t('pending.description') }}
                </p>

                <hr class="border-slate-200" />

                <p class="text-slate-500 text-base text-center leading-7">
                    {{ t('pending.status', { time: '2-15' }) }}
                </p>

                <p class="text-slate-500 text-base text-center leading-7">
                    {{ t('pending.contact', { email: 'support@example.com' }) }}
                </p>

                <button
                    class="bg-slate-900 hover:bg-slate-800 px-4 py-3 rounded-lg w-full font-medium text-white transition"
                >
                    {{ t('pending.log_out') }}
                </button>
            </div>
        </div>
    </div>
</template>