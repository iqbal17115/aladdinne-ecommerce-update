<template>
    <!-- Popular Products
    <section>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-slate-800 text-base font-bold">{{ $t('Popular Products') }}</h3>
            <router-link :to="{ name: 'products' }"
                class="text-primary text-xs font-semibold hover:underline">
                {{ $t('View all') }}
            </router-link>
        </div>

        <swiper :spaceBetween="12" :freeMode="true" :navigation="true"
            :modules="modules" class="sidebar-swiper pb-2"
            :breakpoints="{ 0: { slidesPerView: 2 }, 640: { slidesPerView: 3 }, 768: { slidesPerView: 4 }, 1024: { slidesPerView: 5 }, 1280: { slidesPerView: 6 } }">
            <swiper-slide v-for="p in popularProducts" :key="p.id">
                <router-link :to="{ name: 'productDetails', params: { slug: p.slug } }" class="block group">
                    <div class="rounded-xl overflow-hidden bg-slate-50 border border-slate-100 aspect-square flex items-center justify-center p-2">
                        <img :src="p.thumbnail" :alt="p.name"
                            class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" />
                    </div>
                    <div class="mt-2 px-0.5">
                        <p class="text-slate-700 text-xs font-medium line-clamp-2 leading-snug">{{ p.name }}</p>
                        <div class="flex items-center justify-between mt-1.5 gap-1">
                            <span class="text-slate-900 text-sm font-bold leading-none">
                                {{ masterStore.showCurrency(parseFloat(p.discount_price || p.price).toFixed(2)) }}
                            </span>
                            <div class="flex items-center gap-0.5 shrink-0">
                                <StarIcon class="w-3 h-3 text-amber-400" />
                                <span class="text-slate-500 text-[11px] font-medium">{{ p.rating }}</span>
                            </div>
                        </div>
                        <button
                            class="mt-2 w-full py-1.5 text-[11px] font-semibold rounded-lg border border-primary text-primary hover:bg-primary hover:text-white transition-colors">
                            {{ $t('Buy Now') }}
                        </button>
                    </div>
                </router-link>
            </swiper-slide>
        </swiper>
    </section>
    -->

    <!-- You may also like -->
    <section v-if="relatedProducts?.length > 0" class="mt-2 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-slate-800 text-base font-bold">{{ $t('You may also like') }}</h3>
            <router-link :to="{ name: 'products' }"
                class="text-primary text-xs font-semibold hover:underline">
                {{ $t('View all') }}
            </router-link>
        </div>

        <swiper :spaceBetween="12" :freeMode="true" :navigation="true"
            :modules="modules" class="sidebar-swiper pb-2"
            :breakpoints="{ 0: { slidesPerView: 2 }, 640: { slidesPerView: 3 }, 768: { slidesPerView: 4 }, 1024: { slidesPerView: 5 }, 1280: { slidesPerView: 6 } }">
            <swiper-slide v-for="p in relatedProducts" :key="p.id">
                <ProductCard :product="p" />
            </swiper-slide>
        </swiper>
    </section>
</template>

<script setup>
import { FreeMode, Navigation } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { useMaster } from '../stores/MasterStore';
import ProductCard from './ProductCard.vue';
import 'swiper/css';
import 'swiper/css/free-mode';
import 'swiper/css/navigation';

const masterStore = useMaster();
const modules = [FreeMode, Navigation];

defineProps({
    product: Object,
    popularProducts: Array,
    relatedProducts: Array,
});
</script>

<style>
.sidebar-swiper .swiper-button-prev,
.sidebar-swiper .swiper-button-next {
    @apply w-7 h-7 rounded-full bg-white shadow-md border border-slate-200 text-slate-600 mt-0 top-[38%];
}
.sidebar-swiper .swiper-button-prev::after,
.sidebar-swiper .swiper-button-next::after {
    font-size: 10px !important;
    font-weight: 900;
}
.sidebar-swiper .swiper-button-prev { left: -4px; }
.sidebar-swiper .swiper-button-next { right: -4px; }
</style>
