<template>
    <div class="bg-slate-50 dark:bg-slate-900 min-h-screen">
        <div class="main-container py-6 pb-12">

            <!-- Layout -->
            <div class="flex gap-7 items-start">

                <!-- Blog grid -->
                <div class="flex-1 min-w-0">
                    <div v-if="!isLoading && blogs.length > 0"
                        class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                        <BlogCard v-for="blog in blogs" :key="blog.id" :blog="blog" />
                    </div>

                    <!-- Skeleton -->
                    <div v-else-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                        <SkeletonLoader v-for="i in 9" :key="i" class="w-full h-72 rounded-xl" />
                    </div>

                    <!-- Empty -->
                    <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-100">
                            <DocumentTextIcon class="w-10 h-10 text-slate-400" />
                        </div>
                        <p class="mt-4 text-slate-500 text-lg">{{ $t('No blogs found') }}</p>
                    </div>

                    <!-- Pagination -->

                    <div class="flex items-center justify-between mb-5">
                            <p class="text-slate-500 text-sm">
                                <template v-if="!isLoading && blogs.length > 0">
                                    {{ $t('Showing') }} {{ rangeFrom }}&ndash;{{ rangeTo }} {{ $t('of') }} {{ total }}
                                    {{ $t('results') }}
                                </template>
                            </p>
                            <div v-if="blogs.length > 0 && total > perPage"
                                    class="flex items-center gap-2 mt-8">
                                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                                        class="flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:border-primary hover:text-primary disabled:opacity-40 disabled:cursor-not-allowed transition">
                                        <ChevronLeftIcon class="w-4 h-4" />
                                    </button>

                                    <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
                                        class="flex items-center justify-center w-9 h-9 rounded-lg border text-sm font-medium transition"
                                        :class="page === currentPage
                                            ? 'bg-primary border-primary text-white'
                                            : 'border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:border-primary hover:text-primary'">
                                        {{ page }}
                                    </button>

                                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                                        class="flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:border-primary hover:text-primary disabled:opacity-40 disabled:cursor-not-allowed transition">
                                        <ChevronRightIcon class="w-4 h-4" />
                                    </button>
                                </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <aside class="w-72 shrink-0 hidden lg:flex flex-col gap-6">

                    <!-- Search -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm">
                        <h4 class="text-slate-800 dark:text-white font-semibold text-base mb-3">{{ $t('Search') }}</h4>
                        <form @submit.prevent="onSearch">
                            <div class="flex items-stretch overflow-hidden rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 focus-within:border-slate-300 focus-within:ring-4 focus-within:ring-slate-900/5">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    :placeholder="$t('Search blog...')"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-sm text-slate-700 dark:text-slate-300 outline-none"
                                />
                                <button
                                    type="submit"
                                    class="inline-flex shrink-0 items-center justify-center bg-slate-900 dark:bg-slate-600 px-4 text-white transition hover:bg-slate-700"
                                >
                                    <MagnifyingGlassIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </form>
                        <button v-if="searchQuery" @click="clearSearch"
                            class="mt-2 text-xs text-slate-400 hover:text-red-500 flex items-center gap-1 transition">
                            <XMarkIcon class="w-3.5 h-3.5" />
                            {{ $t('Clear search') }}
                        </button>

                        <div class="mt-5">
                            <h4 class="text-slate-800 dark:text-white font-semibold text-base mb-3">{{ $t('Filter') }}</h4>
                            <select v-model="sortBy" @change="onSortChange"
                                class="w-full text-sm text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 bg-white dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/30 cursor-pointer">
                                <option value="newest">{{ $t('Newest First') }}</option>
                                <option value="oldest">{{ $t('Oldest First') }}</option>
                                <option value="popular">{{ $t('Most Popular') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-slate-800 dark:text-white font-semibold text-base">{{ $t('Categories') }}</h4>
                            <button v-if="categoryID" @click="resetCategory"
                                class="text-xs text-red-400 hover:text-red-600 flex items-center gap-1 transition">
                                <XMarkIcon class="w-3.5 h-3.5" />
                                {{ $t('Reset') }}
                            </button>
                        </div>
                        <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                            <li v-for="cat in visibleCategories" :key="cat.id"
                                class="flex items-center justify-between py-2 cursor-pointer group"
                                @click="selectCategory(cat.id)">
                                <span class="text-sm group-hover:text-primary transition"
                                    :class="categoryID == cat.id ? 'text-primary font-medium' : 'text-slate-600 dark:text-slate-400'">
                                    {{ cat.name }}
                                </span>
                                <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 dark:text-slate-400 px-2 py-0.5 rounded-full">
                                    {{ cat.blogs_count }}
                                </span>
                            </li>
                        </ul>
                        <button v-if="categories.length > 5 && !showAllCategories" @click="showAllCategories = true"
                            class="mt-3 text-sm text-primary hover:underline flex items-center gap-1">
                            {{ $t('View All Categories') }}
                            <ChevronRightIcon class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Popular Posts -->
                    <div v-if="popularPosts.length" class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm">
                        <h4 class="text-slate-800 dark:text-white font-semibold text-base mb-3">{{ $t('Popular Posts') }}</h4>
                        <ul class="flex flex-col gap-4">
                            <li v-for="post in popularPosts" :key="post.id"
                                class="flex gap-3 cursor-pointer group" @click="goToBlog(post.slug)">
                                <div class="w-16 h-14 rounded-lg overflow-hidden shrink-0">
                                    <img :src="post.thumbnail"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-slate-800 dark:text-slate-200 text-sm font-semibold line-clamp-2 leading-snug group-hover:text-primary transition">
                                        {{ post.title }}
                                    </p>
                                    <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">{{ post.created_at }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                </aside>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useMaster } from '../stores/MasterStore';
import BlogCard from '../components/BlogCard.vue';
import SkeletonLoader from '../components/SkeletonLoader.vue';
import {
    DocumentTextIcon,
    MagnifyingGlassIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const master = useMaster();
const router = useRouter();

const isLoading = ref(true);
const blogs = ref([]);
const categories = ref([]);
const currentPage = ref(1);
const perPage = 9;
const total = ref(0);
const categoryID = ref(null);
const sortBy = ref('newest');
const searchQuery = ref('');
const newsletterEmail = ref('');
const showAllCategories = ref(false);

const rangeFrom = computed(() => perPage * (currentPage.value - 1) + 1);
const rangeTo = computed(() => perPage * (currentPage.value - 1) + blogs.value.length);
const totalPages = computed(() => Math.ceil(total.value / perPage));

const popularPosts = computed(() => [...blogs.value].sort((a, b) => (b.total_views ?? 0) - (a.total_views ?? 0)).slice(0, 3));

const visibleCategories = computed(() =>
    showAllCategories.value ? categories.value : categories.value.slice(0, 5)
);

const visiblePages = computed(() => {
    const pages = [];
    const total = totalPages.value;
    const cur = currentPage.value;
    const delta = 2;
    for (let i = Math.max(1, cur - delta); i <= Math.min(total, cur + delta); i++) {
        pages.push(i);
    }
    return pages;
});

onMounted(() => {
    fetchBlogs();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

const fetchBlogs = () => {
    isLoading.value = true;
    axios.get('/blogs', {
        params: {
            page: currentPage.value,
            per_page: perPage,
            category_id: categoryID.value || undefined,
            sort: sortBy.value,
            search: searchQuery.value || undefined,
        },
        headers: { 'Accept-Language': master.locale || 'en' },
    }).then((res) => {
        total.value = res.data.data.total;
        blogs.value = res.data.data.blogs;
        categories.value = res.data.data.categories;
    }).finally(() => {
        isLoading.value = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
};

const goToPage = (page) => {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
    fetchBlogs();
};

const selectCategory = (id) => {
    categoryID.value = categoryID.value === id ? null : id;
    currentPage.value = 1;
    fetchBlogs();
};

const onSortChange = () => {
    currentPage.value = 1;
    fetchBlogs();
};

const onSearch = () => {
    currentPage.value = 1;
    fetchBlogs();
};

const clearSearch = () => {
    searchQuery.value = '';
    currentPage.value = 1;
    fetchBlogs();
};

const resetCategory = () => {
    categoryID.value = null;
    currentPage.value = 1;
    fetchBlogs();
};

const goToBlog = (slug) => {
    router.push('/blog/' + slug);
};

const subscribeNewsletter = () => {
    if (!newsletterEmail.value) return;
    newsletterEmail.value = '';
};
</script>
