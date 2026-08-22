<template>
    <div class="main-container pt-8 pb-12">
        <div class="text-slate-800 dark:text-slate-100 text-lg lg:text-xl font-bold">{{ $t('All Categories') }}</div>

        <!-- categories with subcategories -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4 lg:gap-6 items-start">
            <div v-for="category in categories" :key="category.id"
                class="category-grid-card rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 overflow-hidden h-full">

                <!-- category header -->
                <div class="flex items-start justify-between gap-4 p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700">
                    <router-link :to="{ name: 'products', query: { category_slug: category.slug } }" class="flex items-center gap-3 min-w-0 group flex-1">
                        <div
                            class="category-card-circle relative flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-full overflow-hidden shrink-0">
                            <img :src="category.icon || category.thumbnail" :alt="category.name"
                                class="w-8 h-8 sm:w-9 sm:h-9 object-contain" loading="lazy" />
                        </div>
                        <div class="min-w-0">
                            <div
                                class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-100 group-hover:text-primary transition-colors line-clamp-1">
                                {{ category.name }}
                            </div>
                            <div class="text-xs sm:text-sm text-slate-500 mt-0.5 line-clamp-2">
                                {{ category.description
                                    ? category.description
                                    : `${category.sub_categories?.length || 0} ${$t('subcategories available')}` }}
                            </div>
                        </div>
                    </router-link>

                    <router-link :to="{ name: 'products', query: { category_slug: category.slug } }"
                        class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary-700 transition shrink-0">
                        {{ $t('View All') }}
                        <ChevronRightIcon class="w-4 h-4" />
                    </router-link>
                </div>

                <!-- subcategories -->
                <div v-if="category.sub_categories?.length"
                    class="p-4 sm:p-5 grid grid-cols-1 xs:grid-cols-2 gap-3">
                    <router-link v-for="subcategory in category.sub_categories" :key="subcategory.id"
                        :to="{ name: 'products', query: { category_slug: category.slug, sub_category_slug: subcategory.slug } }"
                        class="group p-3 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-primary hover:bg-primary-50/40 transition-all duration-200 flex items-center gap-3">
                        <img :src="subcategory.thumbnail" :alt="subcategory.name"
                            class="w-10 h-10 rounded-lg object-cover border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0" loading="lazy" />
                        <div class="min-w-0 text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-primary truncate">
                            {{ subcategory.name }}
                        </div>
                    </router-link>
                </div>

                <div v-else class="p-4 sm:p-5 text-sm text-slate-500">
                    {{ $t('No subcategories found for this category.') }}
                </div>
            </div>

            <div v-if="categories.length == 0"
                class="text-slate-950 dark:text-white text-xl font-medium leading-7 md:col-span-2 2xl:col-span-3">
                {{ $t('No Categories Found') }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ChevronRightIcon } from '@heroicons/vue/24/outline';

const categories = ref([]);

onMounted(() => {
    fetchCategories();
    window.scrollTo(0, 0);
});

const fetchCategories = async () => {
    axios.get('/categories').then((response) => {
        categories.value = response.data.data.categories;
    })
};

</script>

<style scoped>
.category-grid-card {
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.category-grid-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.09);
    border-color: rgba(251, 146, 60, 0.2);
}

.category-card-circle {
    background:
        radial-gradient(circle at top, rgba(255, 255, 255, 0.96), rgba(255, 255, 255, 0.65) 45%, rgba(248, 250, 252, 0.95) 100%),
        linear-gradient(135deg, #eef2ff 0%, #fff1f2 48%, #fef3c7 100%);
    box-shadow: 0 10px 25px rgba(148, 163, 184, 0.18);
}
</style>
