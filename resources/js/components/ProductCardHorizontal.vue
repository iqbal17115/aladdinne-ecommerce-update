<template>
    <div class="rounded-lg border dark:border-slate-700 transition-all duration-300 group bg-white dark:bg-slate-800 overflow-hidden relative"
        :class="available ? 'hover:border-primary' : ''">

        <div class="grid grid-cols-3">
            <div class="w-full h-[124px] overflow-hidden shrink-0 cursor-pointer" @click="showProductDetails"
                :class="available ? '' : 'opacity-30'">
                <img :src="props.product?.thumbnail" class="w-full h-full group-hover:scale-110 transition duration-500 object-contain" loading="lazy"/>
            </div>

            <div class="bg-white dark:bg-slate-800 p-2 flex flex-col items-start gap-2 col-span-2">
                <div class="flex flex-col items-start gap-2 w-full cursor-pointer" @click="showProductDetails">

                    <div class="text-slate-950 dark:text-white text-base font-normal leading-normal truncate w-full"
                        :class="available ? '' : 'opacity-30'">
                        {{ props.product?.name }}
                    </div>

                    <div class="flex items-center gap-2 flex-wrap"
                        :class="available ? '' : 'opacity-30'">
                        <div class="text-primary text-base font-bold leading-normal">
                            {{ masterStore.showCurrency(props.product?.discount_price > 0 ?
                                props.product?.discount_price : props.product?.price) }}
                        </div>
                        <div v-if="props.product?.discount_price > 0"
                            class="text-slate-400 text-sm font-normal line-through leading-tight">
                            {{ masterStore.showCurrency(props.product?.price) }}
                        </div>
                        <div v-if="props.product?.discount_percentage > 0"
                            class="px-1 py-0.5 bg-red-500 rounded-2xl text-white text-xs font-medium">
                             {{ Math.round(props.product?.discount_percentage) }}% {{ $t('OFF') }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center w-full">
                        <div class="flex items-center gap-1" :class="available ? '' : 'opacity-30'">
                            <StarIcon class="w-4 h-4 text-yellow-400" />
                            <div class="text-slate-950 dark:text-white text-sm font-bold leading-tight">
                                {{ props.product?.rating.toFixed(1) }}
                            </div>
                            <div class="text-slate-500 dark:text-slate-400 text-sm font-normal leading-tight">
                                ({{ props.product?.total_reviews }})
                            </div>
                        </div>

                        <div class="h-3 w-[0px] border border-slate-200 dark:border-slate-600"></div>

                        <div v-if="available"
                            class="text-right text-slate-500 text-sm font-normal leading-tight">
                            {{ props.product?.total_sold }} {{ $t('Sold') }}
                        </div>
                        <div v-else class="text-right text-red-500 text-sm font-normal leading-tight">
                            {{ $t('Stock Out') }}
                        </div>
                    </div>
                </div>

                <template v-if="!isPreOrder">
                    <div v-if="props.product?.quantity > 0" class="justify-start items-center gap-3 flex">
                        <button class="cursor-pointer" @click="addToBasket(props.product)">
                            <div class="w-5 h-5">
                                <BagIcon />
                            </div>
                        </button>

                        <div class="w-1.5 h-1.5 bg-slate-200 dark:bg-slate-600 rounded-md"></div>

                        <button class="justify-center items-center gap-0.5 flex" @click="buyNow">
                            <div class="text-slate-600 dark:text-slate-300 text-sm font-normal leading-tight">
                                {{ $t('Buy Now') }}
                            </div>
                            <ArrowRightIcon class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                        </button>
                    </div>
                    <button v-else class="justify-start items-center gap-2 flex">
                        <div class="text-red-500 text-sm font-normal leading-tight">{{ $t('Request Stock') }}</div>
                        <ArrowRightIcon class="w-4 h-4 text-red-500" />
                    </button>
                </template>
                <PreOrderCardButton v-else :product="props.product" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ArrowRightIcon, StarIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import BagIcon from '../icons/Bag.vue';
import { useAuth } from '../stores/AuthStore';
import { useBasketStore } from '../stores/BasketStore';
import { useMaster } from '../stores/MasterStore';
import PreOrderCardButton from './PreOrder/PreOrderCardButton.vue';

const router = new useRouter();
const authStore = useAuth();

const basketStore = useBasketStore();

const masterStore = useMaster();

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

const buyNow = () => {
    // if (authStore.token === null) {
    //     return authStore.loginModal = true;
    // }

    basketStore.addToCart({
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

const showProductDetails = () => {
    if (props.product.quantity > 0 || isPreOrder.value) {
        router.push({ name: 'productDetails', params: { slug: props.product.slug } })
    }
}

</script>
