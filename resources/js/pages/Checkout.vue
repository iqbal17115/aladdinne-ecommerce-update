<template>
    <div class="main-container">

        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 overflow-hidden pt-4 mb-3">
            <router-link to="/" class="w-6 h-6">
                <HomeIcon class="w-5 h-5 text-slate-600 dark:text-slate-400" />
            </router-link>

            <div class="grow w-full overflow-hidden">
                <div class="space-x-1 text-slate-600 dark:text-slate-400 text-sm font-normal truncate">
                    <span>{{ $t('Home') }} </span>
                    <span>/</span>
                    <span>{{ $t('Cart') }}</span>
                    <span>/</span>
                    <span>{{ $t('Checkout') }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 my-3 gap-8">

            <div class="col-span-1 xl:col-span-2">

                <!-- <div class="text-slate-950 dark:text-white text-lg sm:text-xl font-medium leading-10 pb-2">{{ $t('Checkout') }}</div> -->

                <div class="p-4 sm:p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
                    <!-- Your Order -->
                    <div class="flex gap-2 justify-between items-center cursor-pointer"
                        @click="showProductItems = !showProductItems">
                        <div class="flex items-center gap-2">
                            <div class="text-slate-950 dark:text-white text-base sm:text-lg font-semibold leading-normal">
                                {{ $t('Your Order') }}
                            </div>
                            <div class="px-2 py-0.5 bg-primary-50 text-primary-600 rounded-full text-xs font-medium">
                                {{ basketStore.checkoutTotalItems }} {{ $t('Items') }}
                            </div>
                        </div>
                        <ChevronDownIcon class="w-5 h-5 text-primary-600 transition duration-300 shrink-0"
                            :class="showProductItems ? 'rotate-180' : ''" />
                    </div>

                    <!-- Product items -->
                    <div v-if="showProductItems">
                        <checkoutProducts />
                    </div>
                </div>

                <!-- Shipping Address -->
                <ShippingAddress />

                <div class="mt-6">
                    <div class="mb-1">
                        <span class="text-slate-950 dark:text-white text-xl font-medium leading-7">{{ $t('Note') }}</span>
                        <span class="text-slate-500 text-lg font-normal leading-7 tracking-tight">
                            ({{ $t('Optional') }})
                        </span>
                    </div>
                    <textarea v-model="note" rows="3" class="form-input"
                        :placeholder="$t('Write your note') + '...'"></textarea>
                </div>

            </div>

            <!-- Order Summary -->
            <CheckoutOrderSummary :note="note" />

        </div>

    </div>
</template>

<script setup>
import { ChevronDownIcon, HomeIcon } from '@heroicons/vue/24/outline';
import { onMounted, ref } from 'vue';

import CheckoutOrderSummary from '../components/CheckoutOrderSummary.vue';
import checkoutProducts from '../components/checkoutProducts.vue';
import ShippingAddress from '../components/CheckoutShippingAddress.vue';

import { useAuth } from '../stores/AuthStore';
const AuthStore = useAuth();

import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';

import { useRouter } from 'vue-router';
const router = new useRouter();

const master = useMaster();
const basketStore = useBasketStore();

const showProductItems = ref(true);

const note = ref("");

onMounted(() => {
    window.scrollTo(0, 0);
    basketStore.coupon_code = "";
    // Meta's InitiateCheckout should fire once per visit, not on every address
    // or coupon change that re-hits the checkout endpoint.
    basketStore.resetCheckoutTracking();
    if (!AuthStore.user && !AuthStore.access_token) {
        router.push({ name: 'home' });
    }
    AuthStore.showAddressModal = false;
    AuthStore.showChangeAddressModal = false;
});

</script>
<style scoped>
.form-label {
    @apply text-slate-700 dark:text-slate-300 text-base font-normal leading-normal;
}

.form-input {
    @apply p-3 rounded-lg border border-slate-200 dark:border-slate-700 focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400;
}

.formInputCoupon {
    @apply rounded-lg border border-slate-200 dark:border-slate-700 focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400;
}

.radio-btn {
    @apply w-5 h-5 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}

.radioBtn2 {
    @apply w-4 h-4 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}
</style>
