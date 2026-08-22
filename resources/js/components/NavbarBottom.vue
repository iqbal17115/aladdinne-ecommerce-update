<template>
    <div class="main-container flex items-center justify-between md:gap-3 lg:gap-4 flex-wrap md:flex-nowrap relative">

        <div class="xl:w-[220px] flex">
            <!--==== Categories dropdown menu ====-->
            <Popover v-slot="{ open, close }">
                <div class="border-r border-slate-100 p-1">
                    <PopoverButton class="h-10 lg:h-11 flex items-center gap-2 outline-none rounded-md px-4 bg-primary text-white transition-all"
                        @click="setPopoverClose(close)">
                        <div class="w-5 flex items-center justify-center">
                             <Bars3Icon class="w-5 h-5" />
                        </div>
                        <div class="hidden xs:block text-sm lg:w-24 xl:w-36 lg:text-base font-normal leading-normal whitespace-nowrap" :class="master.langDirection === 'rtl' ? 'text-right' : 'text-left'">
                            {{ $t('All Categories') }}
                        </div>
                        <ChevronUpIcon v-if="open" class="w-4 h-4 shrink-0" />
                        <ChevronDownIcon v-else class="w-4 h-4 shrink-0" />
                    </PopoverButton>
                </div>

                <transition enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1">

                    <PopoverPanel class="absolute pb-6 left-0 right-0 z-10 mt-0 flex main-container">
                        <div class="rounded-br-2xl rounded-bl-2xl bg-white dark:bg-slate-800 shadow-xl border border-slate-100 dark:border-slate-700 overflow-hidden w-fit max-w-full">
                            <div
                                class="grid"
                                :class="hasActiveCategoryBanner ? 'lg:grid-cols-[280px_minmax(0,1fr)]' : 'lg:grid-cols-[280px_auto]'">
                                <div class="border-b lg:border-b-0 lg:border-r border-slate-100 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-700/50">
                                    <button
                                        v-for="category in menuCategories"
                                        :key="`category-${category.id}`"
                                        type="button"
                                        class="w-full px-4 py-3.5 flex items-center gap-3 text-left border-b border-slate-100 dark:border-slate-600 transition-all duration-200"
                                        :class="activeCategory?.id === category.id ? 'bg-white dark:bg-slate-800 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 hover:text-primary'"
                                        @mouseenter="setActiveCategory(category)"
                                        @focus="setActiveCategory(category)"
                                        @click="goToCategory(category)">
                                        <img :src="category.icon || category.thumbnail"
                                            :alt="category.name"
                                            class="w-7 h-7 rounded-lg object-cover border border-slate-100 bg-white" />
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm  font-medium truncate">
                                                {{ category.name }}
                                            </div>
                                            <!-- <div class="text-xs text-slate-500 mt-0.5">
                                                {{ category.sub_categories?.length || 0 }} {{ $t('items') }}
                                            </div> -->
                                        </div>
                                        <ChevronRightIcon class="w-4 h-4 shrink-0" />
                                    </button>

                                    <button
                                        type="button"
                                        class="w-full px-4 py-3.5 flex items-center gap-2 text-left text-sm lg:text-base font-semibold text-slate-700 dark:text-slate-300 hover:text-primary dark:hover:text-primary transition-colors border-t border-slate-100 dark:border-slate-600"
                                        @click="goToAllCategories">
                                        <Squares2X2Icon class="w-4 h-4" />
                                        {{ $t('View All Categories') }}
                                    </button>
                                </div>

                                <div
                                    class="p-5 lg:p-6 w-fit max-w-full">
                                    <div v-if="activeCategory" class="flex flex-col gap-5">
                                        <div
                                            class="flex flex-col gap-6 xl:flex-row xl:items-start w-fit max-w-full">
                                            <div class="min-w-0" :class="submenuContentWidthClass">
                                                <div class="flex items-start justify-between gap-4 flex-wrap pb-5">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <div class="w-14 h-14 rounded-full bg-primary-50 flex items-center justify-center shrink-0">
                                                            <img :src="activeCategory.icon || activeCategory.thumbnail"
                                                                :alt="activeCategory.name"
                                                                class="w-8 h-8 object-contain" />
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div class="text-xl font-semibold text-slate-900 dark:text-white truncate">
                                                                {{ activeCategory.name }}
                                                            </div>
                                                            <div class="text-sm text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                                                {{ activeCategory.description
                                                                    ? activeCategory.description
                                                                    : `${activeCategory.sub_categories?.length || 0} ${$t('subcategories available')}` }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div v-if="activeCategory.sub_categories?.length"
                                                    class="pt-5"
                                                >
                                                    <div
                                                        class="grid w-fit max-w-full justify-start gap-y-3"
                                                        :class="[
                                                            activeCategoryColumns.length === 1 ? 'grid-cols-1' : '',
                                                            activeCategoryColumns.length === 2 ? 'grid-cols-1 md:grid-cols-2 md:gap-x-4' : '',
                                                            activeCategoryColumns.length === 3 ? 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3 md:gap-x-4 xl:gap-x-4' : '',
                                                            activeCategoryColumns.length >= 4 ? 'grid-cols-1 md:grid-cols-2 xl:grid-cols-4 md:gap-x-4 xl:gap-x-4' : ''
                                                        ]"
                                                    >
                                                        <div
                                                            v-for="(column, columnIndex) in activeCategoryColumns"
                                                            :key="`subcategory-column-${activeCategory.id}-${columnIndex}`"
                                                            class="min-w-0 space-y-2 md:w-[240px]"
                                                        >
                                                            <button
                                                                v-for="subcategory in column"
                                                                :key="`subcategory-${subcategory.id}`"
                                                                type="button"
                                                                class="group flex w-full items-center gap-2.5 rounded-2xl border-slate-100 dark:border-slate-600 bg-white dark:bg-slate-700 px-2.5 py-2.5 text-left transition-all duration-200 hover:border-primary-200 hover:bg-primary-50/40 dark:hover:bg-slate-600"
                                                                @click="goToSubcategory(activeCategory, subcategory)">
                                                                <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                                                    <img :src="subcategory.thumbnail || activeCategory.icon || activeCategory.thumbnail"
                                                                        :alt="subcategory.name"
                                                                        class="h-7 w-7 object-contain" />
                                                                </div>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="text-[15px] font-medium text-slate-800 dark:text-slate-200 truncate group-hover:text-primary transition-colors"
                                                                        :class="route.query?.sub_category_slug == subcategory.slug ? 'text-primary' : ''">
                                                                        {{ subcategory.name }}
                                                                    </div>
                                                                      <div class="truncate text-[12px] font-medium text-slate-600 dark:text-slate-400 group-hover:text-primary transition-colors">
                                                                        {{ subcategory.short_description }}
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="mt-6 flex justify-center">
                                                        <button
                                                            type="button"
                                                            class="inline-flex min-w-[220px] items-center justify-center rounded-lg border border-primary px-6 py-3 text-sm font-semibold text-primary transition hover:bg-primary hover:text-white"
                                                            @click="goToCategory(activeCategory)">
                                                            {{ `${$t('View All')} ${activeCategory.name}` }}
                                                        </button>
                                                    </div>
                                                </div>

                                                <div v-else class="rounded-xl border border-dashed border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 p-6 text-center text-sm text-slate-500 dark:text-slate-400 mt-5">
                                                    {{ $t('No subcategories found for this category.') }}
                                                </div>
                                            </div>

                                            <div
                                                v-if="hasActiveCategoryBanner"
                                                class="hidden xl:block w-[260px] 2xl:w-[300px] rounded-[10px] overflow-hidden border border-slate-100 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 shadow-sm min-h-[420px] self-stretch relative shrink-0">
                                                <img
                                                    :src="activeCategoryBanner"
                                                    :alt="`${activeCategory.name} banner`"
                                                    class="absolute inset-0 w-full h-full object-cover" />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else class="rounded-xl border border-dashed border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                        {{ $t('No categories available right now.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </PopoverPanel>
                </transition>
            </Popover>

        </div>

        <!-- Main menu -->
        <div class="hidden md:inline-flex items-center gap-5 lg:gap-7 xl:gap-9 2xl:gap-10 grow overflow-x-auto border-slate-200 dark:border-slate-700 rounded-lg px-6 lg:px-8 py-2.5">

            <template v-for="menu in master.menus" :key="menu.id">
                <router-link v-if="!menu.is_external" :to="menu.url" :target="menu.target"
                    class="h-9 py-2 border-b-2 border-transparent text-sm lg:text-base font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                    {{ menu.name }}
                </router-link>
                <a v-else :href="menu.url" :target="menu.target"
                    class="h-9 py-2 border-b-2 border-transparent text-sm lg:text-base font-medium text-slate-700 dark:text-slate-300">
                    {{ menu.name }}
                </a>
            </template>

        </div>

        <!-- Download our app -->
        <!-- <div v-if="master.showDownloadApp" class="inline-block">
            <Menu as="div" class="relative text-left" v-slot="{ open }">
                <div>
                    <MenuButton class="flex items-center gap-1 lg:gap-2 pr-1 lg:pr-3 p-3 rounded-lg bg-primary-50 text-primary border border-slate-200"
                        :class="open ? 'bg-primary-100' : 'hover:bg-primary-100'">
                        <DevicePhoneMobileIcon class="w-4 h-5" />
                        <div class="text-sm xl:text-base font-normal leading-normal whitespace-nowrap">{{ $t('Download app') }}</div>
                        <ChevronDownIcon class="w-4 h-4 transition" :class="open ? 'rotate-180' : ''" />
                    </MenuButton>
                </div>

                <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                    <MenuItems
                        class="absolute right-0 z-10 mt-0 lg:w-full origin-top-right p-3 bg-white rounded-xl shadow border border-primary-300  ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <div class="flex-col flex gap-2">
                            <MenuItem v-slot="{ active }">
                            <button :class="active ? 'bg-gray-100 text-gray-900' : 'text-gray-700'" @click="playStore">
                                <img :src="'/assets/icons/playStore.png'" alt="">
                            </button>
                            </MenuItem>

                            <MenuItem v-slot="{ active }">
                            <button :class="active ? 'bg-gray-100 text-gray-900' : 'text-gray-700'" @click="appStore">
                                <img :src="'/assets/icons/appleStore.png'" alt="">
                            </button>
                            </MenuItem>
                        </div>
                    </MenuItems>
                </transition>
            </Menu>
        </div> -->

    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { Bars3Icon, DevicePhoneMobileIcon, ChevronDownIcon, ChevronUpIcon, ChevronRightIcon, Squares2X2Icon } from '@heroicons/vue/24/outline'
