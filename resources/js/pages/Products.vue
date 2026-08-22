<template>
    <div>
        <div class="main-container py-6">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 overflow-hidden pb-4 text-sm">
                <router-link to="/" class="shrink-0 flex items-center gap-1 text-slate-500 hover:text-primary transition">
                    <HomeIcon class="w-4 h-4" />
                    <span>{{ $t("Home") }}</span>
                </router-link>
                <span class="text-slate-400">&#8250;</span>
                <span class="text-slate-800 dark:text-slate-100 font-medium truncate">{{ $t("All Products") }}</span>
            </nav>

            <div class="flex gap-6 items-start">
                <!-- Filter Sidebar (desktop) -->
                <aside class="hidden lg:block w-[280px] shrink-0 sticky top-6 h-[calc(100vh-32px)]">
                    <ProductFilterPanel v-model:filter-form-data="filterFormData" v-model:price-range="priceRange"
                        :filter="filter" :filter-sort-by="filterSortBy" :ratings="ratings" @apply="applyFilter"
                        @clear="clearFilter" />
                </aside>

                <!-- Main content -->
                <div class="grow min-w-0">
                    <!-- Header -->
                    <div v-if="!isLoading" class="flex flex-wrap gap-3 items-center justify-between mb-6">
                        <div>
                            <h1 class="text-slate-950 dark:text-white text-2xl font-bold leading-tight">
                                {{ master.search ? `“${master.search}”` : $t("All Products") }}
                            </h1>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- View toggle -->
                            <div class="hidden sm:flex items-center bg-slate-100 dark:bg-slate-700 rounded-md p-1 gap-1">
                                <button type="button"
                                    class="w-9 h-9 flex items-center justify-center rounded transition"
                                    :class="viewMode == 'grid' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary'"
                                    @click="viewMode = 'grid'">
                                    <Squares2X2Icon class="w-5 h-5" />
                                </button>
                                <button type="button"
                                    class="w-9 h-9 flex items-center justify-center rounded transition"
                                    :class="viewMode == 'list' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary'"
                                    @click="viewMode = 'list'">
                                    <ListBulletIcon class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Filter button (mobile) -->
                            <button type="button"
                                class="lg:hidden p-2.5 bg-slate-200 rounded-md flex items-center gap-2 text-slate-600 dark:text-slate-400 text-sm border-0 outline-none hover:text-primary transition duration-300"
                                @click="showFilterCanvas = true">
                                <FunnelIcon class="w-5 h-5" />
                                {{ $t("Filter") }}
                            </button>
                        </div>
                    </div>
                    <!-- loading header -->
                    <div v-else class="flex gap-3 items-center justify-between mb-6">
                        <SkeletonLoader class="w-48 sm:w-72 h-8 rounded" />
                        <SkeletonLoader class="w-24 sm:w-40 h-10 rounded" />
                    </div>

                    <!-- Grid view -->
                    <div v-if="viewMode == 'grid'"
                        class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6 items-start">
                        <div v-if="!isLoading" v-for="product in products" :key="product.id" class="w-full">
                            <ProductCard :product="product" />
                        </div>

                        <!-- loading -->
                        <div v-else v-for="i in 12" :key="i">
                            <SkeletonLoader class="w-full h-[220px] sm:h-[330px] rounded-lg" />
                        </div>
                    </div>

                    <!-- List view -->
                    <div v-else class="flex flex-col gap-3 sm:gap-4">
                        <div v-if="!isLoading" v-for="product in products" :key="product.id" class="w-full">
                            <ProductListItem :product="product" />
                        </div>

                        <!-- loading -->
                        <div v-else v-for="i in 8" :key="i">
                            <SkeletonLoader class="w-full h-[124px] rounded-lg" />
                        </div>
                    </div>

                    <div v-if="products.length == 0 && !isLoading" class="flex justify-center items-center w-full mt-8">
                        <div class="text-slate-800 dark:text-slate-100 text-base font-normal leading-normal">
                            {{ $t("No products found") }}
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.length > 0 && !isLoading" class="flex justify-between items-center w-full mt-8 gap-4 flex-wrap">
                        <div class="flex items-center gap-4 flex-wrap">
                            <!-- <div class="text-slate-800 dark:text-slate-100 text-base font-normal leading-normal">
                                {{ $t("Showing") }} {{ perPage * (currentPage - 1) + 1 }}
                                {{ $t("to") }}
                                {{ perPage * (currentPage - 1) + products.length }}
                                {{ $t("of") }} {{ totalProducts }} {{ $t("results") }}
                            </div> -->

                            <!-- Items per page -->
                            <div class="flex items-center gap-2">
                                <label for="per-page"
                                    class="text-slate-800 dark:text-slate-100 text-base font-normal leading-normal">
                                    {{ $t("Show") }}
                                </label>
                                <!-- A native <select> can't colour its own options — the
                                     highlight is drawn by the OS — so this is a Listbox. -->
                                <Listbox :model-value="perPage" @update:model-value="onPerPageChange">
                                    <div class="relative">
                                        <ListboxButton
                                            class="inline-flex h-6 items-center justify-between gap-1.5 rounded-md border border-slate-900 dark:border-slate-100 bg-white dark:bg-slate-800 pl-3 pr-3 text-sm font-medium text-dark outline-none transition focus:ring-2 focus:ring-primary">
                                            {{ perPage }}
                                            <ChevronDownIcon class="h-4 w-4" aria-hidden="true" />
                                        </ListboxButton>

                                        <transition enter-active-class="transition ease-out duration-100"
                                            enter-from-class="transform opacity-0 scale-95"
                                            enter-to-class="transform opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75"
                                            leave-from-class="transform opacity-100 scale-100"
                                            leave-to-class="transform opacity-0 scale-95">
                                            <!-- Opens upward: the control sits at the bottom of the page. -->
                                            <ListboxOptions
                                                class="absolute bottom-full left-0 z-20 mb-1 w-full min-w-[4.5rem] origin-bottom rounded-md border border-slate-900 dark:border-slate-100 bg-white dark:bg-slate-800 p-1 shadow-lg focus:outline-none">
                                                <ListboxOption v-for="option in perPageOptions" :key="option"
                                                    :value="option" v-slot="{ active, selected }" as="template">
                                                    <li class="cursor-pointer rounded px-2.5 py-1.5 text-sm transition"
                                                        :class="active
                                                            ? 'bg-primary text-white'
                                                            : selected
                                                                ? 'text-primary font-semibold'
                                                                : 'text-slate-600 dark:text-slate-300'">
                                                        {{ option }}
                                                    </li>
                                                </ListboxOption>
                                            </ListboxOptions>
                                        </transition>
                                    </div>
                                </Listbox>
                            </div>
                        </div>
                        <div>
                            <vue-awesome-paginate :total-items="totalProducts" :items-per-page="perPage" type="button"
                                :max-pages-shown="5" v-model="currentPage" :hide-prev-next-when-ends="true"
                                @click="onClickHandler" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Canvas Drawer (mobile) -->
        <TransitionRoot as="template" :show="showFilterCanvas">
            <Dialog as="div" class="relative z-10 lg:hidden" @close="showFilterCanvas = false">
                <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0"
                    enter-to="opacity-100" leave="ease-in-out duration-500" leave-from="opacity-100"
                    leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-30 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-hidden">
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="pointer-events-none fixed inset-y-0 flex max-w-full"
                            :class="master.langDirection == 'rtl' ? 'left-0 sm:pr-10' : 'right-0 sm:pl-10'">
                            <TransitionChild as="template"
                                enter="transform transition ease-in-out duration-500 sm:duration-700"
                                :enter-from="master.langDirection == 'rtl' ? '-translate-x-full' : 'translate-x-full'"
                                enter-to="translate-x-0"
                                leave="transform transition ease-in-out duration-500 sm:duration-700"
                                leave-from="translate-x-0"
                                :leave-to="master.langDirection == 'rtl' ? '-translate-x-full' : 'translate-x-full'">
                                <DialogPanel class="pointer-events-auto relative w-screen max-w-md">
                                    <TransitionChild as="template" enter="ease-in-out duration-500"
                                        enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-500"
                                        leave-from="opacity-100" leave-to="opacity-0">
                                        <div class="absolute left-0 top-0 -ml-8 flex pr-2 pt-4 sm:-ml-10 sm:pr-4"></div>
                                    </TransitionChild>
                                    <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-slate-800 shadow-xl">
                                        <div class="flex justify-between items-center px-4 pt-4">
                                            <div class="text-slate-950 dark:text-white text-xl font-bold leading-loose">
                                                {{ $t("Filter") }}
                                            </div>
                                            <button
                                                class="w-8 h-8 flex justify-center items-center bg-slate-100 dark:bg-slate-700 rounded-full"
                                                @click="showFilterCanvas = false">
                                                <XMarkIcon class="w-5 h-5 text-slate-700 dark:text-slate-300" />
                                            </button>
                                        </div>
                                        <ProductFilterPanel :show-card="false" v-model:filter-form-data="filterFormData"
                                            v-model:price-range="priceRange" :filter="filter"
                                            :filter-sort-by="filterSortBy" :ratings="ratings"
                                            @apply="applyFilter(); showFilterCanvas = false"
                                            @clear="clearFilter" />
                                    </div>
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from "vue";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot, Listbox, ListboxButton, ListboxOptions, ListboxOption, } from "@headlessui/vue";
import { FunnelIcon, XMarkIcon, Squares2X2Icon, ListBulletIcon, HomeIcon, ChevronDownIcon } from "@heroicons/vue/24/outline";
import ProductCard from "../components/ProductCard.vue";
import ProductListItem from "../components/ProductListItem.vue";
import ProductFilterPanel from "../components/ProductFilterPanel.vue";
import { useMaster } from "../stores/MasterStore";
import { useRoute, useRouter } from "vue-router";

