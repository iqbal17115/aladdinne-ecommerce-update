<template>
    <div  class="main-container py-6">
     <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 overflow-hidden pb-4 text-sm">
                <router-link to="/" class="shrink-0 flex items-center gap-1 text-slate-500 hover:text-primary transition">
                    <HomeIcon class="w-4 h-4" />
                    <span>{{ $t("Home") }}</span>
                </router-link>
                <span class="text-slate-400">&#8250;</span>
                <span class="text-slate-800 dark:text-slate-100 font-medium truncate">{{ $t("Contact Us") }}</span>
            </nav>
        <div class="max-w-6xl mx-auto py-10 md:py-6 lg:py-6 px-4">



            <!-- Page Header -->
            <div class="text-center mb-10">
                <h1 class="text-2xl md:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white mb-4">
                    {{ $t('Contact Us') }}
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm md:text-sm max-w-xl mx-auto leading-relaxed">
                    {{ $t("We'd love to hear from you! Whether you have a question about our products, orders, or anything else, our team is here to help.") }}
                </p>
                <div class="mt-4 mx-auto w-12 h-1 rounded-full bg-primary"></div>
            </div>

            <!-- Two Cards Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                <!-- Left Card: Get in Touch -->
                <div class="lg:col-span-2 bg-rose-50 dark:bg-slate-700 rounded-2xl p-7 flex flex-col justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">{{ $t('Get in Touch') }}</h2>
                        <p class="text-slate-500 dark:text-slate-300 text-sm mb-7">{{ $t('Choose the way that works best for you.') }}</p>

                        <div class="space-y-5">
                            <!-- Email -->
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm mb-1">{{ $t('Email Us') }}</p>
                                    <p class="text-slate-500 dark:text-slate-300 text-sm mb-1">{{ contactInfo.email || '—' }}</p>
                                    <p class="text-slate-400 dark:text-slate-400 text-xs mt-0.5 mb-1">{{ $t("We'll respond within 24 hours") }}</p>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm mb-1">{{ $t('Call Us') }}</p>
                                    <p class="text-slate-500 dark:text-slate-300 text-sm mb-1">{{ contactInfo.phone || '—' }}</p>
                                    <p class="text-slate-400 dark:text-slate-400 text-xs mt-0.5 mb-1">{{ $t('Mon - Fri, 9:00 AM - 6:00 PM') }}</p>
                                </div>
                            </div>

                            <!-- Live Chat -->
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm mb-1">{{ $t('Live Chat') }}</p>
                                    <p class="text-slate-500 dark:text-slate-300 text-sm mb-1">{{ $t('Chat with our support team') }}</p>
                                    <p class="text-slate-400 dark:text-slate-400 text-xs mt-0.5 mb-1">{{ $t('Available on website') }}</p>
                                </div>
                            </div>

                            <!-- Visit Us -->
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm mb-1">{{ $t('Visit Us') }}</p>
                                    <p class="text-slate-500 dark:text-slate-300 text-sm">{{ contactInfo.address || '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Follow Us -->
                    <div v-if="masterStore.socialLinks?.length" class="mt-10">
                        <p class="text-slate-800 dark:text-slate-100 font-semibold text-sm mb-3">{{ $t('Follow Us') }}</p>
                        <div class="flex items-center flex-wrap gap-2">
                            <a v-for="social in masterStore.socialLinks" :key="social.id"
                                :href="social.link" target="_blank" rel="noopener noreferrer"
                                :title="social.name" class="social-icon">
                                <img :src="social.logo" :alt="social.name" class="w-4 h-4 object-contain" />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Card: Send Us a Message -->
                <div class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-7">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">{{ $t('Send Us a Message') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
                        {{ $t("Fill out the form below and we'll get back to you as soon as possible.") }}
                    </p>

                    <form @submit.prevent="contactFormSubmit()">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <!-- Full Name -->
                            <div>
                                <label class="form-label">
                                    {{ $t('Full Name') }} <span class="text-primary">*</span>
                                </label>
                                <input type="text" :placeholder="$t('Enter full name')" class="form-input"
                                    v-model="formData.name"
                                    :class="errors?.name ? 'border-red-400' : 'border-slate-200 dark:border-slate-700'" />
                                <span v-if="errors?.name" class="text-red-500 text-xs mt-1 block">{{ errors.name[0] }}</span>
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label class="form-label">
                                    {{ $t('Phone Number') }} <span class="text-primary">*</span>
                                </label>
                                <input type="text" :placeholder="$t('Enter phone number')" class="form-input"
                                    v-model="formData.phone"
                                    :class="errors?.phone ? 'border-red-400' : 'border-slate-200 dark:border-slate-700'" />
                                <span v-if="errors?.phone" class="text-red-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <label class="form-label">
                                {{ $t('Subject') }} <span class="text-primary">*</span>
                            </label>
                            <input type="text" :placeholder="$t('Enter subject line')" class="form-input"
                                v-model="formData.subject"
                                :class="errors?.subject ? 'border-red-400' : 'border-slate-200 dark:border-slate-700'" />
                            <span v-if="errors?.subject" class="text-red-500 text-xs mt-1 block">{{ errors.subject[0] }}</span>
                        </div>

                        <!-- Message -->
                        <div class="mb-6">
                            <label class="form-label">
                                {{ $t('Message') }} <span class="text-primary">*</span>
                            </label>
                            <textarea rows="5" :placeholder="$t('Write your message') + ' ...'" class="form-input resize-none"
                                v-model="formData.message"
                                :class="errors?.message ? 'border-red-400' : 'border-slate-200 dark:border-slate-700'"></textarea>
                            <span v-if="errors?.message" class="text-red-500 text-xs mt-1 block">{{ errors.message[0] }}</span>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 transition-colors text-white px-7 py-3 rounded-lg font-medium text-sm">
                            {{ $t('Send Message') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { HomeIcon } from '@heroicons/vue/24/outline';
import { useToast } from "vue-toastification";
import { useAuth } from "../stores/AuthStore";
import { useMaster } from "../stores/MasterStore";
import localization from "../localization";

const toast = useToast();
const t = localization.i18n.global.t;
const masterStore = useMaster();
const authStore = useAuth();

const contactInfo = ref({ phone: '', email: '', address: '', whatsapp: '', messenger: '' });

onMounted(() => {
    axios.get('/contact-us').then((response) => {
        contactInfo.value = response.data.data;
    });
});

const formData = ref({
    name: '',
    phone: '',
    subject: '',
    message: '',
});

const errors = ref({});

const contactFormSubmit = () => {
    errors.value = {};
    validateForm();

    if (formData.value.name && formData.value.phone && formData.value.subject && formData.value.message) {
        axios.post('/support', formData.value, {
            headers: {
                'Authorization': authStore.token
            }
        }).then((response) => {
            toast.success(response.data.message, {
                position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
            formData.value = { name: '', phone: '', subject: '', message: '' };
            errors.value = {};
        }).catch((error) => {
            errors.value = error.response.data.errors;
            toast.error(error.response.data.message, {
                position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
        });
    }
};

const validateForm = () => {
    if (!formData.value.name)    errors.value.name    = [t('Name is required.')];
    if (!formData.value.phone)   errors.value.phone   = [t('Phone number is required.')];
    if (!formData.value.subject) errors.value.subject = [t('Subject is required.')];
    if (!formData.value.message) errors.value.message = [t('Message is required.')];
};
</script>

<style scoped>
.form-label {
    @apply text-slate-700 dark:text-slate-300 text-sm font-medium mb-1.5 block;
}

.form-input {
    @apply w-full px-4 py-2.5 rounded-lg border focus:border-primary outline-none text-sm text-slate-800 dark:bg-slate-700 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-400 transition-all;
}

.social-icon {
    @apply w-9 h-9 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center hover:border-slate-400 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all;
}
</style>
