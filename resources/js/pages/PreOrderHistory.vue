<template>
    <div>

        <!-- Header -->
        <AuthPageHeader :title="$t('Pre-orders')" />

        <!-- Order Status -->
        <div
            class="bg-white dark:bg-slate-800 px-3 border-t border-slate-100 dark:border-slate-700 flex gap-4 md:gap-8 overflow-x-auto">
            <label v-for="(count, status) in statusWiseOrders" :key="status" class="statusLinkBtn" :for="'status-' + status">
                <input type="radio" v-model="orderStatus" name="status" class="sr-only" :value="status"
                    :id="'status-' + status" />
                {{ $t(status.charAt(0).toUpperCase() + status.slice(1)) }} ({{ count }})
            </label>
        </div>

        <!-- Pre-order History -->
        <div class="px-2 pt-2 md:px-4 md:pt-4 lg:px-6 lg:pt-6">
            <div class="p-4 lg:p-6 bg-white dark:bg-slate-800 rounded-xl flex flex-col gap-3">

                <!-- Order Item -->
                <div v-for="order in orders" :key="order.id">
                    <PreOrderHistoryItem :order="order" />
                </div>

                <!-- Order list empty -->
                <div v-if="orders.length == 0">
                    <p>{{ $t('No Order Found') }}</p>
                </div>

            </div>
        </div>

        <!-- pagination's -->
        <div v-if="totalItems > perPage" class="px-2 md:px-4 lg:px-6 mt-4">
            <div class="bg-white dark:bg-slate-800 p-3 rounded-xl flex justify-between items-center w-full gap-4 flex-wrap">
                <div class="text-slate-800 dark:text-slate-100 text-base font-normal leading-normal">
                    {{ $t('Showing') }} {{ perPage * (currentPage - 1) + 1 }} {{ $t('to') }} {{ perPage * (currentPage - 1) +
                        orders.length }} {{ $t('of') }} {{ totalItems }} {{ $t('results') }}
                </div>
                <div>
                    <vue-awesome-paginate :total-items="totalItems" :items-per-page="perPage" type="button"
                        :max-pages-shown="3" v-model="currentPage" :hide-prev-next-when-ends="true"
                        @click="onClickHandler" />
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import AuthPageHeader from '../components/AuthPageHeader.vue';
import PreOrderHistoryItem from '../components/PreOrderHistoryItem.vue';

import { useRouter } from 'vue-router';
const router = useRouter();

import { useAuth } from '../stores/AuthStore';
const authStore = useAuth();
const orderStatus = ref('all');

const orders = ref([]);

const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);

const statusWiseOrders = ref({ all: 0 });

const onClickHandler = (page) => {
    currentPage.value = page;
    fetchOrders();
};

watch(orderStatus, () => {
    currentPage.value = 1;
    fetchOrders();
});

onMounted(() => {
    fetchOrders();
});

const fetchOrders = async () => {
    axios.get('/pre-orders', {
        params: {
            order_status: orderStatus.value,
            page: currentPage.value,
            per_page: perPage.value
        },
        headers: {
            Authorization: authStore.token,
        }
    }).then((response) => {
        totalItems.value = response.data.data.total;
        orders.value = response.data.data.preOrders;
        statusWiseOrders.value = response.data.data.status_wise_orders;
    }).catch((error) => {
        if (error.response?.status === 401) {
            authStore.token = null;
            authStore.user = null;
            authStore.addresses = [];
            authStore.favoriteProducts = 0;
            router.push('/');
        }
    });
};

</script>
<style scoped>
.statusLinkBtn {
    @apply py-4 border-b-2 relative has-[:checked]:text-primary text-base font-normal leading-normal has-[:checked]:border-primary cursor-pointer border-transparent shrink-0 whitespace-nowrap;
}
</style>
