import { useMaster } from "../stores/MasterStore";
import { useAuth } from "../stores/AuthStore";

/**
 * Meta Pixel helper.
 *
 * Every event is fired twice on purpose: once from the browser via `fbq` and
 * once from our server via the Conversions API, both carrying the same
 * `eventID`/`event_id` so Meta deduplicates them. The browser call alone loses
 * 30-40% of events to ad blockers and iOS; the server call alone loses the
 * cookies that match a user to an ad click.
 *
 * Prices are always sent in the site's DEFAULT currency (the one products are
 * stored in), never the visitor's selected display currency — otherwise Meta's
 * reports would mix currencies and historical values would shift whenever an
 * exchange rate is edited.
 */

const readMeta = (name) =>
    document.querySelector(`meta[name="${name}"]`)?.getAttribute("content") ?? "";

// Read once — these are rendered server side and never change during a session.
const pixelId = readMeta("meta-pixel-id");
const capiEnabled = readMeta("meta-capi-enabled") === "1";

const FBCLID_STORAGE_KEY = "meta_fbclid";

/**
 * Meta drops `_fbc` itself, but only when the landing page is hit with
 * ?fbclid=. Remember it so later events (checkout, purchase) can still be
 * attributed to the ad click that started the session.
 */
export const rememberFbClickId = () => {
    try {
        const fbclid = new URLSearchParams(window.location.search).get("fbclid");
        if (fbclid) {
            localStorage.setItem(FBCLID_STORAGE_KEY, fbclid);
        }
    } catch {
        // Private browsing / storage disabled — attribution degrades, nothing breaks.
    }
};

const storedClickId = () => {
    try {
        return localStorage.getItem(FBCLID_STORAGE_KEY) || null;
    } catch {
        return null;
    }
};

const newEventId = () => {
    if (window.crypto?.randomUUID) {
        return window.crypto.randomUUID();
    }

    return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
};

export function useMetaPixel() {
    const master = useMaster();
    const authStore = useAuth();

    const isEnabled = () => Boolean(pixelId);

    /** The currency products are priced in, not the one the visitor selected. */
    const currency = () => master.currency?.name || master.defaultCurrency || "USD";

    /** Selling price of a product: discounted when there is a discount. */
    const priceOf = (product) => {
        const discount = Number(product?.discount_price ?? 0);

        return discount > 0 ? discount : Number(product?.price ?? 0);
    };

    /** One line item in Meta's `contents` array. */
    const contentOf = (product, quantity = 1) => ({
        id: String(product?.id ?? ""),
        quantity: Number(quantity) || 1,
        item_price: priceOf(product),
    });

    /**
     * Fire an event to both the browser pixel and the Conversions API.
     *
     * @param {string} eventName   ViewContent | AddToCart | InitiateCheckout | Purchase
     * @param {object} customData  value / currency / contents / content_ids ...
     * @param {object} userData    Guest details collected at checkout, for matching.
     */
    const track = (eventName, customData = {}, userData = {}) => {
        if (!isEnabled()) {
            return;
        }

        const eventId = newEventId();

        try {
            if (typeof window.fbq === "function") {
                window.fbq("track", eventName, customData, { eventID: eventId });
            }
        } catch {
            // A blocked or half-loaded pixel must never break the page.
        }

        if (!capiEnabled) {
            return;
        }

        // Fire and forget: tracking must not delay checkout or surface errors.
        window.axios
            .post(
                "/meta-pixel/track",
                {
                    event_name: eventName,
                    event_id: eventId,
                    event_source_url: window.location.href,
                    custom_data: customData,
                    fbclid: storedClickId(),
                    ...userData,
                },
                // Signed-in visitors match far better: the token lets the server
                // hash their real email/phone instead of relying on cookies alone.
                { headers: { Authorization: authStore.token } }
            )
            .catch(() => {});
    };

    const trackViewContent = (product) => {
        if (!product?.id) {
            return;
        }

        track("ViewContent", {
            content_type: "product",
            content_ids: [String(product.id)],
            content_name: product.name,
            content_category: product.category || product.brand || product.shop?.name || null,
            contents: [contentOf(product)],
            value: priceOf(product),
            currency: currency(),
        });
    };

    const trackAddToCart = (product, quantity = 1) => {
        if (!product?.id) {
            return;
        }

        track("AddToCart", {
            content_type: "product",
            content_ids: [String(product.id)],
            content_name: product.name,
            contents: [contentOf(product, quantity)],
            num_items: Number(quantity) || 1,
            value: priceOf(product) * (Number(quantity) || 1),
            currency: currency(),
        });
    };

    const trackInitiateCheckout = ({ products = [], value = 0 }) => {
        const contents = products.map((item) => contentOf(item, item.quantity));

        track("InitiateCheckout", {
            content_type: "product",
            content_ids: contents.map((item) => item.id),
            contents,
            num_items: contents.reduce((sum, item) => sum + item.quantity, 0),
            value: Number(value) || 0,
            currency: currency(),
        });
    };

    const trackPurchase = ({ products = [], value = 0 }, userData = {}) => {
        const contents = products.map((item) => contentOf(item, item.quantity));

        track(
            "Purchase",
            {
                content_type: "product",
                content_ids: contents.map((item) => item.id),
                contents,
                num_items: contents.reduce((sum, item) => sum + item.quantity, 0),
                value: Number(value) || 0,
                currency: currency(),
            },
            userData
        );
    };

    return {
        isEnabled,
        track,
        trackViewContent,
        trackAddToCart,
        trackInitiateCheckout,
        trackPurchase,
    };
}
