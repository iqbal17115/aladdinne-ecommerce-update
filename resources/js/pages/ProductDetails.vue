<template>
    <div class="main-container">

        <!-- ══ Page Loader ══ -->
        <div v-if="isLoading" class="mt-6">
            <div>
                <div class="flex items-center gap-2 overflow-hidden pt-4">
                    <SkeletonLoader class="w-60 h-4 rounded" />
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mt-6">
                    <div class="lg:col-span-2">
                        <SkeletonLoader class="w-full h-[400px] rounded-2xl" />
                        <div class="flex gap-3 mt-3">
                            <SkeletonLoader v-for="i in 4" :key="i" class="h-20 grow rounded-xl" />
                        </div>
                    </div>
                    <div class="lg:col-span-3 pb-4 flex flex-col gap-4">
                        <SkeletonLoader class="w-28 h-3 rounded" />
                        <SkeletonLoader class="w-10/12 h-7 rounded" />
                        <SkeletonLoader class="w-40 h-4 rounded" />
                        <SkeletonLoader class="w-36 h-8 rounded" />
                        <SkeletonLoader class="w-full h-px rounded" />
                        <SkeletonLoader class="w-full h-4 rounded" />
                        <SkeletonLoader class="w-10/12 h-4 rounded" />
                        <div class="flex gap-2 mt-2">
                            <SkeletonLoader v-for="i in 3" :key="i" class="w-7 h-7 rounded-full" />
                        </div>
                        <SkeletonLoader class="w-full h-px rounded" />
                        <div class="flex gap-3 mt-2">
                            <SkeletonLoader class="flex-1 h-12 rounded-xl" />
                            <SkeletonLoader class="flex-1 h-12 rounded-xl" />
                        </div>
                        <SkeletonLoader class="w-full h-24 rounded-xl" />
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ Main Content ══ -->
        <div v-show="!isLoading">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 overflow-hidden pt-4 text-sm">
                <router-link to="/" class="shrink-0 flex items-center gap-1 text-slate-500 dark:text-slate-400 hover:text-primary transition">
                    <HomeIcon class="w-4 h-4" />
                    <span>{{ $t("Home") }}</span>
                </router-link>
                <template v-if="product.category">
                    <span class="text-slate-400">&#8250;</span>
                    <span class="text-slate-500 dark:text-slate-400">{{ product.category }}</span>
                </template>
                <template v-if="product.sub_category">
                    <span class="text-slate-400">&#8250;</span>
                    <span class="text-slate-500 dark:text-slate-400">{{ product.sub_category }}</span>
                </template>
                <span class="text-slate-400">&#8250;</span>
                <span class="text-slate-800 dark:text-slate-100 font-medium truncate">{{ product.name }}</span>
            </nav>

            <!-- Product + Tabs -->
            <div class="mt-5 px-10">

                <div>

                    <!-- Product section -->
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

                        <!-- Images column (40%) -->
                        <div class="lg:col-span-2">

                            <!-- Main swiper -->
                            <div class="relative bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <div v-if="discountPercentage > 0"
                                    class="absolute top-3 left-3 z-10 px-2.5 py-1 bg-red-500 rounded-lg text-white text-sm font-bold leading-none shadow-sm">
                                    -{{ Math.round(discountPercentage) }}%
                                </div>
                                <swiper :spaceBetween="10" :thumbs="{ swiper: thumbsSwiper }" :modules="modules"
                                    class="product-details-slider h-[448px]">
                                    <swiper-slide v-for="thumbnail in displayThumbnails" :key="thumbnail.id">
                                        <div v-if="thumbnail.thumbnail" class="zoom-container h-full"
                                            @mousemove="handleMouseMove" @mouseleave="resetZoom"
                                            @touchstart="handleMouseMove" @touchmove="handleMouseMove"
                                            @touchend="resetZoom">
                                            <img :src="thumbnail.thumbnail" alt="thumbnail"
                                                class="zoom-image h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="h-full w-full bg-slate-200 flex justify-center items-center">
                                            <video v-if="thumbnail.type == 'file'" controls class="w-full">
                                                <source :src="thumbnail.url" type="video/mp4">
                                            </video>
                                            <div v-else v-html="thumbnail.url" class="w-full overflow-hidden"
                                                ref="iframeContainer"></div>
                                        </div>
                                    </swiper-slide>
                                </swiper>
                            </div>

                            <!-- Thumbnail swiper -->
                            <div class="mt-3 px-1">
                                <swiper @swiper="setThumbsSwiper" :spaceBetween="10" :slidesPerView="4"
                                    :freeMode="true" :navigation="true" :watchSlidesProgress="true"
                                    :modules="modules" class="product-details-thumbnail">
                                    <swiper-slide v-for="thumbnail in displayThumbnails" :key="thumbnail.id">
                                        <img v-if="thumbnail.thumbnail" :src="thumbnail.thumbnail" alt=""
                                            class="h-full w-full object-cover" />
                                        <div v-else
                                            class="h-full w-full bg-slate-200 flex justify-center items-center">
                                            <video v-if="thumbnail.type == 'file'" class="h-full w-full">
                                                <source :src="thumbnail.url" type="video/mp4">
                                            </video>
                                            <div v-else
                                                class="h-full w-full overflow-hidden flex justify-center items-center">
                                                <img :src="'/assets/icons/video-player.svg'" alt="" width="70"
                                                    height="70">
                                            </div>
                                        </div>
                                    </swiper-slide>
                                </swiper>
                            </div>
                        </div>

                        <!-- Info column (60%) -->
                        <div class="lg:col-span-3 min-w-0">

                            <!-- Flash Sale banner -->
                            <div v-if="flashSale"
                                class="bg-slate-100 dark:bg-slate-700 mb-4 rounded-xl flex items-center justify-start gap-2 sm:gap-5 overflow-hidden flex-col sm:flex-row">
                                <div class="px-5 py-2 bg-gradient-to-l from-primary to-primary-800 w-full sm:w-auto shrink-0">
                                    <div class="text-white text-sm font-bold">{{ $t("Flash Sale") }}</div>
                                </div>
                                <div class="h-full flex justify-center items-center flex-wrap pb-2 sm:pb-0">
                                    <div class="text-center text-primary text-sm font-normal pr-2">{{ $t("Ending in") }}</div>
                                    <div class="flex justify-center items-center gap-1 text-white">
                                        <div v-if="endDay > 0" class="p-1 justify-center items-center gap-1 inline-flex">
                                            <div class="text-center text-primary text-base font-semibold">{{ endDay }}</div>
                                            <div class="text-center text-[#687387] text-[9px] font-normal">{{ $t("Days") }}</div>
                                        </div>
                                        <span v-if="endDay > 0" class="text-black text-base font-bold">:</span>
                                        <div class="p-1 justify-center items-center gap-1 inline-flex">
                                            <div class="text-center text-primary text-base font-semibold">{{ endHour }}</div>
                                            <div class="text-center text-[#687387] text-[9px] font-normal">{{ $t("Hours") }}</div>
                                        </div>
                                        <span class="text-black text-base font-bold">:</span>
                                        <div class="p-1 justify-center items-center gap-1 inline-flex">
                                            <div class="text-center text-primary text-base font-semibold">{{ endMinute }}</div>
                                            <div class="text-center text-[#687387] text-[9px] font-normal">{{ $t("Minutes") }}</div>
                                        </div>
                                        <span v-if="endDay <= 0" class="text-black text-base font-bold">:</span>
                                        <div v-if="endDay <= 0" class="p-1 justify-center items-center gap-1 inline-flex">
                                            <div class="text-center text-primary text-base font-semibold">{{ endSecond }}</div>
                                            <div class="text-center text-[#687387] text-[9px] font-normal">{{ $t("Seconds") }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Brand + Title + Actions -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <span class="text-primary text-sm font-semibold tracking-wide">{{ product.brand ?? $t("Unknown Brand") }}</span>
                                    <h1 class="mt-1.5 text-slate-900 dark:text-white text-2xl font-bold leading-snug">{{ product.name }}</h1>
                                    <!-- Short Description -->
                            <p class="mt-3 text-slate-500 dark:text-slate-400 text-sm leading-relaxed">{{ product.short_description }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 pt-1">
                                    <button
                                        class="w-9 h-9 flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900 transition"
                                        @click="favoriteAddOrRemove">
                                        <HeartIcon v-if="!product.is_favorite" class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                                        <HeartIconFill v-else class="w-5 h-5 text-red-500" />
                                    </button>
                                    <Menu as="div" class="relative inline-block text-left">
                                        <MenuButton
                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900 transition">
                                            <ShareIcon class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                                        </MenuButton>
                                        <transition enter-active-class="transition ease-out duration-100"
                                            enter-from-class="transform opacity-0 scale-95"
                                            enter-to-class="transform opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75"
                                            leave-from-class="transform opacity-100 scale-100"
                                            leave-to-class="transform opacity-0 scale-95">
                                            <MenuItems
                                                class="absolute right-0 z-20 mt-2 w-52 origin-top rounded-xl bg-white dark:bg-slate-800 ring-1 shadow-lg ring-black/5 focus:outline-hidden">
                                                <div class="py-1 divide-y divide-gray-100">
                                                    <MenuItem v-for="social in shareOptions" :key="social.name"
                                                        v-slot="{ active }" class="cursor-pointer"
                                                        @click="share(social.name)">
                                                        <div
                                                            class="flex items-center gap-2 justify-between px-4 py-2 hover:bg-slate-50 dark:bg-slate-900 transition-all">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-7 h-7 p-1.5 flex justify-center items-center text-white rounded-full"
                                                                    :style="{ backgroundColor: social.color }">
                                                                    <FontAwesomeIcon :icon="social.icon" class="w-full h-full" />
                                                                </div>
                                                                <span class="capitalize text-sm text-slate-700 dark:text-slate-300">{{ social.name }}</span>
                                                            </div>
                                                            <div class="w-5 h-5 p-1 flex justify-center items-center bg-slate-100 dark:bg-slate-700 rounded-full rotate-45">
                                                                <FontAwesomeIcon :icon="faArrowUp" class="w-full h-full text-slate-400" />
                                                            </div>
                                                        </div>
                                                    </MenuItem>
                                                </div>
                                            </MenuItems>
                                        </transition>
                                    </Menu>
                                </div>
                            </div>

                            <!-- Rating + Reviews + Sold -->
                            <div class="flex items-center gap-2 mt-2.5 flex-wrap">
                                <div class="flex items-center gap-0.5">
                                    <StarIcon v-for="i in 5" :key="i" class="w-4 h-4"
                                        :class="i <= product.rating ? 'text-amber-400' : 'text-slate-200'" />
                                </div>
                                <span class="text-slate-800 dark:text-slate-100 text-sm font-bold">{{ product.rating }}</span>
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-500 dark:text-slate-400 text-sm">({{ product.total_reviews }} {{ $t("reviews") }})</span>
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-600 dark:text-slate-400 text-sm font-medium">{{ product.total_sold }}+ {{ $t("sold") }}</span>
                            </div>

                            <div class="border-t border-slate-100 dark:border-slate-700 my-4"></div>

                            <!-- Price Row -->
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="text-slate-950 dark:text-white text-3xl font-bold tracking-tight">
                                    {{ masterStore.showCurrency(parseFloat(productPrice).toFixed(2)) }}
                                </span>
                                <span v-if="product.discount_price > 0"
                                    class="text-slate-400 text-xl font-normal line-through">
                                    {{ masterStore.showCurrency(parseFloat(mainPrice).toFixed(2)) }}
                                </span>
                                <span v-if="discountPercentage > 0"
                                    class="px-3 py-1 bg-red-50 border border-red-100 text-red-500 rounded-full text-sm font-bold">
                                    {{ $t('Save') }} {{ Math.round(discountPercentage) }}%
                                </span>
                            </div>

                            <!-- Pre-Order conditions -->
                            <div v-if="isPreOrder"
                                class="mt-4 rounded-2xl border border-primary-100 dark:border-slate-700 overflow-hidden">
                                <div class="flex items-center gap-2 px-4 py-2.5 bg-primary-50 dark:bg-slate-700">
                                    <ClockIcon class="w-4 h-4 text-primary shrink-0" />
                                    <span class="text-primary text-sm font-semibold">{{ $t("Pre-Order Item") }}</span>
                                    <span v-if="!preOrderDetails.is_available"
                                        class="ml-auto text-red-500 text-xs font-medium">{{ $t("Currently unavailable") }}</span>
                                </div>
                                <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="flex items-center gap-2">
                                        <TruckIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                        <div class="min-w-0">
                                            <div class="text-slate-400 text-xs">{{ $t("Expected Delivery") }}</div>
                                            <div class="text-slate-800 dark:text-slate-100 text-sm font-medium truncate">
                                                {{ preOrderDetails.expected_delivery_date || $t("To be announced") }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <ArrowPathIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                        <div class="min-w-0">
                                            <div class="text-slate-400 text-xs">{{ $t("Refund") }}</div>
                                            <div class="text-sm font-medium"
                                                :class="preOrderDetails.is_refund ? 'text-emerald-500' : 'text-red-500'">
                                                {{ preOrderDetails.is_refund ? $t("Refundable") : $t("Non-refundable") }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="preOrderDetails.is_prepay" class="flex items-center gap-2 sm:col-span-2">
                                        <div class="w-4 h-4 flex items-center justify-center rounded bg-primary-100 dark:bg-slate-600 text-primary shrink-0 text-[10px] font-bold">%</div>
                                        <div class="min-w-0">
                                            <div class="text-slate-400 text-xs">{{ $t("Prepayment Required") }}</div>
                                            <div class="text-primary text-sm font-semibold">
                                                {{ masterStore.showCurrency(parseFloat(preOrderDetails.prepay_amount || 0).toFixed(2)) }}
                                                <span class="text-slate-400 font-normal">/ {{ $t("unit") }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="preOrderDetails.preorder_notice"
                                    class="px-4 pb-3 text-slate-500 dark:text-slate-400 text-xs leading-relaxed">
                                    {{ preOrderDetails.preorder_notice }}
                                </p>
                            </div>

                            <!-- Current Stock -->
                            <div v-if="masterStore.showProductStock && !isPreOrder"
                                class="mt-3 inline-flex items-center gap-2 rounded-lg bg-slate-100 dark:bg-slate-700 px-3 py-1.5 text-sm text-slate-700 dark:text-slate-300">
                                <span class="font-medium">{{ $t("Current Stock") }}:</span>
                                <span>{{ product.quantity ?? 0 }}</span>
                            </div>

                            <div class="border-t border-slate-100 dark:border-slate-700 my-4"></div>

                            <!-- Color Selector -->
                            <div v-if="product.colors?.length > 0 && product.is_digital == false && !isPreOrder"
                                class="flex items-center gap-4">
                                <div class="text-slate-700 dark:text-slate-300 text-sm font-medium min-w-[72px]">{{ $t("Color") }}:</div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div v-for="color in product.colors" :key="color.id" class="relative">
                                        <input type="radio" name="color" :id="'color-' + color.id"
                                            class="peer hidden" :value="color.id" v-model="formData.color" />
                                        <label :for="'color-' + color.id"
                                            class="w-7 h-7 rounded-full cursor-pointer block border-2 border-white shadow peer-checked:ring-2 peer-checked:ring-primary peer-checked:ring-offset-1"
                                            :style="{ backgroundColor: color.color_code || '#94a3b8' }">
                                        </label>
                                    </div>
                                    <span v-if="formData.color" class="text-slate-700 dark:text-slate-300 text-sm font-semibold ml-1">
                                        {{ product.colors?.find(c => c.id == formData.color)?.name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Size Selector -->
                            <div v-if="product.sizes?.length > 0 && product.is_digital == false && !isPreOrder"
                                class="flex items-center gap-4 mt-4">
                                <div class="text-slate-700 dark:text-slate-300 text-sm font-medium min-w-[72px]">{{ $t("Size") }}:</div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div v-for="size in product.sizes" :key="size.id" class="relative">
                                        <input type="radio" name="size" :id="'size-' + size.id" class="peer hidden"
                                            :value="size.id" v-model="formData.size" />
                                        <label :for="'size-' + size.id"
                                            class="min-w-10 h-9 flex justify-center items-center border-2 border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer peer-checked:border-primary peer-checked:bg-primary-50 dark:peer-checked:bg-slate-700 peer-checked:text-primary dark:peer-checked:text-primary px-3 text-sm font-medium text-slate-700 dark:text-slate-300 transition">
                                            {{ size.name }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity + Stock status -->
                            <div v-if="!isPreOrder" class="flex items-center gap-4 mt-4">
                                <div class="text-slate-700 dark:text-slate-300 text-sm font-medium min-w-[72px]">{{ $t("Quantity") }}:</div>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <div class="inline-flex items-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden shadow-sm">
                                        <button
                                            class="w-9 h-9 flex items-center justify-center bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:bg-slate-700 transition"
                                            @click="cartProduct ? decrementQty() : decrementLocalQty()">
                                            <MinusIcon class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                        </button>
                                        <div class="w-12 text-center text-slate-950 dark:text-white text-base font-semibold border-x border-slate-200 dark:border-slate-700">
                                            {{ cartProduct ? cartProduct.quantity : localQty }}
                                        </div>
                                        <button
                                            class="w-9 h-9 flex items-center justify-center bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:bg-slate-700 transition"
                                            @click="cartProduct ? incrementQty() : incrementLocalQty()">
                                            <PlusIcon class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                        </button>
                                    </div>
                                    <span v-if="(product.quantity ?? 0) > 0"
                                        class="text-emerald-500 text-sm font-semibold">{{ $t("In Stock") }}</span>
                                    <span v-else
                                        class="text-red-500 text-sm font-semibold">{{ $t("Out of Stock") }}</span>
                                </div>
                            </div>

                            <!-- Pre-Order action -->
                            <div v-if="isPreOrder" class="mt-6">
                                <button
                                    class="w-full sm:w-1/2 flex justify-center items-center gap-2 px-6 py-3 rounded-xl bg-primary text-white text-base font-semibold hover:bg-primary-700 transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!preOrderDetails.is_available"
                                    @click="openPreOrder">
                                    <ClockIcon class="w-5 h-5 shrink-0" />
                                    {{ preOrderDetails.is_available ? $t("Pre-Order Now") : $t("Currently unavailable") }}
                                </button>
                            </div>

                            <!-- Add to Cart + Buy Now -->
                            <div v-else class="flex gap-3 mt-6">
                                <button v-if="!cartProduct && product?.is_digital == false"
                                    class="w-1/4 flex justify-center items-center gap-2 px-6 py-2 rounded-xl bg-primary text-white text-base font-semibold hover:bg-primary-700 transition active:scale-95"
                                    @click="addToCart">
                                    <div class="w-5 h-5 shrink-0"><BagIcon /></div>
                                    {{ $t("Add to Cart") }}
                                </button>
                                <button
                                    class="border-2 border-primary text-primary px-6 py-2 rounded-xl text-base font-semibold hover:bg-primary-50 transition active:scale-95"
                                    :class="product?.is_digital == true ? 'my-4 w-1/2' : 'w-1/4'"
                                    @click="buyNow">
                                    {{ $t("Buy Now") }}
                                </button>
                            </div>

                            <!-- Seller Card -->
                            <div v-if="masterStore.multiVendor"
                                class="mt-5 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <div class="flex items-center justify-between gap-3 px-4 py-3">
                                    <router-link :to="shopUrl(product.shop)"
                                        class="flex items-center gap-3 min-w-0">
                                        <div class="w-12 h-12 rounded-full overflow-hidden shrink-0 border border-slate-100 dark:border-slate-700">
                                            <img :src="product.shop?.logo" loading="lazy"
                                                class="w-full h-full object-cover" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-slate-400 text-xs">{{ $t("Sold by") }}</div>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="text-slate-900 dark:text-white text-sm font-bold truncate">{{ product.shop?.name }}</span>
                                                <CheckBadgeIcon class="w-4 h-4 text-blue-500 shrink-0" />
                                            </div>
                                            <div v-if="product.shop?.address" class="text-slate-400 text-xs truncate mt-0.5">
                                                {{ product.shop.address }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                                                <span v-if="product.shop?.positive_seller_count">{{ product.shop.positive_seller_count }}% {{ $t("Positive") }}</span>
                                                <template v-if="product.shop?.positive_seller_count && product.shop?.total_sale">
                                                    <span class="text-slate-300">|</span>
                                                </template>
                                                <span v-if="product.shop?.total_sale">{{ product.shop.total_sale }}+ {{ $t("Sold") }}</span>
                                                <template v-if="(product.shop?.total_sale || product.shop?.positive_seller_count) && product.shop?.joined">
                                                    <span class="text-slate-300">|</span>
                                                </template>
                                                <span v-if="product.shop?.joined">{{ $t("Joined") }} {{ product.shop.joined }}</span>
                                            </div>
                                        </div>
                                    </router-link>
                                    <div class="flex flex-col items-end gap-2 shrink-0">
                                        <div class="flex items-center gap-1">
                                            <StarIcon class="w-4 h-4 text-amber-400" />
                                            <span class="text-slate-800 dark:text-slate-100 text-sm font-bold">{{ product.shop?.rating?.toFixed(1) }}</span>
                                        </div>
                                        <div v-if="authStore.token"
                                            class="w-8 h-8 flex justify-center items-center bg-red-50 rounded-lg cursor-pointer border border-red-100 hover:bg-red-100 transition"
                                            @click="showChat(product.shop?.id, authStore?.user?.id, product.id)">
                                            <img src="/public/assets/icons/shop-chat/chat.svg" alt="chat" class="w-5 h-5">
                                        </div>
                                    </div>
                                </div>
                                <div class="border-t border-slate-100 dark:border-slate-700 px-4 py-2.5 flex items-center justify-between bg-slate-50 dark:bg-slate-900">
                                    <router-link :to="shopUrl(product.shop)"
                                        class="text-primary text-sm font-semibold hover:text-primary-700 transition">
                                        {{ $t("Visit Store") }}
                                    </router-link>
                                    <div v-if="product?.is_digital == false" class="text-slate-400 text-xs">
                                        {{ $t("Est. delivery") }}: <span class="text-slate-600 dark:text-slate-400 font-medium">{{ product.shop?.estimated_delivery_time }}</span>
                                    </div>
                                </div>
                                <RightChatSidebar :show="showSidebar" @close="showSidebar = false" :shop="product?.shop" />
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- ── Description / Reviews / Seller Offers (full width) ── -->
            <div class="mt-8">
                <div class="flex items-center gap-6 flex-wrap border-b border-slate-200 dark:border-slate-700 mb-6">
                    <button
                        class="pb-3 transition text-sm font-semibold border-b-2 -mb-px"
                        :class="aboutProduct ? 'text-primary border-primary' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:text-slate-300'"
                        @click="showAboutProduct()">
                        {{ $t("Description") }}
                    </button>
                    <button
                        class="pb-3 transition text-sm font-semibold border-b-2 -mb-px"
                        :class="review ? 'text-primary border-primary' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:text-slate-300'"
                        @click="showReview()">
                        {{ $t("Reviews") }}
                        <span class="ml-1 text-xs font-normal text-slate-400">({{ product.total_reviews }})</span>
                    </button>
                    <button v-if="masterStore.multiVendor && sellerOffers.length > 0"
                        class="pb-3 transition text-sm font-semibold border-b-2 -mb-px"
                        :class="sellerTab ? 'text-primary border-primary' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:text-slate-300'"
                        @click="showSellerOffers()">
                        {{ $t("Available in other Shop") }}
                    </button>
                </div>

                <!-- Description -->
                <div v-if="aboutProduct" class="description">
                    <div class="prose max-w-none w-full m-0 prose-slate prose-p:text-slate-500 dark:text-slate-400 prose-li:text-slate-500 dark:text-slate-400 text-slate-500 dark:text-slate-400" v-html="product.description"></div>
                </div>

                <!-- Reviews -->
                <div v-if="review">
                    <div class="text-slate-900 dark:text-white text-xl font-semibold mb-4">{{ $t("Rating and Review") }}</div>
                    <div class="max-w-2xl">
                        <ReviewRatings :reviewRatings="averageRatings.percentages"
                            :averageRating="averageRatings?.rating"
                            :totalReview="averageRatings.total_review" />
                    </div>
                    <div class="border-t border-slate-200 dark:border-slate-700 mt-6">
                        <div class="mt-6 text-slate-900 dark:text-white text-xl font-semibold">{{ $t("Reviews") }}</div>
                        <div class="space-y-6 mt-6">
                            <Review v-for="rev in reviews" :key="rev.id" :review="rev" />
                        </div>
                        <div class="flex justify-between items-center w-full mt-8 gap-4 flex-wrap">
                            <div class="text-slate-600 dark:text-slate-400 text-sm">
                                {{ $t("Showing") }} {{ perPage * (currentPage - 1) + 1 }}
                                {{ $t("to") }} {{ perPage * (currentPage - 1) + reviews.length }}
                                {{ $t("of") }} {{ totalReviews }} {{ $t("results") }}
                            </div>
                            <vue-awesome-paginate :total-items="totalReviews" :items-per-page="perPage"
                                type="button" :max-pages-shown="3" v-model="currentPage"
                                :hide-prev-next-when-ends="true" @click="onClickHandler" />
                        </div>
                    </div>
                </div>

                <!-- Seller Offers Tab -->
                <div v-if="sellerTab && masterStore.multiVendor && sellerOffers.length > 0">
                    <div class="space-y-3">
                        <div v-for="offer in sellerOffers" :key="offer.id"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between hover:border-slate-300 transition">
                            <div class="min-w-0 flex items-start gap-3">
                                <img :src="offer.thumbnail || '/default/default.jpg'" :alt="offer.name"
                                    class="h-16 w-16 rounded-xl border border-slate-200 dark:border-slate-700 object-cover shrink-0" />
                                <div class="min-w-0">
                                    <div class="text-slate-900 dark:text-white text-sm font-semibold truncate">{{ offer.name }}</div>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400 truncate">{{ offer.shop?.name }}</div>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                        <div>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $t("Price") }}:</span>
                                            {{ masterStore.showCurrency(parseFloat(offer.discount_price).toFixed(2)) }}
                                        </div>
                                        <div>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $t("Delivery") }}:</span>
                                            {{ offer.shop?.estimated_delivery_time }}
                                        </div>
                                        <div>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $t("Rating") }}:</span>
                                            {{ offer.shop?.rating?.toFixed(1) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button
                                class="shrink-0 px-4 py-2 rounded-lg border text-sm font-semibold transition"
                                :class="offer.id == product.id
                                    ? 'border-primary bg-primary-50 text-primary'
                                    : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-primary hover:text-primary'"
                                @click="viewSellerOffer(offer.id)">
                                {{ offer.id == product.id ? $t("Current seller") : $t("View offer") }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Popular Products + You may also like (full width, below description) ── -->
            <div class="mt-8">
                <ProductDetailsRightSide
                    :product="product"
                    :popularProducts="popularProducts"
                    :relatedProducts="relatedProducts" />
            </div>

        </div>

        <!-- Pre-Order Modal -->
        <PreOrderModal
            :show="showPreOrderModal"
            :product="product"
            :details="preOrderDetails"
            @close="showPreOrderModal = false" />
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { useRoute, useRouter } from "vue-router";
import { shopUrl } from "../composables/useSlugify";
import { useMaster } from "../stores/MasterStore";

import { HeartIcon, HomeIcon, MinusIcon, PlusIcon, ShareIcon, ClockIcon, TruckIcon, ArrowPathIcon } from "@heroicons/vue/24/outline";
import { HeartIcon as HeartIconFill, StarIcon, CheckBadgeIcon } from "@heroicons/vue/24/solid";
import { FreeMode, Navigation, Thumbs } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";

import { useToast } from "vue-toastification";
import { useAuth } from "../stores/AuthStore";
import { useBasketStore } from "../stores/BasketStore";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faFacebookF, faLinkedin, faTwitter, faPinterest, faRedditAlien, faWhatsapp, faTelegram } from '@fortawesome/free-brands-svg-icons';
import { faEnvelope, faArrowUp } from "@fortawesome/free-solid-svg-icons";
import { useShareLink } from "vue3-social-sharing";
const { shareLink } = useShareLink();

import ProductDetailsRightSide from "../components/ProductDetailsRightSide.vue";
import PreOrderModal from "../components/PreOrderModal.vue";
import RightChatSidebar from "../components/RightChatSidebar.vue";
import ToastSuccessMessage from "../components/ToastSuccessMessage.vue";
import BagIcon from "../icons/Bag.vue";
import SkeletonLoader from "../components/SkeletonLoader.vue";
import ReviewRatings from "../components/ReviewRatings.vue";
import ProductCard from "../components/ProductCard.vue";
import Review from "../components/Review.vue";
import localization from "../localization";
import { useSeo } from "../composables/useSeo";
import { useMetaPixel } from "../composables/useMetaPixel";

import "swiper/css";
import "swiper/css/free-mode";
import "swiper/css/navigation";
import "swiper/css/thumbs";

const toast = useToast();
const t = localization.i18n.global.t;
const route = useRoute();
const router = useRouter();
const masterStore = useMaster();
const basketStore = useBasketStore();
const authStore = useAuth();
const { setMeta, setJsonLd } = useSeo();
const { trackViewContent } = useMetaPixel();

const thumbsSwiper = ref(null);
const modules = [FreeMode, Navigation, Thumbs];
const showSidebar = ref(false);

const showChat = async (sid, uid, pid) => {
    await axios.post('/store-message', {
        shop_id: sid, user_id: uid, product_id: pid, type: 'user',
    }, { headers: { Authorization: authStore.token } });
    showSidebar.value = true;
};

const displayThumbnails = ref([]);
const setThumbsSwiper = (swiper) => { thumbsSwiper.value = swiper; };

const formData = ref({ product_id: null, size: null, color: null, unit: null });

const product = ref({});
const productPrice = ref(0);
const mainPrice = ref(0);
const discountPercentage = ref(0);

const relatedProducts = ref([]);
const popularProducts = ref([]);
const sellerOffers = ref([]);

const aboutProduct = ref(true);
const review = ref(false);
const sellerTab = ref(false);

const cartProduct = ref(null);
const isLoading = ref(true);
const localQty = ref(1);

// ── Pre-Order ──
const showPreOrderModal = ref(false);
const isPreOrder = computed(() => masterStore.preOrder && product.value?.is_preorder);
const preOrderDetails = computed(() => product.value?.pre_order_details ?? {});

const openPreOrder = () => {
    if (!authStore.token) {
        authStore.loginModal = true;
        return;
    }
    showPreOrderModal.value = true;
};

onMounted(() => {
    fetchProductDetails();
    window.scrollTo(0, 0);
});

watch(formData, () => {
    calculateProductPrice();
    updateThumbnailsForColor();
    if (product.value.id) {
        findProductInCart(product.value.id);

        // A variant already in the cart shows its own cart quantity, so the
        // local counter only applies to variants that aren't in the cart yet —
        // start those back at 1 instead of carrying over the previous variant's
        // count.
        if (!cartProduct.value) localQty.value = 1;
    }
}, { deep: true });

watch(() => product.value.thumbnails, (newVal) => {
    displayThumbnails.value = newVal ? newVal.slice() : [];
    updateThumbnailsForColor();
}, { deep: true });

const shareOptions = [
    { name: "facebook", icon: faFacebookF, color: "#0d68f1" },
    { name: "linkedin", icon: faLinkedin, color: "#1275b1" },
    { name: "twitter", icon: faTwitter, color: "#47acdf" },
    { name: "pinterest", icon: faPinterest, color: "#bb0f23" },
    { name: "reddit", icon: faRedditAlien, color: "#fc471e" },
    { name: "whatsapp", icon: faWhatsapp, color: "#25d366" },
    { name: "email", icon: faEnvelope, color: "#bb0f23" },
    { name: "telegram", icon: faTelegram, color: "#47acdf" },
];

const share = (network) => {
    let description = product.value.short_description?.replace(/<[^>]*>/g, "") ?? "";
    let currentURL = window.location.href;
    let thumbnail = product.value.thumbnails?.[0];
    shareLink({
        network,
        url: currentURL,
        title: product.value.name,
        description,
        media: thumbnail ? thumbnail.url : null,
        quote: product.value.name,
        hashtags: product.value.meta_keywords,
        twitterUser: product.value.shop?.name
    });
};

const calculateProductPrice = () => {
    var colorPrice = 0;
    var sizePrice = 0;
    const color = product.value.colors?.find((c) => c.id == formData.value.color);
    const size = product.value.sizes?.find((s) => s.id == formData.value.size);
    if (color) colorPrice = color.price ?? 0;
    if (size) sizePrice = size.price ?? 0;

    if (product.value.discount_price > 0) {
        productPrice.value = product.value.discount_price + colorPrice + sizePrice;
        mainPrice.value = product.value.price + colorPrice + sizePrice;
    } else {
        productPrice.value = product.value.price + colorPrice + sizePrice;
        mainPrice.value = productPrice.value;
    }
    discountPercentage.value = (((mainPrice.value - productPrice.value) / mainPrice.value) * 100).toFixed(2);
};

const incrementLocalQty = () => {
    if (localQty.value < (product.value.quantity ?? 0)) localQty.value++;
};

const decrementLocalQty = () => {
    if (localQty.value > 1) localQty.value--;
};

const buyNow = () => {
    basketStore.addToCart({
        product_id: formData.value.product_id,
        is_buy_now: true,
        quantity: cartProduct.value ? cartProduct.value.quantity : localQty.value,
        size: formData.value.size,
        color: formData.value.color,
        unit: null
    }, product.value);
    basketStore.buyNowShopId = product.value?.shop?.id;
};

const viewSellerOffer = (offerId) => {
    if (offerId == product.value.id) return;
    const offer = sellerOffers.value.find(o => o.id == offerId);
    if (offer?.slug) router.push({ name: route.name, params: { slug: offer.slug } });
};

const showAboutProduct = () => { aboutProduct.value = true; review.value = false; sellerTab.value = false; };

watch(route, async () => {
    await nextTick();
    window.scrollTo(0, 0);
    localQty.value = 1;
    fetchProductDetails();
    showAboutProduct();
});

watch(() => basketStore.products, () => {
    if (product.value.id) findProductInCart(product.value.id);
}, { deep: true });

// Only match a cart row when its color/size also match the currently
// selected variant — otherwise selecting a new variant of a product that's
// already in the cart (with a different variant) would hide "Add to Cart".
const findProductInCart = (productId) => {
    let foundProduct = null;
    basketStore.products.forEach((item) => {
        item.products.find((p) => {
            if (
                p.id == productId &&
                (p.color?.id ?? null) == (formData.value.color ?? null) &&
                (p.size?.id ?? null) == (formData.value.size ?? null)
            ) return (foundProduct = p);
        });
    });
    cartProduct.value = foundProduct;
};

const addToCart = () => {
    basketStore.addToCart({ ...formData.value, quantity: localQty.value }, product.value);
    setTimeout(() => { findProductInCart(product.value.id); }, 200);
};

const decrementQty = () => {
    basketStore.decrementQuantity(cartProduct.value);
    setTimeout(() => { findProductInCart(product.value.id); }, 200);
};

const incrementQty = () => {
    basketStore.incrementQuantity(cartProduct.value);
    setTimeout(() => { findProductInCart(product.value.id); }, 200);
};

const favoriteAddOrRemove = () => {
    if (authStore.token === null) return (authStore.loginModal = true);
    axios.post('/favorite-add-or-remove', { product_id: product.value.id }, {
        headers: { Authorization: authStore.token }
    }).then(() => {
        product.value.is_favorite = !product.value.is_favorite;
        const title = product.value.is_favorite ? t('Product added to favorite') : t('Product removed from favorite');
        const message = product.value.is_favorite
            ? t('Product added to favorite successfully')
            : t('Product removed from favorite successfully');
        toast({ component: ToastSuccessMessage, props: { title, message } }, {
            type: "default", hideProgressBar: true, icon: false,
            position: "top-right", toastClassName: "vue-toastification-alert", timeout: 3000
        });
        authStore.fetchFavoriteProducts();
    }).catch(console.log);
};

const showReview = () => {
    aboutProduct.value = false; review.value = true; sellerTab.value = false;
    fetchReviews();
};

const showSellerOffers = () => {
    aboutProduct.value = false; review.value = false; sellerTab.value = true;
};

const flashSale = ref({});
const fetchProductDetails = async () => {
    isLoading.value = true;
    axios.get("/product-details", {
        params: { slug: route.params.slug },
        headers: { Authorization: authStore.token },
    }).then((response) => {
        product.value = response.data.data.product;
        sellerOffers.value = response.data.data.seller_offers ?? [];
        relatedProducts.value = response.data.data.related_products;
        popularProducts.value = response.data.data.popular_products;
        flashSale.value = response.data.data.product.flash_sale;

        setMeta({
            title: product.value.name,
            description: product.value.short_description || product.value.name,
            image: product.value.thumbnails?.[0]?.thumbnail || product.value.thumbnails?.[0]?.url,
            type: 'product',
            keywords: product.value.meta_keywords,
        });

        setJsonLd({
            '@context': 'https://schema.org/',
            '@type': 'Product',
            name: product.value.name,
            description: (product.value.short_description || '').replace(/<[^>]*>/g, ''),
            image: (product.value.thumbnails || []).map(t => t.thumbnail || t.url).filter(Boolean),
            sku: product.value.id,
            brand: product.value.shop?.name ? { '@type': 'Brand', name: product.value.shop.name } : undefined,
            offers: {
                '@type': 'Offer',
                price: product.value.discount_price > 0 ? product.value.discount_price : product.value.price,
                priceCurrency: masterStore.currency?.name || 'USD',
                availability: (product.value.quantity ?? 0) > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                url: window.location.href,
            },
            aggregateRating: product.value.total_reviews > 0 ? {
                '@type': 'AggregateRating',
                ratingValue: product.value.rating,
                reviewCount: product.value.total_reviews,
            } : undefined,
        });

        formData.value.product_id = product.value.id;

        if (flashSale.value) startCountdown();

        formData.value.color = product.value.colors?.length > 0 ? product.value.colors[0].id : null;
        formData.value.size = product.value.sizes?.length > 0 ? product.value.sizes[0].id : null;

        calculateProductPrice();
        findProductInCart(product.value.id);

        displayThumbnails.value = product.value.thumbnails ? product.value.thumbnails.slice() : [];
        updateThumbnailsForColor();

        trackViewContent(product.value);

        setTimeout(() => { isLoading.value = false; }, 100);
    });
};

const updateThumbnailsForColor = () => {
    try {
        const selectedColorId = formData.value.color;
        const baseThumbnails = product.value.thumbnails ? product.value.thumbnails.slice() : [];

        if (!selectedColorId || !product.value?.colors) {
            displayThumbnails.value = baseThumbnails;
            return;
        }

        const color = product.value.colors.find(c => c.id == selectedColorId);
        if (!color) { displayThumbnails.value = baseThumbnails; return; }

        const image = color.image || color.img || color.thumbnail || color.photo || color.image_url || color.url || null;

        if (image) {
            const getSrc = (t) => (t && (t.thumbnail || t.url || ""));
            const foundIndex = baseThumbnails.findIndex(t => getSrc(t) === image);
            if (foundIndex !== -1) {
                const found = baseThumbnails.splice(foundIndex, 1)[0];
                displayThumbnails.value = [found, ...baseThumbnails];
            } else {
                displayThumbnails.value = [{ id: `color-${color.id}`, thumbnail: image, url: image, type: 'image' }, ...baseThumbnails];
            }
        } else {
            displayThumbnails.value = baseThumbnails;
        }
    } catch (e) {
        displayThumbnails.value = product.value.thumbnails ? product.value.thumbnails.slice() : [];
    }
};

const averageRatings = ref({});
const totalReviews = ref(0);
const reviews = ref([]);
const currentPage = ref(1);
const perPage = ref(6);

const onClickHandler = (page) => { currentPage.value = page; fetchReviews(); };

const fetchReviews = async () => {
    axios.get("/reviews", {
        params: { product_id: product.value.id, page: currentPage.value, per_page: perPage.value },
    }).then((response) => {
        totalReviews.value = response.data.data.total;
        reviews.value = response.data.data.reviews;
        averageRatings.value = response.data.data.average_rating_percentage;
    });
};

const endDay = ref("");
const endHour = ref("");
const endMinute = ref("");
const endSecond = ref("");
let countdownInterval = null;

const startCountdown = () => {
    const endDate = new Date(flashSale.value?.end_date).getTime();
    if (flashSale.value?.end_date) {
        countdownInterval = setInterval(() => {
            const now = new Date().getTime();
            const timeLeft = endDate - now;
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                endDay.value = endHour.value = endMinute.value = endSecond.value = "00";
            } else {
                endDay.value = String(Math.floor(timeLeft / (1000 * 60 * 60 * 24))).padStart(2, "0");
                endHour.value = String(Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, "0");
                endMinute.value = String(Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, "0");
                endSecond.value = String(Math.floor((timeLeft % (1000 * 60)) / 1000)).padStart(2, "0");
            }
        }, 1000);
    }
};

onUnmounted(() => { clearInterval(countdownInterval); });

const mouseX = ref(0);
const mouseY = ref(0);

const handleMouseMove = (event) => {
    const container = event.currentTarget;
    const rect = container.getBoundingClientRect();
    let clientX, clientY;
    if (event.type === "touchmove" || event.type === "touchstart") {
        const touch = event.touches[0];
        clientX = touch.clientX; clientY = touch.clientY;
    } else {
        clientX = event.clientX; clientY = event.clientY;
    }
    mouseX.value = ((clientX - rect.left) / rect.width) * 100;
    mouseY.value = ((clientY - rect.top) / rect.height) * 100;
};

const resetZoom = () => { mouseX.value = 50; mouseY.value = 50; };

watch([mouseX, mouseY], ([x, y]) => {
    document.documentElement.style.setProperty('--mouse-x', `${x}%`);
    document.documentElement.style.setProperty('--mouse-y', `${y}%`);
});
</script>

<style scoped>
.zoom-container {
    overflow: hidden;
    position: relative;
    cursor: zoom-in;
    width: 100%;
    height: 100%;
}

.zoom-image {
    transition: transform 0.3s ease, transform-origin 0.3s ease;
    transform: scale(1);
    transform-origin: center center;
}

.zoom-container:hover .zoom-image {
    transform: scale(3.5);
    transform-origin: calc(var(--mouse-x, 50%)) calc(var(--mouse-y, 50%));
}
</style>

<style>
.description img { max-width: 95% !important; }

iframe { width: 100%; height: 300px !important; }
@media(max-width:500px) { iframe { height: 200px !important; } }
@media(max-width:375px) { iframe { height: 180px !important; } }
@media(max-width:320px) { iframe { height: 160px !important; } }

.product-details-slider .swiper-slide { height: 100% !important; }

.product-details-thumbnail .swiper-slide {
    @apply h-20 md:h-[120px] lg:h-[100px];
}

.product-details-thumbnail .swiper-button-prev,
.product-details-thumbnail .swiper-button-next {
    @apply bg-white dark:bg-slate-800 w-6 h-6 rounded-full shadow border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 -translate-y-1/2 mt-0;
}

.product-details-thumbnail .swiper-button-prev::after,
.product-details-thumbnail .swiper-button-next::after { @apply text-base; }

.product-details-thumbnail .swiper-button-next { right: 0px; }
.product-details-thumbnail .swiper-button-prev { left: 0px; }

.product-details-thumbnail .swiper-slide {
    @apply border border-slate-100 dark:border-slate-700 rounded-xl transition overflow-hidden;
}

.product-details-thumbnail .swiper-slide-thumb-active {
    @apply border-2 border-primary;
}
</style>
