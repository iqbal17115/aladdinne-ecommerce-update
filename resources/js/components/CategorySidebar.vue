<template>
    <div class="relative h-full flex flex-col bg-white dark:bg-slate-800 rounded-[12px] shadow-[0_8px_24px_rgba(15,23,42,0.06)] border border-slate-200 dark:border-slate-700 overflow-visible"
        :dir="master.langDirection || 'ltr'"
        @mouseleave="activeCategory = null">
        <!-- <div class="flex items-center gap-2 px-4 py-3.5 border-b border-slate-100 bg-gradient-to-r from-primary to-primary-600 text-white rounded-t-[22px]">
            <Bars3Icon class="w-5 h-5" />
            <div class="text-sm font-bold leading-normal">{{ $t('All Categories') }}</div>
        </div> -->

        <div class="flex-1 overflow-y-auto py-2">
            <button
                v-for="category in categories"
                :key="category.id"
                type="button"

                 class="w-full px-4 py-3 flex items-center gap-3.5 text-left dark:border-b dark:border-slate-600 dark:last:border-b-0 transition-all duration-200"
                :class="activeCategory?.id === category.id ? 'bg-primary-50 dark:bg-slate-700 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-primary'"
                @mouseenter="setActiveCategory(category)"
                @focus="setActiveCategory(category)"
                @click="goToCategory(category)">
                <img
                    :src="category.icon || category.thumbnail"
                    :alt="category.name"
                    loading="lazy"
                    class="w-7 h-7 object-contain shrink-0" />
                <span class="flex-1 min-w-0 text-[15px] font-medium truncate">{{ category.name }}</span>
                <ChevronRightIcon class="w-4 h-4 shrink-0" />
            </button>
        </div>

        <router-link
            to="/categories"
            class="px-4 py-3.5 border-t border-slate-100 dark:border-slate-700 text-[15px] font-semibold text-slate-700 dark:text-slate-300 hover:text-primary transition-colors flex items-center gap-2">
            <Squares2X2Icon class="w-4 h-4" />
            {{ $t('View All Categories') }}
        </router-link>

        <div
            v-if="activeCategory && activeCategory.sub_categories?.length"
            class="hidden xl:block absolute top-0 z-30 w-fit max-w-[calc(100vw-24rem)]"
            :class="[
                master.langDirection === 'rtl' ? 'right-full pr-4' : 'left-full pl-4'
            ]">
            <div class="rounded-[26px] bg-white dark:bg-slate-800 shadow-[0_30px_70px_rgba(15,23,42,0.14)] dark:shadow-[0_30px_70px_rgba(0,0,0,0.4)] dark:border dark:border-slate-700 p-6 min-h-[620px]">
                <div
                    class="flex flex-row items-stretch gap-6 h-full w-fit max-w-full">
                    <div class="min-w-0" :class="submenuContentWidthClass">
                        <div class="flex items-start justify-between gap-4 rounded-[24px] bg-white dark:bg-slate-800 px-1 pb-5 border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-14 h-14 rounded-full bg-primary-50 flex items-center justify-center shrink-0">
                                    <img
                                        :src="activeCategory.icon || activeCategory.thumbnail"
                                        :alt="activeCategory.name"
                                        class="w-8 h-8 object-contain" />
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xl font-bold text-slate-900 dark:text-white truncate">{{ activeCategory.name }}</div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                        {{ activeCategory.description || $t('Explore curated products from this category.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-5">
                            <div
                                class="grid w-fit max-w-full justify-start gap-y-3"
                                :class="[
                                    activeCategoryColumns.length === 1 ? 'grid-cols-1' : '',
                                    activeCategoryColumns.length === 2 ? 'grid-cols-1 md:grid-cols-2 md:gap-x-4' : '',
                                    activeCategoryColumns.length === 3 ? 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3 md:gap-x-4 xl:gap-x-4' : '',
                                    activeCategoryColumns.length >= 4 ? 'grid-cols-1 md:grid-cols-2 xl:grid-cols-4 md:gap-x-4 xl:gap-x-4' : ''
                                ]">
                                <div
                                    v-for="(column, columnIndex) in activeCategoryColumns"
                                    :key="`subcategory-column-${activeCategory.id}-${columnIndex}`"
                                    class="min-w-0 space-y-2 md:w-[240px]">
                                    <button
                                        v-for="subcategory in column"
                                        :key="`subcategory-${subcategory.id}`"
                                        type="button"
                                        class="group flex w-full items-center gap-2.5 rounded-2xl border-slate-100 dark:border-slate-600 bg-white dark:bg-slate-700 px-2.5 py-2.5 text-left transition-all duration-200 hover:border-primary-200 hover:bg-primary-50/40 dark:hover:bg-slate-600"
                                        @click="goToSubcategory(activeCategory, subcategory)">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-600">
                                            <img
                                                :src="subcategory.thumbnail || activeCategory.icon || activeCategory.thumbnail"
                                                :alt="subcategory.name"
                                                class="h-7 w-7 object-contain" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-[15px] font-medium text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors">
                                                {{ subcategory.name }}
                                            </div>
                                            <div class="truncate text-[12px] font-medium text-slate-600 dark:text-slate-400 group-hover:text-primary transition-colors">
                                                {{ subcategory.short_description }}
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-center">
                                <button
                                    type="button"
                                    class="inline-flex min-w-[220px] items-center justify-center rounded-lg border border-primary px-6 py-3 text-sm font-semibold text-primary transition hover:bg-primary hover:text-white"
                                    @click="goToCategory(activeCategory)">
                                    {{ `${$t('View All')} ${activeCategory.name}` }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="hasActiveCategoryBanner"
                        class="hidden xl:block w-[280px] 2xl:w-[320px] rounded-[24px] overflow-hidden border border-slate-100 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 self-stretch min-h-[420px] relative shrink-0">
                        <img
                            :src="activeCategoryBanner"
                            :alt="`${activeCategory.name} banner`"
                            class="absolute inset-0 w-full h-full object-cover" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { ChevronRightIcon, Squares2X2Icon } from '@heroicons/vue/24/outline';
import { useMaster } from '../stores/MasterStore';

const master = useMaster();
const router = useRouter();

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    }
});

