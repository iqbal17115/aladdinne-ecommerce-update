<template>
    <div class="brand-page pb-12 pt-8">
        <div class="main-container">

                        <!-- Breadcrumb -->
                        <nav class="flex items-center gap-1.5 overflow-hidden pb-4 text-sm">
                            <router-link to="/" class="shrink-0 flex items-center gap-1 text-slate-500 hover:text-primary transition">
                                <HomeIcon class="w-4 h-4" />
                                <span>{{ $t("Home") }}</span>
                            </router-link>
                            <span class="text-slate-400">&#8250;</span>
                            <span class="text-slate-800 dark:text-slate-100 font-medium truncate">{{ $t("All Brands") }}</span>
                        </nav>

                        <h1 class="text-slate-800 dark:text-slate-100 text-xl font-bold leading-9">
                            {{ $t('All Brands') }}
                        </h1>
                        <p v-if="!isLoading" class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 sm:text-sm">
                            {{ $t('Explore our top brands and shop the products you love.') }}
                        </p>
                        <div v-else class="mt-4 space-y-2">
                            <SkeletonLoader class="h-4 w-72 max-w-full rounded-full" />
                            <SkeletonLoader class="h-4 w-60 max-w-full rounded-full" />
                        </div>

            <section class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-[260px_minmax(0,1fr)]">
                <aside class="space-y-5">
                    <div class="bg-white border border-slate-200 dark:border-slate-700/70 dark:bg-slate-800 rounded-xl p-5 shadow-[0_18px_60px_rgba(15,23,42,0.06)]">
                        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-3">
                            {{ $t('Search') }}
                        </h2>

                        <form @submit.prevent="submitSearch">
                            <div class="flex items-stretch overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus-within:border-slate-300 focus-within:ring-4 focus-within:ring-slate-900/5">
                                <input
                                    v-model="brandSearch"
                                    type="text"
                                    :placeholder="$t('Search brand...')"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-sm text-slate-700 dark:text-slate-300 outline-none"
                                />
                                <button
                                    type="submit"
                                    class="inline-flex shrink-0 items-center justify-center bg-slate-900 px-4 text-white transition hover:bg-slate-700"
                                    :aria-label="$t('Search')"
                                >
                                    <MagnifyingGlassIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </form>

                        <button
                            v-if="brandSearch"
                            type="button"
                            @click="clearSearch"
                            class="mt-2 text-xs text-slate-400 hover:text-red-500 flex items-center gap-1 transition"
                        >
                            <XMarkIcon class="w-3.5 h-3.5" />
                            {{ $t('Clear search') }}
                        </button>
                    </div>

                    <div class="rounded-[28px] border border-slate-200 dark:border-slate-700/70 bg-white dark:bg-slate-800 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.06)]">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                                {{ $t('Alphabetical') }}
                            </h2>
                            <button
                                v-if="selectedLetter !== 'all'"
                                type="button"
                                class="text-xs font-medium text-primary transition hover:text-primary/80"
                                @click="setSelectedLetter('all')"
                            >
                                {{ $t('Reset') }}
                            </button>
                        </div>

                        <div v-if="isLoading" class="mt-4 grid grid-cols-6 gap-2">
                            <SkeletonLoader v-for="item in 18" :key="`alphabet-${item}`" class="h-8 rounded-lg" />
                        </div>

                        <div v-else class="mt-4 grid grid-cols-6 gap-2">
                            <button
                                type="button"
                                class="alphabet-chip bg-slate-50 dark:bg-slate-700 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300"
                                :class="selectedLetter === 'all' ? 'alphabet-chip--active' : ''"
                                @click="setSelectedLetter('all')"
                            >
                                {{ $t('All') }}
                            </button>
                            <button
                                v-for="letter in alphabetList"
                                :key="letter"
                                type="button"
                                class="alphabet-chip bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-600 text-slate-500 "
                                :class="selectedLetter === letter ? 'alphabet-chip--active' : ''"
                                @click="setSelectedLetter(letter)"
                            >
                                {{ letter }}
                            </button>
                        </div>
                    </div>

                    <div class="partner-card rounded-[28px] border border-slate-200 dark:border-slate-700/70 bg-white dark:bg-slate-800 p-5 shadow-[0_18px_60px_rgba(15,23,42,0.06)]">
                        <div class="partner-badge-wrap bg-white dark:bg-slate-700">
                            <div class="partner-badge" aria-hidden="true">
                                <span class="partner-badge__ribbon partner-badge__ribbon--left"></span>
                                <span class="partner-badge__ribbon partner-badge__ribbon--right"></span>
                                <span class="partner-badge__medal">
                                    <span class="partner-badge__inner"></span>
                                    <span class="partner-badge__star"></span>
                                </span>
                            </div>
                        </div>
                        <h3 class="mt-5 text-center text-base font-semibold text-slate-900 dark:text-white">
                            {{ $t('Become a Partner') }}
                        </h3>
                        <p class="mt-2 max-w-[220px] text-center text-sm leading-6 text-slate-600 dark:text-slate-400">
                            {{ $t('Sell your products with us') }}
                        </p>
                        <a
                            href="/shop/register"
                            class="mt-5 flex h-11 w-full max-w-[180px] items-center justify-center rounded-xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            {{ $t('Apply Now') }}
                        </a>
                    </div>
                </aside>

                <div class="rounded-[10px] p-4 sm:p-5 lg:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">
                                {{ $t('Featured Brands') }}
                            </h2>
                        </div>

                        <!-- <div v-if="!isLoading" class="rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400">
                            {{ filteredTotalBrands }} {{ $t('active brands') }}
                        </div>
                        <SkeletonLoader v-else class="h-10 w-32 rounded-full" /> -->
                    </div>

                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                        <template v-if="isLoading">
                            <div v-for="item in 12" :key="item" class="rounded-[22px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-5 shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
                                <div class="flex h-28 items-center justify-center rounded-[18px] bg-[linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)]">
                                    <SkeletonLoader class="h-16 w-24 rounded-lg" />
                                </div>
                                <SkeletonLoader class="mx-auto mt-5 h-5 w-2/3 rounded-full" />
                                <SkeletonLoader class="mx-auto mt-2 h-4 w-1/2 rounded-full" />
                                <SkeletonLoader class="mt-5 h-10 w-full rounded-xl" />
                            </div>
                        </template>

                         <!-- <p v-if="!isLoading" class="mt-1 text-sm text-slate-500">
                                {{ $t('Showing') }} {{ pageStart }} {{ $t('to') }} {{ pageEnd }} {{ $t('of') }} {{ filteredTotalBrands }} {{ $t('results') }}
                            </p> -->


                        <template v-else-if="paginatedBrands.length">
                            <article
                                v-for="brand in paginatedBrands"
                                :key="brand.id"
                                class="brand-card rounded-[22px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-5 sm:px-5"
                            >
                                <div class="brand-media flex h-32 items-center justify-center rounded-[18px]  px-4  sm:h-36">
                                    <img
                                        :src="brand.thumbnail"
                                        :alt="brand.name"
                                        class="max-h-24 w-auto max-w-[88%] object-contain sm:max-h-28"
                                        loading="lazy"
                                    />
                                </div>

                                <div class="mt-5 text-center">
                                    <h3 class="line-clamp-1 text-base font-semibold text-slate-900 dark:text-white">
                                        {{ brand.name }}
                                    </h3>
                                    <!-- <p class="mt-1 text-sm text-slate-500">
                                        {{ brand.product_count || 0 }} {{ $t('Products') }}
                                    </p> -->
                                </div>

                                <router-link
                                    :to="{ path: '/products', query: { brand_slug: brand.slug } }"
                                    class="mt-5 flex h-10 w-full items-center justify-center rounded-xl border border-slate-300 bg-white dark:bg-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300 transition hover:border-primary hover:bg-primary hover:text-white"
                                >
                                    {{ $t('View Products') }}
                                </router-link>
                            </article>
                        </template>

                        <div v-else class="col-span-full rounded-[28px] border border-dashed border-slate-300 bg-slate-50 dark:bg-slate-900 px-6 py-16 text-center">
                            <div class="text-xl font-semibold text-slate-800 dark:text-slate-100">{{ $t('No Brands Found') }}</div>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ $t('There are no active brands for this alphabet filter right now.') }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="!isLoading && filteredTotalBrands"
                        class="mt-8 flex flex-col gap-4 border-t border-slate-200 dark:border-slate-700 pt-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-slate-500">
                            {{ $t('Showing') }} {{ pageStart }} {{ $t('to') }} {{ pageEnd }} {{ $t('of') }} {{ filteredTotalBrands }} {{ $t('results') }}
                        </p>

                        <vue-awesome-paginate
                            v-if="filteredTotalBrands > perPage"
                            :total-items="filteredTotalBrands"
                            :items-per-page="perPage"
                            type="button"
                            :max-pages-shown="5"
                            :hide-prev-next-when-ends="true"
                            v-model="currentPage"
                            @click="onClickHandler"
                        />
                    </div>
                </div>
            </section>

            <!-- <section class="mt-8">
                <div v-if="isLoading" class="grid grid-cols-1 gap-4 rounded-[28px] border border-slate-200 dark:border-slate-700/70 bg-white dark:bg-slate-800/92 p-4 shadow-[0_16px_44px_rgba(15,23,42,0.06)] sm:grid-cols-2 xl:grid-cols-4">
                    <SkeletonLoader v-for="i in 4" :key="`support-${i}`" class="h-[92px] rounded-2xl" />
                </div>

                <div v-else-if="supportItems.length" class="grid grid-cols-1 gap-4 rounded-[28px] border border-slate-200 dark:border-slate-700/70 bg-white dark:bg-slate-800/92 p-4 shadow-[0_16px_44px_rgba(15,23,42,0.06)] sm:grid-cols-2 xl:grid-cols-5">
                    <article
                        v-for="item in supportItems"
                        :key="item.id"
                        class="support-card flex items-start gap-3 rounded-[22px] border border-slate-200 dark:border-slate-700/80 bg-[linear-gradient(180deg,#ffffff_0%,#f8fafc_100%)] p-4"
                    >
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 ring-1 ring-primary/10">
                            <img :src="item.icon" :alt="item.title" class="h-6 w-6 object-contain" loading="lazy" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-900 dark:text-white sm:text-[15px]">
                                {{ item.title }}
                            </div>
                            <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                                {{ item.description }}
                            </p>
                        </div>
                    </article>
                </div>
            </section> -->
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { MagnifyingGlassIcon, XMarkIcon, HomeIcon } from '@heroicons/vue/24/outline';
import SkeletonLoader from '../components/SkeletonLoader.vue';
import { useMaster } from '../stores/MasterStore';

