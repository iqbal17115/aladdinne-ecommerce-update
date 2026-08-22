import { useMaster } from '../stores/MasterStore';

export function useSeo() {
    const master = useMaster();

    const setMeta = (options = {}) => {
        const {
            title = null,
            description = null,
            image = null,
            type = 'website',
            keywords = null,
            url = window.location.href,
        } = options;

        const appName = master.appName || '';
        const logo = master.logo || '';

        const fullTitle = title ? `${title} - ${appName}` : appName;
        const ogImage = image || logo;
        const rawDesc = description ? description.replace(/<[^>]*>/g, '').trim() : '';
        const desc = rawDesc.length > 160 ? rawDesc.substring(0, 157) + '...' : rawDesc;

        document.title = fullTitle;

        const setOrCreate = (selector, attrKey, attrValue, content) => {
            let el = document.querySelector(selector);
            if (!el) {
                el = document.createElement('meta');
                el.setAttribute(attrKey, attrValue);
                document.head.appendChild(el);
            }
            el.setAttribute('content', content);
        };

        setOrCreate('meta[name="description"]', 'name', 'description', desc);

        if (keywords) {
            setOrCreate('meta[name="keywords"]', 'name', 'keywords', keywords);
        }

        // Open Graph
        setOrCreate('meta[property="og:title"]', 'property', 'og:title', fullTitle);
        setOrCreate('meta[property="og:description"]', 'property', 'og:description', desc);
        setOrCreate('meta[property="og:image"]', 'property', 'og:image', ogImage);
        setOrCreate('meta[property="og:url"]', 'property', 'og:url', url);
        setOrCreate('meta[property="og:type"]', 'property', 'og:type', type);
        setOrCreate('meta[property="og:site_name"]', 'property', 'og:site_name', appName);

        // Twitter Card
        setOrCreate('meta[name="twitter:card"]', 'name', 'twitter:card', 'summary_large_image');
        setOrCreate('meta[name="twitter:title"]', 'name', 'twitter:title', fullTitle);
        setOrCreate('meta[name="twitter:description"]', 'name', 'twitter:description', desc);
        setOrCreate('meta[name="twitter:image"]', 'name', 'twitter:image', ogImage);

        // Canonical
        let canonical = document.querySelector('link[rel="canonical"]');
        if (!canonical) {
            canonical = document.createElement('link');
            canonical.setAttribute('rel', 'canonical');
            document.head.appendChild(canonical);
        }
        canonical.setAttribute('href', url);
    };

    const setJsonLd = (data) => {
        let el = document.querySelector('script[type="application/ld+json"][data-vue-seo]');
        if (!el) {
            el = document.createElement('script');
            el.setAttribute('type', 'application/ld+json');
            el.setAttribute('data-vue-seo', '1');
            document.head.appendChild(el);
        }
        el.textContent = JSON.stringify(data);
    };

    const removeJsonLd = () => {
        const el = document.querySelector('script[type="application/ld+json"][data-vue-seo]');
        if (el) el.remove();
    };

    const setDefault = () => {
        setMeta({
            title: null,
            description: master.footerDescription || '',
            image: master.logo || '',
            type: 'website',
        });
        removeJsonLd();
    };

    return { setMeta, setJsonLd, removeJsonLd, setDefault };
}
