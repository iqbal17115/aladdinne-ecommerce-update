<template>
    <div>
        <!-- login modal-->
        <TransitionRoot as="template" :show="AuthStore.loginModal">
            <Dialog as="div" class="relative z-10" @close="AuthStore.hideLoginModal()">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                    enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity" />
                </TransitionChild>
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full justify-center p-4 text-center items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all my-8 md:my-0 w-full sm:max-w-lg">
                                <div class="bg-white dark:bg-slate-800 p-6 sm:p-9 relative" :class="master.langDirection==='rtl' ? 'text-right' : 'text-left'">
                                    <!-- close button -->
                                    <div class="w-9 h-9 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 transition rounded-full absolute top-4 flex justify-center items-center cursor-pointer" :class="master.langDirection==='rtl' ? 'left-3' : 'right-3'" @click="AuthStore.hideLoginModal()">
                                        <XMarkIcon class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                                    </div>
                                    <!-- end close button -->

                                    <!-- icon badge -->
                                    <div class="flex justify-center">
                                        <div class="relative w-20 h-20 rounded-full bg-primary-50 flex items-center justify-center">
                                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary to-primary-700 flex items-center justify-center shadow-lg">
                                                <ShoppingBagIcon class="w-7 h-7 text-white" />
                                            </div>
                                            <div class="absolute -bottom-0.5 right-0 w-6 h-6 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center shadow">
                                                <LockClosedIcon class="w-3.5 h-3.5 text-primary" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <div class="text-slate-900 dark:text-white text-2xl font-bold">
                                            {{ $t('Welcome back') }}! 👋
                                        </div>
                                        <div class="text-slate-500 text-sm mt-1">
                                            {{ $t('Please login to your account') }}
                                        </div>
                                    </div>

                                    <form @submit.prevent="loginFormSubmit()" class="mt-7">
                                        <!-- Phone Number -->
                                        <div>
                                            <label
                                                class="text-slate-700 dark:text-slate-300 text-sm font-medium mb-1.5 block">
                                                {{ $t('Email / Phone Number') }}
                                            </label>

                                            <div class="relative">
                                                <UserIcon class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                                <input type="text" v-model="loginFormData.phone"
                                                    :placeholder="$t('Enter email or phone number')"
                                                    class="text-base font-normal w-full py-3 pl-11 pr-3 placeholder:text-slate-400 rounded-xl border focus:border-primary outline-none"
                                                    :class="errors && errors?.phone ? 'border-red-500' : 'border-slate-200 dark:border-slate-700'">
                                            </div>
                                            <span v-if="errors && errors?.phone" class="text-red-500 text-sm">
                                                {{ errors?.phone[0] }}
                                            </span>
                                        </div>

                                        <!-- Password -->
                                        <div class="mt-4">
                                            <label
                                                class="text-slate-700 dark:text-slate-300 text-sm font-medium mb-1.5 block">
                                                {{ $t('Password') }}
                                            </label>

                                            <div class="relative">
                                                <LockClosedIcon class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                                <input :type="showLoginPassword ? 'text' : 'password'"
                                                    v-model="loginFormData.password" :placeholder="$t('Enter Password')"
                                                    class="text-base font-normal w-full py-3 pl-11 pr-11 placeholder:text-slate-400 rounded-xl border focus:border-primary outline-none"
                                                    :class="errors && errors?.password ? 'border-red-500' : 'border-slate-200 dark:border-slate-700'">
                                                <button @click="showLoginPassword = !showLoginPassword" type="button">
                                                    <EyeIcon v-if="showLoginPassword"
                                                        class="w-5 h-5 text-slate-400 absolute top-1/2 -translate-y-1/2" :class="master.langDirection==='rtl' ? 'left-3' : 'right-3'" />
                                                    <EyeSlashIcon v-else
                                                        class="w-5 h-5 text-slate-400 absolute top-1/2 -translate-y-1/2" :class="master.langDirection==='rtl' ? 'left-3' : 'right-3'" />
                                                </button>
                                            </div>
                                            <span v-if="errors && errors?.password" class="text-red-500 text-sm">
                                                {{ errors?.password[0] }}
                                            </span>
                                        </div>

                                        <!-- Forgot Password -->
                                        <div class="mt-2 text-right">
                                            <button type="button" class="text-right text-primary text-sm font-medium hover:underline"
                                                @click="showForgetPasswordDialog()">
                                                {{ $t('Forgot Password') }}?
                                            </button>
                                        </div>

                                        <!-- default credentials (local environment only) -->
                                        <div v-if="master.app_environment == 'local'"
                                            class="mt-4 p-3 rounded-xl border border-primary-200 bg-primary-50/60 dark:bg-slate-700/50 dark:border-slate-600 relative cursor-pointer"
                                            @click="fillDefaultCredentials()">
                                            <div class="text-slate-500 dark:text-slate-400 text-xs font-medium">
                                                {{ $t('Use default credentials') }}
                                            </div>
                                            <div class="text-slate-700 dark:text-slate-300 text-sm mt-1">
                                                {{ $t('Email') }}: {{ defaultCredentials.phone }}
                                            </div>
                                            <div class="text-slate-700 dark:text-slate-300 text-sm">
                                                {{ $t('Password') }}: {{ defaultCredentials.password }}
                                            </div>
                                            <button type="button" title="Use default credentials"
                                                class="absolute top-3 w-7 h-7 flex items-center justify-center rounded-lg hover:bg-primary-100 dark:hover:bg-slate-600 transition"
                                                :class="master.langDirection==='rtl' ? 'left-3' : 'right-3'"
                                                @click.stop="fillDefaultCredentials()">
                                                <ClipboardDocumentIcon class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                                            </button>
                                        </div>

                                        <!-- login button -->
                                        <button v-if="!isLoading" type="submit" class="px-6 py-3.5 bg-primary hover:bg-primary-700 transition mt-6 rounded-xl text-white text-base font-semibold w-full">
                                            {{ $t('Log in') }}
                                        </button>
                                        <button v-else type="button"
                                            class="px-6 py-3.5 bg-primary-200 mt-6 rounded-xl text-primary text-base font-semibold w-full flex justify-center items-center gap-1" disabled>
                                            {{ $t('Processing') }}
                                            <LoadingSpin />
                                        </button>
                                    </form>

                                    <!-- social login -->
                                    <div v-if="master.socialAuths?.facebook?.is_active || master.socialAuths?.google?.is_active || master.socialAuths?.apple?.is_active">
                                        <div class="mt-7 relative text-center">
                                            <div class="border-t border-slate-200 dark:border-slate-700"></div>
                                            <span
                                                class="absolute left-1/2 -translate-x-1/2 -top-2.5 bg-white dark:bg-slate-800 px-3 text-slate-400 text-xs">
                                                {{ $t('or continue with') }}
                                            </span>
                                        </div>

                                        <div class="mt-6 grid gap-3"
                                            :class="master.socialAuths.google?.is_active && master.socialAuths.facebook?.is_active ? 'grid-cols-2' : 'grid-cols-1'">
                                            <button v-if="master.socialAuths.google?.is_active" type="button"
                                                @click="googleLogin()"
                                                class="px-4 py-3 flex items-center justify-center gap-2 rounded-xl text-slate-700 dark:text-slate-300 text-base font-semibold outline-none border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:bg-slate-800 active:bg-gray-50 dark:bg-slate-800 transition">
                                                <GoogleIcon />
                                                <span class="leading-none m-0">{{ $t('Google') }}</span>
                                            </button>

                                            <button v-if="master.socialAuths.facebook?.is_active" type="button"
                                                @click="loginWithFacebook()"
                                                class="px-4 py-3 flex items-center justify-center gap-2 rounded-xl text-slate-700 dark:text-slate-300 text-base font-semibold outline-none border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:bg-slate-800 active:bg-gray-50 dark:bg-slate-800 transition">
                                                <font-awesome-icon :icon="faFacebook" class="text-blue-600 text-lg m-0" />
                                                <span class="leading-none m-0">{{ $t('Facebook') }}</span>
                                            </button>
                                        </div>

                                        <button v-if="master.socialAuths.apple?.is_active" type="button"
                                            @click="loginWithApple('apple')"
                                            class="px-4 py-3 mt-3 w-full flex items-center justify-center gap-2 rounded-xl text-white text-base font-semibold border border-black outline-none bg-black hover:bg-[#333] active:bg-black transition">
                                            <font-awesome-icon :icon="faApple" class="text-lg m-0" />
                                            <span class="leading-none m-0">
                                                {{ $t('Apple') }}
                                            </span>
                                        </button>
                                    </div>

                                    <!-- register button -->
                                    <div class="pt-1 mt-7 flex items-center justify-center gap-1 text-base">
                                        <div class="text-slate-600 dark:text-slate-400">
                                            {{ $t('Don’t have an account') }}?
                                        </div>
                                        <button class="text-primary font-semibold hover:underline" @click="showRegisterDialog">
                                            {{ $t('Sign Up') }}
                                        </button>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- forget password dialog -->
        <ForgetPasswordDialogModal :forgetPasswordDialog="forgetPasswordDialog" :countries="countries"
            @closeForget="forgetPasswordDialog = false" />

        <!-- registration dialog -->
        <RegistrationDialogModal :registerDialog="registerDialog" :countries="countries"
            @hideRegisterDialog="registerDialog = false" @showLogin="showLoginDialog" />

    </div>
