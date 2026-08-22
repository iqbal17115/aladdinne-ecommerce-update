<template>
    <div class="p-4 bg-white dark:bg-slate-700 rounded-xl border border-slate-100 dark:border-slate-600 cursor-pointer" @click="goToBlogDetails()">
        <div class="grid grid-cols-3 gap-3 items-center">
            <div class="grid-cols-1 h-[118px] overflow-hidden cursor-pointer">
                <img :src="blog?.thumbnail"
                    class="w-full h-full group-hover:scale-110 transition duration-500 object-cover rounded-lg" loading="lazy" />
            </div>
            <div class="col-span-2">
                <div class="text-slate-900 dark:text-white text-lg font-bold overflow-hidden max-lines-2">
                    {{ blog?.title }}
                </div>

                <div class="text-slate-600 dark:text-slate-400 text-sm font-normal mt-2 overflow-hidden max-lines-2">
                    {{ blog?.description }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const props = defineProps({
    blog: Object
});

const goToBlogDetails = () => {
    router.push('/blog/' + props.blog?.slug);
}
</script>

<style scoped>
.max-lines-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
