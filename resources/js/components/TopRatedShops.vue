<template>
    <div class="main-container py-12">
        <div v-if="!isLoading" class="flex justify-between items-center gap-4">
            <div class="text-slate-800 dark:text-white text-lg md:text-xl font-semibold leading-9">
                {{ $t('Top Rated Shops') }}
            </div>

            <router-link to="/shops" class="flex items-center gap-1">
                <div class="text-slate-600 dark:text-slate-400 text-base font-normal leading-normal">{{ $t('View All') }}</div>
                <ArrowRightIcon class="w-5 h-5 text-slate-600 dark:text-slate-400" />
            </router-link>
        </div>

        <SkeletonLoader v-else class="w-48 sm:w-60 md:w-72 lg:w-96 h-12 rounded-lg" />

        <div class="mt-8 relative">
            <swiper v-if="!isLoading" :spaceBetween="16" :navigation="true" :modules="modules"
                class="top-rated-swiper pb-1"
                :breakpoints="{ 0: { slidesPerView: 2 }, 480: { slidesPerView: 3 }, 768: { slidesPerView: 4 }, 1024: { slidesPerView: 5 }, 1280: { slidesPerView: 6 } }">
                <swiper-slide v-for="shop in shops" :key="shop.id">
                    <router-link :to="shopUrl(shop)"
                        class="top-rated-card group bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-lg">
                        <div class="top-rated-logo-wrap">
                            <img :src="shop.logo" :alt="shop.name" loading="lazy"
                                class="w-full h-full object-cover rounded-full" />
                        </div>

                        <div class="mt-1 flex items-center justify-center gap-1 text-xs md:text-sm leading-none">
                            <StarIcon class="w-3.5 h-3.5 text-amber-400 shrink-0" />
                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ formatRating(shop.rating) }}
                            </span>
                            <span v-if="shop.total_reviews" class="text-slate-400">
                                ({{ shop.total_reviews }})
                            </span>
                        </div>

                        <div
                            class="mt-2 text-slate-800 dark:text-slate-100 text-sm md:text-[15px] font-semibold leading-tight truncate w-full group-hover:text-primary transition-colors">
                            {{ shop.name }}
                        </div>

                        <div class="mt-0.5 text-xs md:text-sm text-slate-500 truncate w-full">
                            {{ shop.total_products }} {{ $t('Products') }}
                        </div>

                        <span class="visit-shop-btn">
                            {{ $t('Visit Shop') }}
                        </span>
                    </router-link>
                </swiper-slide>
            </swiper>

            <div v-else class="grid grid-cols-2 xs:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4">
                <div v-for="i in 6" :key="i">
                    <SkeletonLoader class="w-full h-[220px] rounded-lg" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';
import { Navigation } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { shopUrl } from '../composables/useSlugify';
import SkeletonLoader from './SkeletonLoader.vue';
import 'swiper/css';
import 'swiper/css/navigation';

defineProps({
    shops: {
        type: Array,
        default: () => [],
    },
    isLoading: Boolean,
});

const modules = [Navigation];

const formatRating = (rating) => Number(rating || 0).toFixed(1);
</script>

<style scoped>
.top-rated-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1.5rem 1rem 1.25rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    height: 100%;
}

.top-rated-card:hover {
    transform: translateY(-2px);
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

:global(.dark) .top-rated-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

.top-rated-logo-wrap {
    width: 4.5rem;
    height: 4.5rem;
    flex-shrink: 0;
    border-radius: 9999px;
    overflow: hidden;
    background: #f1f5f9 url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%2394a3b8" stroke-width="1.5"><circle cx="12" cy="8" r="3.25"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0" stroke-linecap="round"/></svg>') center/2.25rem no-repeat;
    border: 1px solid #e2e8f0;
}

:global(.dark) .top-rated-logo-wrap {
    background-color: #334155;
    border-color: #475569;
}

.visit-shop-btn {
    margin-top: 0.875rem;
    width: 80%;
    padding: 0.45rem 0.75rem;
    border-radius: 10px;
    border: 1px solid var(--primary);
    color: var(--primary);
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.top-rated-card:hover .visit-shop-btn {
    background-color: var(--primary-50);
}

:global(.dark) .top-rated-card:hover .visit-shop-btn {
    background-color: rgba(var(--primary-rgb), 0.15);
}

.top-rated-swiper :deep(.swiper-button-prev),
.top-rated-swiper :deep(.swiper-button-next) {
    width: 2rem;
    height: 2rem;
    border-radius: 9999px;
    background: white;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
    border: 1px solid #e2e8f0;
    color: #334155;
    top: 38%;
}

:global(.dark) .top-rated-swiper :deep(.swiper-button-prev),
:global(.dark) .top-rated-swiper :deep(.swiper-button-next) {
    background: #1e293b;
    border-color: #475569;
    color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.top-rated-swiper :deep(.swiper-button-prev::after),
.top-rated-swiper :deep(.swiper-button-next::after) {
    font-size: 12px !important;
    font-weight: 900;
}

.top-rated-swiper :deep(.swiper-button-prev) { left: -12px; }
.top-rated-swiper :deep(.swiper-button-next) { right: -12px; }

@media (min-width: 768px) {
    .top-rated-logo-wrap {
        width: 5rem;
        height: 5rem;
    }
}
</style>
