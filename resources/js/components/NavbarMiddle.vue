<template>
    <div class="bg-white dark:bg-slate-900">
        <div class="main-container flex items-center justify-between gap-6 py-5 lg:gap-8">
            <div class="flex min-w-0 flex-1 items-center lg:grid lg:grid-cols-[220px_minmax(0,1fr)] lg:gap-8 xl:grid-cols-[240px_minmax(0,1fr)] xl:gap-10">
                <router-link to="/" class="shrink-0 lg:min-w-[220px]">
                    <img :src="currentLogo" alt="" class="h-8 w-auto lg:h-12">
                </router-link>

                <div class="hidden min-w-0 lg:flex lg:justify-center">
                    <div class="w-full max-w-[560px] xl:max-w-[700px] 2xl:max-w-[760px]">
                    <div class="relative flex h-12 overflow-visible rounded-[5px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
                        <input
                            type="text"
                            v-model="search"
                            :placeholder="$t('Search for products, brands and more...')"
                            class="min-w-0 grow rounded-l-[5px] border-0 px-5 text-[15px] text-slate-700 dark:text-slate-200 bg-transparent placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none"
                            @keyup.enter="searchProducts()">

                        <Menu as="div" class="relative hidden items-center border-l border-slate-200 dark:border-slate-700 px-4 2xl:flex">
                            <MenuButton
                                class="flex min-w-[170px] items-center justify-between gap-3 py-2 text-left text-sm font-medium text-slate-700 dark:text-slate-300 focus:outline-none">
                                <span class="truncate">{{ selectedCategoryLabel }}</span>
                                <ChevronDownIcon class="h-4 w-4 shrink-0 text-slate-500 dark:text-slate-400" />
                            </MenuButton>

                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95">
                                <MenuItems
                                    class="absolute right-0 top-full z-30 mt-3 w-56 origin-top-right rounded-[5px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-[0_16px_40px_rgba(15,23,42,0.14)] focus:outline-none">
                                    <div class="max-h-64 overflow-y-auto">
                                        <MenuItem v-slot="{ active }">
                                        <button
                                            type="button"
                                            class="block w-full rounded-lg px-3 py-2 text-left text-sm transition"
                                            :class="selectedCategory === 'all' ? 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white' : active ? 'bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-300'"
                                            @click="selectedCategory = 'all'">
                                            {{ $t('All Categories') }}
                                        </button>
                                        </MenuItem>

                                        <MenuItem
                                            v-for="category in master.categories"
                                            :key="`search-category-${category.id}`"
                                            v-slot="{ active }">
                                        <button
                                            type="button"
                                            class="block w-full rounded-lg px-3 py-2 text-left text-sm transition"
                                            :class="selectedCategory === category.slug ? 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white' : active ? 'bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-300'"
                                            @click="selectedCategory = category.slug">
                                            {{ category.name }}
                                        </button>
                                        </MenuItem>
                                    </div>
                                </MenuItems>
                            </transition>
                        </Menu>

                        <button
                            class="flex h-full w-14 shrink-0 items-center justify-center rounded-r-[5px] bg-primary text-white transition hover:bg-primary-700 lg:w-16"
                            @click="searchProducts()">
                            <MagnifyingGlassIcon class="h-5 w-5 lg:h-6 lg:w-6" />
                        </button>
                    </div>
                    </div>
                </div>
            </div>

            <div class="hidden shrink-0 md:flex items-center justify-end gap-1 lg:gap-2">
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 shadow-sm transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary lg:hidden"
                    @click="toggleSearch">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </button>

                <button
                    type="button"
                    class="group flex flex-col items-center gap-1 text-slate-700 dark:text-slate-300 transition-colors hover:text-primary"
                    @click="showWishlist()">
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 transition group-hover:border-primary-200 group-hover:bg-primary-50">
                        <img :src="'/assets/icons/heart.svg'" class="h-6 w-6" />
                        <span
                            class="absolute -right-0.5 -top-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-white">
                            {{ AuthStore.favoriteProducts }}
                        </span>
                    </div>
                    <!-- <span class="text-sm font-semibold">{{ $t('Wishlist') }}</span> -->
                </button>

                <button
                    type="button"
                    class="group flex flex-col items-center gap-1 text-slate-700 dark:text-slate-300 transition-colors hover:text-primary"
                    @click="master.basketCanvas = true">
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 transition group-hover:border-primary-200 group-hover:bg-primary-50">
                        <img :src="'/assets/icons/bag.svg'" class="h-6 w-6" />
                        <span
                            class="absolute -right-0.5 -top-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-white">
                            {{ basketStore.total }}
                        </span>
                    </div>
                    <!-- <span class="text-sm font-semibold">{{ $t('Cart') }}</span> -->
                </button>

                <button
                    v-if="!AuthStore.user"
                    type="button"
                    class="group flex items-center gap-2 bg-white dark:bg-slate-900 py-2 pl-2 pr-2 text-slate-700 dark:text-slate-300 transition hover:text-primary"
                    @click="showLoginDialog">
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 transition group-hover:border-primary-200 group-hover:bg-primary-50">
                        <img :src="'/assets/icons/profile.svg'" class="h-6 w-6" />
                    </div>
                    <!-- <span class="text-base font-medium">{{ $t('Login') }}</span> -->
                </button>

                <div v-else class="shrink-0">
                    <AuthUserDropdown />
                </div>
            </div>

            <!--******=== Mobile View Navbar ===********-->
            <div class="relative flex items-center gap-4 md:hidden">

                <!-- Search Icon -->
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100" @click="toggleSearch">
                    <MagnifyingGlassIcon class="h-5 w-5 text-slate-950" />
                </div>

                <button class="pl-1" @click="master.basketCanvas = true">
                    <div class="relative h-6 w-6">
                        <img :src="'/assets/icons/bag.svg'" class="h-6 w-6 text-primary" />
                        <span
                            class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                            {{ basketStore.total }}
                        </span>
                    </div>
                </button>

                <!-- search modal -->
                <TransitionRoot as="template" :show="showSearch">
                    <Dialog class="relative z-10" @close="showSearch = false">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                        enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100"
                        leave-to="opacity-0">
                        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
                    </TransitionChild>

                    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4 text-center sm:items-center sm:p-0">
                            <TransitionChild as="template" enter="ease-out duration-300"
                                enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                                leave-from="opacity-100 translate-y-0 sm:scale-100"
                                leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                                <DialogPanel
                                    class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 w-full sm:w-full sm:max-w-lg">
                                    <div class="bg-white dark:bg-slate-800 px-4 pb-2 pt-5">
                                        <div class="w-full flex items-center justify-between mb-3 border-b dark:border-slate-700 pb-2">
                                            <span class="text-slate-700 dark:text-slate-200">{{ $t('Search') }}</span>
                                            <button type="button"
                                                class="border border-slate-100 dark:border-slate-700 rounded-full p-1 outline-none"
                                                @click="showSearch = false">
                                                <XMarkIcon class="w-5 h-5 text-slate-950 dark:text-slate-200" />
                                            </button>
                                        </div>
                                        <input type="text" v-model="search" :placeholder="$t('Search product')"
                                            class="px-2 py-2.5 block rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 focus:border-primary w-full placeholder:text-gray-400 dark:placeholder:text-slate-500 outline-none text-base font-normal leading-normal"
                                            @keyup.enter="showSearch = false; searchProducts()" />
                                    </div>
                                    <div class="bg-gray-50 dark:bg-slate-700 px-4 py-3">
                                        <button type="button"
                                            class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-600"
                                            @click=" showSearch = false; searchProducts()">
                                            {{ $t('Search') }}
                                        </button>
                                    </div>
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </div>
                </Dialog>
                </TransitionRoot>

                <!-- Menu Icon -->
                <div class="flex h-10 w-10 items-center justify-end" @click="mobileMenuOpen = true">
                    <Bars3Icon class="h-6 w-6 text-slate-950" />
                </div>

                <!-- Mobile Menu Canvas Drawer -->
                <TransitionRoot as="template" :show="mobileMenuOpen">
                    <Dialog as="div" class="relative z-10" @close="mobileMenuOpen = false">
                    <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0"
                        enter-to="opacity-100" leave="ease-in-out duration-500" leave-from="opacity-100"
                        leave-to="opacity-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-30 transition-opacity" />
                    </TransitionChild>

                    <div class="fixed inset-0 overflow-hidden">
                        <div class="absolute inset-0 overflow-hidden">
                            <div class="pointer-events-none fixed inset-y-0  flex max-w-full"
                                :class="master.langDirection == 'rtl' ? 'left-0 sm:pr-10' : 'right-0 sm:pl-10'">
                                <TransitionChild as="template"
                                    enter="transform transition ease-in-out duration-500 sm:duration-700"
                                    :enter-from="master.langDirection == 'rtl' ? '-translate-x-full' : 'translate-x-full'"
                                    enter-to="translate-x-0"
                                    leave="transform transition ease-in-out duration-500 sm:duration-700"
                                    leave-from="translate-x-0"
                                    :leave-to="master.langDirection == 'rtl' ? '-translate-x-full' : 'translate-x-full'">
                                    <DialogPanel class="pointer-events-auto relative w-screen max-w-md">
                                        <TransitionChild as="template" enter="ease-in-out duration-500"
                                            enter-from="opacity-0" enter-to="opacity-100"
                                            leave="ease-in-out duration-500" leave-from="opacity-100"
                                            leave-to="opacity-0">
                                            <div class="absolute left-0 top-0 -ml-8 flex pr-2 pt-4 sm:-ml-10 sm:pr-4">
                                            </div>
                                        </TransitionChild>
                                        <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-slate-900 shadow-xl p-4">

                                            <div class="flex justify-between items-center">
                                                <div
                                                    class="text-slate-950 dark:text-white text-lg font-bold leading-normal tracking-tight">
                                                    {{ $t('Menu') }}</div>
                                                <button
                                                    class="w-7 h-7 flex justify-center items-center bg-slate-100 dark:bg-slate-700 rounded-full"
                                                    @click="mobileMenuOpen = false">
                                                    <XMarkIcon class="w-5 h-5 text-slate-700 dark:text-slate-300" />
                                                </button>
                                            </div>

                                            <!-- login button -->
                                            <div v-if="!AuthStore.user" class="mt-5 p-2 bg-primary rounded-lg">
                                                <div class="px-3 py-2.5 bg-white dark:bg-slate-800 rounded-md border border-slate-100 dark:border-slate-700 flex justify-between"
                                                    @click="showLoginDialog">
                                                    <div class="flex items-center gap-2">
                                                        <UserIcon class="w-5 h-5 text-slate-600 dark:text-slate-300" />
                                                        <div class="text-slate-600 dark:text-slate-300 text-sm font-normal leading-tight">
                                                            {{ $t('Login') }}
                                                        </div>
                                                    </div>
                                                    <ChevronRightIcon class="w-5 h-5 text-slate-600 dark:text-slate-300" />
                                                </div>
                                            </div>

                                            <div v-else class="bg-primary-100 dark:bg-slate-800 p-3 rounded-lg mt-5">
                                                <AuthUserDropdown />
                                            </div>

                                            <div
                                                class="p-2 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-100 dark:border-slate-700 flex flex-col gap-1 mt-5">

                                                <div class="flex justify-between items-center px-3 py-2.5 bg-white dark:bg-slate-700 rounded-md border border-slate-100 dark:border-slate-600 gap-2"
                                                    @click="showWishlist()">
                                                    <div class="flex items-center gap-2">
                                                        <img :src="'/assets/icons/heart.svg'"
                                                            class="w-5 h-5 text-slate-600" />
                                                        <div class="text-slate-600 dark:text-slate-300 text-sm font-normal leading-tight">
                                                            {{ $t('Wishlist') }}
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="w-5 h-5 bg-red-500 rounded-3xl border border-white flex justify-center items-center text-white">
                                                        <span class="text-white text-xs font-bold">
                                                            {{ AuthStore.favoriteProducts }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="flex justify-between items-center px-3 py-2.5 bg-white dark:bg-slate-700 rounded-md border border-slate-100 dark:border-slate-600 gap-2"
                                                    @click="showMyCart()">
                                                    <div class="flex items-center gap-2">
                                                        <img :src="'/assets/icons/bag.svg'"
                                                            class="w-6 h-6 text-slate-600" />
                                                        <div class="text-slate-600 dark:text-slate-300 text-sm font-normal leading-tight">
                                                            {{ $t('My Cart') }}
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="w-5 h-5 bg-red-500 rounded-3xl border border-white flex justify-center items-center text-white">
                                                        <span class="text-white text-xs font-bold">
                                                            {{ basketStore.total }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="justify-start inline-flex grow flex-col mt-5 gap-2">

                                                <div v-for="menu in master.menus" :key="menu.id" class="w-full  text-base">
                                                    <router-link v-if="!menu.is_external" :to="menu.url"
                                                        class="py-2 font-normal text-slate-600 dark:text-slate-300 border-b-2 border-slate-200 dark:border-slate-700 block">
                                                        {{ menu.name }}
                                                    </router-link>
                                                    <a v-else :href="menu.url" :target="menu.target"
                                                        class="py-2 border-b-2 border-slate-200 dark:border-slate-700 block font-normal text-slate-600 dark:text-slate-300">
                                                        {{ menu.name }}
                                                    </a>
                                                </div>

                                            </div>
                                        </div>
                                    </DialogPanel>
                                </TransitionChild>
                            </div>
                        </div>
                    </div>
                </Dialog>
                </TransitionRoot>
            </div>
        </div>
    </div>
    <!-- Login Dialog Modal -->
    <LoginModal />
    <!-- End Login Dialog Modal -->
</template>

<script setup>
import { Dialog, DialogPanel, Menu, MenuButton, MenuItem, MenuItems, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { Bars3Icon, ChevronDownIcon, ChevronRightIcon, UserIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/solid'
import { computed, ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AuthUserDropdown from './AuthUserDropdown.vue'
import LoginModal from './LoginModal.vue'

import { useAuth } from '../stores/AuthStore'
import { useBasketStore } from '../stores/BasketStore'
import { useMaster } from '../stores/MasterStore'
import { useTheme } from '../composables/useTheme'
import localization from '../localization'

const route = useRoute();
const router = useRouter();
const basketStore = useBasketStore();

const AuthStore = useAuth();
const master = useMaster();
const { isDark } = useTheme();
const t = localization.i18n.global.t;

const search = ref('');
const selectedCategory = ref('all');
const showSearch = ref(false);
const currentLogo = computed(() => {
    if (isDark.value && master.darkLogo) {
        return master.darkLogo;
    }

    return master.logo;
});
const selectedCategoryLabel = computed(() => {
    if (selectedCategory.value === 'all') {
        return t('All Categories');
    }

    const category = master.categories.find((item) => item.slug === selectedCategory.value);
    return category?.name || t('All Categories');
});

const toggleSearch = () => {
    showSearch.value = !showSearch.value
}

const showMyCart = () => {
    mobileMenuOpen.value = false;
    master.basketCanvas = true
}

const showWishlist = () => {
    mobileMenuOpen.value = false;
    if (!AuthStore.token) {
        return showLoginDialog();
    }
    router.push('/wishlist')
}

watch(() => route.path, () => {
    mobileMenuOpen.value = false;
    if (route.path == '/products') {
        search.value = master.search
        selectedCategory.value = route.query.category_slug ?? 'all'
    } else {
        search.value = ''
        selectedCategory.value = 'all'
    }
});

onMounted(() => {
    if (route.path == '/products') {
        search.value = master.search
        selectedCategory.value = route.query.category_slug ?? 'all'
    } else {
        search.value = ''
    }
});

const mobileMenuOpen = ref(false);

const showLoginDialog = () => {
    mobileMenuOpen.value = false;
    AuthStore.showLoginModal();
}

const searchProducts = () => {
    master.search = search.value
    if (route.path != '/products') {
        search.value = '';
    }
    router.push({
        name: 'products',
        query: selectedCategory.value !== 'all' ? { category_slug: selectedCategory.value } : {},
    })
}

</script>

<style scoped>
.router-link-active {
    @apply border-primary text-primary
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