import { useRouter, useRoute } from 'vue-router'

import { useMaster } from "../stores/MasterStore";
const master = useMaster();
const router = useRouter();
const route = useRoute();

const MAX_SUBCATEGORY_COLUMNS = 4;
const SUBCATEGORIES_PER_COLUMN = 6;

const activeCategory = ref(null);
const popoverClose = ref(null);

const menuCategories = computed(() => master.categories);
const activeCategoryBanner = computed(() => activeCategory.value?.banner);
const hasActiveCategoryBanner = computed(() => Boolean(activeCategoryBanner.value));
const submenuContentWidthClass = computed(() => {
    const columnCount = activeCategoryColumns.value.length || 1;

    if (columnCount >= 4) {
        return 'xl:w-[1008px]';
    }

    if (columnCount === 3) {
        return 'xl:w-[752px]';
    }

    if (columnCount === 2) {
        return 'md:w-[496px]';
    }

    return 'w-[240px]';
});

const activeCategoryColumns = computed(() => {
    const subcategories = (activeCategory.value?.sub_categories || []).slice(0, MAX_SUBCATEGORY_COLUMNS * SUBCATEGORIES_PER_COLUMN);

    if (!subcategories.length) {
        return [];
    }

    const columnCount = Math.min(MAX_SUBCATEGORY_COLUMNS, Math.ceil(subcategories.length / SUBCATEGORIES_PER_COLUMN));

    return Array.from({ length: columnCount }, (_, index) => (
        subcategories.slice(index * SUBCATEGORIES_PER_COLUMN, (index + 1) * SUBCATEGORIES_PER_COLUMN)
    )).filter((column) => column.length);
});

