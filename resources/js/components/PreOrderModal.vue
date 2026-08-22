<template>
    <TransitionRoot as="template" :show="show">
        <Dialog as="div" class="relative z-30" @close="close">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-3 text-center sm:p-4">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative flex flex-col max-h-[94vh] w-full md:max-w-4xl overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-2xl transition-all">

                            <!-- Header -->
                            <div
                                class="shrink-0 flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-slate-700 flex items-center justify-center shrink-0">
                                        <ClockIcon class="w-5 h-5 text-primary" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-950 dark:text-white text-base sm:text-lg font-semibold leading-tight">
                                                {{ $t("Pre-Order") }}
                                            </span>
                                            <span class="hidden xs:inline-flex px-2 py-[2px] rounded-full bg-primary-50 dark:bg-primary/20 text-primary text-[10px] font-semibold uppercase tracking-wide">
                                                {{ $t("Reserve now") }}
                                            </span>
                                        </div>
                                        <div class="text-slate-400 text-xs truncate">{{ $t("Secure your item before it arrives") }}</div>
                                    </div>
                                </div>
                                <button
                                    class="w-9 h-9 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-full flex justify-center items-center cursor-pointer shrink-0 transition"
                                    @click="close">
                                    <XMarkIcon class="w-5 h-5 text-slate-600 dark:text-slate-300" />
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="flex-1 overflow-y-auto">
                                <div class="md:grid md:grid-cols-12">

                                    <!-- Left rail : product + summary context -->
                                    <div class="md:col-span-5 bg-slate-50 dark:bg-slate-900/40 md:border-r border-slate-100 dark:border-slate-700 p-5 sm:p-6 space-y-5">

                                        <!-- Product card -->
                                        <div class="flex items-center gap-3">
                                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 shrink-0">
                                                <img :src="productImage" :alt="product.name" class="w-full h-full object-cover" />
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-snug line-clamp-2">
                                                    {{ product.name }}
                                                </div>
                                                <div class="mt-1 text-primary text-sm font-bold">
                                                    {{ master.showCurrency(parseFloat(unitPrice || 0).toFixed(2)) }}
                                                    <span class="text-slate-400 text-xs font-normal">/ {{ unitLabel }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pre-order details -->
                                        <div>
                                            <div class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide mb-2">{{ $t("Pre-Order Details") }}</div>
                                            <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                                                <div class="flex items-center justify-between gap-3 px-4 py-3">
                                                    <span class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs">
                                                        <TruckIcon class="w-4 h-4" /> {{ $t("Expected Delivery") }}
                                                    </span>
                                                    <span class="text-slate-800 dark:text-slate-100 text-xs font-medium text-right">
                                                        {{ details.expected_delivery_date || $t("To be announced") }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between gap-3 px-4 py-3">
                                                    <span class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs">
                                                        <ArrowPathIcon class="w-4 h-4" /> {{ $t("Refundable") }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-[2px] rounded-full"
                                                        :class="details.is_refund
                                                            ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                                                            : 'bg-red-50 text-red-500 dark:bg-red-500/10 dark:text-red-400'">
                                                        {{ details.is_refund ? $t("Yes") : $t("No") }}
                                                    </span>
                                                </div>
                                                <div v-if="details.is_prepay" class="flex items-center justify-between gap-3 px-4 py-3">
                                                    <span class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs">
                                                        <ShieldCheckIcon class="w-4 h-4" /> {{ $t("Prepayment") }}
                                                    </span>
                                                    <span class="text-primary text-xs font-semibold">
                                                        {{ master.showCurrency(parseFloat(details.prepay_amount || 0).toFixed(2)) }} / {{ unitLabel }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notice -->
                                        <p v-if="details.preorder_notice"
                                            class="rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 px-4 py-3 text-amber-700 dark:text-amber-400 text-xs leading-relaxed">
                                            {{ details.preorder_notice }}
                                        </p>

                                        <!-- Amount summary -->
                                        <div>
                                            <div class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide mb-2">{{ $t("Order Summary") }}</div>
                                            <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 space-y-2.5">
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-slate-500 dark:text-slate-400">{{ $t("Subtotal") }} <span class="text-slate-400">× {{ quantity }}</span></span>
                                                    <span class="text-slate-800 dark:text-slate-100 font-medium">{{ master.showCurrency(parseFloat(amounts.totalAmount).toFixed(2)) }}</span>
                                                </div>
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-slate-500 dark:text-slate-400">{{ $t("Delivery Charge") }}</span>
                                                    <span class="text-slate-800 dark:text-slate-100 font-medium">{{ master.showCurrency(parseFloat(amounts.deliveryCharge).toFixed(2)) }}</span>
                                                </div>
                                                <div class="border-t border-dashed border-slate-200 dark:border-slate-700 !my-3"></div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-950 dark:text-white text-sm font-semibold">{{ $t("Total Payable") }}</span>
                                                    <span class="text-slate-950 dark:text-white text-base font-bold">{{ master.showCurrency(parseFloat(amounts.payableAmount).toFixed(2)) }}</span>
                                                </div>
                                                <template v-if="details.is_prepay">
                                                    <div class="mt-2 rounded-lg bg-primary-50 dark:bg-primary/10 px-3 py-2 flex justify-between items-center">
                                                        <span class="text-primary text-xs font-semibold">{{ $t("Pay Now (Prepayment)") }}</span>
                                                        <span class="text-primary text-sm font-bold">{{ master.showCurrency(parseFloat(amounts.prePayableAmount).toFixed(2)) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-[11px] text-slate-400 px-1">
                                                        <span>{{ $t("Due on delivery") }}</span>
                                                        <span>{{ master.showCurrency((parseFloat(amounts.payableAmount) - parseFloat(amounts.prePayableAmount)).toFixed(2)) }}</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Policies -->
                                        <div v-if="master.preOrderPolicy || master.preOrderRefundPolicy" class="space-y-2">
                                            <details v-if="master.preOrderPolicy" class="group rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-3">
                                                <summary class="flex items-center justify-between cursor-pointer text-slate-700 dark:text-slate-200 text-xs font-semibold list-none">
                                                    {{ $t("Pre-Order Policy") }}
                                                    <ChevronDownIcon class="w-4 h-4 text-slate-400 group-open:rotate-180 transition" />
                                                </summary>
                                                <div class="mt-2 text-slate-500 dark:text-slate-400 text-xs leading-relaxed prose-sm max-w-none" v-html="master.preOrderPolicy"></div>
                                            </details>
                                            <details v-if="master.preOrderRefundPolicy" class="group rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-3">
                                                <summary class="flex items-center justify-between cursor-pointer text-slate-700 dark:text-slate-200 text-xs font-semibold list-none">
                                                    {{ $t("Refund Policy") }}
                                                    <ChevronDownIcon class="w-4 h-4 text-slate-400 group-open:rotate-180 transition" />
                                                </summary>
                                                <div class="mt-2 text-slate-500 dark:text-slate-400 text-xs leading-relaxed prose-sm max-w-none" v-html="master.preOrderRefundPolicy"></div>
                                            </details>
                                        </div>
                                    </div>

                                    <!-- Right : the actionable form -->
                                    <div class="md:col-span-7 p-5 sm:p-6 space-y-6">

                                        <!-- Step 1 : Quantity -->
                                        <section>
                                            <div class="flex items-center gap-2 mb-3">
                                                <h4 class="text-slate-950 dark:text-white text-sm font-semibold">{{ $t("Quantity") }}</h4>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="inline-flex items-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                                    <button class="w-10 h-10 flex items-center justify-center bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-700 transition disabled:opacity-40"
                                                        :disabled="quantity <= minQty" @click="decrementQty">
                                                        <MinusIcon class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                                    </button>
                                                    <div class="w-14 text-center text-slate-950 dark:text-white text-base font-semibold border-x border-slate-200 dark:border-slate-700 py-2">
                                                        {{ quantity }}
                                                    </div>
                                                    <button class="w-10 h-10 flex items-center justify-center bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-700 transition disabled:opacity-40"
                                                        :disabled="quantity >= details.preorder_quantity_limit" @click="incrementQty">
                                                        <PlusIcon class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                                    </button>
                                                </div>
                                                <p class="text-xs text-slate-400 text-right">
                                                    {{ $t("Min") }} {{ minQty }} · {{ $t("Max") }} {{ details.preorder_quantity_limit }}
                                                </p>
                                            </div>
                                        </section>

                                        <!-- Step 2 : Shipping address -->
                                        <section>
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-2">
                                                    <h4 class="text-slate-950 dark:text-white text-sm font-semibold">{{ $t("Shipping Address") }}</h4>
                                                </div>
                                                <router-link v-if="authStore.addresses.length > 0" :to="{ name: 'add-new-address' }"
                                                    class="text-primary text-xs font-medium" @click="close">
                                                    + {{ $t("New Address") }}
                                                </router-link>
                                            </div>

                                            <div v-if="authStore.addresses.length === 0"
                                                class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 px-4 py-6 text-center">
                                                <MapPinIcon class="w-6 h-6 text-slate-300 dark:text-slate-500 mx-auto mb-1" />
                                                <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $t("No saved address found") }}</p>
                                                <router-link :to="{ name: 'add-new-address' }" @click="close"
                                                    class="inline-block mt-2 text-primary text-sm font-semibold">
                                                    + {{ $t("Add New Address") }}
                                                </router-link>
                                            </div>

                                            <div v-else class="space-y-2 max-h-44 overflow-y-auto pr-1">
                                                <label v-for="address in authStore.addresses" :key="address.id"
                                                    :for="'pre-address-' + address.id"
                                                    class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition"
                                                    :class="selectedAddressId == address.id
                                                        ? 'border-primary bg-primary-50 dark:bg-slate-700'
                                                        : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                                                    <input type="radio" name="pre-address" :id="'pre-address-' + address.id"
                                                        class="radioBtn2 mt-1" :value="address.id" v-model="selectedAddressId" />
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-slate-900 dark:text-white text-sm font-medium">{{ address.name }}</span>
                                                            <span class="px-1.5 py-[1px] bg-slate-800 dark:bg-slate-600 rounded text-white text-[10px] font-medium uppercase">{{ address.address_type }}</span>
                                                        </div>
                                                        <div class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ address.phone }}</div>
                                                        <div class="text-slate-500 dark:text-slate-400 text-xs truncate">
                                                            {{ (address.flat_no ? address.flat_no + ', ' : '') + address.address_line + (address.area ? ', ' + address.area : '') }}
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </section>

                                        <!-- Step 3 : Payment method -->
                                        <section>
                                            <div class="flex items-center gap-2 mb-3">
                                                <h4 class="text-slate-950 dark:text-white text-sm font-semibold">{{ $t("Payment Method") }}</h4>
                                            </div>

                                            <div class="space-y-2">
                                                <!-- Cash (hidden for prepay) -->
                                                <label v-if="!details.is_prepay && master.cashOnDelivery" for="pre-cash"
                                                    class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                                                    :class="paymentType === 'cash'
                                                        ? 'border-primary bg-primary-50 dark:bg-slate-700'
                                                        : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                                                    <input v-model="paymentType" id="pre-cash" name="pre-payment" type="radio"
                                                        class="radioBtn2" value="cash" />
                                                    <div class="p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 shrink-0">
                                                        <img :src="'/assets/icons/money-2.svg'" alt="" class="w-5 h-5" />
                                                    </div>
                                                    <div class="text-slate-950 dark:text-white text-sm font-medium">{{ $t("Cash on Delivery") }}</div>
                                                </label>

                                                <!-- Online -->
                                                <div v-if="master.onlinePayment"
                                                    class="rounded-xl border transition"
                                                    :class="paymentType === 'card'
                                                        ? 'border-primary bg-primary-50 dark:bg-slate-700'
                                                        : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                                                    <label for="pre-card" class="flex items-center gap-3 p-3 cursor-pointer">
                                                        <input v-model="paymentType" id="pre-card" name="pre-payment" type="radio"
                                                            class="radioBtn2" value="card" />
                                                        <div class="p-2 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 shrink-0">
                                                            <img :src="'/assets/icons/card.svg'" alt="" class="w-5 h-5" />
                                                        </div>
                                                        <div class="grow text-slate-950 dark:text-white text-sm font-medium">
                                                            {{ details.is_prepay ? $t("Pay Prepayment Online") : $t("Online Payment") }}
                                                        </div>
                                                    </label>
                                                    <div v-if="paymentType === 'card'" class="px-3 pb-3">
                                                        <div class="grid grid-cols-3 xs:grid-cols-4 gap-2 pt-3 border-t border-primary-100 dark:border-slate-600">
                                                            <label v-for="gateway in master.paymentGateways" :key="gateway.id"
                                                                :for="'pre-gw-' + gateway.name"
                                                                class="flex items-center justify-center border relative has-[:checked]:border-primary has-[:checked]:shadow-md dark:has-[:checked]:bg-slate-600 p-2 rounded-md bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 cursor-pointer">
                                                                <input v-model="paymentGateway" :id="'pre-gw-' + gateway.name"
                                                                    name="pre-gateway" type="radio" class="sr-only" :value="gateway.name" />
                                                                <img :src="gateway.logo" alt="" class="w-full h-6 object-contain" />
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <!-- Note -->
                                        <section>
                                            <label class="flex items-center gap-2 mb-2 text-slate-950 dark:text-white text-sm font-semibold">
                                                {{ $t("Note") }} <span class="text-slate-400 font-normal">({{ $t("Optional") }})</span>
                                            </label>
                                            <textarea v-model="note" rows="2"
                                                class="p-3 rounded-lg border border-slate-200 dark:border-slate-700 focus:border-primary w-full outline-none text-sm bg-white dark:bg-slate-700 dark:text-white placeholder:text-slate-400"
                                                :placeholder="$t('Write your note') + '...'"></textarea>
                                        </section>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="shrink-0 border-t border-slate-100 dark:border-slate-700 px-5 sm:px-6 py-4">
                                <p v-if="!details.is_available" class="mb-2 text-center text-red-500 text-sm font-medium">
                                    {{ $t("This product is currently not available for pre-order") }}
                                </p>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                                    <div class="flex items-center justify-between sm:block shrink-0">
                                        <div class="text-slate-400 text-[11px] font-medium uppercase tracking-wide">
                                            {{ details.is_prepay ? $t("Pay Now (Prepayment)") : $t("Total Payable") }}
                                        </div>
                                        <div class="text-primary text-xl font-bold leading-tight">
                                            {{ master.showCurrency(parseFloat(details.is_prepay ? amounts.prePayableAmount : amounts.payableAmount).toFixed(2)) }}
                                        </div>
                                    </div>
                                    <button v-if="!isProcessing"
                                        class="w-full sm:w-auto sm:min-w-[220px] px-6 py-3.5 bg-primary rounded-xl text-white text-base font-semibold whitespace-nowrap hover:bg-primary-700 transition active:scale-[.99] disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="!details.is_available"
                                        @click="confirmPreOrder">
                                        {{ $t("Confirm Pre-Order") }}
                                    </button>
                                    <button v-else disabled
                                        class="w-full sm:w-auto sm:min-w-[220px] px-6 py-3.5 bg-primary-200 rounded-xl text-primary text-base font-semibold whitespace-nowrap flex items-center justify-center gap-2">
                                        {{ $t("Processing") }}
                                        <LoadingSpin />
                                    </button>
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
import { computed, ref, watch } from "vue";
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { XMarkIcon, MinusIcon, PlusIcon, ClockIcon, TruckIcon, ChevronDownIcon, ArrowPathIcon, ShieldCheckIcon, MapPinIcon } from "@heroicons/vue/24/outline";
import { useToast } from "vue-toastification";
import { useRouter } from "vue-router";

import { useAuth } from "../stores/AuthStore";
import { useMaster } from "../stores/MasterStore";
import { useMetaPixel } from "../composables/useMetaPixel";
import LoadingSpin from "./LoadingSpin.vue";
import localization from "../localization";

const props = defineProps({
    show: { type: Boolean, default: false },
    product: { type: Object, default: () => ({}) },
    details: { type: Object, default: () => ({}) },
});
const emit = defineEmits(["close"]);

const master = useMaster();
const authStore = useAuth();
const toast = useToast();
const router = useRouter();
const t = localization.i18n.global.t;

const quantity = ref(1);
const minQty = computed(() => Number(props.details?.min_order_quantity) || 1);
const selectedAddressId = ref(null);
const note = ref("");
const isProcessing = ref(false);

const paymentType = ref("cash");
const paymentGateway = ref(null);

const amounts = ref({ totalAmount: 0, deliveryCharge: 0, payableAmount: 0, prePayableAmount: 0 });

const { trackInitiateCheckout, trackPurchase } = useMetaPixel();

/**
 * Pre-orders report the full order value, not the (possibly partial) prepaid
 * amount — that is what the customer has committed to buying.
 */
const preOrderSnapshot = () => ({
    products: [{ ...props.product, quantity: quantity.value }],
    value: amounts.value.payableAmount,
});

// product display helpers
const productImage = computed(() => {
    const list = props.product?.thumbnails ?? [];
    const found = Array.isArray(list) ? list.find((tmb) => tmb?.thumbnail) : null;
    return found?.thumbnail ?? "/default/default.jpg";
});
const unitLabel = computed(() => props.product?.unit?.name || t("unit"));
const unitPrice = computed(() => {
    const p = props.product ?? {};
    return Number(p.discount_price) > 0 ? p.discount_price : p.price;
});

const toastPosition = () => (master.langDirection === "rtl" ? "bottom-right" : "bottom-left");

const close = () => emit("close");

// initialise the modal whenever it opens
watch(
    () => props.show,
    async (open) => {
        if (!open) return;
        checkoutTracked = false;
        quantity.value = minQty.value;
        note.value = "";
        // prepay products can only pay online
        paymentType.value = props.details?.is_prepay ? "card" : (master.cashOnDelivery ? "cash" : "card");
        paymentGateway.value = null;

        if (authStore.addresses.length === 0) {
            await authStore.fetchAddresses();
        }
        const def = authStore.addresses.find((a) => a.is_default) ?? authStore.addresses[0];
        selectedAddressId.value = def?.id ?? null;

        fetchAmounts();
    }
);

watch([quantity, selectedAddressId], () => {
    if (props.show) fetchAmounts();
});

// safety net: preselect default address once the list loads
watch(
    () => authStore.addresses,
    (addrs) => {
        if (props.show && !selectedAddressId.value && addrs.length) {
            const def = addrs.find((a) => a.is_default) ?? addrs[0];
            selectedAddressId.value = def?.id ?? null;
        }
    },
    { deep: true }
);

const incrementQty = () => {
    const limit = props.details?.preorder_quantity_limit ?? 1;
    if (quantity.value < limit) quantity.value++;
    else toast.error(t("Maximum pre-order quantity reached"), { position: toastPosition() });
};

const decrementQty = () => {
    if (quantity.value > minQty.value) quantity.value--;
    else toast.error(t("Minimum pre-order quantity reached"), { position: toastPosition() });
};

const resolvePaymentMethod = () => {
    if (paymentType.value === "cash") return "Cash Payment";
    return paymentGateway.value; // gateway name e.g. "Stripe"
};

/** InitiateCheckout fires once per modal open, not on every amount refetch. */
let checkoutTracked = false;

const fetchAmounts = () => {
    if (!selectedAddressId.value) return;
    axios
        .post(
            "/pre-orders/checkout",
            {
                product_id: props.product.id,
                address_id: selectedAddressId.value,
                quantity: quantity.value,
            },
            { headers: { Authorization: authStore.token } }
        )
        .then((response) => {
            const c = response.data.data.checkout;
            amounts.value = {
                totalAmount: c.totalAmount ?? 0,
                deliveryCharge: c.deliveryCharge ?? 0,
                payableAmount: c.payableAmount ?? 0,
                prePayableAmount: c.prePayableAmount ?? 0,
            };

            if (!checkoutTracked) {
                checkoutTracked = true;
                trackInitiateCheckout(preOrderSnapshot());
            }
        })
        .catch(() => {});
};

const confirmPreOrder = () => {
    if (!authStore.token) {
        close();
        authStore.loginModal = true;
        return;
    }
    if (!selectedAddressId.value) {
        toast.error(t("Please select shipping address"), { position: toastPosition() });
        return;
    }
    const paymentMethod = resolvePaymentMethod();
    if (paymentMethod == null) {
        toast.error(t("Please select payment method"), { position: toastPosition() });
        return;
    }

    isProcessing.value = true;
    axios
        .post(
            "/pre-orders/store",
            {
                product_id: props.product.id,
                quantity: quantity.value,
                customer_note: note.value,
                address_id: selectedAddressId.value,
                payment_method: paymentMethod,
                is_prepay: props.details?.is_prepay ? 1 : 0,
            },
            { headers: { Authorization: authStore.token } }
        )
        .then((response) => {
            isProcessing.value = false;
            const paymentUrl = response.data.data.order_payment_url;
            if (paymentUrl != null) {
                openPaymentPopupWindow(paymentUrl);
            } else {
                onPreOrderSuccess();
            }
        })
        .catch((error) => {
            isProcessing.value = false;
            toast.error(error.response?.data?.message ?? t("Something went wrong"), { position: toastPosition() });
        });
};

const onPreOrderSuccess = () => {
    // A pre-order is a real, paid-for conversion, so it reports as a Purchase.
    // Product and amounts are still intact here — nothing is reset before this.
    trackPurchase(preOrderSnapshot());
    toast.success(t("Pre-order placed successfully"), { position: toastPosition() });
    close();
};

const openPaymentPopupWindow = (url) => {
    const winWidth = 700;
    const winHeight = 700;
    const left = screen.width / 2 - winWidth / 2;
    const top = screen.height / 2 - winHeight / 2;
    const options = `popup,resizable,height=${winHeight},width=${winWidth},top=${top},left=${left}`;
    const win = window.open(url, null, options);

    if (!win) {
        toast.error(t("Please allow popups to complete payment"), { position: toastPosition() });
        return;
    }
    win.focus();

    const intervalID = setInterval(() => {
        try {
            if (win.closed) {
                clearInterval(intervalID);
                toast.error(t("Payment Canceled"), { position: toastPosition() });
                return;
            }
            const pathname = win.location.pathname;
            const currentPath = pathname.replace(/\/order\/\d+/, "");
            if (currentPath === "/payment/cancel") {
                clearInterval(intervalID);
                setTimeout(() => win.close(), 4000);
                toast.error(t("Payment Canceled"), { position: toastPosition() });
            }
            if (currentPath === "/payment/success") {
                clearInterval(intervalID);
                win.close();
                onPreOrderSuccess();
            }
        } catch (e) {
            // cross-origin while on gateway domain — ignore until it returns
        }
    }, 1000);

    // hard stop after 3 minutes
    setTimeout(() => {
        clearInterval(intervalID);
        if (!win.closed) win.close();
    }, 180000);
};
</script>

<style scoped>
.radioBtn2 {
    @apply w-4 h-4 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}

summary::-webkit-details-marker {
    display: none;
}
</style>
