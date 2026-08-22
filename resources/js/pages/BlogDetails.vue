<template>
    <div class="bg-slate-50 dark:bg-slate-900 min-h-screen pb-12">
        <div class="main-container py-4">
            <div class="flex items-center gap-2 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-800/90 px-4 py-3">
                <router-link to="/" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700">
                    <HomeIcon class="h-4 w-4 text-slate-600 dark:text-slate-400" />
                </router-link>

                <div class="grow overflow-hidden">
                    <div class="space-x-1 truncate text-sm font-normal text-slate-500">
                        <router-link to="/">{{ $t("Home") }}</router-link>
                        <span>/</span>
                        <router-link to="/blogs">{{ $t("Blog") }}</router-link>
                        <span>/</span>
                        <span class="text-slate-700">{{ blog.title }}</span>
                    </div>
                </div>
            </div>

            <div v-if="!isLoading" class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-7">
                <div class="col-span-1 xl:col-span-5" :class="master.langDirection == 'rtl' ? 'xl:pl-4' : 'xl:pr-4'">
                    <article class="overflow-hidden rounded-[28px] bg-white dark:bg-slate-800 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
                        <div class="p-5 md:p-8">
                            <div class="inline-flex items-center rounded-md bg-gray-100 dark:bg-slate-700 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-slate-300">
                                {{ blog.category?.name }}
                            </div>

                            <h1 class="mt-4 max-w-4xl text-xl font-medium leading-tight text-slate-950 dark:text-white md:text-3xl">
                                {{ blog.title }}
                            </h1>

                            <div class="mt-5 flex flex-col gap-4 border-b border-slate-200 dark:border-slate-700 pb-6 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-center gap-3">
                                    <img :src="blog.post_by?.profile_photo" alt="author" class="h-12 w-12 rounded-full object-cover ring-2 ring-slate-100 dark:ring-slate-700">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ blog.post_by?.name }}
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                            <span>{{ blog.created_at }}</span>
                                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                            <span class="inline-flex items-center gap-1">
                                                <EyeIcon class="h-4 w-4 text-slate-400" />
                                                {{ blog?.total_views }} {{ $t('Views') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="blog.id" class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-slate-500">{{ $t('Share') }}:</span>
                                    <button type="button" class="share-chip hover:border-[#0d68f1] hover:text-[#0d68f1]" @click="share('facebook')">
                                        <FontAwesomeIcon :icon="faFacebookF" class="h-4 w-4" />
                                    </button>
                                    <button type="button" class="share-chip hover:border-[#1275b1] hover:text-[#1275b1]" @click="share('linkedin')">
                                        <FontAwesomeIcon :icon="faLinkedin" class="h-4 w-4" />
                                    </button>
                                    <button type="button" class="share-chip hover:border-[#47acdf] hover:text-[#47acdf]" @click="share('twitter')">
                                        <FontAwesomeIcon :icon="faTwitter" class="h-4 w-4" />
                                    </button>
                                    <button type="button" class="share-chip hover:border-[#bb0f23] hover:text-[#bb0f23]" @click="share('pinterest')">
                                        <FontAwesomeIcon :icon="faPinterest" class="h-4 w-4" />
                                    </button>
                                    <button type="button" class="share-chip hover:border-[#fc471e] hover:text-[#fc471e]" @click="share('reddit')">
                                        <FontAwesomeIcon :icon="faRedditAlien" class="h-4 w-4" />
                                    </button>
                                    <button type="button" class="share-chip hover:border-[#25d366] hover:text-[#25d366]" @click="share('whatsapp')">
                                        <FontAwesomeIcon :icon="faWhatsapp" class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 pb-5 md:px-8 md:pb-8">
                            <img :src="blog.thumbnail" class=" w-full aspect-[16/9] rounded-[24px] object-cover" alt="thumbnail" loading="lazy" />

                            <div class="prose prose-slate dark:prose-invert mt-8 max-w-none prose-headings:text-slate-950 dark:prose-headings:text-white prose-p:text-slate-700 dark:prose-p:text-slate-300 prose-strong:text-slate-900 dark:prose-strong:text-white">
                                <div v-html="sanitize(blog.description)"></div>
                            </div>

                            <div v-if="blog.tags?.length > 0" class="mt-8 border-t border-slate-200 dark:border-slate-700 pt-6">
                                <div class="text-lg font-semibold text-slate-900 dark:text-white">
                                    {{ $t("Related keywords") }}
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <div v-for="tag in blog.tags" :key="tag.id || tag.name" class="rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                                        #{{ tag.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <aside class="col-span-1 xl:col-span-2">
                    <div class="space-y-6 xl:sticky xl:top-6">
                        <div v-if="relatedProducts.length > 0" class="rounded-[26px] bg-white dark:bg-slate-800 p-5 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-xl font-bold text-slate-950 dark:text-white">
                                    {{ $t('Related Products') }}
                                </h2>
                                <RouterLink to="/products" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:text-primary/80">
                                    {{ $t('View All') }}
                                    <FontAwesomeIcon :icon="faArrowRightLong" class="h-4 w-4" />
                                </RouterLink>
                            </div>

                            <div class="mt-4 space-y-4">
                                <ProductCardHorizontal v-for="relatedProduct in relatedProducts" :key="relatedProduct.id" :product="relatedProduct" />
                            </div>
                        </div>

                        <div v-if="popularBlogs.length > 0" class="rounded-[26px] bg-white dark:bg-slate-800 p-5 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
                            <h2 class="text-xl font-bold text-slate-950 dark:text-white">
                                {{ $t('Popular Blogs') }}
                            </h2>

                            <div class="mt-4 space-y-4">
                                <BlogCardHorizontal v-for="popularBlog in popularBlogs" :key="popularBlog.id" :blog="popularBlog" />
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <div v-if="relatedBlogs.length > 0 && !isLoading" class="mt-10">
                <div class="text-2xl font-bold text-slate-900 dark:text-white md:text-3xl">
                    {{ $t('Related Blogs') }}
                </div>

                <div class="mt-4 overflow-x-auto" :dir="master.langDirection">
                    <swiper :slidesPerView="'auto'" :spaceBetween="20" class="categorySwiper">
                        <swiper-slide v-for="relatedBlog in relatedBlogs" :key="relatedBlog.id">
                            <div class="max-w-[300px] md:max-w-[460px]">
                                <BlogCard :blog="relatedBlog" />
                            </div>
                        </swiper-slide>
                    </swiper>
                </div>
            </div>

            <div v-if="isLoading" class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-7">
                <div class="col-span-1 xl:col-span-5" :class="master.langDirection == 'rtl' ? 'xl:pl-4' : 'xl:pr-4'">
                    <div class="rounded-[28px] bg-white dark:bg-slate-800 p-5 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70 md:p-8">
                        <SkeletonLoader class="h-8 w-24 rounded-full" />
                        <SkeletonLoader class="mt-5 h-12 w-full rounded-2xl" />
                        <SkeletonLoader class="mt-3 h-12 w-10/12 rounded-2xl" />
                        <div class="mt-6 flex items-center gap-3">
                            <SkeletonLoader class="h-12 w-12 rounded-full" />
                            <div class="grow">
                                <SkeletonLoader class="h-4 w-48 rounded-full" />
                                <SkeletonLoader class="mt-2 h-4 w-32 rounded-full" />
                            </div>
                        </div>
                        <SkeletonLoader class="mt-8 h-[360px] w-full rounded-[24px]" />
                        <SkeletonLoader class="mt-8 h-5 w-full rounded-full" />
                        <SkeletonLoader class="mt-3 h-5 w-full rounded-full" />
                        <SkeletonLoader class="mt-3 h-5 w-11/12 rounded-full" />
                    </div>
                </div>

                <div class="col-span-1 xl:col-span-2">
                    <div class="space-y-6">
                        <div class="rounded-[26px] bg-white dark:bg-slate-800 p-5 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
                            <SkeletonLoader class="h-6 w-40 rounded-full" />
                            <div class="mt-4 space-y-4">
                                <SkeletonLoader v-for="i in 3" :key="`product-${i}`" class="h-36 w-full rounded-xl" />
                            </div>
                        </div>
                        <div class="rounded-[26px] bg-white dark:bg-slate-800 p-5 shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/70">
                            <SkeletonLoader class="h-6 w-40 rounded-full" />
                            <div class="mt-4 space-y-4">
                                <SkeletonLoader v-for="i in 3" :key="`blog-${i}`" class="h-32 w-full rounded-xl" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { HomeIcon } from '@heroicons/vue/24/solid';
import { EyeIcon } from '@heroicons/vue/24/outline';
import { useRoute, useRouter } from 'vue-router';
import { onMounted, ref, watch } from 'vue';
import { useMaster } from '../stores/MasterStore';
import DOMPurify from 'dompurify';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faFacebookF, faLinkedin, faTwitter, faPinterest, faRedditAlien, faWhatsapp } from '@fortawesome/free-brands-svg-icons';
import { faArrowRightLong } from '@fortawesome/free-solid-svg-icons';
import SkeletonLoader from '../components/SkeletonLoader.vue';
import { useSeo } from '../composables/useSeo';
import BlogCard from '../components/BlogCard.vue';
import ProductCardHorizontal from '../components/ProductCardHorizontal.vue';
import BlogCardHorizontal from '../components/BlogCardHorizontal.vue';

import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';

import { useShareLink } from "vue3-social-sharing";
const { shareLink } = useShareLink();

const master = useMaster();
const route = useRoute();
const router = useRouter();
const { setMeta, setJsonLd } = useSeo();

const slug = ref(route.params.slug);
const isLoading = ref(true);
const blog = ref({});
const relatedBlogs = ref([]);
const popularBlogs = ref([]);
const relatedProducts = ref([]);

onMounted(() => {
    fetchBlog();
    window.scrollTo(0, 0);
});

watch(route, () => {
    slug.value = route.params.slug;
    fetchBlog();
});

const share = (network) => {
    const description = (blog.value.description || '').replace(/<[^>]*>/g, "");
    const maxLength = 500;
    const trimmedString = description.substring(0, maxLength);
    const result = trimmedString.substring(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" ")));
    const tagsString = (blog.value.tags || []).map(tag => tag.name).join(', ');

    shareLink({
        network: network,
        url: window.location.href,
        title: blog.value.title,
        description: result,
        media: blog.value.thumbnail,
        quote: blog.value.title,
        hashtags: 'blog, ' + tagsString,
        twitterUser: blog.value.post_by?.name
    })
}

