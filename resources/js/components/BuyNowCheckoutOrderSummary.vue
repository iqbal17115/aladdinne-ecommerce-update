<template>
    <div>
        <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="text-slate-950 dark:text-white text-lg font-medium leading-7">
                {{ $t("Order Summary") }}
            </div>

            <!-- Subtotal -->
            <div class="my-4 flex justify-between gap-4">
                <div
                    class="text-slate-950 dark:text-slate-200 text-base font-normal leading-normal"
                >
                    {{ $t("Subtotal") }}
                </div>
                <div
                    class="text-slate-950 dark:text-slate-200 text-base font-normal leading-normal"
                >
                    {{ master.showCurrency(orderData.total_amount) }}
                </div>
            </div>

            <!-- Discount -->
            <div v-if="authStore.user" class="my-4 flex justify-between gap-4">
                <div class="text-red-500 text-base font-normal leading-normal">
                    {{ $t("Discount") }}
                </div>
                <div
                    class="text-slate-950 dark:text-slate-200 text-base font-normal leading-normal"
                >
                    -{{ master.showCurrency(orderData.coupon_discount) }}
                </div>
            </div>

            <div
                v-if="authStore.user"
                class="w-full h-[0px] border-t border-dashed border-slate-400"
            ></div>

            <!-- Subtotal After Discount -->
            <div v-if="authStore.user" class="my-4 flex justify-between gap-4">
                <div
                    class="text-slate-950 dark:text-slate-200 text-base font-normal leading-normal"
                >
                    {{ $t("Subtotal After Discount") }}
                </div>
                <div
                    class="text-slate-950 dark:text-slate-200 text-base font-normal leading-normal"
                >
                    {{
                        master.showCurrency(
                            (
                                orderData.total_amount -
                                orderData.coupon_discount
                            ).toFixed(2)
                        )
                    }}
                </div>
            </div>

            <!-- Shipping Charge -->
            <div class="my-4 flex justify-between gap-4">
                <div
                    class="text-slate-950 dark:text-slate-200 text-base font-normal leading-normal"
                >
                    {{ $t("Shipping Charge") }}
                </div>
                <div
                    class="text-slate-950 dark:text-slate-200 text-base font-normal leading-normal"
                >
                    {{ master.showCurrency(orderData.delivery_charge) }}
                </div>
            </div>

            <div
                v-if="
                    orderData.all_vat_taxes?.length > 0 ||
                    orderData.order_tax_amount > 0
                "
                class="p-3 bg-slate-100 dark:bg-slate-700 text-black dark:text-slate-200 rounded-lg mb-2"
            >
                <h2 class="text-sm sm:text-base font-medium mb-2">
                    {{ $t("VAT & Taxes Summary") }}
                </h2>

                <div class="space-y-2">
                    <div
                        v-for="vatTax in orderData.all_vat_taxes"
                        :key="vatTax.id"
                        class="flex justify-between bg-slate-200 dark:bg-slate-600 p-2 rounded-lg"
                    >
                        <span class="font-medium">
                            {{ vatTax.name }}
                            <small>({{ vatTax.percentage }}%)</small>
                        </span>
                        <span class="font-medium">
                            {{ master.showCurrency(vatTax.amount) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-slate-200 dark:bg-slate-600 rounded-lg">
                    <h3 class="text-sm sm:text-base font-medium">
                        {{ $t("Total Tax Amount") }}:
                    </h3>
                    <p class="text-base font-bold">
                        {{ master.showCurrency(orderData.order_tax_amount) }}
                    </p>
                </div>
            </div>

            <div class="w-full h-[0px] border border-slate-500 dark:border-slate-600"></div>

            <!-- Total Payable -->
            <div class="my-4 flex justify-between gap-4">
                <div
                    class="text-slate-950 dark:text-white text-lm font-medium leading-normal tracking-tight"
                >
                    {{ $t("Total Payable") }}
                </div>
                <div
                    class="text-slate-950 dark:text-white text-lg font-medium leading-normal tracking-tight"
                >
                    {{ master.showCurrency(orderData.payable_amount) }}
                </div>
            </div>

            <!-- Have a coupon -->
            <div v-if="authStore.user" class="p-4 mt-6 bg-slate-100 dark:bg-slate-700 rounded-xl">
                <div class="text-black dark:text-slate-200 text-base font-normal leading-normal">
                    {{ $t("Have a coupon") }}?
                </div>

                <!-- Coupon Input -->
                <div class="relative mt-2">
                    <input
                        type="text"
                        v-model="coupon"
                        class="formInputCoupon pr-14 p-3"
                        :placeholder="$t('Enter coupon code')"
                        :class="hasCoupon ? 'text-green-500 pl-10' : ''"
                    />

                    <button
                        v-if="!hasCoupon"
                        class="bg-slate-700 absolute top-1/2 -translate-y-1/2 right-1.5 h-10 w-10 rounded flex justify-center items-center"
                        @click="ApplyCoupon"
                    >
                        <ArrowRightIcon class="w-6 h-6 text-white" />
                    </button>

                    <button
                        v-else
                        class="bg-slate-100 dark:bg-slate-600 absolute top-1/2 -translate-y-1/2 right-1.5 h-10 w-10 rounded flex justify-center items-center"
                        @click="removeCoupon"
                    >
                        <TrashIcon class="w-6 h-6 text-red-500" />
                    </button>

                    <span class="absolute top-1/2 -translate-y-1/2 left-3">
                        <CheckCircleIcon
                            class="w-6 h-6 text-green-500"
                            v-if="hasCoupon"
                        />
                    </span>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="mt-6">
            <div class="text-slate-950 dark:text-white text-lm font-medium leading-7 mb-4">
                {{ $t("Payment Method") }}
            </div>

            <div class="space-y-3">
                <label
                    v-if="master.cashOnDelivery && props.isDigitalProduct != true"
                    for="cash"
                    class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                    :class="
                        paymentType === 'cash'
                            ? 'border-primary bg-primary-50 dark:bg-slate-700'
                            : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500'
                    "
                >
                    <input
                        v-model="paymentType"
                        id="cash"
                        name="payment"
                        type="radio"
                        class="radioBtn2"
                        value="cash"
                    />
                    <div class="p-2 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 shrink-0">
                        <img :src="'assets/icons/money-2.svg'" alt="" class="w-6 h-6" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-slate-950 dark:text-white text-sm font-medium leading-normal">
                            {{ $t("Cash on Delivery") }}
                        </div>
                        <div class="text-slate-500 dark:text-slate-400 text-xs font-normal leading-normal">
                            {{ $t("Pay with cash upon delivery") }}
                        </div>
                    </div>
                </label>

                <div
                    v-if="master.onlinePayment"
                    class="rounded-xl border transition"
                    :class="
                        paymentType === 'card'
                            ? 'border-primary bg-primary-50 dark:bg-slate-700'
                            : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500'
                    "
                >
                    <label for="card" class="flex items-center gap-3 p-3 cursor-pointer">
                        <input
                            v-model="paymentType"
                            id="card"
                            name="payment"
                            type="radio"
                            class="radioBtn2"
                            value="card"
                        />
                        <div class="p-2 bg-white dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 shrink-0">
                            <img :src="'assets/icons/card.svg'" alt="" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0 grow">
                            <div class="text-slate-950 dark:text-white text-sm font-medium leading-normal">
                                {{ $t("Credit or Debit Card") }}
                            </div>
                            <div class="text-slate-500 dark:text-slate-400 text-xs font-normal leading-normal">
                                {{ $t("Pay securely using your card") }}
                            </div>
                        </div>
                        <ChevronUpIcon
                            v-if="paymentType === 'card'"
                            class="w-5 h-5 text-primary shrink-0"
                        />
                        <ChevronDownIcon v-else class="w-5 h-5 text-slate-400 shrink-0" />
                    </label>

                    <!-- Payment Gateways dropdown -->
                    <Transition
                        leave-active-class="transition ease-in duration-200"
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="transform opacity-0 -translate-y-1"
                        enter-to-class="transform opacity-100 translate-y-0"
                        leave-from-class="transform opacity-100 translate-y-0"
                        leave-to-class="transform opacity-0 -translate-y-1"
                    >
                        <div v-if="paymentType === 'card'" class="px-3 pb-3">
                            <div class="grid grid-cols-3 xs:grid-cols-4 gap-2 pt-3 border-t border-primary-100 dark:border-slate-600">
                                <label
                                    v-for="gateway in master.paymentGateways"
                                    :key="gateway.id"
                                    :for="gateway.name"
                                    class="flex items-center justify-center border relative has-[:checked]:border-primary has-[:checked]:shadow-md dark:has-[:checked]:bg-slate-600 p-2 rounded-md bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 cursor-pointer"
                                >
                                    <input
                                        v-model="paymentGateway"
                                        :id="gateway.name"
                                        name="paymentGateway"
                                        type="radio"
                                        class="sr-only"
                                        :value="gateway.name"
                                    />
                                    <img :src="gateway.logo" alt="" class="w-full h-7 object-contain" />
                                </label>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
            </div>
        </div>

        <div
            v-if="authStore.user &&
                !authStore.user?.account_verified &&
                master.orderPlaceAccountVerify
            "
            class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between mt-3"
        >
            <span class="animated-text">{{
                $t("Please verify your account")
            }}</span>
            <button
                class="p-2 border border-primary rounded-md bg-primary-50 text-primary text-sm font-medium"
                @click="showVerifyOtpModal = true"
            >
                {{ $t("Verify Now") }}
            </button>
        </div>

        <template v-if="authStore.user">
            <button
                v-if="!isProcessing"
                class="px-6 py-4 w-full mt-4 bg-primary rounded-[10px] text-white text-base font-medium"
                @click="processOrderConfirm"
            >
                {{ $t("Place Order") }}
            </button>
            <button
                v-else
                class="px-6 py-4 w-full mt-4 bg-primary-200 rounded-[10px] text-primary text-base font-semibold flex items-center justify-center gap-2"
                disabled
            >
                {{ $t("Processing") }}
                <LoadingSpin />
            </button>
        </template>

        <template v-else>
            <button
                v-if="!isProcessing"
                class="px-6 py-4 w-full mt-4 bg-primary rounded-[10px] text-white text-base font-medium"
                @click="processGuestOrderConfirm"
            >
                {{ $t("Place Order") }}
            </button>
            <button
                v-else
                class="px-6 py-4 w-full mt-4 bg-primary-200 rounded-[10px] text-primary text-base font-semibold flex items-center justify-center gap-2"
                disabled
            >
                {{ $t("Processing") }}
                <LoadingSpin />
            </button>
        </template>

        <!-- End Order Confirm Dialog Modal -->
        <OrderConfirmModal />

        <VerifyOtpModal
            :showModal="showVerifyOtpModal"
            @hideModal="showVerifyOtpModal = false"
        />
    </div>
</template>

<script setup>
import { ArrowRightIcon, TrashIcon, ChevronDownIcon, ChevronUpIcon } from "@heroicons/vue/24/outline";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";
import { onMounted, ref, watch } from "vue";
import OrderConfirmModal from "../components/OrderConfirmModal.vue";
import ToastSuccessMessage from "../components/ToastSuccessMessage.vue";
import LoadingSpin from "../components/LoadingSpin.vue";

import { useToast } from "vue-toastification";
import { useAuth } from "../stores/AuthStore";
import { useBasketStore } from "../stores/BasketStore";
import { useMaster } from "../stores/MasterStore";
import { useGuestAddress } from "../stores/GuestAddressStore";
import { useMetaPixel } from "../composables/useMetaPixel";
import localization from "../localization";

import { useRouter } from "vue-router";
import VerifyOtpModal from "./VerifyOtpModal.vue";
const router = new useRouter();

const basketStore = useBasketStore();
const master = useMaster();
const authStore = useAuth();
const guestAddressStore = useGuestAddress();
const { trackInitiateCheckout, trackPurchase } = useMetaPixel();

/**
 * Coordinates are only known for guests who granted location permission.
 * The API rejects a null latitude/longitude as non-numeric, so leave them out
 * entirely when we don't have them.
 */
const locationPayload = () =>
    guestAddressStore.latitude && guestAddressStore.longitude
        ? { latitude: guestAddressStore.latitude, longitude: guestAddressStore.longitude }
        : {};

/** InitiateCheckout fires once per visit, not on every address/coupon refetch. */
let checkoutTracked = false;

/**
 * Buy-now clears the product and totals the moment the order is accepted, and
 * online payments only confirm later in a popup — so snapshot the purchase up
 * front and fire it from whichever success path we reach.
 */
const pendingPurchase = ref(null);

const firePendingPurchase = () => {
    if (!pendingPurchase.value) {
        return;
    }

    const { snapshot, userData } = pendingPurchase.value;
    pendingPurchase.value = null;
    trackPurchase(snapshot, userData);
};

/**
 * Guest details for Meta's matching. Empty when signed in — the server hashes
 * the account's own email/phone from the auth token instead.
 */
const guestUserData = () =>
    authStore.user
        ? {}
        : {
              email: guestAddressStore.email,
              phone: guestAddressStore.phone,
              first_name: (guestAddressStore.name ?? "").split(" ")[0],
              last_name: (guestAddressStore.name ?? "").split(" ").slice(1).join(" "),
          };

/** The single buy-now line item plus what the customer actually pays. */
const buyNowSnapshot = () => ({
    products: basketStore.buyNowProduct?.products ?? [],
    value: orderData.value.payable_amount,
});

const toast = useToast();
const t = localization.i18n.global.t;

const hasCoupon = ref(false);

const coupon = ref("");

const showVerifyOtpModal = ref(false);

const props = defineProps({
    note: String,
    isDigitalProduct: Boolean,
});

const paymentType = ref(props.isDigitalProduct == true ? "card" : (master.cashOnDelivery ? "cash" : "card"));
const paymentGateway = ref(null);
const paymentMethod = ref(paymentType.value);

watch(paymentType, () => {
    if (paymentType.value === "card") {
        paymentMethod.value = paymentGateway.value;
    } else {
        paymentMethod.value = paymentType.value;
        paymentGateway.value = null;
    }
});

watch(paymentGateway, () => {
    if (paymentType.value === "card") {
        paymentMethod.value = paymentGateway.value;
    }
});

const orderData = ref({
    total_amount: 0,
    delivery_charge: 0,
    coupon_discount: 0,
    payable_amount: 0,
    order_tax_amount: 0,
});

onMounted(() => {
    coupon.value = basketStore.coupon_code;

    if (!basketStore.isLoadingCart) {
        fetchBuyNowCartCheckout();
    }
});

watch(
    () => basketStore.isLoadingCart,
    () => {
        if (!basketStore.isLoadingCart) {
            fetchBuyNowCartCheckout();
        }
    }
);

watch(
    () => basketStore.address,
    () => {
        fetchBuyNowCartCheckout()
    }
);

// The quantity stepper on the buy-now line changes the totals.
watch(
    () => basketStore.buyNowRefreshKey,
    () => {
        fetchBuyNowCartCheckout()
    }
);

const fetchBuyNowCartCheckout = () => {
    const locationData = guestAddressStore.latitude && guestAddressStore.longitude
        ? { latitude: guestAddressStore.latitude, longitude: guestAddressStore.longitude }
        : {};
    axios
        .post(
            "/cart/checkout",
            {
                shop_ids: [basketStore.buyNowShopId],
                is_buy_now: true,
                coupon_code: coupon.value,
                address_id: basketStore.address ? basketStore.address.id : null ,
                ...locationData,
            },
            {
                headers: {
                    Authorization: authStore.token,
                    "X-Guest-Token": authStore.access_token,
                },
            }
        )
        .then((response) => {
            orderData.value = response.data.data.checkout;
            basketStore.buyNowProduct = response.data.data.checkout_items[0];

            if (!checkoutTracked && basketStore.buyNowProduct) {
                checkoutTracked = true;
                trackInitiateCheckout(buyNowSnapshot());
            }

            hasCoupon.value = response.data.data.apply_coupon;

            if (hasCoupon.value && coupon.value.length > 0) {
                toast.success(response.data.message, {
                    position:
                        master.langDirection === "rtl"
                            ? "bottom-right"
                            : "bottom-left",
                });
                basketStore.coupon_code = coupon.value;
            } else if (!hasCoupon.value && coupon.value.length > 0) {
                toast.error(response.data.message, {
                    position:
                        master.langDirection === "rtl"
                            ? "bottom-right"
                            : "bottom-left",
                });
                basketStore.coupon_code = "";
            }
        })
        .catch((error) => {
            toast.error(error.response.data.message, {
                position:
                    master.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });
        });
};

const ApplyCoupon = () => {
    if (coupon.value.length > 0) {
        fetchBuyNowCartCheckout();
    }
};

const removeCoupon = () => {
    coupon.value = "";
    hasCoupon.value = false;
    basketStore.coupon_code = "";
    fetchBuyNowCartCheckout();
};

const content = {
    component: ToastSuccessMessage,
    props: {
        title: t("Order Placed"),
        message: t("Your order has been placed successfully."),
    },
};

const isProcessing = ref(false);
const processOrderConfirm = async () => {
    if (!basketStore.address) {
        toast.error(t("Please select shipping address"));
        return;
    }

    if (props.isDigitalProduct == true && paymentMethod.value == "cash") {
        toast.error(t("Please select payment method"), {
            position:
                master.langDirection === "rtl" ? "bottom-right" : "bottom-left",
        });
        return;
    }

    if (paymentMethod.value == null || paymentMethod.value == "card") {
        toast.error(t("Please select payment option"), {
            position:
                master.langDirection === "rtl" ? "bottom-right" : "bottom-left",
        });
        return;
    }

    await guestAddressStore.captureLocation();

    if (basketStore.buyNowProduct) {
        isProcessing.value = true;
        axios
            .post(
                "/place-order",
                {
                    shop_ids: [basketStore.buyNowShopId],
                    address_id: basketStore.address.id,
                    payment_method: paymentMethod.value,
                    coupon_code: coupon.value,
                    note: props.note,
                    is_buy_now: true,
                    // Only sent when we actually have coordinates — the saved
                    // address already carries the location, and posting nulls
                    // trips the numeric validation.
                    ...locationPayload(),
                },
                {
                    headers: {
                        Authorization: authStore.token,
                        "X-Guest-Token": authStore.access_token,
                    },
                }
            )
            .then((response) => {
                isProcessing.value = false;
                pendingPurchase.value = {
                    snapshot: buyNowSnapshot(),
                    userData: guestUserData(),
                };
                toast(content, {
                    type: "default",
                    hideProgressBar: true,
                    icon: false,
                    position:
                        master.langDirection === "rtl"
                            ? "bottom-right"
                            : "bottom-left",
                    toastClassName: "vue-toastification-alert",
                    timeout: 2000,
                });
                orderData.value.total_amount = 0;
                orderData.value.delivery_charge = 0;
                orderData.value.coupon_discount = 0;
                orderData.value.payable_amount = 0;
                basketStore.buyNowProduct = null;
                basketStore.coupon_code = "";
                let paymentUrl = response.data.data.order_payment_url;

                if (paymentUrl != null) {
                    openPaymentPopupWindow(paymentUrl);
                    return;
                } else {
                    basketStore.showOrderConfirmModal = true;
                    firePendingPurchase();
                }
            })
            .catch((error) => {
                toast.error(error.response.data.message, {
                    position:
                        master.langDirection === "rtl"
                            ? "bottom-right"
                            : "bottom-left",
                });
                isProcessing.value = false;
            });
    } else {
        toast.error(t("Please select at least one product"), {
            position:
                master.langDirection === "rtl" ? "bottom-right" : "bottom-left",
        });
    }
};

const processGuestOrderConfirm = async () => {
    if (paymentMethod.value == null || paymentMethod.value == "card") {
        toast.error(t("Please select payment method"), {
            position:
                master.langDirection === "rtl" ? "bottom-right" : "bottom-left",
        });
        return;
    }

    await guestAddressStore.captureLocation();

    if (basketStore.buyNowProduct) {
        isProcessing.value = true;
        axios
            .post(
                "/place-order",
                {
                    currency_id: master.selectedCurrency.id,
                    shop_ids: [basketStore.buyNowShopId],
                    payment_method: paymentMethod.value,
                    coupon_code: coupon.value,
                    note: props.note,
                    is_buy_now: true,
                    name: guestAddressStore.name,
                    email: guestAddressStore.email,
                    phone: guestAddressStore.phone,
                    area_id: guestAddressStore.area_id,
                    address_line: guestAddressStore.address_line,
                    address_type: guestAddressStore.address_type,
                    latitude: guestAddressStore.latitude,
                    longitude: guestAddressStore.longitude,
                },
                {
                    headers: {
                        Authorization: authStore.token,
                        "X-Guest-Token": authStore.access_token,
                    },
                }
            )
            .then((response) => {
                isProcessing.value = false;
                pendingPurchase.value = {
                    snapshot: buyNowSnapshot(),
                    userData: guestUserData(),
                };
                toast(content, {
                    type: "default",
                    hideProgressBar: true,
                    icon: false,
                    position:
                        master.langDirection === "rtl"
                            ? "bottom-right"
                            : "bottom-left",
                    toastClassName: "vue-toastification-alert",
                    timeout: 2000,
                });
                orderData.value.total_amount = 0;
                orderData.value.delivery_charge = 0;
                orderData.value.coupon_discount = 0;
                orderData.value.payable_amount = 0;
                basketStore.buyNowProduct = null;
                guestAddressStore.clearGuestAddress();
                basketStore.coupon_code = "";
                let paymentUrl = response.data.data.order_payment_url;

                if (paymentUrl != null) {
                    openPaymentPopupWindow(paymentUrl);
                    return;
                } else {
                    basketStore.showOrderConfirmModal = true;
                    firePendingPurchase();
                }
            })
            .catch((error) => {
                guestAddressStore.errors = error.response.data.errors;
                toast.error(error.response.data.message, {
                    position:
                        master.langDirection === "rtl"
                            ? "bottom-right"
                            : "bottom-left",
                });
                isProcessing.value = false;
            });
    } else {
        toast.error(t("Please select at least one product"), {
            position:
                master.langDirection === "rtl" ? "bottom-right" : "bottom-left",
        });
    }
};

const openPaymentPopupWindow = (url) => {
    let winWidth = 700;
    let winHeight = 700;
    let left = screen.width / 2 - winWidth / 2;
    let top = screen.height / 2 - winHeight / 2;

    let options =
        "popup,resizable,height=" +
        winHeight +
        ",width=" +
        winWidth +
        ",top=" +
        top +
        ",left=" +
        left;

    let win = window.open(url, null, options);

    win.title = "Payment Window Screen - Make Payment";

    win.onload = () => {
        win.title = "Payment Window Screen - Make Payment";
    };

    win.focus();

    var intervalID = setInterval(trackURLChanges, 1000);

    function trackURLChanges() {
        try {
            // check if the window is closed
            if (win.closed || !win) {
                clearInterval(intervalID);
                win.close();
                basketStore.orderPaymentCancelModal = true;
                toast.error(t("Payment Canceled"), {
                    position:
                        master.langDirection === "rtl"
                            ? "bottom-right"
                            : "bottom-left",
                });
                router.push({ name: "home" });
                return;
            }

            const pathname = win.location.pathname;

            var currentPath = pathname.replace(/\/order\/\d+/, "");

            if (currentPath == "/payment/cancel") {
                clearInterval(intervalID);
                setTimeout(() => {
                    win.close();
                    basketStore.orderPaymentCancelModal = true;
                    toast.error(t("Sorry! Payment Canceled"), {
                        position:
                            master.langDirection === "rtl"
                                ? "bottom-right"
                                : "bottom-left",
                    });
                    router.push({ name: "home" });
                }, 8000);
                return;
            }

            if (currentPath == "/payment/success") {
                win.close();
                clearInterval(intervalID);
                basketStore.showOrderConfirmModal = true;
                firePendingPurchase();
                return;
            }
        } catch (error) {}
    }

    // payment close after 3 minutes
    setTimeout(() => {
        clearInterval(intervalID);
        win.close();
    }, 180000);
};
</script>

<style scoped>
.formInputCoupon {
    @apply rounded-lg border border-slate-200 dark:border-slate-600 focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400 bg-white dark:bg-slate-700 dark:text-white dark:placeholder:text-slate-500;
}

.animated-text {
    display: inline-block;
    background: linear-gradient(
        90deg,
        red,
        orange,
        indigo,
        yellow,
        green,
        blue,
        violet
    );
    background-size: 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: colorChange 3s linear infinite;
}

@keyframes colorChange {
    0% {
        background-position: 100%;
    }

    100% {
        background-position: 0%;
    }
}
</style>
