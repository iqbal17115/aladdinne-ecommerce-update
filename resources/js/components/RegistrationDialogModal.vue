<template>
    <div>
        <!-- Registration Dialog Modal -->
        <TransitionRoot as="template" :show="registerDialog">
            <Dialog
                as="div"
                class="relative z-10"
                @close="closeRegisterDialog()"
            >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"
                    />
                </TransitionChild>
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div
                        class="flex min-h-full items-center justify-center p-4 text-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all my-8 md:my-0 w-full sm:max-w-lg md:max-w-xl lg:max-w-2xl"
                            >
                                <div
                                    class="bg-white dark:bg-slate-800 p-6 sm:p-9 relative"
                                    :class="
                                        master.langDirection === 'rtl'
                                            ? 'text-right'
                                            : 'text-left'
                                    "
                                >
                                    <!-- close button -->
                                    <div
                                        class="w-9 h-9 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition rounded-full absolute top-4 flex justify-center items-center cursor-pointer"
                                        :class="
                                            master.langDirection === 'rtl'
                                                ? 'left-4'
                                                : 'right-4'
                                        "
                                        @click="closeRegisterDialog()"
                                    >
                                        <XMarkIcon
                                            class="w-5 h-5 text-slate-600 dark:text-slate-300"
                                        />
                                    </div>
                                    <!-- end close button -->

                                    <!-- icon badge -->
                                    <div class="flex justify-center">
                                        <div class="relative w-20 h-20 flex items-center justify-center">
                                            <span class="absolute -left-3 top-2 w-2 h-2 rounded-full bg-primary-200"></span>
                                            <span class="absolute -right-2 bottom-3 w-3 h-3 rounded-full border-2 border-primary-200"></span>
                                            <div class="relative w-20 h-20 rounded-full bg-primary-50 flex items-center justify-center">
                                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary to-primary-700 flex items-center justify-center shadow-lg">
                                                    <UserIcon class="w-7 h-7 text-white" />
                                                </div>
                                                <div class="absolute -bottom-0.5 right-0 w-6 h-6 rounded-full bg-white dark:bg-slate-700 flex items-center justify-center shadow">
                                                    <PlusIcon class="w-3.5 h-3.5 text-primary" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <div class="text-slate-900 dark:text-white text-2xl font-bold">
                                            {{ $t("Create your account") }}
                                        </div>
                                        <div class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                                            {{ $t("Join us and start shopping") }}
                                        </div>
                                    </div>

                                    <form
                                        @submit.prevent="registerFormSubmit()"
                                        class="mt-7"
                                    >
                                        <!-- Full Name -->
                                        <div>
                                            <label
                                                class="text-slate-700 dark:text-slate-300 text-sm font-medium mb-1.5 block"
                                            >
                                                {{ $t("Full Name") }}
                                            </label>

                                            <div class="relative">
                                                <UserIcon class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                                <input
                                                    type="text"
                                                    v-model="registerFormData.name"
                                                    :placeholder="
                                                        $t('Enter full name')
                                                    "
                                                    class="text-base font-normal w-full py-3 pl-11 pr-3 placeholder:text-slate-400 rounded-xl border focus:border-primary outline-none bg-white dark:bg-slate-700 dark:text-white dark:placeholder:text-slate-500"
                                                    :class="
                                                        registerErrors?.name
                                                            ? 'border-red-500'
                                                            : 'border-slate-200 dark:border-slate-600'
                                                    "
                                                />
                                            </div>
                                            <span
                                                v-if="
                                                    registerErrors &&
                                                    registerErrors?.name
                                                "
                                                class="text-red-500 text-sm"
                                            >
                                                {{ registerErrors?.name[0] }}
                                            </span>
                                        </div>

                                        <!-- Country field commented out -->
                                        <!--
                                        <div class="mt-4">
                                            <label
                                                for="name"
                                                class="text-slate-700 dark:text-slate-300 text-sm font-medium mb-1.5 block"
                                            >
                                                {{ $t("Country") }}
                                            </label>
                                            <div class="relative">
                                                <GlobeAltIcon class="w-5 h-5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2 z-10 pointer-events-none" />
                                                <v-select
                                                    :options="countries"
                                                    label="name"
                                                    :reduce="(country) => country.name"
                                                    v-model="registerFormData.country"
                                                    :placeholder="$t('Select Country')"
                                                    class="register-country-select"
                                                    :class="registerErrors?.country ? 'border rounded-xl border-red-500' : 'border-slate-200'"
                                                    aria-autocomplete="none"
                                                    :dir="master.langDirection || 'ltr'"
                                                />
                                            </div>
                                            <span v-if="registerErrors && registerErrors?.country" class="text-red-500 text-sm">
                                                {{ registerErrors?.country[0] }}
                                            </span>
                                        </div>
                                        -->

                                        <!-- Phone Number with intlTelInput -->
                                        <div class="mt-4">
                                            <label
                                                class="text-slate-700 dark:text-slate-300 text-sm font-medium mb-1.5 block"
                                            >
                                                {{ $t("Phone Number") }}
                                            </label>

                                            <div
                                                class="iti-wrapper"
                                                :class="registerErrors?.phone ? 'border-red-500' : 'border-slate-200'"
                                            >
                                                <input
                                                    ref="phoneInputRef"
                                                    type="tel"
                                                    :placeholder="$t('Enter phone number')"
                                                    class="text-base font-normal w-full placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:ring-0 dark:text-white dark:bg-transparent"
                                                    :minlength="master.phoneMinLength"
                                                    :maxlength="master.phoneMaxLength"
                                                />
                                            </div>
                                            <span
                                                v-if="registerErrors && registerErrors?.phone"
                                                class="text-red-500 text-sm"
                                            >
                                                {{ registerErrors?.phone[0] }}
                                            </span>
                                        </div>

                                        <div class="mt-4">
                                            <label
                                                class="text-slate-700 dark:text-slate-300 text-sm font-medium mb-1.5 block"
                                            >
                                                {{ $t("Email Address") }}
                                            </label>
                                            <div class="relative">
                                                <EnvelopeIcon class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                                <input
                                                    type="email"
                                                    v-model="registerFormData.email"
                                                    :placeholder="
                                                        $t('Enter email address')
                                                    "
                                                    class="text-base font-normal w-full py-3 pl-11 pr-3 placeholder:text-slate-400 rounded-xl border focus:border-primary outline-none bg-white dark:bg-slate-700 dark:text-white dark:placeholder:text-slate-500"
                                                    :class="
                                                        registerErrors?.email
                                                            ? 'border-red-500'
                                                            : 'border-slate-200 dark:border-slate-600'
                                                    "
                                                />
                                            </div>
                                            <span
                                                v-if="
                                                    registerErrors &&
                                                    registerErrors?.email
                                                "
                                                class="text-red-500 text-sm"
                                            >
                                                {{ registerErrors?.email[0] }}
                                            </span>
                                        </div>

                                        <!-- Password -->
                                        <div class="mt-4">
                                            <label
                                                class="text-slate-700 dark:text-slate-300 text-sm font-medium mb-1.5 block"
                                            >
                                                {{ $t("Create Password") }}
                                            </label>
                                            <div class="relative">
                                                <LockClosedIcon class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                                <input
                                                    :type="
                                                        showRegisterPassword
                                                            ? 'text'
                                                            : 'password'
                                                    "
                                                    v-model="
                                                        registerFormData.password
                                                    "
                                                    :placeholder="
                                                        $t('Enter Password')
                                                    "
                                                    class="text-base font-normal w-full py-3 pl-11 pr-11 placeholder:text-slate-400 rounded-xl border focus:border-primary outline-none bg-white dark:bg-slate-700 dark:text-white dark:placeholder:text-slate-500"
                                                    :class="
                                                        registerErrors?.password
                                                            ? 'border-red-500'
                                                            : 'border-slate-200 dark:border-slate-600'
                                                    "
                                                />
                                                <button
                                                    type="button"
                                                    @click="
                                                        showRegisterPassword =
                                                            !showRegisterPassword
                                                    "
                                                >
                                                    <EyeIcon
                                                        v-if="
                                                            showRegisterPassword
                                                        "
                                                        class="w-5 h-5 text-slate-400 absolute top-1/2 -translate-y-1/2"
                                                        :class="
                                                            master.langDirection ===
                                                            'rtl'
                                                                ? 'left-3'
                                                                : 'right-3'
                                                        "
                                                    />
                                                    <EyeSlashIcon
                                                        v-else
                                                        class="w-5 h-5 text-slate-400 absolute top-1/2 -translate-y-1/2"
                                                        :class="
                                                            master.langDirection ===
                                                            'rtl'
                                                                ? 'left-3'
                                                                : 'right-3'
                                                        "
                                                    />
                                                </button>
                                            </div>
                                            <span
                                                v-if="
                                                    registerErrors &&
                                                    registerErrors?.password
                                                "
                                                class="text-red-500 text-sm"
                                            >
                                                {{
                                                    registerErrors?.password[0]
                                                }}
                                            </span>
                                        </div>

                                        <!-- Terms -->
                                        <label
                                            class="mt-5 text-slate-600 dark:text-slate-400 text-sm font-normal flex items-start gap-2 flex-wrap cursor-pointer"
                                        >
                                            <input
                                                type="checkbox"
                                                v-model="agreedToTerms"
                                                class="mt-0.5 w-4 h-4 rounded accent-primary shrink-0"
                                            />
                                            <span>
                                                {{
                                                    $t(
                                                        "By clicking the ‘Sign up’ button, you agree to our"
                                                    )
                                                }}
                                                <button
                                                    type="button"
                                                    class="text-primary font-medium hover:underline"
                                                    @click="showTerms"
                                                >
                                                    {{ $t("Terms & Conditions") }}
                                                </button>
                                                {{ $t("and") }}
                                                <button
                                                    type="button"
                                                    class="text-primary font-medium hover:underline"
                                                    @click="showPrivacy"
                                                >
                                                    {{ $t("Privacy Policy") }}
                                                </button>
                                            </span>
                                        </label>
                                        <span
                                            v-if="termsError"
                                            class="text-red-500 text-sm block mt-1"
                                        >
                                            {{ $t("Please accept the Terms & Conditions and Privacy Policy") }}
                                        </span>

                                        <!-- login button -->
                                        <button
                                            v-if="!isLoading"
                                            type="submit"
                                            class="px-6 py-3.5 bg-primary hover:bg-primary-700 transition mt-5 rounded-xl text-white text-base font-semibold w-full"
                                        >
                                            {{ $t("Sign up") }}
                                        </button>
                                        <button
                                            v-else
                                            type="button"
                                            class="px-6 py-3.5 bg-primary-200 mt-5 rounded-xl text-primary text-base font-semibold w-full flex justify-center items-center gap-1"
                                        >
                                            {{ $t("Processing") }}
                                            <LoadingSpin />
                                        </button>

                                        <div
                                            class="pt-1 mt-5 flex items-center justify-center gap-1 text-base"
                                        >
                                            <div
                                                class="text-slate-600 dark:text-slate-400"
                                            >
                                                {{
                                                    $t(
                                                        "Already have an account"
                                                    )
                                                }}?
                                            </div>
                                            <button
                                                type="button"
                                                class="text-primary font-semibold hover:underline"
                                                @click="showLoginDialog"
                                            >
                                                {{ $t("Log in") }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
        <!-- end Registration dialog -->

        <!-- OTP Dialog Modal -->
        <TransitionRoot as="template" :show="OTPDialog">
            <Dialog as="div" class="relative z-10">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity"
                    />
                </TransitionChild>
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div
                        class="flex min-h-full items-center justify-center p-4 text-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all my-8 md:my-0 w-full sm:max-w-lg md:max-w-xl"
                            >
                                <div
                                    class="bg-white dark:bg-slate-800 p-5 sm:p-8 relative"
                                    :class="
                                        master.langDirection === 'rtl'
                                            ? 'text-right'
                                            : 'text-left'
                                    "
                                >
                                    <!-- close button -->
                                    <div
                                        class="w-9 h-9 bg-slate-100 dark:bg-slate-700 rounded-[32px] absolute top-4 flex justify-center items-center cursor-pointer"
                                        :class="
                                            master.langDirection === 'rtl'
                                                ? 'left-4'
                                                : 'right-4'
                                        "
                                        @click="OTPDialog = false"
                                    >
                                        <XMarkIcon
                                            class="w-6 h-6 text-slate-600 dark:text-slate-300"
                                        />
                                    </div>
                                    <!-- end close button -->

                                    <div
                                        class="text-slate-950 dark:text-white text-lg sm:text-2xl font-medium leading-loose"
                                    >
                                        {{ $t("Enter OTP") }}
                                    </div>

                                    <div v-if="!isSendOtpProcess">
                                        <div
                                            class="text-slate-950 dark:text-slate-300 mt-3 text-lg font-normal leading-7 tracking-tight"
                                        >
                                            {{ sendMessage }} <br />
                                            {{ sendOtpEmailOrPhone }}
                                        </div>

                                        <div class="flex gap-3 mt-6">
                                            <input
                                                v-for="(input, index) in inputs"
                                                :key="index"
                                                :id="'input' + index"
                                                type="text"
                                                v-model="input.value"
                                                @input="handleInput(index)"
                                                @keydown="
                                                    handleKeyDown(index, $event)
                                                "
                                                placeholder="-"
                                                class="text-base font-normal w-10 grow text-center p-3 placeholder:text-slate-400 rounded-lg border border-slate-200 dark:border-slate-600 focus:border-primary outline-none bg-white dark:bg-slate-700 dark:text-white"
                                                maxlength="1"
                                            />
                                        </div>

                                        <!-- Confirm button -->
                                        <button
                                            v-if="!isLoadingVerifyOTP"
                                            class="px-6 py-4 bg-primary mt-6 rounded-[10px] text-white text-base font-medium w-full"
                                            @click="verifyOTP"
                                        >
                                            {{ $t("Confirm OTP") }}
                                        </button>
                                        <button
                                            v-else
                                            type="button"
                                            class="px-6 py-4 bg-primary-200 mt-6 rounded-[10px] text-primary text-base font-medium w-full flex items-center justify-center gap-2"
                                            disabled
                                        >
                                            {{ $t("Processing") }}
                                            <LoadingSpin />
                                        </button>

                                        <div
                                            v-if="time > 0"
                                            class="px-4 py-2 mt-6 flex items-center justify-center gap-2"
                                        >
                                            <div
                                                class="text-slate-900 dark:text-slate-300 text-base font-normal leading-normal"
                                            >
                                                {{ $t("Resend code in") }}
                                            </div>

                                            <div
                                                class="text-primary text-base font-normal leading-normal"
                                            >
                                                00:{{ time }} {{ $t("sec") }}
                                            </div>
                                        </div>
                                        <!-- Resend OTP -->
                                        <div
                                            v-else
                                            class="px-4 py-2 mt-6 flex items-center justify-center gap-2"
                                        >
                                            <button
                                                v-if="!isLoadingOTP"
                                                type="button"
                                                class="text-primary text-base font-normal leading-normal"
                                                @click="
                                                    sendOTP(
                                                        sendOTPNumber,
                                                        phoneCode
                                                    )
                                                "
                                            >
                                                {{ $t("Resend OTP") }}
                                            </button>
                                            <button
                                                v-else
                                                type="button"
                                                class="rounded-[10px] text-primary text-base font-medium w-full flex justify-center items-center gap-1"
                                            >
                                                {{ $t("Sending") }}
                                                <LoadingSpin />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- loading -->
                                    <div v-else class="mt-6">
                                        <SkeletonLoader
                                            class="w-8/12 h-3 rounded-lg"
                                        />
                                        <SkeletonLoader
                                            class="w-11/12 h-3 rounded-lg mt-2"
                                        />
                                        <div class="flex gap-3 mt-6">
                                            <SkeletonLoader
                                                class="w-10/12 h-12 rounded-lg"
                                            />
                                            <SkeletonLoader
                                                class="w-10/12 h-12 rounded-lg"
                                            />
                                            <SkeletonLoader
                                                class="w-10/12 h-12 rounded-lg"
                                            />
                                            <SkeletonLoader
                                                class="w-10/12 h-12 rounded-lg"
                                            />
                                        </div>
                                        <SkeletonLoader
                                            class="w-full h-11 rounded-lg mt-6"
                                        />
                                        <SkeletonLoader
                                            class="w-52 mx-auto h-6 rounded-lg mt-5"
                                        />
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
        <!-- end OTP dialog -->
    </div>
</template>

<script setup>
import {
    Dialog,
    DialogPanel,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";
import {
    EnvelopeIcon,
    EyeIcon,
    EyeSlashIcon,
    GlobeAltIcon,
    LockClosedIcon,
    PlusIcon,
    UserIcon,
} from "@heroicons/vue/24/solid";
import { nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import intlTelInput from "intl-tel-input";
import "intl-tel-input/dist/css/intlTelInput.css";
import LoadingSpin from "./LoadingSpin.vue";
import SkeletonLoader from "./SkeletonLoader.vue";
import ToastSuccessMessage from "./ToastSuccessMessage.vue";

import { useToast } from "vue-toastification";
import { useAuth } from "../stores/AuthStore";
import { useMaster } from "../stores/MasterStore";

import axios from "axios";
import { useRouter } from "vue-router";
import localization from "../localization";
const router = useRouter();

const toast = useToast();
const t = localization.i18n.global.t;
const master = useMaster();

const AuthStore = useAuth();
const isLoading = ref(false);

const emits = defineEmits(["hideRegisterDialog", "showLogin"]);

const props = defineProps({
    registerDialog: {
        type: Boolean,
        default: false,
    },
    countries: {
        type: Array,
        default: () => [],
    },
});

const OTPDialog = ref(false);
const showRegisterPassword = ref(false);
const agreedToTerms = ref(false);
const termsError = ref(false);
const phoneInputRef = ref(null);
let itiInstance = null;

const initIti = () => {
    if (!phoneInputRef.value || itiInstance) return;
    itiInstance = intlTelInput(phoneInputRef.value, {
        initialCountry: "bd",
        loadUtils: () => import("intl-tel-input/utils"),
    });

    // set phone_code for the default country immediately, no async lookup needed
    const initial = itiInstance.getSelectedCountry();
    if (initial?.dialCode) registerFormData.value.phone_code = initial.dialCode;

    phoneInputRef.value.addEventListener("input", () => {
        const raw = phoneInputRef.value.value.replace(/[^\d]/g, "");
        registerFormData.value.phone = raw;
    });

    phoneInputRef.value.addEventListener("countrychange", () => {
        const countryData = itiInstance.getSelectedCountry();
        registerFormData.value.phone_code = countryData.dialCode;
    });
};

watch(
    () => props.registerDialog,
    (val) => {
        if (val) {
            nextTick(() => initIti());
        } else if (itiInstance) {
            itiInstance.destroy();
            itiInstance = null;
        }
    }
);

onUnmounted(() => {
    if (itiInstance) {
        itiInstance.destroy();
        itiInstance = null;
    }
});

const showLoginDialog = () => {
    emits("showLogin");
};

const registerFormData = ref({
    name: null,
    phone: null,
    email: null,
    password: null,
    country: null,
    phone_code: null,
});


const registerMessage = {
    component: ToastSuccessMessage,
    props: {
        title: t("Register Successful"),
        message: t("You have successfully registered."),
    },
};

const registerErrors = ref({});

const registerFormSubmit = () => {
    registerErrors.value = {};
    termsError.value = false;

    if (!agreedToTerms.value) {
        termsError.value = true;
        return;
    }

    isLoading.value = true;

    if (itiInstance) {
        const countryData = itiInstance.getSelectedCountry();
        registerFormData.value.phone_code = countryData?.dialCode || null;
        registerFormData.value.phone = phoneInputRef.value?.value.replace(/[^\d]/g, "") || null;
    }

    axios
        .post("/registration", registerFormData.value)
        .then((response) => {
            AuthStore.setToken(response.data.data.access.token);
            AuthStore.setUser(response.data.data.user);

            toast(registerMessage, {
                type: "default",
                hideProgressBar: true,
                icon: false,
                position: "top-right",
                toastClassName: "vue-toastification-alert",
                timeout: 3000,
            });

            closeRegisterDialog();

            const emailOrPhone =
                registerFormData.value.phone ?? registerFormData.value.email;
            if (master.register_otp_verify) {
                OTPDialog.value = true;
                isSendOtpProcess.value = true;
                sendOTP(emailOrPhone, registerFormData.value.phone_code);
            }

            registerFormData.value.name = null;
            registerFormData.value.password = null;
            registerFormData.value.country = null;
            registerFormData.value.phone_code = null;
            registerFormData.value.phone = null;
            registerFormData.value.email = null;
            agreedToTerms.value = false;
            if (phoneInputRef.value) phoneInputRef.value.value = "";
        })
        .catch((error) => {
            if (error.response?.data?.errors) {
                registerErrors.value = error.response.data.errors;
            } else {
                toast.error(
                    error.response?.data?.message ||
                        t("Something went wrong. Please try again."),
                    {
                        position:
                            master.langDirection === "rtl"
                                ? "bottom-right"
                                : "bottom-left",
                    }
                );
            }
        })
        .finally(() => {
            isLoading.value = false;
        });
};

const sendOTPNumber = ref("");
const phoneCode = ref(null);
const sendOtpEmailOrPhone = ref("");
const sendMessage = ref("");
const isLoadingOTP = ref(false);
const isSendOtpProcess = ref(false);

const sendOTP = (phoneNumber = "", phone_code = null) => {
    if (phoneNumber) {
        sendOTPNumber.value = phoneNumber;
        phoneCode.value = phone_code;
    }
    isLoadingOTP.value = true;
    axios
        .post("/send-otp", {
            phone: sendOTPNumber.value,
            phone_code: phoneCode.value,
        })
        .then((response) => {
            isLoadingOTP.value = false;
            isSendOtpProcess.value = false;
            OTPDialog.value = true;
            time.value = 60;
            onTimer();

            toast.success(response.data.message, {
                position:
                    master.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });

            sendMessage.value = response.data.message;
            sendOtpEmailOrPhone.value = response.data.data.email_or_phone;
        })
        .catch((error) => {
            isLoadingOTP.value = false;
            isSendOtpProcess.value = false;
            toast.error(error.response.data.message, {
                position:
                    master.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });
        });
};

const isLoadingVerifyOTP = ref(false);
const verifyOTP = () => {
    isLoadingVerifyOTP.value = true;
    const otp = inputs.value.map((input) => input.value).join("");
    axios
        .post("/verify-otp", {
            phone: sendOTPNumber.value,
            otp: otp,
        })
        .then((response) => {
            isLoadingVerifyOTP.value = false;
            toast.success(response.data.message, {
                position:
                    master.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });

            OTPDialog.value = false;

            fetchUserDetails();
        })
        .catch((error) => {
            isLoadingVerifyOTP.value = false;
            toast.error(error.response.data.message, {
                position:
                    master.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });
        });
};

const fetchUserDetails = () => {
    axios
        .get("/profile", {
            headers: {
                Authorization: AuthStore.token,
                "Accept-Language": master.locale || "en",
            },
        })
        .then((response) => {
            AuthStore.setUser(response.data.data.user);
        })
        .catch((error) => {
            console.log(error);
        });
};

const closeRegisterDialog = () => {
    emits("hideRegisterDialog");
};

const showTerms = () => {
    closeRegisterDialog();
    router.push({ name: "terms-and-conditions" });
};

const showPrivacy = () => {
    closeRegisterDialog();
    router.push({ name: "privacy-policy" });
};

const time = ref(60);

const onTimer = () => {
    if (time.value > 0) {
        setTimeout(() => {
            time.value -= 1;
            onTimer();
        }, 1000);
    }
};

const inputs = ref([
    { value: "" },
    { value: "" },
    { value: "" },
    { value: "" },
]);

const handleInput = (index) => {
    let nextIndex = index + 1;
    if (nextIndex < inputs.value.length && inputs.value[index].value != "") {
        nextTick(() => {
            const inputElement = document.getElementById("input" + nextIndex);
            if (inputElement) {
                inputElement.focus();
            }
        });
    }
};

const handleKeyDown = (index, event) => {
    if (
        event.key === "Backspace" &&
        index > 0 &&
        inputs.value[index].value === ""
    ) {
        let previousIndex = index - 1;
        if (previousIndex >= 0) {
            nextTick(() => {
                const inputElement = document.getElementById(
                    "input" + previousIndex
                );
                if (inputElement) {
                    inputElement.focus();
                }
            });
        }
    }
};
</script>

<style scoped>
.register-country-select :deep(.vs__dropdown-toggle) {
    border-radius: 0.75rem;
    min-height: 48px;
}
.register-country-select :deep(.vs__selected-options) {
    padding-left: 1.75rem;
    flex-wrap: nowrap;
}
.register-country-select :deep(.vs__search),
.register-country-select :deep(.vs__selected) {
    margin: 0;
    padding: 0;
}

/* intlTelInput overrides */
.iti-wrapper :deep(.iti) {
    width: 100%;
    border: 1px solid;
    border-color: inherit;
    border-radius: 0.75rem;
    overflow: visible;
}
.iti-wrapper.border-slate-200 :deep(.iti) {
    border-color: #e2e8f0;
}
.iti-wrapper.border-red-500 :deep(.iti) {
    border-color: #ef4444;
}
.iti-wrapper :deep(.iti__tel-input) {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    padding-right: 0.75rem;
    font-size: 1rem;
    border-radius: 0 0.75rem 0.75rem 0;
}
.iti-wrapper :deep(.iti__flag-container) {
    border-right: 1px solid #e2e8f0;
}
.iti-wrapper :deep(.iti__dropdown-content) {
    border-radius: 0.5rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

:global(.dark) .iti-wrapper :deep(.iti) {
    border-color: #475569;
    background-color: #334155;
}
:global(.dark) .iti-wrapper.border-slate-200 :deep(.iti) {
    border-color: #475569;
}
:global(.dark) .iti-wrapper :deep(.iti__flag-container) {
    border-right-color: #475569;
}
:global(.dark) .iti-wrapper :deep(.iti__dropdown-content) {
    background-color: #1e293b;
    border-color: #475569;
    color: #f1f5f9;
}
:global(.dark) .iti-wrapper :deep(.iti__country:hover),
:global(.dark) .iti-wrapper :deep(.iti__country--highlight) {
    background-color: #334155;
}
:global(.dark) .iti-wrapper :deep(.iti__search-input) {
    background-color: #334155;
    color: #f1f5f9;
    border-color: #475569;
}

/* Dropdown list items — rendered outside wrapper scope */
:global(.dark) .iti__dropdown-content {
    background-color: #1e293b;
    border-color: #475569;
    color: #f1f5f9;
}
:global(.dark) .iti__country,
:global(.dark) .iti__list-item {
    background-color: #1e293b;
    color: #f1f5f9;
}
:global(.dark) .iti__country:hover,
:global(.dark) .iti__list-item:hover,
:global(.dark) .iti__country--highlight {
    background-color: #334155;
}
:global(.dark) .iti__country-name,
:global(.dark) .iti__dial-code {
    color: #f1f5f9;
}
:global(.dark) .iti__divider {
    border-bottom-color: #475569;
}
:global(.dark) .iti__search-input {
    background-color: #334155;
    color: #f1f5f9;
    border-color: #475569;
}
</style>
