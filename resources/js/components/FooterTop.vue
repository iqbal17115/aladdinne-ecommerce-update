<template>
    <div class="pt-6 pb-6 sm:pt-8 sm:pb-8">

        <div class="main-container">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 md:gap-6">

                <!--===== (footer) =====-->
                <div v-for="(footer, index) in master.footers" :key="footer.id">

                    <div v-if="footer.title" class="text-primary-750 dark:text-white text-lg sm:text-[1.2rem] font-semibold tracking-wide leading-normal flex items-center justify-between mb-2"
                        @click="!isLargeScreen ? toggleLinks(index) : ''">
                        {{ footer.title }}
                        <div v-if="footer.items.some(item => item.type === 'link')" class="transition-transform duration-300 block sm:hidden"
                            :class="checkOpen(index) ? 'rotate-180' : ''">
                            <ChevronDownIcon class="w-5 h-5 text-primary-950 dark:text-slate-300" />
                        </div>
                    </div>

                    <div v-for="item in footer.items" :key="item.id">
                        <div v-if="item.type == 'logo'" class="mb-3">
                            <img :src="currentFooterLogo" class="h-16" loading="lazy" />
                        </div>

                        <div v-if="item.type == 'text'" class="text-gray-600 dark:text-slate-300 py-3 text-base sm:text-base font-normal leading-relaxed max-w-[328px]">
                            {{ item.title }}
                        </div>

                        <div v-if="item.type == 'email'" class="pt-3 flex items-center gap-3 max-w-[328px]">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-slate-700 shrink-0">
                                <Mail class="w-4 h-4 text-primary" :size="16" :stroke-width="2" />
                            </span>
                            <div class="text-gray-700 dark:text-slate-300 text-base sm:text-base font-normal leading-normal">
                                {{ item.title }}
                            </div>
                        </div>

                        <div v-if="item.type == 'phone'" class="pt-3 flex items-center gap-3 max-w-[328px]">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-slate-700 shrink-0">
                                <Phone class="w-4 h-4 text-primary" :size="16" :stroke-width="2" />
                            </span>
                            <div class="text-gray-700 dark:text-slate-300 text-base sm:text-base font-normal leading-normal">
                                {{ item.title }}
                            </div>
                        </div>

                        <div v-if="item.type == 'social_links'" class="flex justify-start pt-6 items-center gap-6">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a v-for="socialLink in master.socialLinks" :key="socialLink.name" target="_blank"
                                    :href="socialLink.link"
                                    class="w-9 h-9 flex items-center justify-center overflow-hidden rounded-md bg-white dark:bg-slate-700 shadow-sm hover:scale-105 transition-transform duration-300"
                                    :title="socialLink.name">
                                    <img :src="socialLink.logo" alt="" class="w-5 h-5 object-contain">
                                </a>
                            </div>
                        </div>

                        <div v-if="item.type == 'app_store'" class="pt-3">
                            <div v-if="master.footerQr" class="bg-white dark:bg-slate-700 rounded-md w-28 overflow-hidden shadow-sm">
                                <img :src="master.footerQr" class="h-28 w-full" loading="lazy" />
                                <div class="text-center text-primary-950 dark:text-slate-300 text-base font-normal leading-tight pb-1">
                                    {{ $t('Scan the QR') }}
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 py-1">
                                <button class="border-none hover:scale-105 transition-all duration-500"
                                    @click="appStore">
                                    <img :src="'/assets/icons/appStoreFooter.png'" alt="appStore"
                                        class="h-[44px]" loading="lazy" />
                                </button>
                                <button class="border-none hover:scale-105 transition-all duration-500"
                                    @click="playStore">
                                    <img :src="'/assets/icons/playStoreFooter.png'" alt="playStore"
                                        class="h-[44px]" loading="lazy" />
                                </button>
                            </div>
                        </div>

                        <transition v-if="item.type == 'link'" name="slide" mode="out-in">
                            <div v-if="checkOpen(index) || isLargeScreen || (footer.title ? false : true)" class="w-full">
                                <router-link :to="item.url" :target="item.target" class="pt-3 hover:text-primary text-gray-700 dark:text-slate-400 text-base sm:text-base font-normal leading-normal inline-flex items-center gap-2">
                                    <component :is="resolveIcon(item.icon)" v-if="item.icon" class="w-4 h-4 text-primary" :size="16" :stroke-width="2" />
                                    {{ item.title }}
                                </router-link>
                            </div>
                        </transition>
                    </div>
                </div>

            </div>

            <!--===== Accepted Payment Methods =====-->
            <div v-if="master.paymentGateways?.length" class="mt-8 pt-6 border-t border-primary-100 dark:border-slate-600">
                <div class="text-primary-950 dark:text-slate-200 text-base sm:text-sm font-semibold mb-3">
                    {{ $t('Accepted Payment Methods') }}
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div v-for="gateway in master.paymentGateways" :key="gateway.id"
                        class="bg-white dark:bg-slate-700 rounded-md shadow-sm px-3 py-2 h-12 flex items-center justify-center"
                        :title="gateway.title">
                        <img :src="gateway.logo" :alt="gateway.title" class="h-6 max-w-[64px] object-contain" loading="lazy" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';
