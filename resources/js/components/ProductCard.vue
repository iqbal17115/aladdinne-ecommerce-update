<template>
    <div class="rounded-lg border border-slate-100 dark:border-slate-700 transition-all duration-300 group bg-white dark:bg-slate-800 overflow-hidden relative hover:shadow-md hover:-translate-y-0.5"
        :class="available ? 'hover:border-primary' : ''">

        <div class="flex flex-col">
            <div class="bg-white dark:bg-slate-800">
                <div class="w-full h-36 sm:h-52 overflow-hidden relative"
                    :class="available ? '' : 'opacity-30'">
                    <div class="cursor-pointer w-full h-full" @click="showProductDetails">
                        <!-- thumbnail -->
                        <img :src="props.product?.thumbnail" class="w-full h-full group-hover:scale-110 transition duration-500 object-contain" loading="lazy" />
                    </div>

                    <!--discount--->
                    <div v-if="props.product?.discount_percentage > 0"
                        class="px-1 py-0.5 bg-red-500 rounded-2xl text-white text-xs font-medium absolute top-2 left-2">
                        {{ Math.round(props.product?.discount_percentage) }}% {{ $t('OFF') }}
                    </div>

                    <!--favorite-->
                    <button v-if="props.product?.is_favorite"
                        class="absolute top-2 right-2 w-9 h-9 rounded-[10px] justify-center items-center flex cursor-pointer bg-white dark:bg-slate-700"
                        @click="favoriteAddOrRemove">
                        <HeartIcon class="w-6 h-6 text-red-500" />
                    </button>

                    <!--unfavorite-->
                    <button v-else
                        class="absolute flex sm:hidden group-hover:flex top-2 right-2 w-9 h-9 rounded-[10px] justify-center items-center cursor-pointer bg-white dark:bg-slate-700 transition-all duration-300"
                        @click="favoriteAddOrRemove">
                        <HeartIconOutline class="w-6 h-6 text-slate-600" />
                    </button>

                    <!-- Digital Product Badge -->
                    <span v-if="props.product?.is_digital == true"
                        class="absolute bottom-1 right-2 inline-flex gap-1 items-center rounded-md bg-gradient-to-r from-green-600 to-green-800 px-1.5 py-0.5 text-[11px] font-bold text-white shadow-lg animate-badgePulse"
                    >
                    <ArrowDownTrayIcon class="w-3 h-3" />
                        {{ $t('Quick Access') }}
                    </span>


                </div>
                <div class="cursor-pointer" @click="showProductDetails">
                    <div class="bg-white dark:bg-slate-800 px-3 pt-2 pb-1 flex flex-col items-start gap-2 col-span-2">

                        <div class="text-slate-950 dark:text-white text-base font-normal leading-normal truncate w-full"
                            :class="available ? '' : 'opacity-30'">
                            {{ props.product?.name }}
                        </div>

                        <div class="flex items-center gap-1.5" :class="available ? '' : 'opacity-30'">
                            <StarIcon class="w-4 h-4 text-yellow-400" />
                            <!-- rating -->
                            <div class="text-slate-950 dark:text-white text-sm font-bold leading-tight">
                                {{ props.product?.rating }}
                            </div>
                            <!-- Total Review -->
                            <div class="text-slate-500 text-sm font-normal leading-tight">
                                ({{ props.product?.total_reviews }})
                            </div>
                            <span class="text-slate-300 dark:text-slate-600">&middot;</span>
                            <!-- total sold -->
                            <div v-if="available"
                                class="text-slate-500 text-sm font-normal leading-tight">
                                {{ props.product?.total_sold }} {{ $t('Sold') }}
                            </div>
                            <!-- Stock Out -->
                            <div v-else class="text-red-500 text-sm font-normal leading-tight">
                                {{ $t('Stock Out') }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2" :class="available ? '' : 'opacity-30'">
                            <!-- price -->
                            <div class="text-primary text-base font-bold leading-normal">
                                {{ masterStore.showCurrency(props.product?.discount_price > 0 ?
                                    props.product?.discount_price : props.product?.price) }}
                            </div>
                            <!-- discount price -->
                            <div v-if="props.product?.discount_price > 0"
                                class="text-slate-400 text-sm font-normal line-through leading-tight">
                                {{ masterStore.showCurrency(props.product?.price) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full px-3 pb-3 pt-1">
                <template v-if="!isPreOrder">
                    <div v-if="props.product?.quantity > 0" class="justify-start items-center gap-2 flex w-full">
                        <button v-if="props.product?.is_digital == false"
                            class="cursor-pointer w-10 h-10 shrink-0 bg-slate-200  dark:bg-slate-700 dark:border-slate-500 rounded-[8px] justify-center items-center flex"
                            @click="addToBasket(props.product)">
                            <ShoppingCartIcon class="w-5 h-5 text-slate-800 dark:text-slate-300" />
                        </button>

                        <button
                            class="justify-center items-center gap-0.5 flex bg-primary hover:bg-primary-600 grow py-2.5 rounded-[8px] transition-colors"
                            @click="buyNow">
                            <div class="text-white text-sm font-semibold leading-tight">{{ $t('Buy Now')}}</div>
                        </button>
                    </div>
                    <button v-else
                        class="justify-center items-center gap-0.5 flex bg-red-100 dark:bg-red-900/30 py-2.5 rounded-[8px] w-full"
                        disabled>
                        <div class="text-red-400 text-sm font-semibold leading-tight">
                            <!-- Request Stock -->
                            {{ $t('Buy Now') }}
                        </div>
                    </button>
                </template>
                <PreOrderCardButton v-else :product="props.product" class="w-full" />
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes badgePulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.9; }
}

.animate-badgePulse {
    animation: badgePulse 2s infinite ease-in-out;
}
</style>

<script setup>
import { HeartIcon as HeartIconOutline, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import { HeartIcon, StarIcon, ShoppingCartIcon } from '@heroicons/vue/24/solid';
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';
import localization from '../localization';
import PreOrderCardButton from './PreOrder/PreOrderCardButton.vue';

const router = useRouter();

const masterStore = useMaster();

const basketStore = useBasketStore();
const authStore = useAuth();

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
    // add product to basket
    basketStore.addToCart({
        is_buy_now: false,
        product_id: props.product?.id,
        quantity: 1,
        size: defaultSize(),
        color: defaultColor(),
        unit: null
    }, product);
};

const buyNow = async () => {
    // if (authStore.token === null) {
    //     return authStore.loginModal = true;
    // }

  await basketStore.addToCart({
        product_id: props.product?.id,
        is_buy_now: true,
        quantity: 1,
        size: defaultSize(),
        color: defaultColor(),
        unit: null
    }, props.product);

    basketStore.buyNowShopId = props.product?.shop.id;
    // router.push({ name: 'buynow' })
};

const isFavorite = ref(props.product?.is_favorite);

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
        props.product.is_favorite = !props.product.is_favorite
        isFavorite.value = response.data.data.product.is_favorite
        if (isFavorite.value === false) {
            toast.warning(t('Product removed from favorite'), {
               position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
        } else {
            toast.success(t('Product added to favorite'), {
               position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
        }
        authStore.favoriteRemove = true
        authStore.fetchFavoriteProducts();
    });
}

const showProductDetails = () => {
    if (props.product.quantity > 0 || isPreOrder.value) {
        router.push({ name: 'productDetails', params: { slug: props.product.slug } })
    }
}

</script>
