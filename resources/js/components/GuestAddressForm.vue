<template>
    <div class="p-6 bg-white dark:bg-slate-800  rounded-2xl border border-slate-200 dark:border-slate-600 mt-3">
        <form>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="form-label mb-2">
                        {{ $t("Name") }}
                        <small class="text-red-500">*</small>
                    </label>
                    <input
                        type="text"
                        id="name"
                        v-model="guestAddressStore.name"
                        :placeholder="$t('Enter name')"
                        class="form-input"
                        :class="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.name
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-600'
                        "
                    />
                    <span
                        v-if="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.name
                        "
                        class="text-red-500 text-sm"
                        >{{ guestAddressStore.errors?.name[0] }}</span
                    >
                </div>
                <div>
                    <label for="email" class="form-label mb-2">
                        {{ $t("Email") }}
                        <small class="text-red-500">*</small>
                    </label>
                    <input
                        type="email"
                        id="email"
                        v-model="guestAddressStore.email"
                        :placeholder="$t('Enter email')"
                        class="form-input"
                        :class="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.email
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-600'
                        "
                    />
                    <span
                        v-if="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.email
                        "
                        class="text-red-500 text-sm"
                        >{{ guestAddressStore.errors?.email[0] }}</span
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
                        v-model="guestAddressStore.phone"
                        :class="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.phone
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-600'
                        "
                        maxlength="11"
                        @input="
                            guestAddressStore.phone =
                                guestAddressStore.phone.replace(/[^\d]/g, '')
                        "
                    />
                    <span
                        v-if="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.phone
                        "
                        class="text-red-500 text-sm"
                        >{{ guestAddressStore.errors?.phone[0] }}</span
                    >
                </div>
            </div>

            <div class="mt-6">
                <div
                    class="text-slate-950 dark:text-white text-base font-medium leading-normal mb-2"
                >
                    {{ $t("Pin Your Location") }}
                </div>
                <MapDisplay
                    :enableSetLocation="true"
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
                        v-model="guestAddressStore.area_id"
                        @change="onAreaChange"
                        :class="[
                            'form-input',
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.area
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-600',
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
                        v-if="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.area
                        "
                        class="text-red-500 text-sm"
                        >{{ guestAddressStore.errors?.area[0] }}</span
                    >
                </div>

                <div>
                    <label for="Thana" class="form-label mb-2">
                        {{ $t("Thana") }}
                        <small class="text-red-500">*</small>
                    </label>
                    <select
                        id="Thana"
                        v-model="guestAddressStore.thana_id"
                        :disabled="!guestAddressStore.area_id"
                        :class="[
                            'form-input',
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.thana_id
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-600',
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
                        v-if="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.thana_id
                        "
                        class="text-red-500 text-sm"
                        >{{ guestAddressStore.errors?.thana_id[0] }}</span
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="address" class="form-label mb-2">
                        {{ $t("Address Line") }}
                        <small class="text-red-500">*</small>
                    </label>
                    <input
                        type="text"
                        id="address"
                        v-model="guestAddressStore.address_line"
                        :placeholder="$t('Enter address')"
                        class="form-input"
                        :class="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.address_line
                                ? 'border-red-500'
                                : 'border-slate-200 dark:border-slate-600'
                        "
                    />
                    <span
                        v-if="
                            guestAddressStore.errors &&
                            guestAddressStore.errors?.address_line
                        "
                        class="text-red-500 text-sm"
                        >{{ guestAddressStore.errors?.address_line[0] }}</span
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
                    <div class="flex items-center flex-wrap gap-2">
                        <label
                            for="home"
                            class="px-3 py-2 bg-white dark:bg-slate-700 rounded-[42px] border border-slate-200 dark:border-slate-600 flex gap-2 items-center text-slate-600 dark:text-slate-300 text-base font-normal leading-normal cursor-pointer has-[:checked]:border-primary has-[:checked]:text-primary"
                        >
                            <input
                                type="radio"
                                id="home"
                                v-model="guestAddressStore.address_type"
                                name="tag"
                                value="home"
                                class="radio-btn"
                                :checked="
                                    guestAddressStore.address_type === 'home'
                                "
                            />
                            <span class="text-base font-normal">{{
                                $t("HOME")
                            }}</span>
                        </label>

                        <label
                            for="office"
                            class="px-3 py-2 bg-white dark:bg-slate-700 rounded-[42px] border border-slate-200 dark:border-slate-600 flex gap-2 items-center text-slate-600 dark:text-slate-300 text-base font-normal leading-normal cursor-pointer has-[:checked]:border-primary has-[:checked]:text-primary"
                        >
                            <input
                                type="radio"
                                id="office"
                                v-model="guestAddressStore.address_type"
                                name="tag"
                                value="office"
                                class="radio-btn"
                                :checked="
                                    guestAddressStore.address_type === 'office'
                                "
                            />
                            <span class="text-base font-normal">{{
                                $t("OFFICE")
                            }}</span>
                        </label>

                        <label
                            for="other"
                            class="px-3 py-2 bg-white dark:bg-slate-700 rounded-[42px] border border-slate-200 dark:border-slate-600 flex gap-2 items-center text-slate-600 dark:text-slate-300 text-base font-normal leading-normal cursor-pointer has-[:checked]:border-primary has-[:checked]:text-primary"
                        >
                            <input
                                type="radio"
                                id="other"
                                v-model="guestAddressStore.address_type"
                                name="tag"
                                value="other"
                                class="radio-btn"
                                :checked="
                                    guestAddressStore.address_type === 'other'
                                "
                            />
                            <span class="text-base font-normal">{{
                                $t("OTHER")
                            }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useAuth } from "../stores/AuthStore";
