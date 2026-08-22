<template>
    <div
        class="p-2 md:px-4 md:py-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-100 dark:border-slate-700 flex gap-2 lg:gap-4 flex-col lg:flex-row items-center justify-between">

        <div class="w-full lg:w-[50%] flex flex-col overflow-hidden">
            <div class="flex gap-1 justify-between">
                <div class="text-sm sm:text-base font-normal shrink-0">
                    <span class="text-slate-500">
                        {{ $t('Order ID') }}
                    </span>
                    <span class="text-blue-500">
                        {{ props.order?.order_code }}
                    </span>
                </div>

                <!-- Order Status -->
                <div class="text-xs sm:text-sm font-normal px-1.5 py-0.5 rounded-[10px] inline-block lg:hidden text-ellipsis overflow-hidden text-white"
                    :class="statusColor(props.order?.order_status)">
                    {{ props.order?.order_status }}
                </div>
            </div>

            <div class="inline-flex gap-2 text-xs sm:text-sm leading-tight text-ellipsis">
                <span class="text-slate-500 dark:text-slate-400 shrink-0">{{ $t('Placed on') }}</span>
                <span class="text-slate-950 dark:text-white whitespace-nowrap text-ellipsis overflow-hidden">
                    {{ props.order?.placed_at }}
                </span>
            </div>
        </div>

        <div class="grow flex w-full gap-2 items-center justify-between">
            <div class="text-sm sm:text-base font-normal xl:w-20">
                <span class="text-slate-500 dark:text-slate-400">
                    {{ $t('QTY') }}:
                </span>
                <span class="text-slate-950 dark:text-white">{{ props.order?.quantity }}</span>
            </div>

            <div class="text-sm sm:text-base font-normal xl:w-36">
                <span class="text-slate-500 dark:text-slate-400">
                    {{ $t('Amount') }}:
                </span>
                <span class="text-slate-950 dark:text-white">
                    {{ props.order?.currency_symbol }}{{ props.order?.amount }}
                </span>
            </div>

            <div class="hidden lg:block xl:w-40">
                <div class="text-sm font-normal px-1.5 py-0.5 rounded-[10px] inline-block text-white"
                    :class="statusColor(props.order?.order_status)">
                    {{ props.order?.order_status }}
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <router-link :to="'/pre-order-history/' + props.order?.id"
                    class="text-slate-500 dark:text-slate-400 text-sm sm:text-base font-normal leading-normal">
                    {{ $t('View Details') }}
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { statusColor } from "../utils/preOrderStatus";

const props = defineProps({
    order: Object
});
</script>