const sanitize = (html) => {
    return DOMPurify.sanitize(html);
}

const fetchBlog = () => {
    window.scrollTo(0, 0);
    isLoading.value = true;
    axios.get('/blog/' + slug.value + '/details', {
        headers: {
            'Accept-Language': master.locale || 'en',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then((response) => {
        blog.value = response.data.data.blog;
        relatedBlogs.value = response.data.data.related_blogs;
        popularBlogs.value = response.data.data.popular_blogs;
        relatedProducts.value = response.data.data.related_products;
        isLoading.value = false;

        setMeta({
            title: blog.value.title,
            description: blog.value.description || blog.value.title,
            image: blog.value.thumbnail,
            type: 'article',
            keywords: (blog.value.tags || []).map(tag => tag.name).join(', '),
        });

        setJsonLd({
            '@context': 'https://schema.org',
            '@type': 'Article',
            headline: blog.value.title,
            image: blog.value.thumbnail,
            datePublished: blog.value.created_at,
            author: blog.value.post_by?.name ? {
                '@type': 'Person',
                name: blog.value.post_by.name,
            } : undefined,
            publisher: {
                '@type': 'Organization',
                name: master.appName || '',
                logo: { '@type': 'ImageObject', url: master.logo || '' },
            },
        });
        scrollToTop();
    }).catch((error) => {
        isLoading.value = false;
        if (error.response.status === 400) {
            router.push({ name: 'not-found' });
        }
    });
}

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: "smooth",
    });
}
</script>

<style>
.categorySwiper .swiper-slide {
    width: auto !important;
}

.share-chip {
    display: inline-flex;
    height: 2.25rem;
    width: 2.25rem;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    border: 1px solid rgb(226 232 240);
    color: rgb(100 116 139);
    transition: 0.2s ease;
}

.dark .share-chip {
    border-color: rgb(71 85 105);
    color: rgb(148 163 184);
}
</style>