const master = useMaster();

const isLoading = ref(true);
const brands = ref([]);
const supportItems = ref([]);
const totalBrands = ref(0);
const currentPage = ref(1);
const perPage = 12;
const selectedLetter = ref('all');
const brandSearch = ref('');
const hasLoadedSupportItems = ref(false);
const alphabetList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '#'];

const filteredBrands = computed(() => {
    return brands.value.filter((brand) => {
        const name = brand.name || '';
        const matchesLetter = selectedLetter.value === 'all' || matchesSelectedLetter(name);

        return matchesLetter;
    });
});

const filteredTotalBrands = computed(() => filteredBrands.value.length);

const paginatedBrands = computed(() => {
    const start = (currentPage.value - 1) * perPage;

    return filteredBrands.value.slice(start, start + perPage);
});

const pageStart = computed(() => {
    if (!filteredTotalBrands.value) {
        return 0;
    }

    return (currentPage.value - 1) * perPage + 1;
});

const pageEnd = computed(() => {
    if (!filteredTotalBrands.value) {
        return 0;
    }

    return Math.min(currentPage.value * perPage, filteredTotalBrands.value);
});

onMounted(() => {
    fetchBrandsPageData();
    window.scrollTo(0, 0);
});

const onClickHandler = (page) => {
    currentPage.value = page;
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
};

