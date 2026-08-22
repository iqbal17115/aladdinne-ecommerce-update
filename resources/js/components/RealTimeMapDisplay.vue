<script setup>
import { GoogleMap, Marker, Polyline } from "vue3-google-map";
import { ref, watch } from "vue";

const props = defineProps({
    width: { type: String, default: "100%" },
    height: { type: String, default: "500px" },
    riderLocation: {
        type: Object,
        required: true,
        default: { lat: 0, lng: 0 },
    },
    // Expecting { lat: number, lng: number }
    customerLocation: {
        type: Object,
        required: true,
        default: { lat: 0, lng: 0 },
    },
});

const apiKey = "AIzaSyBO0p0HW9N2P0f_4QIuzdIK14ffob5B_BQ";
const mapRef = ref(null);
const pathCoordinates = ref([]);

const calculateRoute = () => {
    if (!mapRef.value?.ready) return;

    const google = mapRef.value.api;
    const directionsService = new google.DirectionsService();

    directionsService.route(
        {
            origin: props.riderLocation,
            destination: props.customerLocation,
            travelMode: google.TravelMode.DRIVING,
        },
        (result, status) => {
            if (status === "OK") {
                // We set the path coordinates from the result
                pathCoordinates.value = result.routes[0].overview_path;
                console.log("Success: Road path loaded.");
            } else {
                console.error("Directions request failed due to: " + status);
            }
        }
    );
};

/**
 * WATCHERS
 * These trigger whenever the parent sends new coordinates
 */

// 1. Watch for the map being ready
watch(
    () => mapRef.value?.ready,
    (ready) => {
        if (ready) calculateRoute();
    }
);

// 2. Watch for Rider or Customer movement
watch(
    [() => props.riderLocation, () => props.customerLocation],
    () => {
        calculateRoute();
    },
    { deep: true } // deep: true is important for Objects
);
</script>

<template>
    <div style="width: 100%; height: 300px">
        <GoogleMap
            ref="mapRef"
            :api-key="apiKey"
            style="width: 100%; height: 100%"
            :center="riderLocation"
            :zoom="13"
        >
            <Polyline
                v-if="pathCoordinates.length > 0"
                :options="{
                    path: pathCoordinates,
                    strokeColor: '#4285F4',
                    strokeWeight: 6,
                    strokeOpacity: 0.9,
                }"
            />

            <Marker
                :options="{
                    position: riderLocation,
                    icon: {
                        url: 'https://cdn-icons-png.flaticon.com/512/12660/12660130.png', // Replace with your Rider SVG URL
                        scaledSize: { width: 40, height: 40 },
                        anchor: { x: 20, y: 25 },
                    },
                }"
            />

            <Marker
                :options="{
                    position: customerLocation,
                    icon: {
                        url: 'https://cdn-icons-png.flaticon.com/512/3135/3135768.png', // Replace with your Customer SVG URL
                        scaledSize: { width: 30, height: 30 },
                        anchor: { x: 15, y: 15 },
                    },
                }"
            />
        </GoogleMap>
    </div>
</template>
