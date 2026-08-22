<template>
    <div v-if="!isLoading" class="main-container bg-white dark:bg-slate-900 mt-3" :dir="master.langDirection || 'ltr'">
        <div class="grid grid-cols-1 xl:grid-cols-[280px_minmax(0,1fr)] gap-5 xl:gap-6 items-start">
            <CategorySidebar class="hidden xl:flex xl:row-span-2 h-full" :categories="sidebarCategories" />

            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_260px] gap-5">
                <div class="min-w-0 rounded-[14px] overflow-hidden bg-white dark:bg-slate-800">
                    <swiper
                        :navigation="false"
                        :pagination="{ clickable: true }"
                        :slides-per-view="1"
                        :space-between="20"
                        :modules="modules"
                        class="mySwiper"
                        :loop="false"
                        :autoplay="{
                            delay: 4000,
                            disableOnInteraction: false
                        }">
                        <swiper-slide v-for="banner in banners" :key="banner.id">
                            <a
                                v-if="banner.url && isExternalUrl(banner.url)"
                                :href="banner.url"
                                target="_blank"
                                rel="noopener noreferrer">
                                <img :src="banner.thumbnail" loading="lazy" class="w-full h-[200px] sm:h-[280px] md:h-[360px] lg:h-[420px] xl:h-[460px] object-cover" />
                            </a>
                            <router-link v-else-if="banner.url" :to="banner.url">
                                <img :src="banner.thumbnail" loading="lazy" class="w-full h-[200px] sm:h-[280px] md:h-[360px] lg:h-[420px] xl:h-[460px] object-cover" />
                            </router-link>
                            <img
                                v-else
                                :src="banner.thumbnail"
                                loading="lazy"
                                class="w-full h-[200px] sm:h-[280px] md:h-[360px] lg:h-[420px] xl:h-[460px] object-cover" />
                        </swiper-slide>
                    </swiper>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-5 overflow-hidden">
                    <div
                        v-for="ad in ads"
                        :key="ad.id"
                        class="rounded-[12px] overflow-hidden bg-white dark:bg-slate-800">
                        <img :src="ad.thumbnail" loading="lazy" class="w-full h-[180px] lg:h-full object-cover aspect-[4/3]" />
                    </div>
                </div>
            </div>

            <div
                class="support-strip grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 rounded-[14px] bg-white dark:bg-slate-800 px-3 sm:px-4 xl:px-5 py-2 shadow-[0_8px_24px_rgba(15,23,42,0.06)]">
                <div
                    v-for="item in supportItems"
                    :key="item.id"
                    class="support-strip__item flex items-center gap-3 px-3 py-3 sm:px-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary/10 via-white dark:via-slate-700 to-primary/5 ring-1 ring-slate-100 dark:ring-slate-600">
                        <img :src="item.icon" class="w-6 h-6 shrink-0 object-contain" loading="lazy" alt="icon" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-[15px] font-semibold text-slate-900 dark:text-white leading-tight">{{ item.title }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-5">{{ item.description }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="main-container mt-3">
        <div class="grid grid-cols-1 xl:grid-cols-[280px_minmax(0,1fr)] gap-5 xl:gap-6">
            <div class="hidden xl:block h-full">
                <SkeletonLoader class="w-full h-[540px] rounded-[24px]" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_260px] gap-5">
                <div class="w-full h-[200px] sm:h-[280px] md:h-[360px] lg:h-[420px] xl:h-[460px] rounded-[24px] overflow-hidden">
                    <SkeletonLoader class="w-full h-full rounded-[24px]" />
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-5">
                    <div v-for="i in 2" :key="i" class="w-full h-[180px] rounded-[24px] overflow-hidden">
                        <SkeletonLoader class="w-full h-full rounded-[24px]" />
                    </div>
                </div>
            </div>

            <div class="rounded-[24px] bg-white dark:bg-slate-800 px-3 sm:px-4 xl:px-5 py-2 shadow-[0_20px_48px_rgba(15,23,42,0.10)]">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5">
                    <SkeletonLoader v-for="i in 5" :key="`support-${i}`" class="w-full h-[84px] rounded-2xl" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, A11y, Autoplay } from 'swiper/modules';
import { computed } from 'vue';
import { useMaster } from '../stores/MasterStore';
import CategorySidebar from './CategorySidebar.vue';
const master = useMaster();
import SkeletonLoader from './SkeletonLoader.vue';

// Import Swiper styles
import 'swiper/css';

import 'swiper/css/navigation';
import 'swiper/css/pagination';

const modules = [
    Navigation, Pagination, A11y, Autoplay
];

const sidebarCategories = computed(() => master.categories.slice(0, 12));

const props = defineProps({
    banners: Array,
    ads: Array,
    supportItems: {
        type: Array,
        default: () => []
    },
    isLoading: {
        type: Boolean,
        default: true
    }
})

const isExternalUrl = (url) => /^https?:\/\//i.test(url);

</script>

<style>
.mySwiper .swiper-button-prev,
.mySwiper .swiper-button-next {
    position: absolute;
    width: 28px;
    height: 28px;
    background-color: #fff;
    color: #475569 !important;
    border-radius: 8px !important;
    margin-top: auto;
}

.mySwiper .swiper-button-next {
    left: auto;
    right: 18px;
    bottom: 18px;
}

.mySwiper .swiper-button-prev {
    left: auto;
    right: 54px;
    bottom: 18px;
}

.mySwiper .swiper-button-prev:after,
.mySwiper .swiper-button-next:after {
    font-size: 16px !important;
}

.mySwiper .swiper-pagination-bullet-active {
    @apply bg-primary w-6 h-2 rounded;
}

.support-strip__item {
    position: relative;
}

@media (min-width: 1280px) {
    .support-strip__item:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 50%;
        right: 0;
        width: 1px;
        height: 42px;
        transform: translateY(-50%);
        background: linear-gradient(180deg, rgba(226, 232, 240, 0), rgba(226, 232, 240, 1), rgba(226, 232, 240, 0));
    }
}
</style>
