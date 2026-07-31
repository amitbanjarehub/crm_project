document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('leadFilterForm');
    const searchInput = document.getElementById('leadSearchInput');
    const clearSearchButton = document.getElementById('clearLeadSearch');
    const exportButton = document.getElementById('leadExportButton');

    if (!filterForm || !searchInput) {
        return;
    }

    let searchTimer = null;
    let activeRequest = null;

    /**
     * Search input ke according clear button show/hide karega.
     */
    function updateClearButton() {
        if (!clearSearchButton) {
            return;
        }

        clearSearchButton.classList.toggle(
            'is-hidden',
            searchInput.value.trim() === ''
        );
    }

    /**
     * Form ke filters se GET URL banayega.
     */
    function buildFilterUrl() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);

        // Filter change hone par first page se result dikhana hai.
        params.delete('page');

        // Empty query parameters URL se remove karo.
        Array.from(params.keys()).forEach(function (key) {
            const values = params.getAll(key);

            const isEmpty = values.every(function (value) {
                return String(value).trim() === '';
            });

            if (isEmpty) {
                params.delete(key);
            }
        });

        const queryString = params.toString();

        return queryString
            ? filterForm.action + '?' + queryString
            : filterForm.action;
    }

    /**
 * Current Lead filters ke according
 * Excel export button URL update karega.
 */
    function updateExportButton() {
        if (!exportButton) {
            return;
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);

        /*
         * Pagination aur per-page export query
         * me required nahi hain.
         */
        params.delete('page');
        params.delete('per_page');

        Array.from(params.keys()).forEach(function (key) {
            const values = params.getAll(key);

            const isEmpty = values.every(function (value) {
                return String(value).trim() === '';
            });

            if (isEmpty) {
                params.delete(key);
            }
        });

        const baseUrl =
            exportButton.dataset.exportUrl;

        const queryString =
            params.toString();

        exportButton.href = queryString
            ? baseUrl + '?' + queryString
            : baseUrl;
    }

    /**
     * Page reload ki jagah sirf table/pagination replace karega.
     */
    async function loadLeadResults(url) {
        const resultsArea = document.getElementById('leadResultsArea');

        if (!resultsArea) {
            window.location.href = url;
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();

        resultsArea.classList.add('is-loading');

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                signal: activeRequest.signal,
            });

            if (!response.ok) {
                throw new Error('Unable to load lead results.');
            }

            const html = await response.text();

            const parser = new DOMParser();
            const documentResult = parser.parseFromString(
                html,
                'text/html'
            );

            const newResultsArea =
                documentResult.getElementById('leadResultsArea');

            if (!newResultsArea) {
                throw new Error('Lead result section not found.');
            }

            resultsArea.innerHTML = newResultsArea.innerHTML;

            window.history.replaceState(
                {},
                '',
                url
            );
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = url;
            }
        } finally {
            const currentResultsArea =
                document.getElementById('leadResultsArea');

            if (currentResultsArea) {
                currentResultsArea.classList.remove('is-loading');
            }
        }
    }

    /**
     * Current filters ke according result load karega.
     */
    // function applyFilters() {
    //     loadLeadResults(buildFilterUrl());
    // }

    function applyFilters() {
        updateExportButton();

        loadLeadResults(
            buildFilterUrl()
        );
    }

    /**
     * Search typing par debounce ke saath live filtering.
     */
    searchInput.addEventListener('input', function () {
        updateClearButton();

        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            applyFilters();
        }, 350);
    });

    /**
     * Enter press karne par normal page reload rokega.
     */
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();

        clearTimeout(searchTimer);
        applyFilters();
    });

    /**
     * Search clear button.
     */
    if (clearSearchButton) {
        clearSearchButton.addEventListener('click', function () {
            searchInput.value = '';

            updateClearButton();
            searchInput.focus();

            clearTimeout(searchTimer);
            applyFilters();
        });
    }

    /**
     * Dropdown change aur per-page select ke liye event delegation.
     */
    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('lead-auto-filter')) {
            applyFilters();
            return;
        }

        if (event.target.id === 'leadPerPageSelect') {
            const perPageInput =
                document.getElementById('leadPerPageInput');

            if (perPageInput) {
                perPageInput.value = event.target.value;
            }

            applyFilters();
        }
    });

    /**
     * Pagination ko AJAX se load karega.
     */
    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest(
            '#leadResultsArea .custom-pagination a'
        );

        if (!paginationLink) {
            return;
        }

        event.preventDefault();

        loadLeadResults(paginationLink.href);
    });

    updateClearButton();
    updateExportButton();
});