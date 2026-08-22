export const slugify = (text) => {
    return String(text || '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

const SHOP_SLUG_MAP_KEY = 'shopSlugIdMap';

const readShopSlugMap = () => {
    try {
        return JSON.parse(localStorage.getItem(SHOP_SLUG_MAP_KEY)) || {};
    } catch (e) {
        return {};
    }
};

export const cacheShopSlug = (shop) => {
    if (!shop?.id || !shop?.name) return;
    const map = readShopSlugMap();
    map[slugify(shop.name)] = shop.id;
    localStorage.setItem(SHOP_SLUG_MAP_KEY, JSON.stringify(map));
};

export const getCachedShopId = (slug) => {
    return readShopSlugMap()[slug] || null;
};

export const shopUrl = (shop) => {
    cacheShopSlug(shop);
    const slug = slugify(shop?.name);
    return slug ? `/shops/${slug}` : `/shops/${shop?.id}`;
};
