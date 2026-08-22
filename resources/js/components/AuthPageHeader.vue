<template>
    <div class="py-3 px-2 text-slate-800 dark:text-white text-lg md:text-lm font-medium font-['Roboto'] tracking-tight md:leading-loose bg-white dark:bg-slate-800">
        {{ title }}
    </div>
</template>

<script setup>
const props = defineProps({
    title: String
});

</script>