import { useGuestAddress } from "../stores/GuestAddressStore";
import ToastSuccessMessage from "./ToastSuccessMessage.vue";
import LoadingSpin from "./LoadingSpin.vue";
import MapDisplay from "./MapDisplay.vue";

import { useMaster } from "../stores/MasterStore";
import { useBasketStore } from "../stores/BasketStore";

const masterStore = useMaster();
const guestAddressStore = useGuestAddress();
const basketStore = useBasketStore();

const toast = useToast();
const route = useRoute();
const router = useRouter();
const authStore = useAuth();

const areaOptions = ref([]);
const thanaOptions = ref([]);

const getAreaOptions = () => {
    axios
        .get("/areas", {
            headers: {
                Authorization: authStore.token,
            },
        })
        .then((response) => {
            areaOptions.value = response.data.data.areas;
            guestAddressStore.area_id = response.data.data.areas[0].id;
            getThanaOptions(guestAddressStore.area_id);
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
            if (thanaOptions.value.length > 0) {
                guestAddressStore.thana_id = thanaOptions.value[0].id;
            }
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

const onAreaChange = () => {
    guestAddressStore.thana_id = "";
    getThanaOptions(guestAddressStore.area_id);
};

const setCurrentLocation = () => {
    if (!navigator.geolocation) {
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            guestAddressStore.latitude = position.coords.latitude;
            guestAddressStore.longitude = position.coords.longitude;
        },
        () => {
            guestAddressStore.latitude = 0;
            guestAddressStore.longitude = 0;
        }
    );
};

const updateLocation = (coords) => {
    guestAddressStore.latitude = coords.lat;
    guestAddressStore.longitude = coords.lng;
};

onMounted(() => {
    getAreaOptions();
    setCurrentLocation();
});

watch(
    () => guestAddressStore.thana_id,
    () => {
        basketStore.fetchCheckoutProducts(null, guestAddressStore.area_id, guestAddressStore.thana_id);
    },
);
</script>

<style scoped>
.form-label {
    @apply text-slate-700 dark:text-slate-300 text-base font-normal leading-normal;
}

.form-input {
    @apply p-3 rounded-lg border focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400 bg-white dark:bg-slate-700 dark:text-white dark:placeholder:text-slate-500;
}

.formInputCoupon {
    @apply rounded-lg border border-slate-200 focus:border-primary w-full outline-none text-base font-normal leading-normal placeholder:text-slate-400;
}

.radio-btn {
    @apply w-4 h-4 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}

.radioBtn2 {
    @apply w-4 h-4 border appearance-none border-slate-300 rounded-full checked:bg-primary ring-primary checked:outline-1 outline-offset-1 checked:outline-primary checked:outline transition duration-100 ease-in-out m-0;
}
</style>
