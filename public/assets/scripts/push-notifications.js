(function () {
    const config = window.AdminPushConfig || {};
    const fcmTokenField = document.getElementById('fcm_token') || document.getElementById('device_key');
    const deviceTypeField = document.getElementById('device_type');
    const deviceNameField = document.getElementById('device_name');
    const loginForm = fcmTokenField ? fcmTokenField.closest('form') : null;
    const badge = document.getElementById('notif-badge') || document.getElementById('totalNotify');
    const dropdownCount = document.getElementById('notif-dropdown-count') || document.getElementById('totalNotify');
    const dropdownList = document.getElementById('notif-dropdown-list') || document.getElementById('notifications');
    const hasFirebaseSdk = typeof window.firebase !== 'undefined';
    const userId = Number(config.userId || 0);

    let firebaseConfigCache = null;
    let firebaseMessaging = null;
    let swRegistrationPromise = null;
    let loginTokenPromise = null;
    let unreadRefreshPromise = null;

    const lastSeenNotificationStorageKey = userId ? `notif_last_seen_id_${userId}` : null;
    const unreadCountStorageKey = userId ? `notif_unread_count_${userId}` : null;
    const notificationPermissionPromptKey = userId ? `notif_permission_prompted_${userId}` : 'notif_permission_prompted_guest';

    function buildReadUrl(notificationId, redirectTo) {
        if (!config.notificationReadUrlTemplate || !notificationId) {
            return redirectTo || config.notificationListUrl || '#';
        }

        const baseUrl = config.notificationReadUrlTemplate.replace('__NOTIFICATION__', String(notificationId));

        if (!redirectTo) {
            return baseUrl;
        }

        const separator = baseUrl.includes('?') ? '&' : '?';
        return `${baseUrl}${separator}redirect_to=${encodeURIComponent(redirectTo)}`;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getBrowserInfo() {
        const ua = navigator.userAgent;
        let browser = 'Unknown Browser';

        if (ua.includes('Edg')) {
            browser = 'Edge';
        } else if (ua.includes('Firefox')) {
            browser = 'Firefox';
        } else if (ua.includes('Chrome')) {
            browser = 'Chrome';
        } else if (ua.includes('Safari')) {
            browser = 'Safari';
        }

        return `${browser} on ${navigator.platform}`;
    }

    function normalizeNotificationPayload(payload) {
        const source = payload || {};

        return {
            notificationId: source.notificationId ?? source.notification_id ?? source.data?.notification_id ?? null,
            title: source.title ?? source.notification?.title ?? source.data?.title ?? config.text?.defaultNotificationTitle ?? 'New notification',
            description: source.description ?? source.content ?? source.notification?.body ?? source.data?.description ?? source.data?.content ?? '',
            url: source.url ?? source.data?.url ?? '',
            unreadCountDelta: parseInt(source.unreadCountDelta ?? source.unread_count_delta ?? 1, 10) || 1,
            isRead: Boolean(Number(source.is_read ?? source.isRead ?? 0)),
            icon: source.icon ?? 'bi-bell-fill',
            type: source.type ?? 'primary',
            createdAt: source.created_at_human ?? source.created_at ?? source.time ?? config.text?.justNow ?? 'Just now',
        };
    }

    function buildNotificationMarkup(notification) {
        const normalized = normalizeNotificationPayload(notification);
        const href = normalized.isRead
            ? (normalized.url || config.notificationListUrl || '#')
            : buildReadUrl(normalized.notificationId, normalized.url || null);
        const iconClass = normalized.type === 'danger' ? 'cardIcon' : 'pdfIcon';

        return `
            <a href="${escapeHtml(href)}" class="item d-flex gap-2 align-items-center" data-notification-id="${escapeHtml(normalized.notificationId || '')}">
                <div class="iconBox ${iconClass}">
                    <i class="bi ${escapeHtml(normalized.icon)}"></i>
                </div>
                <div class="notification w-100 ${normalized.isRead ? '' : 'unread'}">
                    <div class="userName">
                        <p class="massTitle">${escapeHtml(normalized.title)}</p>
                        <span class="time">${escapeHtml(normalized.createdAt)}</span>
                    </div>
                    <div>
                        <p class="description">${escapeHtml(normalized.description || config.text?.noDescription || 'No description available.')}</p>
                    </div>
                </div>
            </a>
        `;
    }

    function setBadgeCount(count) {
        if (!badge) {
            return;
        }

        const safeCount = Number.isFinite(count) ? count : 0;
        badge.textContent = String(safeCount);

        if (dropdownCount) {
            dropdownCount.textContent = String(safeCount);
        }

        badge.classList.toggle('hidden', safeCount <= 0);
    }

    function renderNotificationList(notifications) {
        if (!dropdownList) {
            return;
        }

        if (!Array.isArray(notifications) || notifications.length === 0) {
            dropdownList.innerHTML = `
                <div id="notif-dropdown-empty" class="text-center p-3">
                    <p class="massTitle">${escapeHtml(config.text?.noNotificationsTitle || 'No notifications yet')}</p>
                    <p class="description mb-0">${escapeHtml(config.text?.noNotificationsDescription || 'New notifications will appear here in real time.')}</p>
                </div>
            `;
            return;
        }

        dropdownList.innerHTML = notifications.map(buildNotificationMarkup).join('');
    }

    function prependRealtimeNotification(notification) {
        if (!dropdownList) {
            return;
        }

        const currentNotifications = Array.from(dropdownList.querySelectorAll('a[data-notification-id]')).map((item) => ({
            id: item.getAttribute('data-notification-id') || null,
            url: item.getAttribute('href') || config.notificationListUrl || '#',
            title: item.querySelector('.massTitle')?.textContent?.trim() || '',
            content: item.querySelector('.description')?.textContent?.trim() || '',
            time: item.querySelector('.time')?.textContent?.trim() || '',
            is_read: item.querySelector('.notification.unread') ? 0 : 1,
            icon: item.querySelector('i')?.className.replace('bi ', '') || 'bi-bell-fill',
            type: item.querySelector('.cardIcon') ? 'danger' : 'primary',
        }));

        renderNotificationList([{
            id: notification.notificationId || null,
            url: notification.url || config.notificationListUrl || '#',
            title: notification.title || config.text?.notificationLabel || 'Notification',
            content: notification.description || '',
            time: config.text?.justNow || 'Just now',
            is_read: 0,
            icon: notification.icon || 'bi-bell-fill',
            type: notification.type || 'primary',
        }, ...currentNotifications].slice(0, 10));
    }

    async function fetchFirebaseConfig() {
        if (firebaseConfigCache) {
            return firebaseConfigCache;
        }

        if (config.firebaseWebConfig) {
            firebaseConfigCache = config.firebaseWebConfig;
            return firebaseConfigCache;
        }

        if (!config.firebaseWebConfigUrl) {
            return null;
        }

        const response = await fetch(config.firebaseWebConfigUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            return null;
        }

        const payload = await response.json();

        if (!payload?.success || !payload?.config) {
            return null;
        }

        firebaseConfigCache = payload.config;
        return firebaseConfigCache;
    }

    async function ensureNotificationPermission(options) {
        if (!('Notification' in window)) {
            return 'denied';
        }

        if (Notification.permission !== 'default') {
            return Notification.permission;
        }

        if (!options?.requestIfDefault) {
            return Notification.permission;
        }

        try {
            return await Notification.requestPermission();
        } catch (error) {
            return Notification.permission;
        }
    }

    async function ensureServiceWorkerRegistration() {
        if (!('serviceWorker' in navigator) || !window.isSecureContext || !config.serviceWorkerPath) {
            return null;
        }

        if (!swRegistrationPromise) {
            swRegistrationPromise = (async () => {
                let registration = await navigator.serviceWorker.getRegistration(config.serviceWorkerPath);

                if (!registration) {
                    registration = await navigator.serviceWorker.register(config.serviceWorkerPath, {
                        scope: '/',
                    });
                }

                await navigator.serviceWorker.ready;
                return registration;
            })().catch((error) => {
                console.error('[Push] Service worker registration failed:', error);
                swRegistrationPromise = null;
                return null;
            });
        }

        return swRegistrationPromise;
    }

    async function syncFirebaseConfigToServiceWorker(registration) {
        if (!registration) {
            return;
        }

        const webConfig = await fetchFirebaseConfig();
        if (!webConfig) {
            return;
        }

        const worker = registration.active || registration.waiting || registration.installing;
        if (!worker) {
            return;
        }

        worker.postMessage({
            type: 'FIREBASE_CONFIG',
            config: {
                apiKey: webConfig.apiKey,
                authDomain: webConfig.authDomain,
                projectId: webConfig.projectId,
                storageBucket: webConfig.storageBucket,
                messagingSenderId: webConfig.messagingSenderId,
                appId: webConfig.appId,
            },
        });
    }

    async function ensureFirebaseMessaging() {
        if (!hasFirebaseSdk) {
            return null;
        }

        if (firebaseMessaging) {
            return firebaseMessaging;
        }

        const webConfig = await fetchFirebaseConfig();
        if (!webConfig) {
            return null;
        }

        const firebaseConfig = {
            apiKey: webConfig.apiKey,
            authDomain: webConfig.authDomain,
            projectId: webConfig.projectId,
            storageBucket: webConfig.storageBucket,
            messagingSenderId: webConfig.messagingSenderId,
            appId: webConfig.appId,
        };

        if (!window.firebase.apps.length) {
            window.firebase.initializeApp(firebaseConfig);
        }

        firebaseMessaging = window.firebase.messaging();
        return firebaseMessaging;
    }

    async function getBrowserFcmToken(options) {
        const permission = await ensureNotificationPermission({
            requestIfDefault: Boolean(options?.requestPermission),
        });

        if (permission !== 'granted') {
            return null;
        }

        const registration = await ensureServiceWorkerRegistration();
        await syncFirebaseConfigToServiceWorker(registration);

        const messaging = await ensureFirebaseMessaging();
        const webConfig = await fetchFirebaseConfig();

        if (!registration || !messaging || !webConfig?.vapidKey) {
            return null;
        }

        return messaging.getToken({
            vapidKey: webConfig.vapidKey,
            serviceWorkerRegistration: registration,
        });
    }

    async function showBrowserNotification(title, url, notificationId, description) {
        const permission = await ensureNotificationPermission({ requestIfDefault: false });

        if (permission !== 'granted') {
            return;
        }

        const notificationTitle = config.appName ? `${config.appName} - ${title}` : title;
        const notificationOptions = {
            body: description || config.text?.defaultNotificationBody || 'New notification',
            icon: config.notificationIcon,
            badge: config.notificationIcon,
            tag: `notif-${notificationId || Date.now()}`,
            data: {
                url: url || config.notificationListUrl || '/',
                notificationId: notificationId || null,
            },
        };

        try {
            const registration = await ensureServiceWorkerRegistration();

            if (registration?.showNotification) {
                await registration.showNotification(notificationTitle, notificationOptions);
                return;
            }
        } catch (error) {
            console.error('[Push] Browser notification via service worker failed:', error);
        }

        try {
            const notification = new Notification(notificationTitle, notificationOptions);

            notification.onclick = function () {
                window.focus();

                window.location.href = buildReadUrl(notificationId, url || config.notificationListUrl || '/');
            };
        } catch (error) {
            console.error('[Push] Browser notification fallback failed:', error);
        }
    }

    function rememberNotification(notificationId) {
        if (!lastSeenNotificationStorageKey || !notificationId) {
            return;
        }

        localStorage.setItem(lastSeenNotificationStorageKey, String(notificationId));
    }

    function maybeToast(title, description) {
        if (typeof window.toastMagic === 'undefined') {
            return;
        }

        window.toastMagic.success(title, description || '');
    }

    async function refreshUnreadCount() {
        if (!config.unreadCountUrl || !badge) {
            return;
        }

        if (unreadRefreshPromise) {
            return unreadRefreshPromise;
        }

        unreadRefreshPromise = (async function () {
            const response = await fetch(config.unreadCountUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const responseData = payload.data || payload;
            const nextCount = parseInt(responseData.count ?? responseData.total ?? 0, 10) || 0;
            const previousCount = parseInt(localStorage.getItem(unreadCountStorageKey) || '0', 10) || 0;
            const latestNotifications = Array.isArray(responseData.latest_notifications)
                ? responseData.latest_notifications
                : (Array.isArray(responseData.notifications) ? responseData.notifications : []);
            const latestNotification = responseData.latest_notification ?? latestNotifications[0] ?? null;
            const latestNotificationId = parseInt(latestNotification?.id ?? 0, 10) || 0;
            const lastSeenNotificationId = parseInt(localStorage.getItem(lastSeenNotificationStorageKey) || '0', 10) || 0;

            setBadgeCount(nextCount);
            renderNotificationList(latestNotifications);
            localStorage.setItem(unreadCountStorageKey, String(nextCount));

            if (nextCount <= previousCount) {
                return;
            }

            const diff = nextCount - previousCount;
            const title = latestNotification?.title || (diff === 1
                ? (config.text?.defaultNotificationTitle || 'New notification')
                : `${diff} new notifications`);
            const description = latestNotification?.description || latestNotification?.content || '';
            const url = latestNotification?.url || '';

            if (latestNotificationId && latestNotificationId <= lastSeenNotificationId) {
                return;
            }

            maybeToast(title, description);
            await showBrowserNotification(title, url, latestNotificationId, description);
            rememberNotification(latestNotificationId);
        })().catch((error) => {
            console.error('[Push] Failed to refresh unread count:', error);
        }).finally(() => {
            unreadRefreshPromise = null;
        });

        return unreadRefreshPromise;
    }

    async function bootstrapAuthenticatedNotifications() {
        if (!userId || !badge) {
            return;
        }

        try {
            if (Notification.permission === 'default' && !sessionStorage.getItem(notificationPermissionPromptKey)) {
                sessionStorage.setItem(notificationPermissionPromptKey, '1');
                await ensureNotificationPermission({ requestIfDefault: true });
            }

            const registration = await ensureServiceWorkerRegistration();
            await syncFirebaseConfigToServiceWorker(registration);

            const permission = await ensureNotificationPermission({ requestIfDefault: false });

            if (permission === 'granted') {
                const messaging = await ensureFirebaseMessaging();

                if (messaging && !window.__ADMIN_PUSH_ON_MESSAGE_BOUND__) {
                    window.__ADMIN_PUSH_ON_MESSAGE_BOUND__ = true;

                    // onMessage only fires when the tab IS in the foreground.
                    // Echo's .listen() already handles toast + badge + dropdown in that case.
                    // For push-only messages there is no Echo/database notification,
                    // so we surface those directly here.
                    // (When the tab is in the background/closed the service worker handles the OS push.)
                    messaging.onMessage(async function (payload) {
                        const notification = normalizeNotificationPayload(payload);

                        if (!notification.notificationId) {
                            maybeToast(notification.title, notification.description);
                            await showBrowserNotification(
                                notification.title,
                                notification.url,
                                notification.notificationId,
                                notification.description
                            );
                        }

                        setTimeout(refreshUnreadCount, 300);
                    });
                }
            }
        } catch (error) {
            console.error('[Push] Authenticated notification bootstrap failed:', error);
        }

        refreshUnreadCount();
        window.setInterval(refreshUnreadCount, 10000);

        if (window.Echo && typeof window.Echo.private === 'function') {
            try {
                window.Echo.private(`notifications.${userId}`)
                    .listen('.notification.created', async function (payload) {
                        const notification = normalizeNotificationPayload(payload);
                        const currentCount = parseInt(badge.textContent || '0', 10) || 0;

                        setBadgeCount(currentCount + notification.unreadCountDelta);
                        prependRealtimeNotification(notification);
                        maybeToast(notification.title, notification.description);
                        await showBrowserNotification(
                            notification.title,
                            notification.url,
                            notification.notificationId,
                            notification.description
                        );
                        rememberNotification(notification.notificationId);
                        setTimeout(refreshUnreadCount, 1500);
                    });
            } catch (error) {
                console.error('[Push] Echo subscription failed:', error);
            }
        }
    }

    async function prepareLoginPushFields() {
        if (!loginForm || !fcmTokenField || !deviceTypeField) {
            return;
        }

        deviceTypeField.value = 'web';
        if (deviceNameField) {
            deviceNameField.value = getBrowserInfo();
        }

        try {
            const registration = await ensureServiceWorkerRegistration();
            await syncFirebaseConfigToServiceWorker(registration);
        } catch (error) {
            console.error('[Push] Login pre-bootstrap failed:', error);
        }

        if (Notification.permission === 'granted') {
            loginTokenPromise = getBrowserFcmToken({ requestPermission: false })
                .then((token) => {
                    if (token) {
                        fcmTokenField.value = token;
                    }

                    return token;
                })
                .catch(() => null);
        }

        loginForm.addEventListener('submit', async function (event) {
            if (loginForm.dataset.pushPrepared === '1') {
                return;
            }

            loginForm.dataset.pushPrepared = '1';
            event.preventDefault();

            try {
                const token = await (loginTokenPromise || getBrowserFcmToken({ requestPermission: true }));

                if (token) {
                    fcmTokenField.value = token;
                }
            } catch (error) {
                console.error('[Push] Login token capture failed:', error);
            }

            loginForm.submit();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        prepareLoginPushFields();
        bootstrapAuthenticatedNotifications();
    });
})();
