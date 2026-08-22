<template>
    <section class="main-container mt-10 mb-12 overflow-hidden">

        <div v-if="!isLoading" class="flex items-center justify-between gap-3 sm:gap-4 flex-wrap">
            <div class="text-slate-950 dark:text-white text-lg sm:text-lg md:text-xl font-semibold leading-tight">
                {{ $t('Shop By Categories') }}
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <router-link
                    to="/categories"
                    class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-400 hover:text-primary text-xs sm:text-sm md:text-base font-medium transition-colors"
                >
                    {{ $t('View All') }}
                    <ChevronRightIcon class="w-4 h-4" />
                </router-link>
                <button
                    class="md:hidden w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-primary hover:text-primary justify-center items-center flex transition-all shrink-0"
                    @click="swiperNextSlide"
                >
                    <ChevronRightIcon class="w-5 h-5 text-current" />
                </button>
            </div>
        </div>
        <!-- loading -->
        <SkeletonLoader v-else class="w-48 sm:w-60 md:w-72 lg:w-96 h-12 rounded-lg" />

        <div class="mt-5 sm:mt-6 md:mt-8" :dir="master.langDirection || 'ltr'">
            <swiper
                :breakpoints="breakpoints"
                :loop="false"
                :watch-overflow="true"
                ref="swiperRef"
                class="categories-swiper"
                @swiper="onSwiper"
                :modules="[Navigation]"
            >
                <swiper-slide v-if="!isLoading" v-for="category in categories" :key="category.id">
                    <CategoryCard :category="category" />
                </swiper-slide>

                <!-- loading -->
                <swiper-slide v-else v-for="i in 8" :key="i">
                    <SkeletonLoader class="w-full h-[146px] rounded-lg" />
                </swiper-slide>
            </swiper>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { ChevronRightIcon } from '@heroicons/vue/20/solid';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation } from 'swiper/modules';
import CategoryCard from './CategoryCard.vue';
import SkeletonLoader from './SkeletonLoader.vue';
import { useMaster } from '../stores/MasterStore';

const master = useMaster();
import 'swiper/css';

const props = defineProps({
    categories: Array,
    isLoading: {
        type: Boolean,
        default: true
    }
});

const swiperInstance = ref()

function onSwiper(swiper) {
    swiperInstance.value = swiper
}

const swiperNextSlide = () => {
    if (!swiperInstance.value) return;
    swiperInstance.value.slideNext();
};

const breakpoints = {
    0: {
        slidesPerView: 2,
        spaceBetween: 10
    },
    420: {
        slidesPerView: 2.4,
        spaceBetween: 12
    },
    576: {
        slidesPerView: 3.2,
        spaceBetween: 14
    },
    768: {
        slidesPerView: 4,
        spaceBetween: 16
    },
    992: {
        slidesPerView: 5,
        spaceBetween: 18
    },
    1024: {
        slidesPerView: 6,
        spaceBetween: 20
    },
    1280: {
        slidesPerView: 8,
        spaceBetween: 24
    }
};

</script>

<style lang="scss" scoped>
.categories-swiper :deep(.swiper-slide) {
    height: auto;
}
</style>
