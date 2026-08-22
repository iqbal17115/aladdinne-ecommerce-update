<template>
    <div class="mt-6 p-4 sm:p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex justify-between items-center gap-2">
            <div class="text-slate-950 dark:text-white text-base sm:text-lg font-semibold leading-normal">
                {{ $t('Shipping Address') }}
            </div>

            <button v-if="authStore.addresses.length > 0" class="text-primary text-sm font-medium leading-normal"
                @click="authStore.showChangeAddressModal = true">
                {{ $t('Change') }}
            </button>
        </div>

        <!-- Shipping Address form -->
        <Transition
            v-if="authStore.user" leave-active-class="transition ease-in duration-300"
            enter-active-class="transition ease-out duration-300" enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <div v-if="authStore.addresses.length == 0" class="mt-4">
                <address-form />
            </div>
        </Transition>


        <Transition
            v-if="authStore.access_token && !authStore.user"
            leave-active-class="transition ease-in duration-300"
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div class="mt-4">
                <guest-address-form />
            </div>
        </Transition>

        <!-- Selected Address -->
        <div v-if="authStore.addresses.length > 0"
            class="mt-4 p-4 flex items-center gap-3 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 w-full">
            <div
                class="flex w-11 h-11 bg-primary-50 rounded-xl items-center justify-center shrink-0">
                <MapPinIcon class="w-5 h-5 text-primary" />
            </div>
            <div class="overflow-hidden min-w-0">
                <div class="flex items-center gap-2">
                    <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal truncate">
                        {{ basketStore.address?.name }}
                    </div>
                    <div class="px-2 py-0.5 bg-slate-900 rounded-full text-white text-[10px] font-medium uppercase shrink-0">
                        {{ basketStore.address?.address_type }}
                    </div>
                </div>
                <div class="text-slate-500 dark:text-slate-400 text-sm font-normal leading-normal mt-0.5">
                    {{ basketStore.address?.phone }}
                </div>
                <div class="text-slate-400 text-sm font-normal leading-normal truncate">
                    {{ (basketStore.address?.flat_no ? basketStore.address?.flat_no + ', ' : '') + basketStore.address?.address_line + ', ' +
                (basketStore.address?.address_line2 ? basketStore.address?.address_line2 + ', ' : '') }} {{ basketStore.address?.area }}
                </div>
            </div>
        </div>

        <!-- Change Address Dialog Modal -->
        <AddressChangeDialogModal />
        <!-- End Change Address Dialog Modal -->

        <!-- new Address Dialog Modal -->
        <AddressFormModal />
        <!-- End new Address Dialog Modal -->

    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';

import { MapPinIcon } from '@heroicons/vue/24/solid';
import AddressChangeDialogModal from './AddressChangeDialogModal.vue';
import AddressForm from './AddressForm.vue';
import GuestAddressForm from "./GuestAddressForm.vue";
import AddressFormModal from './AddressFormModal.vue';

import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';

const basketStore = useBasketStore();

const changeAddress = ref(false);

const authStore = useAuth();

onMounted(() => {
    authStore.fetchAddresses()
    fetchADefaultAddress()
})

const fetchADefaultAddress = () => {
    if (!basketStore.address) {
        authStore.addresses.forEach((address) => {
            if (address.is_default) {
                basketStore.address = address
                return true;
            }
        })
    }
}

watch(
    () => authStore.user,
    () => {
        authStore.fetchAddresses();
        fetchADefaultAddress();
    }
);
</script>
