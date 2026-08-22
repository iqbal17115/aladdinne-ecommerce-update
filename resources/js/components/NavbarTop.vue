<template>
    <div class="border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
        <div class="main-container flex flex-col gap-3 py-3 text-slate-600 dark:text-slate-300 lg:grid lg:grid-cols-[auto_1fr_auto] lg:items-center lg:gap-6 lg:py-2.5">
            <div class="flex min-w-0 items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <TruckIcon class="h-4 w-4 text-primary" />
                    <span>{{ topMessage }}</span>
            </div>

            <div class="hidden items-center justify-center gap-5 lg:flex">
                <template v-for="(item, index) in utilityLinks" :key="item.key">
                    <a
                        :href="item.href"
                        class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300 transition-colors hover:text-primary">
                        <component :is="item.icon" class="h-4 w-4 text-primary" />
                        <span>{{ item.label }}</span>
                    </a>
                    <div
                        v-if="index !== utilityLinks.length - 1"
                        class="h-4 w-px bg-slate-200 dark:bg-slate-600"></div>
                </template>
            </div>

            <div class="flex items-center gap-3 self-start sm:self-auto lg:justify-self-end lg:gap-4">
                <Menu as="div" class="relative inline-block text-left">
                    <div>
                        <MenuButton
                            class="inline-flex items-center gap-1 text-sm font-medium text-slate-700 dark:text-slate-300 transition-colors hover:text-primary">
                            {{ currentLanguage }}
                            <ChevronDownIcon class="h-4 w-4" aria-hidden="true" />
                        </MenuButton>
                    </div>

                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <MenuItems
                            class="absolute z-20 mt-2 w-32 origin-top-right rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-1 shadow-lg focus:outline-none"
                            :class="master.langDirection == 'rtl' ? 'left-0' : 'right-0'">
                            <MenuItem v-for="language in master.languages" v-slot="{ active }" :key="language.id">
                            <button
                                type="button"
                                @click="setCurrentLanguage(language.name); reloadPage()"
                                class="block w-full rounded-lg px-3 py-2 text-left text-sm"
                                :class="active ? 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-300'">
                                {{ language.title }}
                            </button>
                            </MenuItem>
                        </MenuItems>
                    </transition>
                </Menu>

                <div class="h-4 w-px bg-slate-200 dark:bg-slate-600"></div>

                <Menu as="div" class="relative inline-block text-left">
                    <div>
                        <MenuButton
                            class="inline-flex items-center gap-1 text-sm font-medium text-slate-700 dark:text-slate-300 transition-colors hover:text-primary">
                            {{ (master.selectedCurrency?.symbol || '$') + ' ' + (master.selectedCurrency?.name || 'USD') }}
                            <ChevronDownIcon class="h-4 w-4" aria-hidden="true" />
                        </MenuButton>
                    </div>

                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <MenuItems
                            class="absolute z-20 mt-2 w-28 origin-top-right rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-1 shadow-lg focus:outline-none"
                            :class="master.langDirection == 'rtl' ? 'left-0' : 'right-0'">
                            <MenuItem v-for="currency in master.currencies" v-slot="{ active }" :key="currency.id">
                            <button
                                type="button"
                                @click="setCurrentCurrency(currency)"
                                class="block w-full rounded-lg px-3 py-2 text-left text-sm"
                                :class="active ? 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-300'">
                                {{ currency.symbol }} {{ currency.name }}
                            </button>
                            </MenuItem>
                        </MenuItems>
                    </transition>
                </Menu>
                <div class="h-4 w-px bg-slate-200 dark:bg-slate-600"></div>

                <!-- Dark mode toggle -->
                <button
                    type="button"
                    @click="toggleTheme"
                    class="flex items-center justify-center w-7 h-7 rounded-full text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary transition-colors"
                    :title="isDark ? $t('Switch to Light') : $t('Switch to Dark')">
                    <SunIcon v-if="isDark" class="h-4 w-4" />
                    <MoonIcon v-else class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'
import { ArrowPathRoundedSquareIcon, FireIcon, MoonIcon, QuestionMarkCircleIcon, SunIcon, TruckIcon } from '@heroicons/vue/24/outline'
import localization from '../localization';
import { useTheme } from '../composables/useTheme';
const { isDark, toggleTheme } = useTheme();

import { useMaster } from "../stores/MasterStore";
import { computed, onMounted, ref, watch } from 'vue';
const master = useMaster();

const currentLanguage = ref('English');
const translate = localization.i18n.global.t;
const topMessage = computed(() => `${translate('Free Delivery on orders over')} ${master.showCurrency(999)}`);

const utilityLinks = computed(() => [
    {
        key: 'deals',
        label: translate('Daily Deals'),
        href: '/best-deal',
        icon: FireIcon
    },
    {
        key: 'returns',
        label: translate('Easy Returns'),
        href: '/return-policy',
        icon: ArrowPathRoundedSquareIcon
    },
    {
        key: 'help',
        label: translate('Help Center'),
        href: '/contact-us',
        icon: QuestionMarkCircleIcon
    }
]);

onMounted(() => {
    setCurrentLanguage(master.locale);
});

watch(() => master.locale, (oldValue, newValue) => {
    if (oldValue !== newValue) {
        setCurrentLanguage(master.locale);
    }
});

const setCurrentLanguage = (lang) => {
    master.locale = lang;
    localStorage.setItem('locale', lang);

    const language = master.languages.find(lang => lang.name === master.locale);
    if (language) {
        currentLanguage.value = language.title;
        master.langDirection = language.direction || 'ltr';
    }
    localization.fetchLocalizationData();
};

const setCurrentCurrency = (currency) => {
    master.selectedCurrency = currency;
};

const reloadPage = () => {
    window.location.reload();
}

</script>
