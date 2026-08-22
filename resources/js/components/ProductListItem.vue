<template>
    <div class="rounded-lg sm:rounded-xl border border-slate-100 dark:border-slate-700 transition-all duration-300 group bg-white dark:bg-slate-800 overflow-hidden relative hover:shadow-md"
        :class="available ? 'hover:border-primary' : ''">

        <div class="flex items-center gap-3 sm:gap-5 p-2 sm:p-3">
            <!-- Thumbnail -->
            <div class="w-24 h-24 sm:w-32 sm:h-32 shrink-0 overflow-hidden relative rounded-lg bg-slate-50 cursor-pointer"
                @click="showProductDetails" :class="available ? '' : 'opacity-30'">
                <img :src="props.product?.thumbnail"
                    class="w-full h-full group-hover:scale-110 transition duration-500 object-contain" loading="lazy" />

                <!-- discount badge -->
                <div v-if="props.product?.discount_percentage > 0"
                    class="px-1.5 py-0.5 bg-red-500 rounded-2xl text-white text-[10px] sm:text-xs font-medium absolute top-1.5 left-1.5">
                    {{ Math.round(props.product?.discount_percentage) }}% {{ $t('OFF') }}
                </div>

                <!-- digital product badge -->
                <span v-if="props.product?.is_digital == true"
                    class="absolute bottom-1 right-1 inline-flex gap-1 items-center rounded-md bg-gradient-to-r from-green-600 to-green-800 px-1 py-0.5 text-[9px] sm:text-[11px] font-bold text-white shadow-lg">
                    <ArrowDownTrayIcon class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                    {{ $t('Quick Access') }}
                </span>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0 flex items-center justify-between gap-3 sm:gap-6">
                <div class="min-w-0 flex flex-col gap-1.5 cursor-pointer" @click="showProductDetails">
                    <div class="text-slate-950 dark:text-white text-sm sm:text-base font-medium leading-normal truncate"
                        :class="available ? '' : 'opacity-30'">
                        {{ props.product?.name }}
                    </div>

                    <div v-if="props.product?.short_description" class="text-slate-500 dark:text-slate-400 text-sm leading-snug line-clamp-2"
                        :class="available ? '' : 'opacity-30'">
                        {{ props.product?.short_description }}
                    </div>

                    <div class="flex items-center gap-2 flex-wrap" :class="available ? '' : 'opacity-30'">
                        <div class="flex items-center gap-1">
                            <StarIcon class="w-4 h-4 text-amber-400" />
                            <div class="text-slate-950 dark:text-white text-sm font-bold leading-tight">
                                {{ props.product?.rating }}
                            </div>
                            <div class="text-slate-500 text-sm font-normal leading-tight">
                                ({{ props.product?.total_reviews }})
                            </div>
                        </div>

                        <div class="h-3 w-px bg-slate-200"></div>

                        <div v-if="available" class="text-slate-500 text-sm font-normal leading-tight">
                            {{ props.product?.total_sold }} {{ $t('Sold') }}
                        </div>
                        <div v-else class="text-red-500 text-sm font-normal leading-tight">
                            {{ $t('Stock Out') }}
                        </div>
                    </div>
                </div>

                <!-- Price -->
                <div class="shrink-0 flex flex-col items-end" :class="available ? '' : 'opacity-30'">
                    <div class="text-primary text-base sm:text-lg font-bold leading-normal">
                        {{ masterStore.showCurrency(props.product?.discount_price > 0 ?
                            props.product?.discount_price : props.product?.price) }}
                    </div>
                    <div v-if="props.product?.discount_price > 0"
                        class="text-slate-400 text-xs sm:text-sm font-normal line-through leading-tight">
                        {{ masterStore.showCurrency(props.product?.price) }}
                    </div>
                </div>
            </div>

            <!-- favorite -->
            <button v-if="props.product?.is_favorite"
                class="shrink-0 w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] justify-center items-center flex cursor-pointer bg-white dark:bg-slate-700 border border-slate-100 dark:border-slate-600"
                @click="favoriteAddOrRemove">
                <HeartIcon class="w-5 h-5 sm:w-6 sm:h-6 text-red-500" />
            </button>
            <button v-else
                class="shrink-0 w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] justify-center items-center flex cursor-pointer bg-white dark:bg-slate-700 border border-slate-100 dark:border-slate-600 transition-all duration-300"
                @click="favoriteAddOrRemove">
                <HeartIconOutline class="w-5 h-5 sm:w-6 sm:h-6 text-slate-600" />
            </button>

            <!-- Buy actions -->
            <div class="hidden md:flex shrink-0 items-center gap-3">
                <template v-if="!isPreOrder">
                    <template v-if="props.product?.quantity > 0">
                        <button v-if="props.product?.is_digital == false"
                            class="cursor-pointer w-10 h-10 bg-slate-200 dark:bg-slate-700 rounded-[10px] border  dark:border-slate-600 justify-center items-center flex shrink-0"
                            @click="addToBasket(props.product)">
                            <div class="w-5 h-5">
                                <ShoppingCartIcon  class="w-5 h-5 text-slate-600 dark:text-slate-300" />
                            </div>
                        </button>

                        <button
                            class="justify-center items-center gap-0.5 flex bg-primary  px-5 py-2.5 rounded-[10px] whitespace-nowrap"
                            @click="buyNow">
                            <div class="text-white text-sm font-normal leading-tight">{{ $t('Buy Now') }}</div>
                        </button>
                    </template>
                    <button v-else
                        class="justify-center items-center gap-0.5 flex border border-red-300 px-5 py-2.5 rounded-[10px] whitespace-nowrap"
                        disabled>
                        <div class="text-red-300 text-sm font-normal leading-tight">
                            {{ $t('Buy Now') }}
                        </div>
                    </button>
                </template>
                <PreOrderCardButton v-else :product="props.product" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { HeartIcon as HeartIconOutline } from '@heroicons/vue/24/outline';
