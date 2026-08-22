<template>
    <Menu as="div" class="relative hidden md:inline-block text-left" v-slot="{ open }">
        <div>
            <!-- <MenuButton
                class="inline-flex w-full items-center gap-3 bg-white dark:bg-slate-800 py-2 pl-2 pr-3 transition"
                :class="open ? 'border-primary-200 bg-primary-50 text-primary' : 'text-slate-700 dark:text-slate-300 hover:border-primary-200 hover:text-primary'">
                <img :src="authStore.user?.profile_photo" alt="" class="h-11 w-11 rounded-full object-cover">
                <span class="max-w-[100px] truncate text-sm font-medium leading-normal">
                    {{ authStore.user?.name }}
                </span>
                <ChevronDownIcon class="h-4 w-4 shrink-0" :class="open ? 'rotate-180' : ''" />
            </MenuButton> -->
            <MenuButton
    class="inline-flex w-full items-center gap-3  py-2 pl-2 pr-3 transition"
    :class="open ? 'border-primary-200 bg-primary-50 text-primary' : 'text-slate-700 dark:text-slate-300 hover:border-primary-200 hover:text-primary'">

    <img
        :src="authStore.user?.profile_photo"
        alt=""
        class="h-11 w-11 rounded-full object-cover"
    >

    <div class="flex flex-1 flex-col items-start">
        <span class="max-w-[100px] truncate text-sm font-medium leading-normal">
            {{ authStore.user?.name }}
        </span>

        <span class="max-w-[100px] truncate text-xs font-normal text-slate-500 leading-normal">
            {{ $t('My Account') }}
        </span>
    </div>

    <ChevronDownIcon
        class="h-4 w-4 shrink-0 transition-transform"
        :class="open ? 'rotate-180' : ''"
    />