import SkeletonLoader from "../components/SkeletonLoader.vue";

const priceRange = ref([0, 1000]);
const viewMode = ref("grid");

const master = useMaster();
const route = useRoute();
const router = useRouter();
const isLoading = ref(true);

onMounted(() => {
    fetchProducts();
    fetchCategories();
    window.scrollTo(0, 0);
    setRangeValue.value = true;
});

onBeforeUnmount(() => {
    setRangeValue.value = false;
    master.search = null;
});

const search = master.search;

watch(() => master.search, () => {
    fetchProducts();
});

const selectedBrandSlug = ref(route.query.brand_slug ?? "");

watch(() => route.query.brand_slug, (brandSlug) => {
    selectedBrandSlug.value = brandSlug ?? "";
    currentPage.value = 1;
    fetchProducts();
});

const selectedCategorySlug = ref(route.query.category_slug ?? "");
const selectedSubCategorySlug = ref(route.query.sub_category_slug ?? "");
const selectedIsDigital = ref(
    route.query.is_digital === "true" || route.query.is_digital === "1" ? true : null
);

watch(() => route.query.is_digital, (val) => {
    selectedIsDigital.value = val === "true" || val === "1" ? true : null;
    filterFormData.value.is_digital = selectedIsDigital.value;
    currentPage.value = 1;
    fetchProducts();
});

