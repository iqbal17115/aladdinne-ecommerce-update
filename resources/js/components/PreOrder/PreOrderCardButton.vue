<template>
    <button
        class="justify-center items-center gap-1.5 flex bg-primary hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-[8px] transition-colors whitespace-nowrap"
        @click.stop="goToDetails"
    >
        <ClockIcon class="w-4 h-4 shrink-0" />
        {{ $t('Preorder') }}
    </button>
</template>

<script setup>
import { ClockIcon } from '@heroicons/vue/24/outline';
import { useRouter } from 'vue-router';

const props = defineProps({
    product: { type: Object, default: () => ({}) },
});

const router = useRouter();

const goToDetails = () => {
    router.push({ name: 'productDetails', params: { slug: props.product?.slug } });
};
</script>