</MenuButton>
        </div>

        <transition enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <MenuItems
                class="absolute z-50 mt-2 w-56 origin-top-right rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg"
                :class="master.langDirection == 'rtl' ? 'left-0' : 'right-0'">
                <div class="py-3 px-4 flex flex-col gap-2">
                    <MenuItem>
                    <router-link to="/dashboard"
                        class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem">
                        <DashboardIcon width="24" height="24" colorClass="currentColor" /> {{ $t('Dashboard') }}
                    </router-link>
                    </MenuItem>

                    <MenuItem>
                    <router-link to="/order-history"
                        class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem">
                        <ArchiveBoxIcon class="w-5 h-5 md:w-6 md:h-6" /> {{ $t('Order History') }}
                    </router-link>
                    </MenuItem>

                    <MenuItem>
                    <router-link to="/profile"
                        class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem">
                        <UserIcon class="w-5 h-5 md:w-6 md:h-6" /> {{ $t('My Profile') }}
                    </router-link>
                    </MenuItem>

                    <MenuItem>
                    <router-link to="/change-password"
                        class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem">
                        <KeyIcon width="24" height="24" colorClass="currentColor" /> {{ $t('Change Password') }}
                    </router-link>
                    </MenuItem>

                    <MenuItem>
                    <button class="flex gap-2 text-red-500 text-base py-2 hover:text-red-600 menuLinkItem"
                        @click="logoutModal = true">
                        <LogoutIcon width="24" height="24" colorClass="currentColor" /> {{ $t('Log Out') }}
                    </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>

    <div class="relative md:hidden">
        <div class="flex justify-between w-full items-center gap-2 rounded-lg text-primary pb-2 mb-2">
            <div class="flex items-center gap-2 min-w-0">
                <div class="relative shrink-0">
                    <img :src="authStore.user?.profile_photo" alt="" class="w-8 h-8 object-cover rounded-full">
                    <div class="w-4 h-4 absolute -bottom-2 right-2/4 translate-x-2/4 rounded-2xl bg-primary-100">
                        <ChevronDownIcon class="w-4 h-4" />
                    </div>
                </div>
                <span class="truncate text-base font-normal leading-normal">
                    {{ authStore.user?.name }}
                </span>
            </div>
        </div>

        <div class="flex flex-col gap-2 p-2 bg-white dark:bg-slate-800 rounded-lg">
            <router-link to="/dashboard"
                class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem w-full justify-between">
                <span class="flex gap-2">
                    <DashboardIcon width="24" height="24" colorClass="currentColor" /> {{ $t('Dashboard') }}
                </span>
                <ChevronRightIcon class="w-4 h-4 text-slate-400" />
            </router-link>

            <router-link to="/order-history"
                class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem w-full justify-between">
                <span class="flex gap-2 truncate overflow-hidden">
                    <ArchiveBoxIcon class="w-5 h-5 md:w-6 md:h-6" /> {{ $t('Order History') }}
                </span>
                <ChevronRightIcon class="w-4 h-4 text-slate-400" />
            </router-link>

            <router-link to="/profile"
                class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem w-full justify-between">
                <span class="flex gap-2 truncate overflow-hidden">
                    <UserIcon class="w-5 h-5 md:w-6 md:h-6" /> {{ $t('My Profile') }}
                </span>
                <ChevronRightIcon class="w-4 h-4 text-slate-400" />
            </router-link>

            <router-link to="/change-password"
                class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem w-full justify-between">
                <span class="flex gap-2 truncate overflow-hidden">
                    <KeyIcon width="24" height="24" colorClass="currentColor" /> {{ $t('Change Password') }}
                </span>
                <ChevronRightIcon class="w-4 h-4 text-slate-400" />
            </router-link>

            <button
                class="flex gap-2 text-slate-600 dark:text-slate-400 text-base py-2 hover:text-primary menuLinkItem w-full justify-between"
                @click="logoutModal = true">
                <span class="flex gap-2">
                    <LogoutIcon width="24" height="24" colorClass="currentColor" /> {{ $t('Log Out') }}
                </span>
                <ChevronRightIcon class="w-4 h-4 text-slate-400" />
            </button>
        </div>
    </div>

    <!-- Logout modal -->
    <TransitionRoot as="template" :show="logoutModal">
        <Dialog as="div" class="relative z-10" @close="logoutModal = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-2xl ring-1 ring-black/5 transition-all sm:my-8 w-full sm:max-w-md">

                            <button type="button"
                                class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                                @click="logoutModal = false">
                                <XMarkIcon class="w-5 h-5" />
                            </button>

                            <div class="bg-white dark:bg-slate-800 px-6 py-8 sm:px-8 sm:py-10 text-center">

                                <div class="relative w-20 h-20 mx-auto flex justify-center items-center">
                                    <span class="absolute inset-0 rounded-full bg-red-500/10 dark:bg-red-500/20 animate-ping"></span>
                                    <span class="absolute inset-0 rounded-full bg-red-500/10 dark:bg-red-500/15"></span>
                                    <div
                                        class="relative w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex justify-center items-center shadow-lg shadow-red-500/30">
                                        <img :src="'/assets/icons/logoutWhite.svg'" alt="icon" loading="lazy" class="w-7 h-7" />
                                    </div>
                                </div>

                                <div class="mt-6 text-center text-slate-900 dark:text-white text-2xl font-bold leading-8">
                                    {{ $t('Log Out') }}?</div>

                                <div class="mt-2 text-center text-slate-500 dark:text-slate-400 text-base font-normal leading-6">
                                    {{ $t('Are you sure you want to log out? You will need to sign in again to access your account') }}.
                                </div>

                                <div class="flex justify-between items-center gap-3 mt-8">
                                    <button
                                        class="grow text-slate-700 dark:text-slate-200 text-base font-medium px-6 py-3.5 rounded-xl border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                        @click="logoutModal = false">{{ $t('Cancel') }}</button>

                                    <button
                                        class="grow text-white bg-gradient-to-br from-red-500 to-red-600 text-base font-medium px-6 py-3.5 rounded-xl shadow-md shadow-red-500/30 hover:shadow-lg hover:shadow-red-500/40 hover:brightness-105 active:scale-[0.98] transition-all"
                                        @click="logout">{{ $t('Yes, Log Out') }}</button>
                                </div>

                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, Menu, MenuButton, MenuItem, MenuItems, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { ChevronDownIcon, ChevronRightIcon, XMarkIcon } from '@heroicons/vue/20/solid'
import { UserIcon, ArchiveBoxIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'

import { useToast } from 'vue-toastification'
import DashboardIcon from '../icons/Dashboard.vue'
import KeyIcon from '../icons/Key.vue'
import LogoutIcon from '../icons/Logout.vue'
import { useAuth } from '../stores/AuthStore'
import { useBasketStore } from '../stores/BasketStore'
import { useMaster } from '../stores/MasterStore'
import localization from '../localization'

const master = useMaster();
const authStore = useAuth();
const basketStore = useBasketStore();

const toast = useToast();
const t = localization.i18n.global.t;

const logoutModal = ref(false)

const logout = () => {
    authStore.logout();
    basketStore.total = 0;
    basketStore.checkoutProducts = [];
    basketStore.products = [];
    basketStore.address = null;
    basketStore.selectedShopIds = [];
    basketStore.coupon_code = '';
    basketStore.payable_amount = 0;
    basketStore.delivery_charge = 0;
    basketStore.coupon_discount = 0;

    toast.success(t('Logout successfully'), {
        position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
    });
}
</script>

<style>
.menuLinkItem:hover img {
    filter: brightness(0) saturate(100%) invert(39%) sepia(96%) saturate(6525%) hue-rotate(256deg) brightness(97%) contrast(91%);
}
</style>
