(function() {
    'use strict';

    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function getSessionId() {
        let sessionId = localStorage.getItem('sessionId');
        if (!sessionId) {
            sessionId = generateUUID();
            localStorage.setItem('sessionId', sessionId);
        }
        return sessionId;
    }

    function getUserId() {
        // This is a placeholder. In a real application, you would get the user ID from a cookie or a global JS variable.
        return window.userId || null;
    }

    function trackEvent(eventType, eventData) {
        const payload = {
            event_type: eventType,
            session_id: getSessionId(),
            user_id: getUserId(),
            page_path: window.location.pathname,
            page_title: document.title,
            user_agent: navigator.userAgent,
            referrer_url: document.referrer,
            browser_language: navigator.language,
            device_type: getDeviceType(),
            custom_data: eventData
        };

        navigator.sendBeacon('/api/track_event.php', JSON.stringify(payload));
    }

    function getDeviceType() {
        const ua = navigator.userAgent;
        if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
            return "tablet";
        }
        if (/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
            return "mobile";
        }
        return "desktop";
    }

    // Track page view
    window.addEventListener('load', function() {
        trackEvent('page_view', {});
    });

    // Track product view
    if (window.location.pathname.includes('/product/')) {
        const productId = new URLSearchParams(window.location.search).get('id');
        if (productId) {
            trackEvent('product_view', { product_id: productId });
        }
    }

    // Track search
    const searchForm = document.querySelector('form[action="/search/"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(event) {
            const searchInput = searchForm.querySelector('input[name="query"]');
            if (searchInput) {
                trackEvent('search', { search_query: searchInput.value });
            }
        });
    }

    // Expose trackEvent to be used for other interactions
    window.trackEvent = trackEvent;

})();
