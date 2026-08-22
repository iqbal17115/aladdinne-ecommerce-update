<template>
    <div class=" min-h-screen">

        <!-- Hero Section -->
        <div class="main-container pt-10 pb-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-10">

                <!-- Left: Badge + Title + Subtitle -->
                <div class="max-w-lg">
                    <!-- <div class="flex items-center gap-2 mb-5">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0"></span>
                        <span class="text-red-500 text-xs font-bold uppercase tracking-[0.2em]">
                            {{ $t('LIMITED') }} · {{ $t('FLASH SALE') }}
                        </span>
                    </div> -->
                    <h1 class="text-xl sm:text-4xl lg:text-2xl font-black text-gray-700 dark:text-slate-300 leading-none mb-5">
                        {{ flashSale.name || $t('Flash Sale') }}.
                    </h1>
                    <p class="text-gray-500 text-sm sm:text-sm leading-relaxed max-w-xs">
                        {{ flashSale.description || '' }}
                    </p>
                </div>

                <!-- Right: Countdown -->
                <div class="flex flex-col items-start lg:items-end gap-3">
                    <span class="text-gray-400 text-xs font-semibold uppercase tracking-[0.18em]">
                        {{ $t('ENDING IN') }}
                    </span>
                    <div class="flex items-end gap-2 sm:gap-4">
                        <div v-if="endDay > 0" class="text-center">
                            <div class="text-6xl sm:text-5xl lg:text-6xl font-black text-gray-900 leading-none tabular-nums">
                                {{ endDay }}
                            </div>
                            <div class="text-gray-400 text-[10px] uppercase tracking-widest mt-1">{{ $t('DAYS') }}</div>
                        </div>
                        <span v-if="endDay > 0" class="text-gray-400 text-3xl lg:text-4xl font-thin mb-5">:</span>

                        <div class="text-center">
                            <div class="text-6xl sm:text-6xl lg:text-6xl font-black text-gray-900 leading-none tabular-nums">
                                {{ endHour }}
                            </div>
                            <div class="text-gray-400 text-[10px] uppercase tracking-widest mt-1">{{ $t('HRS') }}</div>
                        </div>
                        <span class="text-gray-400 text-3xl lg:text-4xl font-thin mb-5">:</span>

                        <div class="text-center">
                            <div class="text-6xl sm:text-6xl lg:text-6xl font-black text-gray-900 leading-none tabular-nums">
                                {{ endMinute }}
                            </div>
                            <div class="text-gray-400 text-[10px] uppercase tracking-widest mt-1">{{ $t('MIN') }}</div>
                        </div>
                        <span class="text-gray-400 text-3xl lg:text-4xl font-thin mb-5">:</span>

                        <div class="text-center">
                            <div class="text-6xl sm:text-6xl lg:text-6xl font-black text-red-500 leading-none tabular-nums">
                                {{ endSecond }}
                            </div>
                            <div class="text-gray-400 text-[10px] uppercase tracking-widest mt-1">{{ $t('SEC') }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Divider -->
        <div class="main-container">
            <hr class="border-gray-300">
        </div>

        <!-- Featured Deals Header + Categories -->
        <div class="main-container pt-6 pb-4">
            <!-- Categories slider -->
            <div class="overflow-x-auto">
                <swiper :slidesPerView="'auto'" :spaceBetween="10" class="categorySwiper">
                    <swiper-slide>
                        <div class="px-4 py-2 rounded-[5px] border text-sm font-medium cursor-pointer transition-all duration-200"
                            :class="!categoryId
                                ? 'bg-primary-500 text-white border-primary-500'
                                : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-400 border-gray-200 hover:border-gray-500'"
                            @click="selectCategory('')">
                            {{ $t('All') }}
                        </div>
                    </swiper-slide>

                    <swiper-slide v-for="category in masterStore.categories" :key="category.id">
                        <div class="px-4 py-2 rounded-[5px] border text-sm font-medium cursor-pointer transition-all duration-200"
                            :class="(category.id == categoryId)
                                ? 'bg-gray-900 text-white border-gray-900'
                                : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-400 border-gray-200 hover:border-gray-500'"
                            @click="selectCategory(category.id)">
                            {{ category.name }}
                        </div>
                    </swiper-slide>
                </swiper>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="main-container pb-14">
            <div
                class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-5 items-start">
                <div v-for="product in products" :key="product.id" class="w-full">
                    <ProductCard :product="product" />
                </div>
            </div>

            <div v-if="products.length == 0" class="flex justify-center items-center w-full mt-12">
                <div class="text-gray-500 text-base font-normal">
                    {{ $t('No products found') }}
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center w-full mt-8 gap-4 flex-wrap">
                <div class="text-gray-600 dark:text-slate-400 text-sm font-normal leading-normal">
                    {{ $t('Showing') }} {{ perPage * (currentPage - 1) + 1 }} {{ $t('to') }} {{ perPage * (currentPage - 1) + products.length }}
                    {{ $t('of') }} {{ totalProducts }} {{ $t('results') }}
                </div>
                <div>
                    <vue-awesome-paginate :total-items="totalProducts" :items-per-page="perPage" type="button"
                        :max-pages-shown="5" v-model="currentPage"
                        :hide-prev-next-when-ends="true"
                        @click="onClickHandler" />
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import ProductCard from '../components/ProductCard.vue';
import 'swiper/css';

import { useMaster } from '../stores/MasterStore';
const masterStore = useMaster();

const route = useRoute();
const { name } = useRoute().params;

const categoryId = ref(null);

onMounted(() => {
    fetchFlashSaleProducts();
    window.scrollTo(0, 0);
});

const currentPage = ref(1);
const perPage = 12;

const onClickHandler = (page) => {
    currentPage.value = page;
    fetchFlashSaleProducts();
};

const flashSale = ref({});
const products = ref([]);
const totalProducts = ref(0);

const fetchFlashSaleProducts = async () => {
    axios.get('/flash-sale/' + name + '/details', {
        params: {
            category_id: categoryId.value,
            page: currentPage.value,
            per_page: perPage,
        }
    }).then((response) => {
        flashSale.value = response.data.data.flash_sale;
        totalProducts.value = response.data.data.total_products;
        products.value = response.data.data.products;
        startCountdown();
    });
};

watch(() => categoryId.value, () => {
    currentPage.value = 1;
    fetchFlashSaleProducts();
});

const selectCategory = (id) => {
    categoryId.value = id;
};

const endDay = ref('00');
const endHour = ref('00');
const endMinute = ref('00');
const endSecond = ref('00');
let countdownInterval = null;

const startCountdown = () => {
    if (countdownInterval) clearInterval(countdownInterval);
    const endDate = new Date(flashSale.value.end_date).getTime();

    countdownInterval = setInterval(() => {
        const now = new Date().getTime();
        const timeLeft = endDate - now;

        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            endDay.value = '00';
            endHour.value = '00';
            endMinute.value = '00';
            endSecond.value = '00';
        } else {
            endDay.value = String(Math.floor(timeLeft / (1000 * 60 * 60 * 24))).padStart(2, '0');
            endHour.value = String(Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            endMinute.value = String(Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            endSecond.value = String(Math.floor((timeLeft % (1000 * 60)) / 1000)).padStart(2, '0');
        }
    }, 1000);
};

onUnmounted(() => {
    clearInterval(countdownInterval);
});
</script>

<style>
.categorySwiper .swiper-slide {
    width: auto !important;
}
</style>
