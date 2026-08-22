<template>
  <div class="space-y-4 mt-4 transition duration-300">
    <div>
      <!-- Shop Name -->
      <div v-if="master.multiVendor" class="text-slate-950 dark:text-white text-sm font-medium leading-normal mb-1">
        {{ basketStore.buyNowProduct?.shop_name }}
      </div>

      <div class="divide-y divide-slate-100 dark:divide-slate-700">
        <!-- item -->
        <div
          v-for="product in basketStore.buyNowProduct?.products"
          :key="product.cart_id ?? product.id"
          class="flex gap-3 justify-between items-center w-full py-3 first:pt-0 last:pb-0"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div
              class="w-14 h-14 shrink-0 rounded-lg border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700 flex items-center justify-center overflow-hidden"
            >
              <img
                :src="product.thumbnail"
                class="w-full h-full object-contain"
              />
            </div>
            <div class="min-w-0">
              <!-- Brand -->
              <div class="text-primary text-sm font-medium leading-normal truncate">
                {{ product?.brand }}
              </div>
              <!-- Product Name -->
              <div class="text-slate-800 dark:text-slate-200 text-sm font-medium leading-normal truncate">
                {{ product?.name }}
              </div>
              <!-- Variant -->
              <div v-if="product.color || product.size" class="flex items-center gap-1.5 mt-1">
                <div
                  v-if="product.color"
                  class="flex items-center gap-1 px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-slate-600 dark:text-slate-300 text-xs font-normal"
                >
                  <span
                    v-if="product.color.color_code"
                    class="w-2.5 h-2.5 rounded-full border border-slate-300 dark:border-slate-500 shrink-0"
                    :style="{ backgroundColor: product.color.color_code }"
                  ></span>
                  {{ product.color.name }}
                </div>
                <div
                  v-if="product.size"
                  class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-slate-600 dark:text-slate-300 text-xs font-normal"
                >
                  {{ product.size.name }}
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col items-end gap-1.5 shrink-0">
            <div class="text-slate-950 dark:text-white text-sm font-semibold leading-normal">
              {{ master.showCurrency((product?.discount_price > 0) ? product?.discount_price : product?.price) }}
            </div>
            <!-- Quantity Increase Or Decrease -->
            <div
              class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden"
            >
              <button
                type="button"
                class="w-7 h-7 flex items-center justify-center bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-700 transition disabled:opacity-40 disabled:cursor-not-allowed"
                :disabled="product.quantity <= 1"
                :aria-label="$t('Decrease quantity')"
                @click="basketStore.changeBuyNowQuantity(product, 'decrement')"
              >
                <MinusIcon class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400" />
              </button>

              <div
                class="w-9 text-center text-slate-950 dark:text-white text-sm font-semibold border-x border-slate-200 dark:border-slate-700"
              >
                {{ product.quantity }}
              </div>

              <button
                type="button"
                class="w-7 h-7 flex items-center justify-center bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                :aria-label="$t('Increase quantity')"
                @click="basketStore.changeBuyNowQuantity(product, 'increment')"
              >
                <PlusIcon class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { MinusIcon, PlusIcon } from "@heroicons/vue/24/outline";
import { useAuth } from "../stores/AuthStore";
import { useBasketStore } from "../stores/BasketStore";
import { useMaster } from "../stores/MasterStore";

const AuthStore = useAuth();
const master = useMaster();
const basketStore = useBasketStore();
</script>

<style lang="scss" scoped></style>