</template>

<script setup>
import { faApple, faFacebook } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { ClipboardDocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { EyeIcon, EyeSlashIcon, LockClosedIcon, ShoppingBagIcon, UserIcon } from '@heroicons/vue/24/solid'
import { onMounted, ref } from 'vue'
import GoogleIcon from '../icons/Google.vue'
import ForgetPasswordDialogModal from './ForgetPasswordDialogModal.vue'
import LoadingSpin from './LoadingSpin.vue'
import RegistrationDialogModal from './RegistrationDialogModal.vue'
import ToastSuccessMessage from './ToastSuccessMessage.vue'

import { jwtDecode } from "jwt-decode"
import { useToast } from 'vue-toastification'
import { googleSdkLoaded } from 'vue3-google-login'
import { useAuth } from '../stores/AuthStore'
import { useBasketStore } from '../stores/BasketStore'
import { useMaster } from '../stores/MasterStore'
import localization from '../localization'

const toast = useToast();
const t = localization.i18n.global.t;
const basketStore = useBasketStore();
const master = useMaster();

const AuthStore = useAuth();

const showLoginPassword = ref(false);

const forgetPasswordDialog = ref(false);
const registerDialog = ref(false);
const isLoading = ref(false);

const loginFormData = ref({
    phone: '',
    password: ''
});

const defaultCredentials = {
    phone: 'user@readyecommerce.com',
    password: 'secret'
};

onMounted(async () => {
    fetchCountries();

    await loadFacebookSDK();
    initializeFB();
});

const fillDefaultCredentials = () => {
    loginFormData.value.phone = defaultCredentials.phone;
    loginFormData.value.password = defaultCredentials.password;
}

const showForgetPasswordDialog = () => {
    forgetPasswordDialog.value = true
    AuthStore.hideLoginModal();
}

const errors = ref({});

const content = {
    component: ToastSuccessMessage,
    props: {
        title: t('Login Successful'),
        message: t('You have successfully logged in.'),
    },
};

const countries = ref([]);

const fetchCountries = () => {
    axios.get('/countries').then((response) => {
        countries.value = response.data.data.countries
    })
}

const loginFormSubmit = () => {
    errors.value = {}
    isLoading.value = true
    axios.post('/login', loginFormData.value).then((response) => {
        AuthStore.setToken(response.data.data.access.token);
        AuthStore.setUser(response.data.data.user);
        AuthStore.hideLoginModal();
        basketStore.fetchCart()
        isLoading.value = false;
        toast(content, {
            type: "default",
            hideProgressBar: true,
            icon: false,
            position: "top-right",
            toastClassName: "vue-toastification-alert",
            timeout: 3000
        });
        AuthStore.fetchFavoriteProducts()
    }).catch((error) => {
        isLoading.value = false
        toast.error(error.response.data.message, {
           position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
        errors.value = error.response.data.errors
    })
}

const showRegisterDialog = () => {
    AuthStore.hideLoginModal();
    registerDialog.value = true
}

const showLoginDialog = () => {
    registerDialog.value = false
    AuthStore.showLoginModal();
}

const applyDefaultCredentials = () => {
    loginFormData.value.phone = defaultLoginCredentials.phone;
    loginFormData.value.password = defaultLoginCredentials.password;
}

/**
 * Initiates the Google login process.
 *
 * Uses the Google Accounts JavaScript library to initialize a client that
 * requests authorization to access the user's email and profile information.
 * Once authorized, the client receives an authorization code which is then
 * sent to the backend to exchange for an access token.
 */
const googleLogin = () => {
    googleSdkLoaded((google) => {
        google.accounts.oauth2.initCodeClient({
            client_id: master.socialAuths.google.client_id,
            scope: 'email profile openid',
            redirect_uri: 'postmessage',
            callback: (response) => {
                if (response.code) {
                    sendCodeToBackend(response.code, 'google');
                }
            },
        }).requestCode();
    });
};

/**
 * Loads the Facebook SDK by appending a script tag to the document body.
 * @returns {Promise<void>}
 */
const loadFacebookSDK = () => {
    return new Promise((resolve) => {
        if (window.FB) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://connect.facebook.net/en_US/sdk.js';
        script.async = true;
        script.defer = true;
        script.onload = () => resolve();
        document.body.appendChild(script);
    });
};

/**
 * Initializes the Facebook SDK.
 * This function is called after the Facebook SDK has been loaded.
 * @see loadFacebookSDK
 */
const initializeFB = () => {
    window.fbAsyncInit = () => {
        FB.init({
            appId: master.socialAuths?.facebook?.client_id, // Replace with your Facebook App ID
            autoLogAppEvents: true,
            xfbml: true,
            version: 'v20.0', // Use the latest Graph API version
        });
    };
};

/**
 * Logs the user in with their Facebook account.
 * @returns {void}
 * @private
 */
const loginWithFacebook = () => {
    FB.login((response) => {
        if (response.authResponse) {
            FB.api('/me', { fields: 'name,email' }, (userInfo) => {
                console.log('User Info:', userInfo);
                // Handle login success here, such as sending info to your backend
                sendCodeToBackend(response.authResponse?.accessToken, 'facebook', userInfo);
            });
        } else {
            console.error('User cancelled login or did not fully authorize.');
        }
    },
        { scope: 'public_profile,email' }
    );
};

/**
 * Loads the Apple ID SDK by appending a script tag to the document body.
 * @returns {Promise<void>}
 */
const loadAppleSDK = () => {
    return new Promise((resolve, reject) => {
        if (window.AppleID) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load Apple ID SDK'));
        document.body.appendChild(script);
    });
};

/**
 * Signs in with Apple using the Apple ID SDK.
 *
 * @returns {Promise<void>}
 */
const loginWithApple = async () => {
    try {
        await loadAppleSDK();

        window.AppleID.auth.init({
            clientId: master.socialAuths.apple?.client_id,
            scope: 'name email',
            redirectURI: master.socialAuths.apple.redirect_url,
            state: '123456',
            usePopup: true,
        });

        // Sign in with Apple
        const data = await window.AppleID.auth.signIn();
        const { authorization: { id_token: token, code } } = data;

        if (token && code) {
            const decoded = jwtDecode(token);
            sendCodeToBackend('1122', 'apple', decoded);
        } else {
            console.error('Token or code is missing');
        }
    } catch (error) {
        console.error('Error during sign in:', error);
    }
};

/**
 * Sends the authorization code to the backend to get an access token.
 *
 * @param {String} code - The authorization code
 * @param {String} provider - The provider ('google' or 'apple'), defaults to 'google'
 * @param {Object} data - Additional data to send with the request, defaults to empty object
 *
 * @returns {Promise<void>}
 */
async function sendCodeToBackend(code, provider = 'google', data = {}) {
    try {
        const response = await axios.post('/auth/' + provider + '/token', {
            code,
            data,
        });

        if (response.data?.data?.user) {
            AuthStore.setToken(response.data.data.access.token);
            AuthStore.setUser(response.data.data.user);
            AuthStore.hideLoginModal();
            toast.success(t('Login Successful'), {
               position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
            });
            basketStore.fetchCart();
        }
    } catch (error) {
        toast.error(error.response.data.message, {
           position: master.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
        });
    }
}

</script>
