<?php
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/content.php';
?>

<script>
function handleSort(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', value);
    url.searchParams.delete('page');

    if (typeof window.loadProductsPage === 'function') {
        window.loadProductsPage(url.toString(), { pushState: true });
        return;
    }

    window.location.href = url.toString();
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const MIN = 0;
    const MAX = 5000000;
    let activeProductsRequest = null;

    function getProductsPageContent() {
        return document.getElementById('productsPageContent');
    }

    function getNormalizedProductsUrl(inputUrl) {
        const url = new URL(inputUrl || window.location.href, window.location.origin);
        url.searchParams.delete('ajax');
        return url;
    }

    function formatPrice(price) {
        if (price >= 1000000) {
            return (price / 1000000).toFixed(price % 1000000 === 0 ? 0 : 1) + 'tr';
        }

        if (price >= 1000) {
            return (price / 1000).toFixed(0) + 'k';
        }

        return price + 'đ';
    }

    function syncSearchInputs(nextValue) {
        document.querySelectorAll('[data-products-search-form="true"] input[name="search"]').forEach((input) => {
            input.value = nextValue;
        });
    }

    function updateTrack(sourceElement = null) {
        const minSlider = document.getElementById('minPriceSlider');
        const maxSlider = document.getElementById('maxPriceSlider');
        const minDisplay = document.getElementById('minPriceDisplay');
        const maxDisplay = document.getElementById('maxPriceDisplay');
        const sliderTrack = document.getElementById('priceSliderTrack');

        if (!minSlider || !maxSlider || !minDisplay || !maxDisplay || !sliderTrack) {
            return;
        }

        let minVal = parseInt(minSlider.value, 10);
        let maxVal = parseInt(maxSlider.value, 10);

        if (minVal >= maxVal) {
            if (sourceElement === minSlider) {
                minVal = Math.max(MIN, maxVal - 10000);
                minSlider.value = String(minVal);
            } else {
                maxVal = Math.min(MAX, minVal + 10000);
                maxSlider.value = String(maxVal);
            }
        }

        const minPercent = (Number(minSlider.value) / MAX) * 100;
        const maxPercent = (Number(maxSlider.value) / MAX) * 100;

        sliderTrack.style.left = minPercent + '%';
        sliderTrack.style.width = (maxPercent - minPercent) + '%';
        minDisplay.textContent = formatPrice(parseInt(minSlider.value, 10));
        maxDisplay.textContent = formatPrice(parseInt(maxSlider.value, 10));
    }

    function initializeProductsPageControls() {
        const content = getProductsPageContent();
        if (!content) {
            return;
        }

        const currentUrl = getNormalizedProductsUrl(window.location.href);
        syncSearchInputs(currentUrl.searchParams.get('search') || '');
        updateTrack();

        if (content.dataset.pageTitle) {
            document.title = content.dataset.pageTitle;
        }
    }

    async function loadProductsPage(nextUrl, options = {}) {
        const content = getProductsPageContent();
        if (!content) {
            window.location.href = getNormalizedProductsUrl(nextUrl).toString();
            return;
        }

        const targetUrl = getNormalizedProductsUrl(nextUrl);
        const ajaxUrl = new URL(targetUrl.toString());
        ajaxUrl.searchParams.set('ajax', '1');

        if (activeProductsRequest) {
            activeProductsRequest.abort();
        }

        const controller = new AbortController();
        activeProductsRequest = controller;
        content.classList.add('opacity-60', 'pointer-events-none', 'transition-opacity');
        content.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(ajaxUrl.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error('Unable to load products');
            }

            const html = await response.text();
            if (controller.signal.aborted) {
                return;
            }

            content.outerHTML = html;
            initializeProductsPageControls();

            const historyUrl = targetUrl.pathname + targetUrl.search;
            if (options.pushState) {
                window.history.pushState({ path: historyUrl }, '', historyUrl);
            } else if (options.replaceState) {
                window.history.replaceState({ path: historyUrl }, '', historyUrl);
            }

            if (options.closeMobileSearch) {
                const mobileModal = document.getElementById('mobileSearchModal');
                if (mobileModal && !mobileModal.classList.contains('hidden') && typeof toggleMobileSearch === 'function') {
                    toggleMobileSearch();
                }
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = targetUrl.toString();
            }
        } finally {
            if (activeProductsRequest === controller) {
                activeProductsRequest = null;
                const nextContent = getProductsPageContent();
                if (nextContent) {
                    nextContent.classList.remove('opacity-60', 'pointer-events-none', 'transition-opacity');
                    nextContent.removeAttribute('aria-busy');
                }
            }
        }
    }

    window.loadProductsPage = loadProductsPage;
    window.history.replaceState(
        { path: window.location.pathname + window.location.search },
        '',
        window.location.pathname + window.location.search
    );

    initializeProductsPageControls();

    document.addEventListener('submit', function (event) {
        const target = event.target;
        if (!(target instanceof HTMLFormElement) || target.dataset.productsSearchForm !== 'true') {
            return;
        }

        if (!getProductsPageContent()) {
            return;
        }

        event.preventDefault();

        const nextUrl = getNormalizedProductsUrl(window.location.href);
        const formData = new FormData(target);

        formData.forEach((value, key) => {
            const normalizedValue = String(value).trim();
            if (normalizedValue === '') {
                nextUrl.searchParams.delete(key);
                return;
            }

            nextUrl.searchParams.set(key, normalizedValue);
        });

        nextUrl.searchParams.delete('page');
        syncSearchInputs(String(formData.get('search') || '').trim());

        loadProductsPage(nextUrl.toString(), {
            pushState: true,
            closeMobileSearch: !!target.closest('#mobileSearchModal'),
        });
    });

    document.addEventListener('click', function (event) {
        const ajaxLink = event.target instanceof Element ? event.target.closest('[data-products-ajax-link="true"]') : null;
        if (ajaxLink instanceof HTMLAnchorElement) {
            if (
                event.defaultPrevented ||
                event.button !== 0 ||
                ajaxLink.target === '_blank' ||
                event.metaKey ||
                event.ctrlKey ||
                event.shiftKey ||
                event.altKey
            ) {
                return;
            }

            if (!getProductsPageContent()) {
                return;
            }

            event.preventDefault();
            loadProductsPage(ajaxLink.href, {
                pushState: true,
                closeMobileSearch: !!ajaxLink.closest('#mobileSearchModal'),
            });
            return;
        }

        const applyButton = event.target instanceof Element ? event.target.closest('#applyPriceFilter') : null;
        if (applyButton) {
            const minSlider = document.getElementById('minPriceSlider');
            const maxSlider = document.getElementById('maxPriceSlider');
            if (!minSlider || !maxSlider) {
                return;
            }

            const minPrice = parseInt(minSlider.value, 10);
            const maxPrice = parseInt(maxSlider.value, 10);
            const url = getNormalizedProductsUrl(window.location.href);

            if (minPrice > MIN) {
                url.searchParams.set('min_price', String(minPrice));
            } else {
                url.searchParams.delete('min_price');
            }

            if (maxPrice < MAX) {
                url.searchParams.set('max_price', String(maxPrice));
            } else {
                url.searchParams.delete('max_price');
            }

            url.searchParams.delete('page');
            loadProductsPage(url.toString(), { pushState: true });
            return;
        }

        const resetButton = event.target instanceof Element ? event.target.closest('#resetPrice') : null;
        if (resetButton) {
            const url = getNormalizedProductsUrl(window.location.href);
            url.searchParams.delete('min_price');
            url.searchParams.delete('max_price');
            url.searchParams.delete('page');
            loadProductsPage(url.toString(), { pushState: true });
        }
    });

    document.addEventListener('input', function (event) {
        if (!(event.target instanceof HTMLInputElement)) {
            return;
        }

        if (event.target.id === 'minPriceSlider' || event.target.id === 'maxPriceSlider') {
            updateTrack(event.target);
        }
    });

    window.addEventListener('popstate', function () {
        if (!getProductsPageContent()) {
            return;
        }

        loadProductsPage(window.location.href, { replaceState: true });
    });
});
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
