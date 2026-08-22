<template>
    <div>
        <!--==== Copyright ====-->
        <div class="main-container py-3 w-full border-t border-primary-100 dark:border-slate-700">
            <div class="text-center text-gray-600 dark:text-slate-400 text-base sm:text-sm font-normal leading-tight">
                © {{ new Date().getFullYear() }} {{ master.footerText || $t('All right reserved by company') }}
            </div>
        </div>

    </div>
</template>

<script setup>
import { useMaster } from '../stores/MasterStore';

const master = useMaster();

</script>
