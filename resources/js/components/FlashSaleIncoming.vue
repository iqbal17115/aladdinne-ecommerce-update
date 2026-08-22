<template>
    <div class="mb-12">
        <div class="main-container">
            <section
                class="incoming-banner relative isolate overflow-hidden rounded-[28px] px-4 py-3 sm:px-6 sm:py-4 lg:px-10 lg:py-5">
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -left-12 top-10 h-36 w-36 rounded-full bg-white/30 dark:bg-slate-800/8 blur-2xl sm:h-48 sm:w-48"></div>
                    <div class="incoming-banner-glow absolute -right-16 bottom-0 h-48 w-48 rounded-full blur-2xl sm:h-64 sm:w-64"></div>
                    <div class="absolute left-0 bottom-10 grid grid-cols-6 gap-2 opacity-20">
                        <span v-for="dot in 18" :key="`left-dot-${dot}`" class="h-1.5 w-1.5 rounded-full bg-white dark:bg-slate-800"></span>
                    </div>
                    <div class="absolute left-[8%] top-[18%] h-2 w-2 rounded-full bg-white dark:bg-slate-800/50"></div>
                    <div class="incoming-banner-dot absolute left-[16%] top-[28%] h-1.5 w-1.5 rounded-full"></div>
                    <div class="incoming-banner-dot absolute right-[14%] top-[16%] h-2.5 w-2.5 rounded-full opacity-70"></div>
                    <div class="absolute right-[8%] top-[10%] grid grid-cols-6 gap-2 opacity-25">
                        <span v-for="dot in 24" :key="dot" class="h-1.5 w-1.5 rounded-full bg-white dark:bg-slate-800"></span>
                    </div>
                    <div class="incoming-sparkle absolute left-[2%] top-[10%] h-5 w-5 opacity-55"></div>
                    <div class="incoming-sparkle absolute left-[22%] top-[20%] h-3.5 w-3.5 opacity-70"></div>
                    <div class="incoming-sparkle absolute right-[3%] top-[19%] h-8 w-8 opacity-20"></div>
                </div>

                <div class="relative z-10 grid items-center gap-6 xl:grid-cols-[240px_minmax(0,1fr)_230px]">
                    <div class="mx-auto flex w-full max-w-[260px] items-end justify-center xl:mx-0">
                        <div class="relative h-[125px] w-[175px] sm:h-[135px] sm:w-[190px]">
                            <div class="absolute left-1 top-2 h-4 w-4 rounded-full bg-white dark:bg-slate-800/70"></div>
                            <div class="absolute bottom-4 left-0 flex gap-2 opacity-30">
                                <span v-for="dot in 4" :key="`calendar-left-dot-${dot}`" class="h-2 w-2 rounded-full bg-white dark:bg-slate-800"></span>
                            </div>

                            <div
                                class="incoming-calendar-card absolute left-8 top-5 w-[135px] rounded-[22px] border border-white/20 p-2.5 shadow-[0_22px_48px_rgba(0,0,0,0.28)] sm:w-[150px]">
                                <div class="mb-2 flex justify-center gap-3">
                                    <span v-for="ring in 4" :key="`calendar-ring-${ring}`"
                                        class="h-6 w-3.5 rounded-full border-[4px] border-white/95 border-b-0"></span>
                                </div>

                                <div class="rounded-[18px] bg-white dark:bg-slate-800 px-3 py-3 text-center sm:px-4 sm:py-3.5">
                                    <div class="text-[13px] font-black uppercase tracking-[0.08em] text-slate-700 dark:text-slate-300 sm:text-[15px]">
                                        {{ $t("Coming") }}
                                    </div>
                                    <div class="text-[22px] font-black uppercase leading-none text-primary sm:text-[30px]">
                                        {{ $t("Soon") }}
                                    </div>
                                </div>
                            </div>

                            <!-- <div
                                class="incoming-clock-face absolute -bottom-12 right-1 flex h-12 w-12 items-center justify-center rounded-full border-[4px] shadow-[0_16px_36px_rgba(0,0,0,0.24)] sm:h-20 sm:w-20 sm:-bottom-16">
                                <div class="incoming-clock-inner relative h-10 w-10 rounded-full border-2 sm:h-12 sm:w-12">
                                    <span class="absolute left-1/2 top-3 h-5 w-1 -translate-x-1/2 rounded-full bg-primary"></span>
                                    <span class="absolute left-1/2 top-1/2 h-1 w-5 -translate-y-1/2 rounded-full bg-primary"></span>
                                    <span class="absolute bottom-2 left-1/2 h-1.5 w-1.5 -translate-x-1/2 rounded-full bg-primary"></span>
                                </div>
                            </div> -->
                        </div>
                    </div>

                    <div class="min-w-0 text-center">
                        <!-- <div
                            class="mx-auto mb-4 inline-flex items-center gap-2 rounded-[10px] bg-white dark:bg-slate-800/95 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-primary shadow-sm sm:text-sm">
                            <CalendarDaysIcon class="h-4 w-4" />
                            {{ $t("Coming Soon") }}
                        </div> -->

                        <h2
                            class="relative flex flex-wrap items-center justify-center gap-3 text-[24px] font-black uppercase leading-none tracking-[0.05em] text-white drop-shadow-sm sm:gap-4 sm:text-[34px] md:text-[40px] xl:text-[38px] 2xl:text-[48px]">
                            <span class="text-white">{{ $t("Flash") }}</span>
                            <span class="incoming-sale-text bg-clip-text text-transparent">
                                {{ $t("Sale") }}
                            </span>
                        </h2>

                        <p class="mx-auto mt-2 max-w-2xl text-sm text-white/85 sm:text-lg">
                            {{ $t("The biggest deals are almost here! Get ready to shop & save big.") }}
                        </p>

                        <div class="mt-5 flex flex-wrap items-center justify-center gap-3 sm:gap-4">
                            <template v-if="showDays">
                                <div class="incoming-time-box">
                                    <div class="incoming-time-value">{{ endDay }}</div>
                                    <div class="incoming-time-label">{{ $t("Days") }}</div>
                                </div>
                                <span class="incoming-time-separator">:</span>
                            </template>

                            <div class="incoming-time-box">
                                <div class="incoming-time-value">{{ endHour }}</div>
                                <div class="incoming-time-label">{{ $t("Hours") }}</div>
                            </div>
                            <span class="incoming-time-separator">:</span>

                            <div class="incoming-time-box">
                                <div class="incoming-time-value">{{ endMinute }}</div>
                                <div class="incoming-time-label">{{ $t("Minutes") }}</div>
                            </div>
                            <span class="incoming-time-separator">:</span>

                            <div class="incoming-time-box">
                                <div class="incoming-time-value">{{ endSecond }}</div>
                                <div class="incoming-time-label">{{ $t("Seconds") }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-4 xl:items-end">
                        <div
                            class="incoming-deal-card relative w-full max-w-[240px] rounded-[24px] border border-dashed bg-white dark:bg-slate-800/8 px-4 py-3 backdrop-blur-sm">
                            <div class="incoming-deal-arrow absolute right-[-16px] top-1/2 hidden h-9 w-9 -translate-y-1/2 rotate-45 rounded-[10px] border border-inherit border-l-0 border-b-0 bg-inherit xl:block"></div>
                            <div class="flex items-center gap-3">
                                <div class="incoming-bell-badge flex h-10 w-10 items-center justify-center rounded-full text-white shadow-lg">
                                    <BellAlertIcon class="h-5 w-5" />
                                </div>
                                <div class="text-left">
                                    <div class="text-base font-black uppercase leading-tight text-white">
                                        {{ $t("Exclusive Offers") }}
                                    </div>
                                    <div class="incoming-off-text text-xs font-black  leading-tight">
                                        {{ $t("Big Deals Are Waiting For You") }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative flex justify-center xl:justify-end">
                            <div class="incoming-pedestal absolute bottom-0 right-[-18px] hidden h-20 w-52 rounded-t-full xl:block"></div>
                            <div class="absolute bottom-0 h-14 w-44 rounded-full bg-black/25 blur-md"></div>
                            <div class="incoming-bag relative mt-2">
                                <svg viewBox="0 0 220 220" class="h-[100px] w-[100px] overflow-visible sm:h-[115px] sm:w-[115px]" fill="none" aria-hidden="true">
                                    <!-- Confetti decorations -->
                                    <rect x="22" y="28" width="10" height="10" rx="2.5" fill="#FFE36E" transform="rotate(20 27 33)" opacity="0.92" />
                                    <rect x="180" y="18" width="8" height="8" rx="2" fill="var(--primary-200)" transform="rotate(-12 184 22)" opacity="0.85" />
                                    <rect x="14" y="80" width="7" height="7" rx="1.5" fill="var(--primary-300)" transform="rotate(35 18 84)" opacity="0.75" />
                                    <rect x="195" y="70" width="6" height="6" rx="1.5" fill="#FFE36E" transform="rotate(-25 198 73)" opacity="0.80" />
                                    <circle cx="42" cy="55" r="4" fill="#ffffff" opacity="0.40" />
                                    <circle cx="178" cy="45" r="3" fill="#ffffff" opacity="0.35" />
                                    <!-- Bag shadow/platform -->
                                    <ellipse cx="110" cy="204" rx="56" ry="10" fill="rgba(0,0,0,0.22)" />
                                    <!-- Bag body -->
                                    <rect x="55" y="100" width="112" height="90" rx="18" fill="url(#bagBody)" />
                                    <!-- Bag top fold/lip -->
                                    <rect x="55" y="100" width="112" height="22" rx="18" fill="url(#bagLip)" />
                                    <rect x="55" y="112" width="112" height="10" fill="url(#bagLip)" />
                                    <!-- Bag handle -->
                                    <path d="M82 100 C82 70 100 58 110 58 C120 58 138 70 138 100" stroke="url(#handleGrad)" stroke-width="9" stroke-linecap="round" fill="none" />
                                    <!-- Bag shine -->
                                    <ellipse cx="85" cy="130" rx="10" ry="18" fill="white" opacity="0.10" transform="rotate(-10 85 130)" />
                                    <!-- Lightning bolt -->
                                    <path class="incoming-bolt" d="M118 122 L100 155 L112 155 L104 182 L124 146 L112 146 L122 122 Z" fill="white" opacity="0.95" />
                                    <defs>
                                        <linearGradient id="bagBody" x1="55" y1="100" x2="167" y2="190" gradientUnits="userSpaceOnUse">
                                            <stop offset="0%" stop-color="var(--primary-300)" />
                                            <stop offset="55%" stop-color="var(--primary)" />
                                            <stop offset="100%" stop-color="var(--primary-700)" />
                                        </linearGradient>
                                        <linearGradient id="bagLip" x1="55" y1="100" x2="167" y2="122" gradientUnits="userSpaceOnUse">
                                            <stop offset="0%" stop-color="var(--primary-200)" />
                                            <stop offset="100%" stop-color="var(--primary-300)" />
                                        </linearGradient>
                                        <linearGradient id="handleGrad" x1="82" y1="58" x2="138" y2="100" gradientUnits="userSpaceOnUse">
                                            <stop offset="0%" stop-color="var(--primary-100)" />
                                            <stop offset="100%" stop-color="var(--primary-300)" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from "vue";
import { BellAlertIcon, CalendarDaysIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    flashSale: Object,
});

const endDay = ref("");
const endHour = ref("");
const endMinute = ref("");
const endSecond = ref("");
let countdownInterval = null;
const showDays = computed(() => Number(endDay.value) > 0);

const startCountdown = () => {
    const endDate = new Date(props.flashSale.end_date).getTime();

    countdownInterval = setInterval(() => {
        const now = new Date().getTime();
        const timeLeft = endDate - now;

        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            endDay.value = "00";
            endHour.value = "00";
            endMinute.value = "00";
            endSecond.value = "00";
        } else {
            endDay.value = String(Math.floor(timeLeft / (1000 * 60 * 60 * 24))).padStart(2, "0");
            endHour.value = String( Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, "0");
            endMinute.value = String(Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, "0");
            endSecond.value = String(Math.floor((timeLeft % (1000 * 60)) / 1000)).padStart(2, "0");
        }
    }, 1000);
};