const setDefaultActiveCategory = () => {
    const currentCategorySlug = route.query?.category_slug;
    const matchedCategory = menuCategories.value.find((category) => category.slug === currentCategorySlug);

    activeCategory.value = matchedCategory || menuCategories.value[0] || null;
};

watch(menuCategories, () => {
    setDefaultActiveCategory();
}, { immediate: true });

watch(() => route.query?.category_slug, () => {
    setDefaultActiveCategory();
});

const setPopoverClose = (close) => {
    popoverClose.value = close;
};

const closePopover = () => {
    if (typeof popoverClose.value === 'function') {
        popoverClose.value();
    }
};

const setActiveCategory = (category) => {
    activeCategory.value = category;
};

const goToCategory = (category) => {
    if (!category?.slug) {
        return;
    }

    closePopover();
    router.push({ name: 'products', query: { category_slug: category.slug } });
};

const goToSubcategory = (category, subcategory) => {
    if (!category?.slug || !subcategory?.slug) {
        return;
    }

    closePopover();
    router.push({ name: 'products', query: { category_slug: category.slug, sub_category_slug: subcategory.slug } });
};

const goToAllCategories = () => {
    closePopover();
    router.push('/categories');
};

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
.router-link-active {
    @apply border-b-2 border-primary text-primary
}
</style>
