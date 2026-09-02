<template>
    <div class="p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 mt-3">
        <form @submit.prevent="addressFormSubmit()">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="form-label mb-2">
                        {{ $t("Name") }}
                        <small class="text-red-500">*</small>
                    </label>
                    <input
                        type="text"
                        id="name"
                        v-model="formData.name"
                        :placeholder="$t('Enter name')"
                        class="form-input"
                        :class="
                            errors && errors?.name
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-700'
                        "
                    />
                    <span
                        v-if="errors && errors?.name"
                        class="text-red-500 text-sm"
                        >{{ errors?.name[0] }}</span
                    >
                </div>
                <div>
                    <label for="Phone" class="form-label mb-2">
                        {{ $t("Phone") }}
                        <small class="text-red-500">*</small>
                    </label>
                    <input
                        type="text"
                        id="Phone"
                        :placeholder="$t('Enter phone')"
                        class="form-input"
                        v-model="formData.phone"
                        :class="
                            errors && errors?.phone
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-700'
                        "
                        maxlength="11"
                        @input="
                            formData.phone = formData.phone.replace(
                                /[^\d]/g,
                                ''
                            )
                        "
                    />
                    <span
                        v-if="errors && errors?.phone"
                        class="text-red-500 text-sm"
                        >{{ errors?.phone[0] }}</span
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="email" class="form-label mb-2">
                        {{ $t("Email") }}
                        <span class="text-slate-400 text-sm">({{ $t('Optional') }})</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        v-model="formData.email"
                        :placeholder="$t('Enter email')"
                        class="form-input"
                        :class="
                            errors && errors?.email
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-700'
                        "
                    />
                    <span
                        v-if="errors && errors?.email"
                        class="text-red-500 text-sm"
                        >{{ errors?.email[0] }}</span
                    >
                </div>
            </div>

            <div class="mt-6">
                <MapDisplay
                    :enableSetLocation="true"
                    :hasOldValue="true"
                    :latitude="Number(formData.latitude)"
                    :longitude="Number(formData.longitude)"
                    @location-updated="updateLocation"
                />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="Area" class="form-label mb-2">
                        {{ $t("Area") }}</label
                    >
                    <select
                        id="Area"
                        v-model="formData.area_id"
                        @change="onAreaChange"
                        :class="[
                            'form-input',
                            errors && errors?.area
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-700',
                        ]"
                    >
                        <!-- Placeholder option (disabled so user must pick another option) -->
                        <option value="" disabled selected>
                            {{ $t("Enter Area") }}
                        </option>

                        <!-- Options -->
                        <option v-for="area in areaOptions" :value="area.id">
                            {{ area.name }}
                        </option>
                    </select>
                    <span
                        v-if="errors && errors?.area"
                        class="text-red-500 text-sm"
                        >{{ errors?.area[0] }}</span
                    >
                </div>

                <div>
                    <label for="Thana" class="form-label mb-2">
                        {{ $t("Thana") }}
                        <small class="text-red-500">*</small>
                    </label>
                    <select
                        id="Thana"
                        v-model="formData.thana_id"
                        :disabled="!formData.area_id"
                        :class="[
                            'form-input',
                            errors && errors?.thana_id
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-700',
                        ]"
                    >
                        <option value="" disabled selected>
                            {{ $t("Select Thana") }}
                        </option>
                        <option v-for="thana in thanaOptions" :key="thana.id" :value="thana.id">
                            {{ thana.name }}
                        </option>
                    </select>
                    <span
                        v-if="errors && errors?.thana_id"
                        class="text-red-500 text-sm"
                        >{{ errors?.thana_id[0] }}</span
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="address" class="form-label mb-2">
                        {{ $t("Address Line ") }}
                        <small class="text-red-500">*</small>
                    </label>
                    <input
                        type="text"
                        id="address"
                        v-model="formData.address_line"
                        :placeholder="$t('Enter address ')"
                        class="form-input"
                        :class="
                            errors && errors?.address_line
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-700'
                        "
                    />
                    <span
                        v-if="errors && errors?.address_line"
                        class="text-red-500 text-sm"
                        >{{ errors?.address_line[0] }}</span
                    >
                </div>
            </div>

            <div class="mt-4">
                <div
                    class="text-slate-950 dark:text-white text-base font-medium leading-normal"
                >
                    {{ $t("Address Tag") }}
                </div>

                <div
                    class="flex justify-between items-center gap-2 mt-2 flex-wrap"
                >
                    <div class="flex items-center gap-2">
                        <label
                            for="home"
                            class="px-3 py-2 bg-white dark:bg-slate-800 rounded-[42px] border flex gap-2 items-center text-slate-600 dark:text-slate-400 text-base font-normal leading-normal cursor-pointer has-[:checked]:border-primary has-[:checked]:text-primary"
                        >
                            <input
                                type="radio"
                                id="home"
                                v-model="formData.address_type"
                                name="tag"
                                value="home"
                                class="radio-btn"
                                :checked="formData.address_type === 'home'"
                            />
                            <span class="text-base font-normal">{{
                                $t("HOME")
                            }}</span>
                        </label>

                        <label
                            for="office"
                            class="px-3 py-2 bg-white dark:bg-slate-800 rounded-[42px] border flex gap-2 items-center text-slate-600 dark:text-slate-400 text-base font-normal leading-normal cursor-pointer has-[:checked]:border-primary has-[:checked]:text-primary"
                        >
                            <input
                                type="radio"
                                id="office"
                                v-model="formData.address_type"
                                name="tag"
                                value="office"
                                class="radio-btn"
                                :checked="formData.address_type === 'office'"
                            />
                            <span class="text-base font-normal">{{
                                $t("OFFICE")
                            }}</span>
                        </label>

                        <label
                            for="other"
                            class="px-3 py-2 bg-white dark:bg-slate-800 rounded-[42px] border flex gap-2 items-center text-slate-600 dark:text-slate-400 text-base font-normal leading-normal cursor-pointer has-[:checked]:border-primary has-[:checked]:text-primary"
                        >
                            <input
                                type="radio"
                                id="other"
                                v-model="formData.address_type"
                                name="tag"
                                value="other"
                                class="radio-btn"
                                :checked="formData.address_type === 'other'"
                            />
                            <span class="text-base font-normal">{{
                                $t("OTHER")
                            }}</span>
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            id="default"
                            v-model="formData.is_default"
                            name="default"
                            type="checkbox"
                            class="w-4 h-4"
                            :checked="formData.is_default"
                        />
                        <label
                            for="default"
                            class="text-slate-500 text-sm font-normal leading-tight m-0"
                        >
                            {{ $t("Make it default address") }}
                        </label>
                    </div>
                </div>
                <div class="flex justify-between items-center gap-2 mt-6">
                    <button
                        v-if="!isLoading"
                        type="submit"
                        class="px-4 py-3 md:px-6 md:py-4 bg-primary text-white text-sm md:text-base rounded-[10px] w-[140px] md:w-[152px]"
                    >
                        {{ $t("Update") }}
                    </button>
                    <button
                        v-else
                        type="button"
                        class="px-4 py-3 md:px-6 md:py-4 bg-primary-200 text-primary text-sm md:text-base rounded-[10px] flex justify-center items-center gap-1"
                        disabled
                    >
                        {{ $t("Processing") }} <LoadingSpin />
                    </button>

                    <button
                        type="button"
                        class="bg-white dark:bg-slate-800 rounded-lg py-2 md:py-4 px-2"
                        @click="deleteModal = true"
                    >
                        <div
                            class="text-red-500 text-base font-medium leading-normal"
                        >
                            {{ $t("Delete this") }}
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- delete modal -->
    <TransitionRoot as="template" :show="deleteModal">
        <Dialog as="div" class="relative z-10" @close="deleteModal = false">
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
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
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
                            class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                        >
                            <div class="bg-white dark:bg-slate-800 p-5 sm:p-8 text-center">
                                <div
                                    class="bg-red-500 w-14 h-14 md:w-20 md:h-20 rounded-full mx-auto flex justify-center items-center"
                                >
                                    <TrashIcon
                                        class="w-8 h-8 md:w-12 md:h-12 text-white"
                                    />
                                </div>

                                <div
                                    class="mt-3 text-center text-gray-900 text-2xl md:text-3xl font-bold"
                                >
                                    {{ $t("Delete address") }}
                                </div>

                                <div
                                    class="mt-4 text-center text-slate-700 dark:text-slate-300 text-base md:text-xl font-normal"
                                >
                                    {{
                                        $t(
                                            "Are you sure want to delete this address"
                                        )
                                    }}
                                </div>

                                <div
                                    class="flex justify-between items-center gap-4 mt-8"
                                >
                                    <button
                                        class="text-slate-800 dark:text-slate-100 grow text-base font-medium px-4 py-3 md:px-6 md:py-4 rounded-lg md:rounded-[10px] border border-slate-300"
                                        @click="deleteModal = false"
                                    >
                                        {{ $t("Cancel") }}
                                    </button>

                                    <button
                                        class="text-white grow bg-red-500 text-base font-medium px-4 py-3 md:px-6 md:py-4 rounded-lg md:rounded-[10px]"
                                        @click="deleteAddress"
                                    >
                                        {{ $t("Yes") }}
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import {
    Dialog,
    DialogPanel,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import { TrashIcon } from "@heroicons/vue/24/outline";
import { ref, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useAuth } from "../stores/AuthStore";
import ToastSuccessMessage from "./ToastSuccessMessage.vue";
import LoadingSpin from "./LoadingSpin.vue";

import { useMaster } from "../stores/MasterStore";
import MapDisplay from "./MapDisplay.vue";
import localization from "../localization";
const masterStore = useMaster();

const t = localization.i18n.global.t;
const toast = useToast();
const router = useRouter();
const authStore = useAuth();

const deleteModal = ref(false);

const props = defineProps({
    address: Object,
});

const formData = ref({
    name: "",
    email: "",
    phone: "",
    area_id: "",
    thana_id: "",
    flat_no: "",
    post_code: "",
    address_line: "",
    address_line2: "",
    address_type: "home",
    latitude: "",
    longitude: "",
    is_default: false,
});

watch(
    () => props.address,
    () => {
        formData.value = props.address;
        if (formData.value?.area_id) {
            getThanaOptions(formData.value.area_id);
        }
    }
);

const onAreaChange = () => {
    formData.value.thana_id = "";
    getThanaOptions(formData.value.area_id);
};

const errors = ref({});

const areaOptions = ref([]);
const thanaOptions = ref([]);

const UpdateContent = {
    component: ToastSuccessMessage,
    props: {
        title: t("Address Updated!"),
        message: t("Address updated successfully"),
    },
};

const content = {
    component: ToastSuccessMessage,
    props: {
        title: t("Address Deleted!"),
        message: t("Address deleted successfully"),
    },
};

const deleteAddress = () => {
    axios
        .delete(`/address/${props.address.id}/delete`, {
            headers: {
                Authorization: authStore.token,
            },
        })
        .then(() => {
            toast(content, {
                type: "default",
                hideProgressBar: true,
                icon: false,
                position:
                    masterStore.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
                toastClassName: "vue-toastification-alert",
                timeout: 2000,
            });
            router.push({ name: "manage-address" });
        })
        .catch((error) => {
            toast.error(error.response.data.message, {
                position:
                    masterStore.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });
            if (error.response.status === 401) {
                authStore.token = null;
                authStore.user = null;
                authStore.addresses = [];
                authStore.favoriteProducts = 0;
                router.push("/");
            }
        });
};

const isLoading = ref(false);
const addressFormSubmit = () => {
    isLoading.value = true;
    axios
        .post(`/address/${props.address.id}/update`, formData.value, {
            headers: {
                Authorization: authStore.token,
            },
        })
        .then(() => {
            isLoading.value = false;
            formData.value = {};
            toast(UpdateContent, {
                type: "default",
                hideProgressBar: true,
                icon: false,
                position:
                    masterStore.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
                toastClassName: "vue-toastification-alert",
                timeout: 2000,
            });
            router.push({ name: "manage-address" });
        })
        .catch((error) => {
            isLoading.value = false;
            errors.value = error.response.data.errors;
            toast.error(error.response.data.message, {
                position:
                    masterStore.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });

            if (error.response.status === 401) {
                authStore.token = null;
                authStore.user = null;
                authStore.addresses = [];
                authStore.favoriteProducts = 0;
                router.push("/");
            }
        });
};

const getAreaOptions = () => {
    axios
        .get("/areas", formData.value, {
            headers: {
                Authorization: authStore.token,
            },
        })
        .then((response) => {
            areaOptions.value = response.data.data.areas;
        })
        .catch((error) => {
            toast.error(error.response.data.message, {
                position:
                    masterStore.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });
        });
};

const getThanaOptions = (areaId) => {
    thanaOptions.value = [];

    if (!areaId) {
        return;
    }

    axios
        .get(`/areas/${areaId}/thanas`, {
            headers: {
                Authorization: authStore.token,
            },
        })
        .then((response) => {
            thanaOptions.value = response.data.data.thanas;
        })
        .catch((error) => {
            toast.error(error.response.data.message, {
                position:
                    masterStore.langDirection === "rtl"
                        ? "bottom-right"
                        : "bottom-left",
            });
        });
};

const updateLocation = (coords) => {
    formData.value.latitude = coords.lat;
    formData.value.longitude = coords.lng;
};

onMounted(() => {
    getAreaOptions();
    if (formData.value?.area_id) {
        getThanaOptions(formData.value.area_id);
    }
});
</script>

<style scoped>
.form-label {
    @apply text-slate-700 dark:text-slate-300 text-base font-normal leading-normal;
}

.form-input {
    @apply p-3 rounded-lg border focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400;
}

.formInputCoupon {
    @apply rounded-lg border border-slate-200 dark:border-slate-700 focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400;
}

.radio-btn {
    @apply w-4 h-4 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}

.radioBtn2 {
    @apply w-4 h-4 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}
</style>
