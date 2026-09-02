import axios from "axios";
import { defineStore } from "pinia";
import { useToast } from "vue-toastification";
import AddToCartDialog from "../components/AddCartPopupDialog.vue";
import RemoveCartPopupDialog from "../components/RemoveCartPopupDialog.vue";

import { useAuth } from "./AuthStore";
import { useMaster } from "./MasterStore";
import { useGuestAddress } from "./GuestAddressStore";
import { useMetaPixel } from "../composables/useMetaPixel";
import router from '@/router'

const toast = useToast();

/**
 * InitiateCheckout must fire once per checkout visit, but the checkout endpoint
 * is re-hit on every address/coupon change — so the guard lives outside the
 * store state (it is per-visit, not something worth persisting).
 */
let checkoutTracked = false;

/** Flatten the shop-grouped checkout payload into a plain product list. */
const flattenCheckoutProducts = (checkoutProducts) =>
    (checkoutProducts ?? []).flatMap((shop) => shop.products ?? []);

export const useBasketStore = defineStore("basketStore", {
    state: () => ({
        total: 0,
        products: [],
        checkoutProducts: [],
        selectedShopIds: [],
        total_amount: 0,
        delivery_charge: 0,
        coupon_discount: 0,
        payable_amount: 0,
        order_tax_amount: 0,
        coupon_code: "",
        all_vat_taxes: [],
        showOrderConfirmModal: false,
        orderPaymentCancelModal: false,
        address: null,
        buyNowShopId: null,
        buyNowProduct: null,
        // Bumped whenever the buy-now line changes, so the buy-now order
        // summary knows to re-fetch its totals.
        buyNowRefreshKey: 0,
        isLoadingCart: false,
    }),

    getters: {
        totalAmount: (state) => {
            let total = 0;
            state.products.forEach((item) => {
                item.products.forEach((product) => {
                    let price =
                        product.discount_price > 0
                            ? product.discount_price
                            : product.price;
                    total += price * product.quantity;
                });
            });
            return total.toFixed(2);
        },

        totalCheckoutAmount: (state) => {
            let total = 0;
            state.checkoutProducts.forEach((item) => {
                item.products.forEach((product) => {
                    let price =
                        product.discount_price > 0
                            ? product.discount_price
                            : product.price;
                    total += price * product.quantity;
                });
            });
            return total.toFixed(2);
        },

        checkoutTotalItems: (state) => {
            let total = 0;
            state.checkoutProducts.forEach((item) => {
                total += item.products.length;
            });
            return total;
        },
    },

    actions: {
        /**
         * Add a product to cart.
         * @param {object} data - object containing product id, quantity, color, size, unit.
         * @param {object} product - the product to add to cart.
         * @returns {Promise}
         */
        addToCart(data, product) {
            const masterStore = useMaster();
            if (data.product_id) {
                this.isLoadingCart = true;
                const content = {
                    component: AddToCartDialog,
                    props: {
                        product: product,
                    },
                };
                const authStore = useAuth();
                axios.post("/cart/store", data, {
                    headers: {
                        Authorization: authStore.token,
                        'X-Guest-Token': authStore.access_token
                    },
                }).then((response) => {

                    if (response.data.data.access_token) {
                        authStore.setAccessToken(response.data.data.access_token);
                        this.addToCart(data, product);
                        return
                    }


                    this.isLoadingCart = false;

                    // After the guest-token retry above, so it only fires once.
                    useMetaPixel().trackAddToCart(product, data.quantity ?? 1);

                    if (!data.is_buy_now) {
                        this.total = response.data.data.total;
                        this.products = response.data.data.cart_items;
                        toast(content, {
                            type: "default",
                            hideProgressBar: true,
                            icon: false,
                            position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                            toastClassName: "vue-toastification-alert",
                            timeout: 3000,
                        });

                        if (response.data.data.info) {
                            toast.warning(response.data.data.info, {
                                position: authStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                            });
                        }
                    } else {
                        // changing the route
                        router.push({ name: 'buynow' })

                    }
                }).catch((error) => {
                    this.isLoadingCart = false;
                    if (error.response.status == 401) {
                        toast.error("Please login first!", {
                            position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                        });
                        const authStore = useAuth();
                        authStore.logout();
                        authStore.showLoginModal();
                    } else {
                        toast.error(error.response.data.message, {
                            position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                        });
                    }
                    return error;
                });
            }
        },

        /**
         * Fetches the cart data from the server and updates the state.
         * If the user is not logged in, it clears the cart and related state.
         */
        fetchCart() {
            const authStore = useAuth();
            if (authStore.token || authStore.access_token) {
                axios.get("/carts", {
                    headers: {
                        Authorization: authStore.token,
                        'X-Guest-Token': authStore.access_token
                    },
                }).then((response) => {
                    this.total = response.data.data.total;
                    this.products = response.data.data.cart_items;

                    if (response.data.data.info) {
                        toast.warning(response.data.data.info, {
                            position: authStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                        });
                    }
                }).catch((error) => {
                    if (error.response.status === 401) {
                        authStore.token = null;
                        authStore.user = null;
                        authStore.addresses = [];
                        authStore.favoriteProducts = 0;
                    }
                });
            } else {
                this.total = 0;
                this.products = [];
                this.checkoutProducts = [];
                this.selectedShopIds = [];
                this.total_amount = 0;
                this.delivery_charge = 0;
                this.coupon_discount = 0;
                this.payable_amount = 0;
                this.coupon_code = "";
                this.address = null;
                authStore.user = null;
                authStore.addresses = [];
                authStore.token = null;
            }
        },

        /**
         * Decrement the quantity of a given product in the cart
         * @param {object} product - the product to decrement the quantity for
         */
        decrementQuantity(product) {
            const authStore = useAuth();
            const masterStore = useMaster();
            if (product) {
                const content = {
                    component: RemoveCartPopupDialog,
                    props: {
                        product: product,
                    },
                };
                axios.post("/cart/decrement",
                    {
                        product_id: product.id,
                        cart_id: product.cart_id
                    },
                    {
                        headers: {
                            Authorization: authStore.token,
                            'X-Guest-Token': authStore.access_token
                        },
                    }
                ).then((response) => {
                    this.total = response.data.data.total;
                    this.products = response.data.data.cart_items;
                    this.fetchCheckoutProducts();

                    if (
                        response.data.message == "product removed from cart"
                    ) {
                        const shopIds = this.products.map(
                            (shop) => shop.shop_id
                        );
                        this.selectedShopIds = this.selectedShopIds.filter(
                            (id) => shopIds.includes(id)
                        );
                        // const exists = shopIds.some((id) => selectedShopIds.includes(id));

                        if (this.products.length === 0) {
                            this.selectedShopIds = [];
                            this.checkoutProducts = [];
                            this.total_amount = 0;
                            this.delivery_charge = 0;
                            this.coupon_discount = 0;
                            this.payable_amount = 0;
                        }

                        toast(content, {
                            type: "default",
                            hideProgressBar: true,
                            icon: false,
                            position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                            toastClassName: "vue-toastification-alert",
                            timeout: 3000,
                        });

                        if (response.data.data.info) {
                            toast.warning(response.data.data.info, {
                                position: authStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                            });
                        }
                    }
                }).catch((error) => {
                    if (error.response.status == 401) {
                        authStore.token = null;
                        authStore.user = null;
                        authStore.addresses = [];
                        authStore.favoriteProducts = 0;
                    }
                });
            }
        },

        /**
         * Increment the quantity of the given product in the cart
         * @param {object} product - the product to increment the quantity for
         */
        incrementQuantity(product) {
            const authStore = useAuth();
            const masterStore = useMaster();
            if (product) {
                axios.post("/cart/increment", {
                    product_id: product.id,
                    cart_id: product.cart_id
                }, {
                    headers: {
                        Authorization: authStore.token,
                        'X-Guest-Token': authStore.access_token
                    },
                }).then((response) => {
                    this.total = response.data.data.total;
                    this.products = response.data.data.cart_items;
                    this.fetchCheckoutProducts();

                    if (response.data.data.info) {
                        toast.warning(response.data.data.info, {
                            position: authStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                        });
                    }
                }).catch((error) => {
                    toast.error(error.response.data.message, {
                        position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                    });
                    if (error.response.status == 401) {
                        authStore.token = null;
                        authStore.user = null;
                        authStore.addresses = [];
                        authStore.favoriteProducts = 0;
                    }
                });
            }
        },

        /**
         * Change the quantity of the buy-now line.
         *
         * The regular increment/decrement actions refresh the normal cart and
         * its checkout, which don't include buy-now rows — so buy-now needs its
         * own pair that posts `is_buy_now` and refreshes `buyNowProduct`.
         *
         * @param {object} product - the buy-now line item
         * @param {'increment'|'decrement'} direction
         */
        changeBuyNowQuantity(product, direction) {
            const authStore = useAuth();
            const masterStore = useMaster();

            if (!product) return;

            // Never let the last unit go — deleting the row would leave the
            // buy-now checkout page with nothing to order.
            if (direction === "decrement" && product.quantity <= 1) return;

            axios.post(`/cart/${direction}`, {
                product_id: product.id,
                cart_id: product.cart_id,
                is_buy_now: true,
            }, {
                headers: {
                    Authorization: authStore.token,
                    'X-Guest-Token': authStore.access_token
                },
            }).then((response) => {
                this.buyNowProduct = response.data.data.cart_items[0] ?? null;
                this.buyNowRefreshKey++;

                if (response.data.data.info) {
                    toast.warning(response.data.data.info, {
                        position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                    });
                }
            }).catch((error) => {
                toast.error(error.response?.data?.message, {
                    position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                });
            });
        },

        /**
         * Remove the given product from the cart
         * @param {object} product - the product to remove from the cart
         */
        removeFromBasket(product) {
            const authStore = useAuth();
            if (product) {
                axios.post("/cart/delete",
                    { product_id: product.id, cart_id: product.cart_id },
                    {
                        headers: {
                            Authorization: authStore.token,
                            'X-Guest-Token': authStore.access_token
                        },
                    }
                ).then((response) => {
                    this.total = response.data.data.total;
                    this.products = response.data.data.cart_items;
                    this.fetchCheckoutProducts();

                    if (response.data.data.info) {
                        toast.warning(response.data.data.info, {
                            position: authStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                        });
                    }
                }).catch((error) => {
                    if (error.response.status == 401) {
                        authStore.token = null;
                        authStore.user = null;
                        authStore.addresses = [];
                        authStore.favoriteProducts = 0;
                    }
                });
            }
        },

        /**
         * Select or deselect the given shop for checkout
         * @param {number} shop - the shop to select or deselect
         */
        selectCartItemsForCheckout(shop) {
            if (!this.selectedShopIds.includes(shop)) {
                this.selectedShopIds.push(shop);
            } else {
                this.selectedShopIds = this.selectedShopIds.filter(
                    (item) => item !== shop
                );
            }
            this.fetchCheckoutProducts();
        },

        /**
         * Fetches the checkout products for the currently selected shops and updates
         * the checkout-related state, including total amount, delivery charge, coupon
         * discount, and payable amount. If the checkout products are empty, it clears
         * the selected shop IDs. Uses the auth token for authorization.
         */
        /**
         * What is being bought right now, captured for the Meta Purchase event.
         * Must be called BEFORE the cart state is cleared on a successful order.
         */
        metaPurchaseSnapshot() {
            return {
                products: flattenCheckoutProducts(this.checkoutProducts),
                value: this.payable_amount,
            };
        },

        /** Allow the next checkout visit to fire InitiateCheckout again. */
        resetCheckoutTracking() {
            checkoutTracked = false;
        },

        fetchCheckoutProducts(addressId, area_id, thana_id) {
            const authStore = useAuth();
            const masterStore = useMaster();
            const guestAddressStore = useGuestAddress();
            if (authStore.token || authStore.access_token) {
                const locationData = guestAddressStore.latitude && guestAddressStore.longitude
                    ? { latitude: guestAddressStore.latitude, longitude: guestAddressStore.longitude }
                    : {};
                axios.post("/cart/checkout", {
                    shop_ids: this.selectedShopIds,
                    address_id: addressId && addressId,
                    area_id: area_id && area_id,
                    thana_id: thana_id && thana_id,
                    ...locationData,
                }, {
                    headers: {
                        Authorization: authStore.token,
                        'X-Guest-Token': authStore.access_token
                    },
                }).then((response) => {
                    this.checkoutProducts = response.data.data.checkout_items;
                    this.total_amount = response.data.data.checkout.total_amount;
                    this.delivery_charge = response.data.data.checkout.delivery_charge;
                    this.coupon_discount = response.data.data.checkout.coupon_discount;
                    this.payable_amount = response.data.data.checkout.payable_amount;
                    this.order_tax_amount = response.data.data.checkout.order_tax_amount;
                    this.all_vat_taxes = response.data.data.checkout.all_vat_taxes;
                    if (this.checkoutProducts.length === 0) {
                        this.selectedShopIds = [];
                    }

                    if (!checkoutTracked && this.checkoutProducts.length > 0) {
                        checkoutTracked = true;
                        useMetaPixel().trackInitiateCheckout({
                            products: flattenCheckoutProducts(this.checkoutProducts),
                            value: this.payable_amount,
                        });
                    }
                }).catch((error) => {
                    if (error.response.status == 401) {
                        authStore.token = null;
                        authStore.user = null;
                        authStore.addresses = [];
                        authStore.favoriteProducts = 0;
                        toast.error(error.response.data.message, {
                            position: masterStore.langDirection === 'rtl' ? "bottom-right" : "bottom-left",
                        });
                    }
                });
            }
        },

        checkShopIsSelected(shopId) {
            return this.selectedShopIds.includes(shopId);
        },
    },

    persist: true,
});
