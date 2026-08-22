<template>
    <div class="relative bg-white bg-cover bg-center bg-no-repeat "
        :style="master.footerBgImage ? { backgroundImage: `url(${master.footerBgImage})` } : {}">
        <div class="absolute inset-0 bg-slate-900/70 hidden dark:block pointer-events-none"></div>
        <div class="relative z-10">
            <FooterTop />
            <FooterBottom />
        </div>
    </div>
</template>

<script setup>
import FooterTop from './FooterTop.vue';
import FooterBottom from './FooterBottom.vue';
import { useMaster } from '../stores/MasterStore';

const master = useMaster();
</script>

