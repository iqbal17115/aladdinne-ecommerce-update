<template>
    <div class="main-container py-7">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-1.5 overflow-hidden pb-4 text-sm">
            <router-link to="/" class="shrink-0 flex items-center gap-1 text-slate-500 hover:text-primary transition">
                <HomeIcon class="w-4 h-4" />
                <span>{{ $t("Home") }}</span>
            </router-link>
            <span class="text-slate-400">&#8250;</span>
            <span class="text-slate-800 dark:text-slate-100 font-medium truncate">{{ $t("All Shops") }}</span>
        </nav>

        <div v-if="!isLoading" class="text-slate-800 dark:text-slate-100 text-xl font-bold leading-9">
            {{ $t('All Shops') }}
        </div>
        <p v-if="!isLoading" class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 sm:text-sm">
                            {{ $t('Explore our all shops and shop the products you love') }}
        </p>
        <!-- loading -->
         <SkeletonLoader v-else class="w-48 sm:w-60 md:w-72 lg:w-96 h-12 rounded-lg" />

        <!-- Shops -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-start">

            <div v-if="!isLoading" v-for="shop in shops" :key="shop.id" class="w-full">
                <ShopCard :shop="shop" />
            </div>

            <!-- loading -->
            <div v-else v-for="i in 8" :key="i">
                <SkeletonLoader class="w-full h-[200px] sm:h-[250px] rounded-lg" />
            </div>

        </div>

        <!-- Pagination -->
        <div v-if="shops.length > 0 && !isLoading" class="flex justify-between items-center w-full mt-8  gap-4 flex-wrap">
            <div class="text-slate-800 dark:text-slate-100 text-base font-normal leading-normal">
                {{ $t('Showing') }} {{ perPage * (currentPage - 1) + 1 }} {{ $t('to') }} {{ perPage * (currentPage - 1)
                    + shops.length }} {{ $t('of') }} {{
                    totalShops }} {{ $t('results') }}
            </div>
            <div>
                <vue-awesome-paginate :total-items="totalShops" :items-per-page="perPage" type="button"
                    :hide-prev-next-when-ends="true" :max-pages-shown="5" v-model="currentPage"
                    @click="onClickHandler" />
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { HomeIcon } from '@heroicons/vue/24/outline';
import ShopCard from '../components/ShopCard.vue';
import { useMaster } from '../stores/MasterStore';
import SkeletonLoader from '../components/SkeletonLoader.vue';
const masterStore = useMaster();

import { useRouter } from 'vue-router';
const router = new useRouter();

const isLoading = ref(true);
const currentPage = ref(1);
const perPage = ref(12);
const totalShops = ref(0);

const shops = ref([]);

onMounted(() => {
    if (!masterStore.multiVendor) {
        router.push('/');
        return;
    }
    fetchShops();
    window.scrollTo(0, 0);
});

const onClickHandler = async (page) => {
    currentPage.value = page;
    fetchShops();
};

const fetchShops = async () => {
    window.scrollTo(0, 0)
    isLoading.value = true;
    axios.get('/shops', {
        params: {
            page: currentPage.value,
            per_page: perPage.value
        },
        headers: {
            'Accept-Language': masterStore.locale || 'en',
        }
    }).then((response) => {
        totalShops.value = response.data.data.total;
        shops.value = response.data.data.shops;
        setTimeout(() => {
            isLoading.value = false;
        }, 300);
    })
};

</script>