watch(() => route.query.category_slug, (categorySlug) => {
    selectedCategorySlug.value = categorySlug ?? "";
    currentPage.value = 1;
    fetchProducts();
});

watch(() => route.query.sub_category_slug, (subCategorySlug) => {
    selectedSubCategorySlug.value = subCategorySlug ?? "";
    currentPage.value = 1;
    fetchProducts();
});

const currentPage = ref(1);
const perPage = ref(20);
const perPageOptions = [10, 20, 50, 100];

const onClickHandler = (page) => {
    currentPage.value = page;
    fetchProducts();
};

// A bigger page size can leave the current page number out of range, so start
// the listing over from the first page.
const onPerPageChange = (value) => {
    perPage.value = value;
    currentPage.value = 1;
    fetchProducts();
};

const showFilterCanvas = ref(false);

const filterFormData = ref({
    rating: null,
    sort_type: "default",
    brand_id: "",
    color_id: null,
    size_id: null,
    min_price: null,
    max_price: null,
    category_id: "",
    sub_category_id: "",
    is_digital: selectedIsDigital.value,
    is_preorder: null,
});

const filter = ref({
    sizes: [],
    brands: [],
    colors: [],
    min_price: 0,
    max_price: 1000,
});

const ratings = [5, 4, 3, 2, 1];

