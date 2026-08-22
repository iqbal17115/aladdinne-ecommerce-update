import axios from "axios";
import { defineStore } from "pinia";

export const useGuestAddress = defineStore("guestAddress", {
    state: () => ({
        name: null,
        email: null,
        phone: null,
        area_id: null,
        address_line: null,
        address_type: "home",
        latitude: null,
        longitude: null,
        errors: {}
    }),
    actions: {
        captureLocation() {
            return new Promise((resolve) => {
                if (this.latitude && this.longitude) return resolve();
                if (!navigator.geolocation) return resolve();
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.latitude = position.coords.latitude;
                        this.longitude = position.coords.longitude;
                        resolve();
                    },
                    () => resolve(),
                    { timeout: 5000 }
                );
            });
        },
        clearGuestAddress() {
            this.name = null;
            this.email = null;
            this.phone = null;
            this.area_id = null;
            this.address_line = null;
            this.latitude = null;
            this.longitude = null;
            this.errors = {};
        },
    },

});
