<template>
    <router-link :to="shopUrl(props.shop)"
        class="w-full flex items-center gap-4 bg-white dark:bg-slate-800 rounded-2xl p-4 hover:shadow-md transition duration-300">

        <!--==== Shop Logo ====-->
        <div class="w-16 h-16 shrink-0 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700">
            <img :src="props.shop?.logo" alt="logo" loading="lazy" class="w-full h-full object-cover" />
        </div>

        <div class="min-w-0 flex-1">
            <!--==== Shop Name ====-->
            <div class="text-slate-800 dark:text-slate-100 text-base font-bold leading-normal truncate">
                {{ props.shop?.name }}
            </div>

            <!--==== Rating ====-->
            <div class="flex items-center gap-1 mt-0.5">
                <StarIcon class="w-4 h-4 text-yellow-400" />
                <span class="text-slate-700 dark:text-slate-300 text-sm font-bold">{{ props.shop?.rating?.toFixed(1) }}</span>
                <span v-if="props.shop?.total_reviews" class="text-slate-400 text-sm font-normal">
                    ({{ props.shop?.total_reviews }})
                </span>
            </div>

            <!--==== Products ====-->
            <div class="text-slate-500 text-sm font-normal leading-tight mt-0.5">
                {{ props.shop?.total_products }}+ {{ $t('Products') }}
            </div>
        </div>

    </router-link>
</template>

<script setup>
import { StarIcon } from '@heroicons/vue/24/solid';
import { shopUrl } from '../composables/useSlugify';

const props = defineProps({
    shop: Object
});

</script>
