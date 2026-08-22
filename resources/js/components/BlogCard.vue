<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden cursor-pointer group shadow-sm hover:shadow-md transition-shadow duration-300"
        @click="goToBlogDetails()">
        <div class="w-full h-52 overflow-hidden">
            <img :src="blog?.thumbnail"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy" />
        </div>

        <div class="p-4">
            <span     class="inline-flex items-center rounded-md bg-gray-100 dark:bg-slate-700 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-slate-300">
                {{ blog?.category?.name }}
            </span>

            <h3 class="text-slate-900 dark:text-white text-base font-bold mt-2 line-clamp-2 leading-snug">
                {{ blog?.title }}
            </h3>

            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1.5 line-clamp-2 leading-relaxed">
                {{ blog?.description }}
            </p>

            <div class="flex items-center gap-3 mt-3 text-slate-400 dark:text-slate-500 text-xs">
                <span>{{ blog?.created_at }}</span>
                <span class="flex items-center gap-1">
                    <EyeIcon class="w-3.5 h-3.5" />
                    {{ blog?.total_views }} {{ $t('views') }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { EyeIcon } from '@heroicons/vue/24/outline';

const router = useRouter();

const props = defineProps({
    blog: Object
});

const goToBlogDetails = () => {
    router.push('/blog/' + props.blog?.slug);
};
</script>