const products = ref([]);
const totalProducts = ref(0);
const setRangeValue = ref(true);

const fetchProducts = async () => {
    isLoading.value = true;
    window.scrollTo({
        top: 0,
        behavior: "smooth",
    });
    const params = {
        page: currentPage.value,
        per_page: perPage.value,
        search: master.search,
        brand_slug: selectedBrandSlug.value || undefined,
        category_slug: selectedCategorySlug.value || undefined,
        sub_category_slug: selectedSubCategorySlug.value || undefined,
        ...filterFormData.value,
    };
    if (!params.is_digital) delete params.is_digital;
    if (!params.is_preorder) delete params.is_preorder;

    axios.get("/products", {
        params,
        headers: {
            "Accept-Language": master.locale || "en",
        },
    }).then((response) => {
        totalProducts.value = response.data.data.total;
        products.value = response.data.data.products;
        filter.value = response.data.data.filters;

        if (setRangeValue.value) {
            priceRange.value = [
                Math.floor(filter.value.min_price),
                Math.floor(filter.value.max_price),
            ];
        }

        setTimeout(() => {
            isLoading.value = false;
        }, 200);

    }).catch((error) => {
        isLoading.value = false;
    })
};

const fetchCategories = () => {
    axios.get("/categories", {
        headers: {
            "Accept-Language": master.locale || "en",
        },
    }).then((response) => {
        master.categories = response.data.data.categories;
    }).catch(() => { });
};

const clearSlugQueryParams = () => {
    const query = { ...route.query };
    delete query.brand_slug;
    delete query.category_slug;
    delete query.sub_category_slug;
    delete query.is_digital;
    router.replace({ name: "products", query });
};

const clearFilter = () => {
    filterFormData.value = {
        rating: null,
        sort_type: "default",
        brand_id: "",
        color_id: null,
        size_id: null,
        min_price: filter.value.min_price,
        max_price: filter.value.max_price,
        category_id: "",
        sub_category_id: "",
        is_digital: null,
        is_preorder: null,
    };
    selectedBrandSlug.value = "";
    selectedCategorySlug.value = "";
    selectedSubCategorySlug.value = "";
    selectedIsDigital.value = null;
    clearSlugQueryParams();
    priceRange.value = [
        Math.floor(filter.value.min_price),
        Math.floor(filter.value.max_price),
    ];
    setRangeValue.value = true;
    currentPage.value = 1;
    fetchProducts();
};

const applyFilter = () => {
    filterFormData.value.min_price = priceRange.value[0];
    filterFormData.value.max_price = priceRange.value[1];

    selectedCategorySlug.value = "";
    selectedSubCategorySlug.value = "";

    const query = { ...route.query };
    delete query.brand_slug;
    delete query.category_slug;
    delete query.sub_category_slug;
    if (filterFormData.value.is_digital) {
        query.is_digital = "true";
    } else {
        delete query.is_digital;
    }
    router.replace({ name: "products", query });

    setRangeValue.value = false;
    master.search = null;
    currentPage.value = 1;
    showFilterCanvas.value = false;
    fetchProducts();
};

const filterSortBy = [
    {
        name: "Default Sorting",
        value: "default",
    },
    {
        name: "High to Low",
        value: "high_to_low",
    },
    {
        name: "Low to High",
        value: "low_to_high",
    },
    {
        name: "Most Selling",
        value: "top_selling",
    },
    {
        name: "New Product",
        value: "newest",
    },
];
</script>
