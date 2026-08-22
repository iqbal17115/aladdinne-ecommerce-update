<script setup>
import { GoogleMap, Marker, Polyline } from "vue3-google-map";
import { ref, watch } from "vue";

const props = defineProps({
  width: { type: String, default: "100%" },
  height: { type: String, default: "500px" },
});

const apiKey = "AIzaSyBO0p0HW9N2P0f_4QIuzdIK14ffob5B_BQ"; 
const mapRef = ref(null);
const pathCoordinates = ref([]);

const riderLocation = { lat: 23.7732037, lng: 90.3599352 };
const customerLocation = { lat: 23.8148100, lng: 90.4241962 };

const calculateRoute = () => {
  if (!mapRef.value?.ready) return;

  const google = mapRef.value.api;
  const directionsService = new google.DirectionsService();

  directionsService.route(
    {
      origin: riderLocation,
      destination: customerLocation,
      travelMode: google.TravelMode.DRIVING,
    },
    (result, status) => {
      if (status === "OK") {
        // We set the path coordinates from the result
        pathCoordinates.value = result.routes[0].overview_path;
        console.log("Success: Road path loaded.", pathCoordinates.value);
      } else {
        console.error("Directions request failed due to: " + status);
      }
    }
  );
};

watch(
  () => mapRef.value?.ready,
  (isReady) => {
    if (isReady) calculateRoute();
  }
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
          strokeOpacity: 0.9
        }"
      />
      
      <Marker :options="{ position: riderLocation, label: 'A' }" />
      <Marker :options="{ position: customerLocation, label: 'B' }" />
    </GoogleMap>
  </div>
</template>