const normalizeFirstCharacter = (value) => value.trim().charAt(0).toUpperCase();

const matchesSelectedLetter = (name) => {
    const firstCharacter = normalizeFirstCharacter(name);

    if (selectedLetter.value === '#') {
        return /[^A-Z]/.test(firstCharacter);
    }

    return firstCharacter === selectedLetter.value;
};

const setSelectedLetter = (letter) => {
    selectedLetter.value = letter;
    currentPage.value = 1;
};

const submitSearch = () => {
    currentPage.value = 1;
    fetchBrandsPageData();
};

const clearSearch = () => {
    if (!brandSearch.value) {
        return;
    }

    brandSearch.value = '';

    currentPage.value = 1;
    fetchBrandsPageData();
};

const fetchBrandsPageData = async () => {
    isLoading.value = true;

    try {
        const requests = [
            axios.get('/brands', {
                params: {
                    search: brandSearch.value.trim() || undefined,
                },
                headers: {
                    'Accept-Language': master.locale || 'en',
                },
            }),
        ];

        if (!hasLoadedSupportItems.value) {
            requests.push(
                axios.get('/blogs', {
                    params: {
                        page: 1,
                        per_page: 1,
                    },
                    headers: {
                        'Accept-Language': master.locale || 'en',
                    },
                })
            );
        }

        const [brandsResult, blogsResult] = await Promise.allSettled(requests);

        if (brandsResult.status === 'fulfilled') {
            totalBrands.value = brandsResult.value.data.data.total || 0;
            brands.value = brandsResult.value.data.data.brands || [];
        } else {
            totalBrands.value = 0;
            brands.value = [];
        }

        if (blogsResult?.status === 'fulfilled') {
            supportItems.value = blogsResult.value.data.data.support_items || blogsResult.value.data.data.supportItems || [];
            hasLoadedSupportItems.value = true;
        } else if (!hasLoadedSupportItems.value) {
            supportItems.value = [];
        }

        currentPage.value = 1;
    } catch (error) {
        brands.value = [];
        supportItems.value = [];
        totalBrands.value = 0;
        currentPage.value = 1;
    } finally {
        setTimeout(() => {
            isLoading.value = false;
        }, 250);
    }
};
</script>

