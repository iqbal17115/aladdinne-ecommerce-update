<template>
    <div class="sticky top-0 bg-white dark:bg-slate-900 z-10 border-b border-transparent dark:border-slate-800">
        <NavbarMiddle />
        <div
            class="transition-[height] duration-300 ease-out"
            :class="showBottomNav ? 'overflow-visible' : 'overflow-hidden'"
            :style="{ height: showBottomNav ? `${bottomNavHeight}px` : '0px' }">
            <div ref="bottomNavRef">
                <NavbarBottom />
            </div>
        </div>
    </div>
</template>

<script setup>
import { nextTick, onMounted, onUnmounted, ref } from "vue";
import NavbarMiddle from "../components/NavbarMiddle.vue";
import NavbarBottom from "../components/NavbarBottom.vue"

const showBottomNav = ref(true);
const bottomNavRef = ref(null);
const bottomNavHeight = ref(0);

let lastScrollY = window.scrollY;
let lastToggleAt = 0;
let ticking = false;

const TOGGLE_COOLDOWN = 350;

const updateHeight = () => {
    if (bottomNavRef.value) {
        bottomNavHeight.value = bottomNavRef.value.offsetHeight;
    }
};

const handleScroll = () => {
    const currentScrollY = window.scrollY;
    const delta = currentScrollY - lastScrollY;
    const now = performance.now();

    if (currentScrollY <= 0) {
        if (!showBottomNav.value) {
            showBottomNav.value = true;
            lastToggleAt = now;
        }
        lastScrollY = currentScrollY;
    } else if (Math.abs(delta) > 10 && now - lastToggleAt > TOGGLE_COOLDOWN) {
        const next = delta < 0;
        if (next !== showBottomNav.value) {
            showBottomNav.value = next;
            lastToggleAt = now;
        }
        lastScrollY = currentScrollY;
    }

    ticking = false;
};

const onScroll = () => {
    if (!ticking) {
        window.requestAnimationFrame(handleScroll);
        ticking = true;
    }
};

onMounted(async () => {
    await nextTick();
    updateHeight();
    window.addEventListener('resize', updateHeight);
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('resize', updateHeight);
    window.removeEventListener('scroll', onScroll);
});
</script>

<style lang="scss" scoped></style>
