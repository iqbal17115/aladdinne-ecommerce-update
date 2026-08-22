<template>
    <router-link :to="routeUrl" class="block w-full">
        <div class="group flex flex-col items-center text-center gap-2.5 md:gap-3 py-2 px-1">
            <div
                class="category-card-circle relative flex items-center justify-center w-[68px] h-[68px] xs:w-[76px] xs:h-[76px] md:w-24 md:h-24 rounded-full overflow-hidden transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-[0_18px_35px_rgba(15,23,42,0.12)]"
            >
                <img
                    :src="imageSrc"
                    :alt="props.category?.name"
                    class="w-10 h-10 xs:w-11 xs:h-11 md:w-14 md:h-14 object-contain transition duration-500 group-hover:scale-110"
                    loading="lazy"
                />
            </div>
            <div
                class="max-w-full text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors text-[11px] xs:text-xs md:text-sm font-semibold leading-4 md:leading-5 line-clamp-2 min-h-[2rem] md:min-h-[2.5rem]"
            >
                {{ props.category?.name }}
            </div>
        </div>
    </router-link>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const props = defineProps({
    category: Object
});

const routeUrl = ref({ name: 'products', query: { category_slug: props.category?.slug } });
const imageSrc = computed(() => props.category?.icon || props.category?.thumbnail);

onMounted(() => {
    if (route.name === 'shop-detail') {
        routeUrl.value = `/shops/${route.params.id}/categories/${props.category?.id}`
    }
});

</script>

<style scoped>
.category-card-circle {
    background:
        radial-gradient(circle at top, rgba(255, 255, 255, 0.96), rgba(255, 255, 255, 0.65) 45%, rgba(248, 250, 252, 0.95) 100%),
        linear-gradient(135deg, #eef2ff 0%, #fff1f2 48%, #fef3c7 100%);
    box-shadow: 0 10px 25px rgba(148, 163, 184, 0.18);
}
</style>