onMounted(startCountdown);

onUnmounted(() => {
    clearInterval(countdownInterval);
});
</script>

<style scoped>
.incoming-calendar-card {
    background: linear-gradient(180deg, var(--primary-300) 0%, var(--primary) 100%);
    animation: incoming-float 1.6s ease-in-out infinite;
}

.incoming-bag {
    animation: incoming-float 1.8s ease-in-out infinite;
    transform-origin: center bottom;
}

.incoming-bolt {
    animation: incoming-bolt-flash 1s ease-in-out infinite;
    transform-origin: center;
}

@keyframes incoming-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

@keyframes incoming-bolt-flash {
    0%, 100% { opacity: 0.45; }
    50% { opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .incoming-calendar-card,
    .incoming-bag,
    .incoming-bolt {
        animation: none;
    }
}

.incoming-clock-face {
    border-color: color-mix(in srgb, var(--primary-200) 70%, white);
    background: radial-gradient(circle at 35% 35%, #fff 0%, color-mix(in srgb, var(--primary-100) 40%, white) 55%, color-mix(in srgb, var(--primary-200) 60%, white) 100%);
}

.incoming-clock-inner {
    border-color: color-mix(in srgb, var(--primary-300) 55%, transparent);
}

.incoming-banner {
    background:
        radial-gradient(ellipse at 20% 50%, rgba(255, 120, 160, 0.10) 0%, transparent 38%),
        radial-gradient(ellipse at 80% 20%, rgba(255, 255, 255, 0.06) 0%, transparent 35%),
        linear-gradient(135deg, color-mix(in srgb, var(--primary) 82%, white) 0%, var(--primary-800) 40%, var(--primary-950, #5a0020) 100%);
}

.incoming-banner-glow {
    background: color-mix(in srgb, var(--primary-300) 55%, transparent);
}

.incoming-banner-dot {
    background-color: color-mix(in srgb, var(--primary-100) 80%, white);
}

.incoming-sparkle {
    background: color-mix(in srgb, var(--primary-100) 85%, white);
    clip-path: polygon(50% 0%, 62% 38%, 100% 50%, 62% 62%, 50% 100%, 38% 62%, 0% 50%, 38% 38%);
}

.incoming-sale-text {
    background-image: linear-gradient(90deg, #ffffff 0%, #ffe7ef 25%, var(--primary-200) 62%, #ffd6e5 100%);
}

.incoming-title-accent {
    position: absolute;
    width: 24px;
    height: 24px;
}

.incoming-title-accent::before,
.incoming-title-accent::after {
    content: "";
    position: absolute;
    right: 0;
    width: 18px;
    height: 4px;
    border-radius: 999px;
    background: color-mix(in srgb, var(--primary-100) 78%, white);
}

.incoming-title-accent::before {
    top: 5px;
    transform: rotate(22deg);
}

.incoming-title-accent::after {
    bottom: 5px;
    transform: rotate(-22deg);
}

.incoming-deal-card {
    border-color: rgba(255, 255, 255, 0.35);
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
}

.incoming-deal-arrow {
    backdrop-filter: blur(12px);
    border-color: rgba(255, 255, 255, 0.35);
    background: rgba(255, 255, 255, 0.08);
}

.incoming-bell-badge {
    background: linear-gradient(135deg, var(--primary-300) 0%, var(--primary-700) 100%);
    box-shadow: 0 6px 18px color-mix(in srgb, var(--primary) 50%, transparent);
}

.incoming-off-text {
    color: #ffe36e;
    text-shadow: 0 1px 6px rgba(0,0,0,0.15);
}

.incoming-pedestal {
    background: linear-gradient(180deg, color-mix(in srgb, var(--primary-600) 80%, black) 0%, color-mix(in srgb, var(--primary-900) 90%, black) 100%);
    box-shadow: inset 0 12px 24px rgba(255, 255, 255, 0.10), 0 8px 24px rgba(0,0,0,0.3);
}

.incoming-time-box {
    min-width: 88px;
    border-radius: 18px;
    background: linear-gradient(175deg, #ffffff 0%, #fff0f6 100%);
    padding: 0.4rem 0.9rem 0.35rem;
    box-shadow: 0 16px 40px rgba(72, 8, 36, 0.22), 0 2px 6px rgba(0,0,0,0.08);
    border: 1px solid rgba(255,255,255,0.9);
}

.incoming-time-value {
    text-align: center;
    font-size: 1.9rem;
    line-height: 1;
    font-weight: 900;
    color: var(--primary);
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
}

.incoming-time-label {
    margin-top: 0.2rem;
    text-align: center;
    font-size: 0.8rem;
    font-weight: 500;
    color: #9ca3af;
    letter-spacing: 0.02em;
}

.incoming-time-separator {
    color: rgba(255, 255, 255, 0.70);
    font-size: 2.75rem;
    font-weight: 900;
    line-height: 1;
    text-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

@media (max-width: 640px) {
    .incoming-title-accent {
        display: none;
    }

    .incoming-time-box {
        min-width: 72px;
        padding: 0.5rem 0.6rem;
        border-radius: 16px;
    }

    .incoming-time-value {
        font-size: 1.7rem;
    }

    .incoming-time-label {
        font-size: 0.8rem;
    }

    .incoming-time-separator {
        font-size: 2rem;
    }
}
</style>