const activeCategory = ref(null);
const MAX_SUBCATEGORY_COLUMNS = 4;
const SUBCATEGORIES_PER_COLUMN = 6;

const activeCategoryBanner = computed(() => activeCategory.value?.banner);

const hasActiveCategoryBanner = computed(() => Boolean(activeCategoryBanner.value));

const submenuContentWidthClass = computed(() => {
    const columnCount = activeCategoryColumns.value.length || 1;

    if (columnCount >= 4) {
        return 'w-[1008px]';
    }

    if (columnCount === 3) {
        return 'w-[752px]';
    }

    if (columnCount === 2) {
        return 'w-[496px]';
    }

    return 'w-[240px]';
});

const activeCategoryColumns = computed(() => {
    const subcategories = (activeCategory.value?.sub_categories || []).slice(0, MAX_SUBCATEGORY_COLUMNS * SUBCATEGORIES_PER_COLUMN);

    if (!subcategories.length) {
        return [];
    }

    const columnCount = Math.min(MAX_SUBCATEGORY_COLUMNS, Math.ceil(subcategories.length / SUBCATEGORIES_PER_COLUMN));

    return Array.from({ length: columnCount }, (_, index) => (
        subcategories.slice(index * SUBCATEGORIES_PER_COLUMN, (index + 1) * SUBCATEGORIES_PER_COLUMN)
    )).filter((column) => column.length);
});

watch(() => props.categories, (categories) => {
    if (!categories.length) {
        activeCategory.value = null;
        return;
    }

    if (activeCategory.value && !categories.find((category) => category.id === activeCategory.value.id)) {
        activeCategory.value = null;
    }
}, { immediate: true });

const setActiveCategory = (category) => {
    activeCategory.value = category;
};

const goToCategory = (category) => {
    if (!category?.slug) {
        return;
    }

    router.push({ name: 'products', query: { category_slug: category.slug } });
};

const goToSubcategory = (category, subcategory) => {
    if (!category?.slug || !subcategory?.slug) {
        return;
    }

    router.push({ name: 'products', query: { category_slug: category.slug, sub_category_slug: subcategory.slug } });
};
</script>