<style scoped>
/* .brand-page {
    min-height: 100vh;
    background: #f8fafc;
} */

.hero-shell {
    position: relative;
    background: rgba(255, 255, 255, 0.96);
}

.brand-card {
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}

.brand-card:hover {
    transform: translateY(-4px);
    border-color: rgba(251, 146, 60, 0.28);
    box-shadow: 0 22px 40px rgba(15, 23, 42, 0.1);
}

.brand-media img {
    filter: saturate(1.02);
    transition: transform 0.22s ease;
}

.brand-card:hover .brand-media img {
    transform: scale(1.04);
}

.support-card {
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.95);
}

.alphabet-chip {
    display: flex;
    height: 2rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    border-width: 1px;
    border-style: solid;
    font-size: 0.75rem;
    font-weight: 700;
    transition: all 0.2s ease;
}

.alphabet-chip:hover {
    border-color: rgba(251, 146, 60, 0.3);
    color: rgb(234 88 12);
}

.alphabet-chip--active {
    border-color: rgb(15 23 42);
    background: rgb(15 23 42);
    color: rgb(255 255 255);
}


.partner-card {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.partner-badge-wrap {
    display: flex;
    height: 3.5rem;
    width: 3.5rem;
    align-items: center;
    justify-content: center;
    border-radius: 1rem;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.partner-badge {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2.4rem;
}

.partner-badge__medal {
    position: relative;
    display: flex;
    height: 1.8rem;
    width: 1.8rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    border: 2px solid rgb(245 158 11);
    background: linear-gradient(180deg, #fde68a 0%, #fbbf24 100%);
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.24);
}

.partner-badge__inner {
    position: absolute;
    inset: 0.22rem;
    border-radius: 9999px;
    border: 1.5px solid rgba(255, 255, 255, 0.95);
    background: linear-gradient(180deg, #fef3c7 0%, #fde68a 100%);
}

.partner-badge__ribbon {
    position: absolute;
    bottom: 0;
    width: 0.62rem;
    height: 1rem;
    background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
    clip-path: polygon(0 0, 100% 0, 100% 80%, 50% 100%, 0 80%);
    z-index: 0;
}

.partner-badge__ribbon--left {
    left: 0.2rem;
    transform: rotate(-8deg);
}

.partner-badge__ribbon--right {
    right: 0.2rem;
    transform: rotate(8deg);
}

.partner-badge__star {
    position: absolute;
    width: 0.72rem;
    height: 0.72rem;
    background: rgb(245 158 11);
    clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 92%, 50% 71%, 21% 92%, 32% 57%, 2% 35%, 39% 35%);
    z-index: 1;
}
</style>
