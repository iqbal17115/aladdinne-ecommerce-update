<template>
    <div>
        <div class="bg-white dark:bg-slate-800 dark:bg-slate-800 px-3 text-slate-600 dark:text-slate-400 dark:text-slate-300 flex items-center gap-1 pt-2">
            <HomeIcon class="w-5 h-5 md:w-6 md:h-6" />
            <router-link
                to="/order-history"
                class="leading-normal hover:text-primary"
            >
                {{ $t("Order History") }}
            </router-link>
            <span class="leading-normal">/ {{ $t("Order Details") }}</span>
        </div>

        <!-- Header -->
        <OrderDetailsPageHeader :title="$t('Order Details')">
            <RouterLink :to="'/return-product/' + order.id">
                <button
                    v-if="order.is_returnable"
                    class="h-10 px-5 py-1 rounded outline outline-1 outline-offset-[-1px] outline-primary inline-flex justify-start items-center gap-2 text-primary text-base font-medium leading-normal transition-all duration-200 hover:bg-primary hover:text-white"
                >
                    {{ $t("Return Product") }}
                </button>
            </RouterLink>
        </OrderDetailsPageHeader>

        <!-- Order details -->
        <div class="px-2 pt-2 md:px-4 md:pt-4 lg:px-6 lg:pt-8">
            <OrderDetailsOrderStatus :order="order" />

            <div
                class="grid grid-cols-3 gap-4 md:gap-6 p-3 md:p-4 xl:p-8 bg-white dark:bg-slate-800 rounded-lg md:rounded-2xl"
            >
                <!-- column 1 -->
                <div class="col-span-3 lg:col-span-2 bg-white dark:bg-slate-800 space-y-5">
                    <div
                        class="rounded-xl md:rounded-2xl outline outline-1 outline-offset-[-1px] outline-slate-100 p-3 md:p-4 xl:p-6"
                    >
                        <div
                            class="text-slate-500 text-xs md:text-sm font-normal leading-tight md:leading-none"
                        >
                            {{ $t("Purchased from") }}
                        </div>

                        <div class="flex items-center gap-4 mt-2">
                            <img
                                class="w-9 h-9 md:w-12 md:h-12"
                                :src="order.shop?.logo"
                            />
                            <div
                                class="flex flex-wrap gap-2 justify-between items-start grow"
                            >
                                <div>
                                    <div
                                        class="text-slate-950 dark:text-white text-sm md:text-base font-medium leading-tight md:leading-normal"
                                    >
                                        {{ order.shop?.name }}
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <StarIcon
                                            class="w-5 h-5 text-yellow-400"
                                        />
                                        <span
                                            class="text-slate-800 dark:text-slate-100 text-xs md:text-sm font-medium leading-tight"
                                            >{{ order.shop?.rating }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="w-full h-[0px] border-t border-slate-200 dark:border-slate-700 my-4"
                        ></div>

                        <div
                            class="text-slate-600 dark:text-slate-400 text-sm font-normal leading-tight"
                        >
                            {{ $t("Products") }} ({{ order.products?.length }})
                        </div>

                        <OrderProducts
                            :order="order"
                            @refresh="fetchOrderDetails"
                        />
                    </div>

                    <div v-if="doesRiderAssigned">
                        <RealTimeMapDisplay
                            height="100%"
                            :riderLocation="riderCoords"
                            :customerLocation="customerCoords"
                        />
                    </div>
                </div>

                <!-- column 2 -->
                <div class="col-span-3 lg:col-span-1">
                    <OrderDetailsSummery
                        :order="order"
                        @update:paymentSuccess="fetchOrderDetails"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { HomeIcon } from "@heroicons/vue/24/outline";
import { StarIcon } from "@heroicons/vue/24/solid";
import { onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import AuthPageHeader from "../components/AuthPageHeader.vue";
import OrderDetailsOrderStatus from "../components/OrderDetailsOrderStatus.vue";
import OrderDetailsSummery from "../components/OrderDetailsSummery.vue";
import OrderProducts from "../components/OrderProducts.vue";

import { useAuth } from "../stores/AuthStore";
import { useMaster } from "../stores/MasterStore";
import OrderDetailsPageHeader from "../components/OrderDetailsPageHeader.vue";
import RealTimeMapDisplay from "../components/RealTimeMapDisplay.vue";
import Pusher from "pusher-js";

const authStore = useAuth();
const masterStore = useMaster();
const route = useRoute();
const router = useRouter();

const order = ref({});

const riderCoords = ref({ lat: 0, lng: 0 });

const customerCoords = ref({ lat: 0, lng: 0 });

const doesRiderAssigned = ref(false);

watch(
    () => authStore.orderCancel,
    () => {
        if (authStore.orderCancel == true) {
            fetchOrderDetails();
        }
        authStore.orderCancel = false;
    }
);

onMounted(() => {
    fetchOrderDetails();
    window.scrollTo(0, 0, { behavior: "smooth" });
});

const fetchOrderDetails = async () => {
    axios
        .get("/order-details", {
            params: { order_id: route.params.id },
            headers: {
                Authorization: authStore.token,
            },
        })
        .then((response) => {
            order.value = response.data.data.order;

            console.log(response.data.data.order);

            if (response.data.data.order.rider.length == 0) {
                return;
            } else {
                doesRiderAssigned.value = true;
                handlePusherChanel(response.data.data.order.rider.id);
            }

            customerCoords.value = {
                lat: Number(response.data.data.order.address.latitude),
                lng: Number(response.data.data.order.address.longitude),
            };

            riderCoords.value = {
                lat: Number(response.data.data.order.rider.latitude),
                lng: Number(response.data.data.order.rider.longitude),
            };
        })
        .catch((error) => {
            if (error.response.status === 401) {
                authStore.token = null;
                authStore.user = null;
                authStore.addresses = [];
                authStore.favoriteProducts = 0;
                router.push("/");
            }
        });
};

const channel = ref(null);
const pusher = ref(null);

const handlePusherChanel = (riderId) => {
    let userId = authStore.user.id;

    if (!masterStore.pusher_app_key) {
        return;
    }

    pusher.value = new Pusher(masterStore.pusher_app_key, {
        cluster: masterStore.pusher_app_cluster,
        encrypted: true,
    });



    channel.value = pusher.value.subscribe("rider-location." + riderId);

    channel.value.bind("rider.location.updated", function (data) {


        riderCoords.value = {
            lat: Number(data.location.latitude),
            lng: Number(data.location.longitude),
        };
    });
};

const closePusher = () => {
    if (channel.value) channel.value.unsubscribe();
    if (pusher.value) pusher.value.disconnect();
};

onUnmounted(() => {
    closePusher();
});
</script>
