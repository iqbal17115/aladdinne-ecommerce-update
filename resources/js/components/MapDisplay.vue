<script setup>
import { GoogleMap, Marker } from "vue3-google-map";
import { ref, onMounted, watch } from "vue";

const props = defineProps({
    enableSetLocation: {
        type: Boolean,
        default: false,
    },
    width: {
        type: String,
        default: "100%",
    },
    height: {
        type: String,
        default: "300px",
    },
    latitude: {
        type: Number,
    },
    longitude: {
        type: Number,
    },
    hasOldValue: {
        type: Boolean,
        default: false,
    },
});

// 1. Define the emits
const emit = defineEmits(["location-updated"]);

const isReady = ref(false);

// This ref will now track where the marker is pinned
const center = ref({
    lat: 40.6892,
    lng: -74.0445,
});

const apiKey = "AIzaSyBO0p0HW9N2P0f_4QIuzdIK14ffob5B_BQ";

// 1. Create the click handler function
const handleMapClick = (event) => {
    if (!props.enableSetLocation) {
        return;
    }

    // event.latLng.lat() and .lng() are methods from the Google Maps API
    const newLat = event.latLng.lat();
    const newLng = event.latLng.lng();

    // Update the ref to move the marker
    center.value = {
        lat: newLat,
        lng: newLng,
    };

    console.log("New Marker Position:", center.value);
    emit("location-updated", center.value);
};

onMounted(() => {
    isReady.value = true;

    if (navigator.geolocation && !props.hasOldValue) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                center.value = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                };
                emit("location-updated", center.value);
            },
            (err) => {
                console.warn("Location access denied");
            }
        );
    }
});

watch(
    [() => props.latitude, () => props.longitude],
    () => {

        center.value = {
            lat: props.latitude,
            lng: props.longitude,
        };
        emit("location-updated", center.value);
    }
);
</script>

<template>
    <div style="width: 100%; height: 300px">
        <GoogleMap
            v-if="isReady"
            :api-key="apiKey"
            :style="'width:' + width + '; height:' + height"
            :center="center"
            :zoom="15"
            @click="handleMapClick"
        >
            <Marker :options="{ position: center }" />
        </GoogleMap>
    </div>
</template>