import { Phone, Mail } from 'lucide-vue-next';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';

library.add(fas);

import * as LucideIcons from 'lucide-vue-next';

// Legacy FontAwesome-style names -> Lucide names, so pre-existing rows still render
const legacyIconAlias = {
    'table-cells': 'layout-grid',
    'display': 'monitor',
    'arrows-rotate': 'refresh-cw',
    'circle-question': 'circle-help',
    'envelope': 'mail',
    'file-lines': 'file-text',
    'file-contract': 'scroll-text',
    'shield-halved': 'shield-check',
    'rotate-left': 'rotate-ccw',
    'bag-shopping': 'shopping-bag',
    'bolt': 'zap',
    'box': 'package',
};

// kebab-case -> PascalCase (lucide component name). e.g. 'shopping-bag' -> 'ShoppingBag'
const toPascalCase = (name) =>
    name.replace(/(^\w|-\w)/g, (s) => s.replace('-', '').toUpperCase());

// Resolve ANY Lucide icon name set from the admin panel, dynamically.
const resolveIcon = (raw) => {
    if (!raw) return null;
    const name = legacyIconAlias[raw] || raw;
    return LucideIcons[toPascalCase(name)] || LucideIcons.Link2;
};

import { useMaster } from "../stores/MasterStore";
import { useTheme } from '../composables/useTheme';
const master = useMaster();
const { isDark } = useTheme();

const currentFooterLogo = computed(() => {
    if (isDark.value && master.darkLogo) {
        return master.darkLogo;
    }

    return master.footerLogo;
});

const [item0, item1, item2, item3] = [ref(false), ref(false), ref(false), ref(false)];

const isLargeScreen = ref(window.innerWidth >= 640);

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize);
});

const checkOpen = (index) => {
    const items = [item0, item1, item2, item3];
    return items[index]?.value || false;
}

const handleResize = () => {
    isLargeScreen.value = window.innerWidth >= 640;
    if (isLargeScreen.value) {
        item0.value = item1.value = item2.value = item3.value = true;
    } else {
        item0.value = item1.value = item2.value = item3.value = false;
    }
};

const toggleLinks = (index) => {
    switch (index) {
        case 0:
            item0.value = !item0.value;
            break;
        case 1:
            item1.value = !item1.value;
            break;
        case 2:
            item2.value = !item2.value;
            break;
        case 3:
            item3.value = !item3.value;
            break;
    }
}

const appStore = () => {
    if (master.appStoreLink) {
        window.open(master.appStoreLink, '_blank');
    }
}

const playStore = () => {
    if (master.playStoreLink) {
        window.open(master.playStoreLink, '_blank');
    }
}

</script>
<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
}

.slide-enter-from,
.slide-leave-to {
    max-height: 0;
    opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
    max-height: 500px;
    opacity: 1;
}
</style>
