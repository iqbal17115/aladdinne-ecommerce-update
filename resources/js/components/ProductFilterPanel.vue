<template>
    <div class="bg-white dark:bg-slate-800"
        :class="showCard ? 'rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col h-full overflow-hidden' : 'p-4'">

        <!-- Header -->
        <div class="flex justify-between items-center shrink-0"
            :class="showCard ? 'px-4 pt-4 pb-3' : 'pb-3 border-b border-slate-100 dark:border-slate-700'">
            <div class="text-slate-950 dark:text-white text-lg font-bold leading-normal">
                {{ $t("Filter By") }}
            </div>
            <button type="button" class="text-primary text-sm font-medium hover:underline" @click="onClear">
                {{ $t("Clear All") }}
            </button>
        </div>

        <!-- Scrollable sections -->
        <div :class="showCard ? 'px-4 pb-5 overflow-y-auto flex-1 min-h-0' : ''">

         <!-- Product Price -->
        <div class="py-4 border-b border-slate-100 dark:border-slate-700">
            <button type="button" class="w-full flex justify-between items-center"
                @click="openSections.price = !openSections.price">
                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
                    {{ $t("Product Price") }}
                </div>
                <ChevronUpIcon class="w-4 h-4 text-slate-500 transition-transform"
                    :class="openSections.price ? '' : 'rotate-180'" />
            </button>
            <div v-show="openSections.price" class="mt-3">
                <div class="w-[96%] mx-auto">
                    <vue-slider v-model="range" :min="filter.min_price" :max="filter.max_price"></vue-slider>
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <input type="number" v-model.number="range[0]" :min="filter.min_price" :max="range[1]"
                        class="w-0 grow p-2 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 outline-none text-sm" />
                    <span class="text-slate-400 dark:text-slate-500 text-sm">{{ $t("To") }}</span>
                    <input type="number" v-model.number="range[1]" :min="range[0]" :max="filter.max_price"
                        class="w-0 grow p-2 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 outline-none text-sm" />
                    <button type="button"
                        class="px-3 py-2 bg-primary rounded-md text-white text-sm font-medium shrink-0"
                        @click="onApply">
                        {{ $t("Apply") }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Sort by -->
        <div class="py-4 border-b border-slate-100 dark:border-slate-700">
            <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal mb-2">
                {{ $t("Sort By") }}
            </div>
            <select v-model="form.sort_type"
                class="w-full p-2.5 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 outline-none text-sm text-slate-700 dark:text-slate-300">
                <option v-for="shortBy in filterSortBy" :key="shortBy.value" :value="shortBy.value">
                    {{ $t(shortBy.name) }}
                </option>
            </select>
        </div>

        <!-- Digital Products -->
        <div class="py-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between gap-3">
                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
                    {{ $t("Digital Products") }}
                </div>
                <button type="button"
                    class="relative w-11 h-6 rounded-full transition-colors duration-200 focus:outline-none shrink-0"
                    :class="form.is_digital ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-600'"
                    @click="form.is_digital = form.is_digital ? null : true">
                    <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                        :class="form.is_digital ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
        </div>

        <!-- Preorder Products -->
        <div v-if="master.preOrder" class="py-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between gap-3">
                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
                    {{ $t("Preorder Products") }}
                </div>
                <button type="button"
                    class="relative w-11 h-6 rounded-full transition-colors duration-200 focus:outline-none shrink-0"
                    :class="form.is_preorder ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-600'"
                    @click="form.is_preorder = form.is_preorder ? null : true">
                    <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                        :class="form.is_preorder ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
        </div>

        <!-- Categories -->
        <div class="py-4 border-b border-slate-100 dark:border-slate-700">
            <button type="button" class="w-full flex justify-between items-center"
                @click="openSections.categories = !openSections.categories">
                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
                    {{ $t("Categories") }}
                </div>
                <ChevronUpIcon class="w-4 h-4 text-slate-500 transition-transform"
                    :class="openSections.categories ? '' : 'rotate-180'" />
            </button>
            <div v-show="openSections.categories" class="flex flex-col gap-1 mt-3">
                <div v-for="category in visibleCategories" :key="category.id">
                    <div class="flex items-center justify-between gap-2 px-1 py-1.5">
                        <label class="cursor-pointer text-slate-700 dark:text-slate-300 flex items-center gap-2 grow min-w-0">
                            <input type="checkbox" :checked="form.category_id == category.id"
                                @change="selectCategory(category)"
                                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary shrink-0" />
                            <span class="text-sm truncate">{{ category.name }}</span>
                        </label>
                        <button v-if="category.sub_categories?.length" type="button"
                            class="shrink-0 text-slate-400 hover:text-primary"
                            @click="toggleExpandedCategory(category.id)">
                            <ChevronDownIcon class="w-4 h-4 transition-transform"
                                :class="expandedCategoryId == category.id ? 'rotate-180' : ''" />
                        </button>
                    </div>
                    <div v-if="category.sub_categories?.length && expandedCategoryId == category.id"
                        class="flex flex-col gap-1 ps-6">
                        <label v-for="sub in category.sub_categories" :key="sub.id"
                            class="cursor-pointer text-slate-600 dark:text-slate-400 flex items-center gap-2 px-1 py-1">
                            <input type="checkbox" :checked="form.sub_category_id == sub.id"
                                @change="selectSubCategory(category, sub)"
                                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary" />
                            <span class="text-sm truncate">{{ sub.name }}</span>
                        </label>
                    </div>
                </div>
                <button v-if="master.categories.length > categoryLimit" type="button"
                    class="text-primary text-sm font-medium text-left mt-1"
                    @click="showAllCategories = !showAllCategories">
                    {{ showAllCategories ? $t("View Less") : $t("View More") }}
                </button>
            </div>
        </div>

        <!-- Brand -->
        <div v-if="filter.brands?.length" class="py-4 border-b border-slate-100 dark:border-slate-700">
            <button type="button" class="w-full flex justify-between items-center"
                @click="openSections.brands = !openSections.brands">
                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
                    {{ $t("Brands") }}
                </div>
                <ChevronUpIcon class="w-4 h-4 text-slate-500 transition-transform"
                    :class="openSections.brands ? '' : 'rotate-180'" />
            </button>
            <div v-show="openSections.brands" class="flex flex-col gap-1 mt-3">
                <label v-for="brand in visibleBrands" :key="brand.id"
                    class="cursor-pointer text-slate-700 dark:text-slate-300 flex items-center gap-2 px-1 py-1">
                    <input type="checkbox" :checked="form.brand_id == brand.id"
                        @change="toggleSingle('brand_id', brand.id)"
                        class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary" />
                    <span class="text-sm truncate">{{ brand.name }}</span>
                </label>
                <button v-if="filter.brands.length > brandLimit" type="button"
                    class="text-primary text-sm font-medium text-left mt-1"
                    @click="showAllBrands = !showAllBrands">
                    {{ showAllBrands ? $t("View Less") : $t("View More") }}
                </button>
            </div>
        </div>

        <!-- Color -->
        <div v-if="filter.colors?.length" class="py-4 border-b border-slate-100 dark:border-slate-700">
            <button type="button" class="w-full flex justify-between items-center"
                @click="openSections.colors = !openSections.colors">
                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
                    {{ $t("Color") }}
                </div>
                <ChevronUpIcon class="w-4 h-4 text-slate-500 transition-transform"
                    :class="openSections.colors ? '' : 'rotate-180'" />
            </button>
            <div v-show="openSections.colors" class="flex flex-wrap gap-3 mt-3">
                <button v-for="color in filter.colors" :key="color.id" type="button"
                    :title="color.name"
                    class="w-7 h-7 rounded-full border-2 transition"
                    :class="form.color_id == color.id ? 'border-primary' : 'border-transparent'"
                    :style="{ backgroundColor: color.color_code }"
                    @click="toggleSingle('color_id', color.id)"></button>
            </div>
        </div>

        <!-- Size -->
        <div v-if="filter.sizes?.length" class="py-4">
            <button type="button" class="w-full flex justify-between items-center"
                @click="openSections.sizes = !openSections.sizes">
                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
                    {{ $t("Size") }}
                </div>
                <ChevronUpIcon class="w-4 h-4 text-slate-500 transition-transform"
                    :class="openSections.sizes ? '' : 'rotate-180'" />
            </button>
            <div v-show="openSections.sizes" class="flex flex-wrap gap-2 mt-3">
                <button v-for="size in filter.sizes" :key="size.id" type="button"
                    class="min-w-[42px] px-2 py-1.5 rounded-md border text-sm transition"
                    :class="form.size_id == size.id ? 'border-primary text-primary bg-primary-50' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-primary dark:bg-slate-700'"
                    @click="toggleSingle('size_id', size.id)">
                    {{ size.name }}
                </button>
            </div>
        </div>


        <!-- Customer Review -->
        <div class="py-4 border-b border-slate-100 dark:border-slate-700">
            <button type="button" class="w-full flex justify-between items-center"
                @click="openSections.review = !openSections.review">
                <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
                    {{ $t("Customer Review") }}
                </div>
                <ChevronUpIcon class="w-4 h-4 text-slate-500 transition-transform"
                    :class="openSections.review ? '' : 'rotate-180'" />
            </button>
            <div v-show="openSections.review" class="flex flex-col gap-2 mt-3">
                <label v-for="ratingNumber in ratings" :key="ratingNumber" :for="`rating${ratingNumber}-${uid}`"
                    class="cursor-pointer text-slate-700 dark:text-slate-300 flex items-center gap-2 px-1 py-1">
                    <input type="checkbox" :id="`rating${ratingNumber}-${uid}`"
                        :checked="form.rating == ratingNumber"
                        @change="toggleSingle('rating', ratingNumber)"
                        class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary" />
                    <span class="flex items-center">
                        <StarIcon v-for="i in 5" :key="i" class="w-4 h-4"
                            :class="i <= ratingNumber ? 'text-amber-500' : 'text-gray-200 dark:text-slate-600'" />
                    </span>
                    <span class="text-sm">&amp; {{ $t("Up") }}</span>
                </label>
            </div>
        </div>


            <!-- Apply -->
            <button type="button"
                class="w-full mt-4 px-4 py-3 bg-primary rounded-[10px] text-white text-base font-medium leading-normal"
                @click="onApply">
                {{ $t("Apply Filter") }}
            </button>
        </div>
        <!-- end scrollable sections -->
    </div>
</template>

<script setup>
import { computed, reactive, ref } from "vue";
import { ChevronDownIcon, ChevronUpIcon } from "@heroicons/vue/24/outline";
import { StarIcon } from "@heroicons/vue/24/solid";
import { useMaster } from "../stores/MasterStore";
import VueSlider from "vue-slider-component";
import "vue-slider-component/theme/default.css";

const master = useMaster();
const uid = Math.random().toString(36).slice(2, 8);

const props = defineProps({
    filter: { type: Object, required: true },
    filterSortBy: { type: Array, required: true },
    ratings: { type: Array, required: true },
    showCard: { type: Boolean, default: true },
});

const emit = defineEmits(["apply", "clear"]);

const form = defineModel("filterFormData", { type: Object, required: true });
const range = defineModel("priceRange", { type: Array, required: true });

const openSections = reactive({
    review: true,
    price: true,
    categories: true,
    brands: true,
    colors: true,
    sizes: true,
});

const toggleSingle = (key, value) => {
    form.value[key] = form.value[key] == value ? null : value;
};

const expandedCategoryId = ref(null);

const toggleExpandedCategory = (categoryId) => {
    expandedCategoryId.value = expandedCategoryId.value == categoryId ? null : categoryId;
};

const selectCategory = (category) => {
    form.value.category_id = form.value.category_id == category.id ? "" : category.id;
    form.value.sub_category_id = "";
};

const selectSubCategory = (category, sub) => {
    form.value.sub_category_id = form.value.sub_category_id == sub.id ? "" : sub.id;
    form.value.category_id = category.id;
};

const categoryLimit = 5;
const brandLimit = 5;
const showAllCategories = ref(false);
const showAllBrands = ref(false);

const visibleCategories = computed(() =>
    showAllCategories.value
        ? master.categories
        : master.categories.slice(0, categoryLimit)
);

const visibleBrands = computed(() =>
    showAllBrands.value
        ? props.filter.brands
        : props.filter.brands.slice(0, brandLimit)
);

const onApply = () => emit("apply");
const onClear = () => emit("clear");
</script>

<style>
.vue-slider-process {
    @apply bg-primary;
}
</style>