import { HeartIcon, StarIcon, ShoppingCartIcon } from '@heroicons/vue/24/solid';
import { ArrowDownTrayIcon } from '@heroicons/vue/20/solid';
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import BagIcon from '../icons/Bag.vue';
import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';
import localization from '../localization';
import PreOrderCardButton from './PreOrder/PreOrderCardButton.vue';

const router = useRouter();
const authStore = useAuth();

const basketStore = useBasketStore();

const masterStore = useMaster();

const toast = useToast();
const t = localization.i18n.global.t;

const props = defineProps({
    product: Object
});

// ── Pre-Order ──
const isPreOrder = computed(() => masterStore.preOrder && props.product?.is_preorder);
const available = computed(() => props.product?.quantity > 0 || isPreOrder.value);

// No variant picker on the card, so fall back to the product's first
// color/size (matching the default selection on the product details page)
// instead of adding a "no variant" cart line for products that have variants.
const defaultColor = () => props.product?.colors?.length > 0 ? props.product.colors[0].id : null;
const defaultSize = () => props.product?.sizes?.length > 0 ? props.product.sizes[0].id : null;

const addToBasket = (product) => {
    basketStore.addToCart({
        is_buy_now: false,
        product_id: props.product?.id,
        quantity: 1,
        size: defaultSize(),
        color: defaultColor(),
        unit: null
    }, product);
};

const buyNow = () => {
    basketStore.addToCart({
        product_id: props.product?.id,
        is_buy_now: true,
        quantity: 1,
        size: defaultSize(),
        color: defaultColor(),
        unit: null
    }, props.product);

    basketStore.buyNowShopId = props.product?.shop.id;
};

const favoriteAddOrRemove = () => {
    if (authStore.token === null) {
        return authStore.loginModal = true;
    }
    axios.post('/favorite-add-or-remove', {
        product_id: props.product.id
    }, {
        headers: {
            Authorization: authStore.token
        }
    }).then((response) => {
        props.product.is_favorite = response.data.data.product.is_favorite;
        if (props.product.is_favorite === false) {
            toast.warning(t('Product removed from favorite'), {
               position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
        } else {
            toast.success(t('Product added to favorite'), {
               position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
        }
        authStore.favoriteRemove = true;
        authStore.fetchFavoriteProducts();
    });
}

const showProductDetails = () => {
    if (props.product.quantity > 0 || isPreOrder.value) {
        router.push({ name: 'productDetails', params: { slug: props.product.slug } })
    }
}
</script>
