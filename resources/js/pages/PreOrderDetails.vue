<template>
    <div>
        <!-- Breadcrumb -->
        <div
            class="bg-white dark:bg-slate-800 px-3 text-slate-600 dark:text-slate-400 flex items-center gap-1 pt-2">
            <HomeIcon class="w-5 h-5 md:w-6 md:h-6" />
            <router-link to="/pre-order-history" class="leading-normal hover:text-primary">
                {{ $t("Pre-orders") }}
            </router-link>
            <span class="leading-normal">/ {{ $t("Order Details") }}</span>
        </div>

        <!-- Header -->
        <div
            class="py-3 px-3 bg-white dark:bg-slate-800 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="text-slate-800 dark:text-white text-lg font-medium tracking-tight">
                    {{ $t("Order") }} #{{ order.order_code }}
                </span>
                <span
                    class="text-xs sm:text-sm font-medium px-2.5 py-1 rounded-full inline-block text-white"
                    :class="statusColor(order.order_status)">
                    {{ order.order_status }}
                </span>
            </div>
            <div class="text-slate-500 dark:text-slate-400 text-sm">
                {{ $t("Placed on") }} {{ order.placed_at }}
            </div>
        </div>

        <div class="px-2 pt-2 md:px-4 md:pt-4 lg:px-6 lg:pt-6 space-y-4 md:space-y-6">

            <!-- Order Status Progress -->
            <div class="p-4 md:p-6 bg-white dark:bg-slate-800 rounded-xl md:rounded-2xl">
                <div
                    class="text-slate-950 dark:text-white text-base md:text-lg font-medium mb-6">
                    {{ $t("Order Status") }}
                </div>

                <!-- Cancelled / Refunded banner -->
                <div v-if="isCancelledStatus(order.order_status)"
                    class="flex items-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20">
                    <XCircleIcon class="w-8 h-8 text-red-500 shrink-0" />
                    <div>
                        <div class="text-red-600 dark:text-red-400 font-medium">
                            {{ order.order_status == 'Refund' ? $t("Order Refunded") : $t("Order Cancelled") }}
                        </div>
                        <div class="text-slate-500 dark:text-slate-400 text-sm">
                            {{ $t("This pre-order is no longer being processed.") }}
                        </div>
                    </div>
                </div>

                <!-- Progress stepper -->
                <div v-else class="flex overflow-x-auto pb-2">
                    <template v-for="(step, index) in progress.steps" :key="step">
                        <!-- Node -->
                        <div class="flex flex-col items-center shrink-0 w-24 md:w-auto md:flex-1">
                            <div class="flex items-center w-full">
                                <!-- left connector -->
                                <div class="h-0.5 flex-1"
                                    :class="index === 0 ? 'bg-transparent' : (index <= progress.currentIndex ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-700')">
                                </div>
                                <!-- circle -->
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 border-2 transition-colors"
                                    :class="stepCircleClass(index)">
                                    <CheckIcon v-if="index < progress.currentIndex"
                                        class="w-5 h-5 text-white" />
                                    <span v-else class="text-xs font-semibold"
                                        :class="index === progress.currentIndex ? 'text-white' : 'text-slate-400'">
                                        {{ index + 1 }}
                                    </span>
                                </div>
                                <!-- right connector -->
                                <div class="h-0.5 flex-1"
                                    :class="index === progress.steps.length - 1 ? 'bg-transparent' : (index < progress.currentIndex ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-700')">
                                </div>
                            </div>
                            <div class="mt-2 text-center text-xs md:text-sm leading-tight px-1"
                                :class="index <= progress.currentIndex ? 'text-slate-950 dark:text-white font-medium' : 'text-slate-400'">
                                {{ $t(step) }}
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Details grid -->
            <div class="grid grid-cols-3 gap-4 md:gap-6">
                <!-- Left column -->
                <div class="col-span-3 lg:col-span-2 space-y-4 md:space-y-6">

                    <!-- Product -->
                    <div
                        class="p-4 md:p-6 bg-white dark:bg-slate-800 rounded-xl md:rounded-2xl">
                        <div
                            class="text-slate-950 dark:text-white text-base md:text-lg font-medium mb-4">
                            {{ $t("Product") }}
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="grow">
                                <router-link v-if="order.item?.product_slug"
                                    :to="'/products/' + order.item.product_slug + '/details'"
                                    class="text-slate-950 dark:text-white text-sm md:text-base font-medium leading-snug hover:text-primary">
                                    {{ order.item?.product_name }}
                                </router-link>
                                <span v-else
                                    class="text-slate-950 dark:text-white text-sm md:text-base font-medium leading-snug">
                                    {{ order.item?.product_name }}
                                </span>

                                <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm">
                                    <div class="text-slate-500 dark:text-slate-400">
                                        {{ $t("Unit Price") }}:
                                        <span class="text-slate-950 dark:text-white">
                                            {{ order.currency_symbol }}{{ fmt(order.item?.price) }}
                                        </span>
                                    </div>
                                    <div v-if="order.item?.discount > 0"
                                        class="text-slate-500 dark:text-slate-400">
                                        {{ $t("Discount") }}:
                                        <span class="text-green-600">
                                            -{{ order.currency_symbol }}{{ fmt(order.item?.discount) }}
                                        </span>
                                    </div>
                                    <div class="text-slate-500 dark:text-slate-400">
                                        {{ $t("Quantity") }}:
                                        <span class="text-slate-950 dark:text-white">
                                            {{ order.item?.quantity }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $t("Total") }}
                                </div>
                                <div
                                    class="text-slate-950 dark:text-white text-base md:text-lg font-semibold">
                                    {{ order.currency_symbol }}{{ fmt(order.item?.total_price) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shop -->
                    <div v-if="order.shop?.id"
                        class="p-4 md:p-6 bg-white dark:bg-slate-800 rounded-xl md:rounded-2xl">
                        <div
                            class="text-slate-500 dark:text-slate-400 text-xs md:text-sm mb-3">
                            {{ $t("Purchased from") }}
                        </div>
                        <div class="flex items-center gap-4">
                            <img class="w-12 h-12 rounded-full object-cover"
                                :src="order.shop?.logo" :alt="order.shop?.name" />
                            <div>
                                <div
                                    class="text-slate-950 dark:text-white text-sm md:text-base font-medium">
                                    {{ order.shop?.name }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <StarIcon class="w-4 h-4 text-yellow-400" />
                                    <span
                                        class="text-slate-800 dark:text-slate-100 text-xs md:text-sm">
                                        {{ order.shop?.rating }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div v-if="order.address?.name"
                        class="p-4 md:p-6 bg-white dark:bg-slate-800 rounded-xl md:rounded-2xl">
                        <div
                            class="text-slate-950 dark:text-white text-base md:text-lg font-medium mb-3">
                            {{ $t("Delivery Address") }}
                        </div>
                        <div class="flex items-start gap-3">
                            <MapPinIcon class="w-5 h-5 text-primary shrink-0 mt-0.5" />
                            <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                <div class="text-slate-950 dark:text-white font-medium">
                                    {{ order.address?.name }}
                                    <span v-if="order.address?.phone" class="text-slate-500 dark:text-slate-400 font-normal">
                                        · {{ order.address?.phone }}
                                    </span>
                                </div>
                                <div>
                                    <span v-if="order.address?.flat_no">{{ order.address?.flat_no }}, </span>
                                    {{ order.address?.address_line }}
                                    <span v-if="order.address?.address_line2">, {{ order.address?.address_line2 }}</span>
                                </div>
                                <div>
                                    <span v-if="order.address?.area">{{ order.address?.area }}</span>
                                    <span v-if="order.address?.post_code"> - {{ order.address?.post_code }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column: Summary -->
                <div class="col-span-3 lg:col-span-1">
                    <div
                        class="p-4 md:p-6 bg-white dark:bg-slate-800 rounded-xl md:rounded-2xl space-y-4">
                        <div
                            class="text-slate-950 dark:text-white text-base md:text-lg font-medium">
                            {{ $t("Payment Summary") }}
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">{{ $t("Order ID") }}</span>
                                <span class="text-blue-500 font-medium">{{ order.order_code }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">{{ $t("Payment Method") }}</span>
                                <span class="text-slate-950 dark:text-white">{{ order.payment_method }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">{{ $t("Payment Status") }}</span>
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full text-white"
                                    :class="order.payment_status == 'Paid' ? 'bg-green-500' : 'bg-amber-500'">
                                    {{ order.payment_status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">{{ $t("Quantity") }}</span>
                                <span class="text-slate-950 dark:text-white">{{ order.quantity }}</span>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-700 pt-3 flex items-center justify-between">
                            <span class="text-slate-950 dark:text-white font-medium">{{ $t("Total Amount") }}</span>
                            <span class="text-primary text-lg font-semibold">
                                {{ order.currency_symbol }}{{ fmt(order.amount) }}
                            </span>
                        </div>

                        <!-- Complete payment online -->
                        <div v-if="order.is_online_payable"
                            class="border-t border-slate-100 dark:border-slate-700 pt-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-950 dark:text-white font-medium">
                                    {{ order.payment_status == 'Prepaid' ? $t("Remaining Balance") : $t("Amount Due") }}
                                </span>
                                <span class="text-amber-600 dark:text-amber-500 text-lg font-semibold">
                                    {{ order.currency_symbol }}{{ fmt(order.due_amount) }}
                                </span>
                            </div>
                            <div class="text-slate-500 dark:text-slate-400 text-xs">
                                {{ order.payment_status == 'Prepaid' ? $t("Pay the remaining balance of your pre-order") : $t("Select a payment method to pay online") }}
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <label v-for="gateway in master.paymentGateways" :key="gateway.id"
                                    :for="'pay-gw-' + gateway.name"
                                    class="flex items-center justify-center border relative has-[:checked]:border-primary has-[:checked]:shadow-md dark:has-[:checked]:bg-slate-600 p-2 rounded-md bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 cursor-pointer">
                                    <input v-model="selectedGateway" :id="'pay-gw-' + gateway.name" name="pay-gateway"
                                        type="radio" class="sr-only" :value="gateway.name" />
                                    <img :src="gateway.logo" alt="" class="w-full h-6 object-contain" />
                                </label>
                            </div>
                            <button type="button" :disabled="isProcessing" @click="payNow"
                                class="w-full h-11 px-4 rounded-lg bg-primary text-white text-sm font-medium flex items-center justify-center gap-2 transition-all hover:opacity-90 disabled:opacity-60">
                                <LoadingSpin v-if="isProcessing" />
                                <span v-else>{{ $t("Pay Now") }} · {{ order.currency_symbol }}{{ fmt(order.due_amount) }}</span>
                            </button>
                        </div>

                        <!-- Refund status -->
                        <div v-if="order.refund_status"
                            class="border-t border-slate-100 dark:border-slate-700 pt-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-950 dark:text-white font-medium">{{ $t("Refund") }}</span>
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full text-white"
                                    :class="refundBadgeClass">
                                    {{ $t(order.refund_status_label || order.refund_status) }}
                                </span>
                            </div>
                            <div v-if="order.refund_status == 'Requested'" class="text-slate-500 dark:text-slate-400 text-xs">
                                {{ $t("Your refund request is awaiting admin approval.") }}
                            </div>
                            <div v-else-if="order.refund_status == 'Approved'" class="text-slate-500 dark:text-slate-400 text-xs">
                                {{ $t("Your refund has been approved and is being processed.") }}
                            </div>
                            <div v-else-if="order.refund_status == 'Refunded'"
                                class="flex items-center justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">{{ $t("Refunded Amount") }}</span>
                                <span class="text-green-600 font-semibold">
                                    {{ order.currency_symbol }}{{ fmt(order.refund_amount) }}
                                </span>
                            </div>
                            <div v-else-if="order.refund_status == 'Rejected'"
                                class="text-red-500 text-xs">
                                {{ order.refund_note ? $t("Rejected") + ": " + order.refund_note : $t("Your refund request was rejected.") }}
                            </div>
                        </div>

                        <!-- Request refund -->
                        <div v-if="order.is_refund_requestable"
                            class="border-t border-slate-100 dark:border-slate-700 pt-3 space-y-3">
                            <template v-if="!showRefundForm">
                                <button type="button" @click="showRefundForm = true"
                                    class="w-full h-10 px-4 rounded-lg outline outline-1 outline-offset-[-1px] outline-red-400 flex items-center justify-center gap-2 text-red-500 text-sm font-medium transition-all hover:bg-red-500 hover:text-white">
                                    <ArrowUturnLeftIcon class="w-5 h-5" />
                                    {{ $t("Request Refund") }}
                                </button>
                            </template>
                            <template v-else>
                                <div class="text-slate-950 dark:text-white font-medium text-sm">
                                    {{ $t("Request Refund") }}
                                </div>
                                <textarea v-model="refundReason" rows="3"
                                    :placeholder="$t('Tell us why you want a refund (optional)')"
                                    class="w-full text-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-950 dark:text-white p-2 focus:outline-none focus:border-primary"></textarea>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="showRefundForm = false"
                                        class="flex-1 h-10 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-medium">
                                        {{ $t("Cancel") }}
                                    </button>
                                    <button type="button" :disabled="isRefunding" @click="requestRefund"
                                        class="flex-1 h-10 rounded-lg bg-red-500 text-white text-sm font-medium flex items-center justify-center gap-2 disabled:opacity-60">
                                        <LoadingSpin v-if="isRefunding" />
                                        <span v-else>{{ $t("Submit") }}</span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Cancel order -->
                        <div v-if="order.is_cancellable"
                            class="border-t border-slate-100 dark:border-slate-700 pt-3 space-y-3">
                            <template v-if="!showCancelConfirm">
                                <button type="button" @click="showCancelConfirm = true"
                                    class="w-full h-10 px-4 rounded-lg outline outline-1 outline-offset-[-1px] outline-red-400 flex items-center justify-center gap-2 text-red-500 text-sm font-medium transition-all hover:bg-red-500 hover:text-white">
                                    <XCircleIcon class="w-5 h-5" />
                                    {{ $t("Cancel Order") }}
                                </button>
                            </template>
                            <template v-else>
                                <div class="text-slate-950 dark:text-white font-medium text-sm">
                                    {{ $t("Cancel this pre-order?") }}
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $t("Any amount you already paid will be refunded to you.") }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="showCancelConfirm = false"
                                        class="flex-1 h-10 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-medium">
                                        {{ $t("No, keep it") }}
                                    </button>
                                    <button type="button" :disabled="isCancelling" @click="cancelOrder"
                                        class="flex-1 h-10 rounded-lg bg-red-500 text-white text-sm font-medium flex items-center justify-center gap-2 disabled:opacity-60">
                                        <LoadingSpin v-if="isCancelling" />
                                        <span v-else>{{ $t("Yes, cancel") }}</span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Downloads -->
                        <div class="space-y-2 pt-1">
                            <a v-if="order.invoice_url" :href="order.invoice_url" target="_blank"
                                class="w-full h-10 px-4 rounded-lg outline outline-1 outline-offset-[-1px] outline-primary flex items-center justify-center gap-2 text-primary text-sm font-medium transition-all hover:bg-primary hover:text-white">
                                <DocumentArrowDownIcon class="w-5 h-5" />
                                {{ $t("Download Invoice") }}
                            </a>
                            <a v-if="order.payment_status == 'Paid' && order.payment_receipt_url"
                                :href="order.payment_receipt_url" target="_blank"
                                class="w-full h-10 px-4 rounded-lg bg-primary/10 flex items-center justify-center gap-2 text-primary text-sm font-medium transition-all hover:bg-primary hover:text-white">
                                <ReceiptPercentIcon class="w-5 h-5" />
                                {{ $t("Payment Receipt") }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    HomeIcon,
    MapPinIcon,
    XCircleIcon,
    CheckIcon,
    DocumentArrowDownIcon,
    ReceiptPercentIcon,
    ArrowUturnLeftIcon,
} from "@heroicons/vue/24/outline";
import { StarIcon } from "@heroicons/vue/24/solid";
import { useToast } from "vue-toastification";

import { useAuth } from "../stores/AuthStore";
import { useMaster } from "../stores/MasterStore";
import LoadingSpin from "../components/LoadingSpin.vue";
import localization from "../localization";
import { statusColor, isCancelledStatus, progressFor } from "../utils/preOrderStatus";

const authStore = useAuth();
const master = useMaster();
const toast = useToast();
const route = useRoute();
const router = useRouter();
const t = localization.i18n.global.t;
const toastPosition = () => (master.langDirection === "rtl" ? "bottom-right" : "bottom-left");

const order = ref({});
const selectedGateway = ref(null);
const isProcessing = ref(false);
const showRefundForm = ref(false);
const refundReason = ref("");
const isRefunding = ref(false);
const showCancelConfirm = ref(false);
const isCancelling = ref(false);

const progress = computed(() => progressFor(order.value.order_status));

const refundBadgeClass = computed(() => {
    switch (order.value.refund_status) {
        case "Refunded":
            return "bg-green-500";
        case "Approved":
            return "bg-blue-500";
        case "Rejected":
            return "bg-red-500";
        default:
            return "bg-amber-500";
    }
});

const stepCircleClass = (index) => {
    if (index < progress.value.currentIndex) return "bg-primary border-primary";
    if (index === progress.value.currentIndex) return "bg-primary border-primary";
    return "bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700";
};

const fmt = (value) =>
    Number(value ?? 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

onMounted(() => {
    fetchOrderDetails();
    window.scrollTo(0, 0, { behavior: "smooth" });
});

const fetchOrderDetails = async () => {
    axios
        .get("/pre-orders/details/" + route.params.id, {
            headers: {
                Authorization: authStore.token,
            },
        })
        .then((response) => {
            order.value = response.data.data.preOrder;
        })
        .catch((error) => {
            if (error.response?.status === 401) {
                authStore.token = null;
                authStore.user = null;
                authStore.addresses = [];
                authStore.favoriteProducts = 0;
                router.push("/");
            } else if (error.response?.status === 404) {
                router.push("/pre-order-history");
            }
        });
};

const payNow = () => {
    if (!selectedGateway.value) {
        toast.error(t("Please select payment method"), { position: toastPosition() });
        return;
    }

    isProcessing.value = true;
    axios
        .post(
            "/pre-orders/pay",
            {
                pre_order_id: order.value.id,
                payment_method: selectedGateway.value,
            },
            { headers: { Authorization: authStore.token } }
        )
        .then((response) => {
            isProcessing.value = false;
            const paymentUrl = response.data.data.order_payment_url;
            if (paymentUrl) {
                openPaymentPopupWindow(paymentUrl);
            }
        })
        .catch((error) => {
            isProcessing.value = false;
            toast.error(error.response?.data?.message ?? t("Something went wrong"), { position: toastPosition() });
        });
};

const requestRefund = () => {
    isRefunding.value = true;
    axios
        .post(
            "/pre-orders/refund-request",
            {
                pre_order_id: order.value.id,
                reason: refundReason.value,
            },
            { headers: { Authorization: authStore.token } }
        )
        .then((response) => {
            isRefunding.value = false;
            showRefundForm.value = false;
            refundReason.value = "";
            toast.success(response.data.message ?? t("Refund request submitted successfully"), { position: toastPosition() });
            if (response.data.data?.preOrder) {
                order.value = response.data.data.preOrder;
            } else {
                fetchOrderDetails();
            }
        })
        .catch((error) => {
            isRefunding.value = false;
            toast.error(error.response?.data?.message ?? t("Something went wrong"), { position: toastPosition() });
        });
};

const cancelOrder = () => {
    isCancelling.value = true;
    axios
        .post(
            "/pre-orders/cancel",
            { pre_order_id: order.value.id },
            { headers: { Authorization: authStore.token } }
        )
        .then((response) => {
            isCancelling.value = false;
            showCancelConfirm.value = false;
            toast.success(response.data.message ?? t("Order cancelled successfully"), { position: toastPosition() });
            if (response.data.data?.preOrder) {
                order.value = response.data.data.preOrder;
            } else {
                fetchOrderDetails();
            }
        })
        .catch((error) => {
            isCancelling.value = false;
            toast.error(error.response?.data?.message ?? t("Something went wrong"), { position: toastPosition() });
        });
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
                toast.success(t("Payment successful"), { position: toastPosition() });
                selectedGateway.value = null;
                fetchOrderDetails();
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
