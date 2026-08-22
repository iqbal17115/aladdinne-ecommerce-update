<template>
    <div class="bg-white dark:bg-slate-800">
        <div class="main-container py-12">

            <!-- Section Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <!-- <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                        <span class="text-red-500 text-xs font-bold uppercase tracking-[0.18em]">
                            {{ $t('LIMITED TIME OFFER') }}
                        </span> -->
                    </div>
                     <div class="text-slate-950 dark:text-white text-lg sm:text-lg md:text-xl font-semibold leading-tight">
                        {{ $t('Flash Sale') }}
                    </div>
                </div>
                <router-link :to="'/flash-sale/' + flashSale?.name?.toLowerCase().replace(/\s+/g, '-')"
                    class="flex items-center gap-1.5 px-5 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 hover:border-gray-500 hover:text-gray-900 transition-all duration-200 mt-1">
                    {{ $t('View All') }}
                    <ArrowRightIcon class="w-4 h-4" />
                </router-link>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,_1.5fr)_minmax(0,_3fr)] gap-6">

                <!-- Left: Campaign Banner with image background -->
                <div class="rounded-2xl overflow-hidden isolate flex flex-col relative min-h-[420px]"
                    :style="flashSale?.thumbnail
                        ? `background-image: url('${flashSale.thumbnail}'); background-size: cover; background-position: center;`
                        : 'background: linear-gradient(160deg, var(--primary-300) 0%, var(--primary-600) 100%);'">

                    <!-- Dark overlay for text readability -->
                    <div class="absolute inset-0 bg-black/50 rounded-2xl"></div>

                    <!-- Content (above overlay) -->
                    <div class="relative z-10 flex flex-col flex-1 p-10">

                        <!-- Limited Time Badge -->
                        <div class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800/90 backdrop-blur-sm text-gray-700 dark:text-slate-300 text-xs font-semibold px-3 py-1.5 rounded-[8px] self-start mb-5">
                            <svg class="w-3.5 h-3.5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                            {{ $t('Limited Time Only') }}
                        </div>

                        <!-- Spacer top -->
                        <div class="flex-1"></div>

                        <!-- Title -->
                        <h2 class="text-white text-4xl sm:text-5xl font-black leading-tight mb-3 drop-shadow-sm">
                            <span class="block">{{ (flashSale?.title || flashSale?.name)?.split(' ')[0] }}</span>
                            <span class="block">{{ (flashSale?.title || flashSale?.name)?.split(' ').slice(1).join(' ') }}</span>
                        </h2>

                        <!-- Subtitle -->
                        <p class="text-white/80 text-sm leading-relaxed mb-6">
                            {{ flashSale?.description }}
                        </p>

                        <!-- Spacer bottom -->
                        <div class="flex-1"></div>

                        <!-- Sale ends in label -->
                        <div class="text-white/90 text-sm font-medium mb-3">{{ $t('Sale ends in') }}</div>

                        <!-- Countdown -->
                        <div class="flex items-center gap-3 mb-6">
                            <div v-if="endDay > 0" class="text-center bg-white dark:bg-slate-800 rounded-xl py-2.5 px-4 shadow-md min-w-[60px]">
                                <div class="text-primary text-2xl font-black leading-none tabular-nums">{{ endDay }}</div>
                                <div class="text-gray-400 text-[10px] font-medium mt-0.5">{{ $t('Days') }}</div>
                            </div>
                            <span v-if="endDay > 0" class="text-white font-bold text-xl">:</span>

                            <div class="text-center bg-white dark:bg-slate-800 rounded-xl py-2.5 px-4 shadow-md min-w-[60px]">
                                <div class="text-primary text-2xl font-black leading-none tabular-nums">{{ endHour }}</div>
                                <div class="text-gray-400 text-[10px] font-medium mt-0.5">{{ $t('Hours') }}</div>
                            </div>
                            <span class="text-white font-bold text-xl">:</span>

                            <div class="text-center bg-white dark:bg-slate-800 rounded-xl py-2.5 px-4 shadow-md min-w-[60px]">
                                <div class="text-primary text-2xl font-black leading-none tabular-nums">{{ endMinute }}</div>
                                <div class="text-gray-400 text-[10px] font-medium mt-0.5">{{ $t('Minutes') }}</div>
                            </div>
                            <span class="text-white font-bold text-xl">:</span>

                            <div class="text-center bg-gray-900 rounded-xl py-2.5 px-4 shadow-md min-w-[60px]">
                                <div class="text-white text-2xl font-black leading-none tabular-nums">{{ endSecond }}</div>
                                <div class="text-gray-400 text-[10px] font-medium mt-0.5">{{ $t('Seconds') }}</div>
                            </div>
                        </div>

                        <!-- Shop Now Button -->
                        <router-link :to="'/flash-sale/' + flashSale?.name?.toLowerCase().replace(/\s+/g, '-')"
                            class="flex items-center justify-center gap-2 bg-primary hover:bg-primary-600 text-white text-base font-bold py-3.5 rounded-2xl transition-colors duration-200 shadow-lg">
                            {{ $t('Shop Now') }}
                            <ArrowRightIcon class="w-5 h-5" />
                        </router-link>

                    </div>
                </div>

                <!-- Right: Product Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div v-for="product in flashSale.products" :key="product.id"
                        class="bg-white dark:bg-slate-800 border border-gray-200 rounded-xl overflow-hidden hover:border-gray-400 hover:shadow-md transition-all duration-200 flex flex-col"
                        :class="product.quantity > 0 ? '' : 'opacity-75'">

                        <!-- Product Image -->
                        <div class="relative overflow-hidden group/img">
                            <div class="cursor-pointer" @click="goToProduct(product)">
                                <img :src="product.thumbnail" loading="lazy"
                                    class="w-full h-44 object-contain hover:scale-105 transition duration-500"
                                    :class="product.quantity > 0 ? '' : 'opacity-40'" />
                            </div>
                            <span v-if="product.discount_percentage > 0"
                                class="absolute top-2 left-2 bg-primary text-white text-[11px] font-bold px-2 py-0.5 rounded">
                                -{{ product.discount_percentage }}% {{ $t('OFF') }}
                            </span>
                            <!-- Favorite button -->
                            <button v-if="product.is_favorite"
                                class="absolute top-2 right-2 w-9 h-9 rounded-[10px] justify-center items-center flex cursor-pointer bg-white dark:bg-slate-800 shadow"
                                @click.stop="favoriteAddOrRemove(product)">
                                <HeartIconSolid class="w-5 h-5 text-red-500" />
                            </button>
                            <button v-else
                                class="absolute flex top-2 right-2 w-9 h-9 rounded-[10px] justify-center items-center cursor-pointer bg-white dark:bg-slate-800 shadow"
                                @click.stop="favoriteAddOrRemove(product)">
                                <HeartIconOutline class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                            </button>
                        </div>

                        <!-- Product Info -->
                        <div class="p-3 flex flex-col flex-1 gap-2 cursor-pointer" @click="goToProduct(product)">
                            <div class="text-gray-900 dark:text-white text-sm font-semibold leading-snug line-clamp-2">
                                {{ product.name }}
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-primary text-base font-black">
                                    {{ masterStore.showCurrency(product.discount_price > 0 ? product.discount_price : product.price) }}
                                </span>
                                <span v-if="product.discount_price > 0"
                                    class="text-gray-400 text-sm line-through">
                                    {{ masterStore.showCurrency(product.price) }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                <StarIcon class="w-4 h-4 text-yellow-400 flex-shrink-0" />
                                <span class="font-bold text-gray-800 dark:text-slate-200">{{ product.rating?.toFixed(1) }}</span>
                                <span>({{ product.total_reviews }})</span>
                                <span class="text-gray-300">·</span>
                                <span v-if="product.quantity > 0">{{ product.total_sold }} {{ $t('Sold') }}</span>
                                <span v-else class="text-primary font-medium">{{ $t('Sold out') }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="px-3 pb-3">
                            <button v-if="product.quantity > 0"
                                class="w-full bg-primary hover:bg-primary-600 text-white text-sm font-semibold py-2.5 rounded-lg flex items-center justify-center gap-1.5 transition-colors duration-200"
                                @click="buyNow(product)">
                                {{ $t('Buy Now') }}
                                <ArrowRightIcon class="w-4 h-4" />
                            </button>
                            <button v-else
                                class="w-full border border-primary text-primary text-sm font-semibold py-2.5 rounded-lg flex items-center justify-center gap-1.5 hover:bg-primary-50 transition-colors duration-200">
                                {{ $t('Request Stock') }}
                                <ArrowRightIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { ArrowRightIcon, StarIcon, HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid';
import { HeartIcon as HeartIconOutline } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';
import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';
import { useAuth } from '../stores/AuthStore';
import localization from '../localization';

const props = defineProps({
    flashSale: Object
});

const router = useRouter();
const basketStore = useBasketStore();
const masterStore = useMaster();
const authStore = useAuth();
const toast = useToast();
const t = localization.i18n.global.t;

const goToProduct = (product) => {
    if (product.quantity > 0) {
        router.push({ name: 'productDetails', params: { slug: product.slug } });
    }
};

const favoriteAddOrRemove = (product) => {
    if (authStore.token === null) {
        return authStore.loginModal = true;
    }
    axios.post('/favorite-add-or-remove', { product_id: product.id }, {
        headers: { Authorization: authStore.token }
    }).then((response) => {
        product.is_favorite = response.data.data.product.is_favorite;
        if (product.is_favorite) {
            toast.success(t('Product added to favorite'), { position: masterStore.langDirection === 'rtl' ? 'bottom-right' : 'bottom-left' });
        } else {
            toast.warning(t('Product removed from favorite'), { position: masterStore.langDirection === 'rtl' ? 'bottom-right' : 'bottom-left' });
        }
        authStore.favoriteRemove = true;
        authStore.fetchFavoriteProducts();
    });
};

// No variant picker on the card, so fall back to the product's first
// color/size instead of adding a "no variant" cart line for products that have variants.
const buyNow = (product) => {
    basketStore.addToCart({
        product_id: product.id,
        is_buy_now: true,
        quantity: 1,
        size: product.sizes?.length > 0 ? product.sizes[0].id : null,
        color: product.colors?.length > 0 ? product.colors[0].id : null,
        unit: null
    }, product);
    basketStore.buyNowShopId = product.shop?.id;
};

const endDay = ref('00');
const endHour = ref('00');
const endMinute = ref('00');
const endSecond = ref('00');
let countdownInterval = null;

const startCountdown = () => {
    if (countdownInterval) clearInterval(countdownInterval);
    const endDate = new Date(props.flashSale.end_date).getTime();

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

onMounted(startCountdown);

onUnmounted(() => {
    clearInterval(countdownInterval);
});
</script>
