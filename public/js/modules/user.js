document.addEventListener("DOMContentLoaded", function () {
    const perPageSelect = document.getElementById("perPageSelect");
    const searchInput = document.getElementById("userSearchInput");

    if (perPageSelect) {
        perPageSelect.addEventListener("change", function () {
            const url = new URL(window.location.href);

            url.searchParams.set("per_page", this.value);
            url.searchParams.set("page", 1);

            window.location.href = url.toString();
        });
    }

    if (searchInput) {
        let searchTimer = null;

        searchInput.addEventListener("input", function () {
            clearTimeout(searchTimer);

            searchTimer = setTimeout(function () {
                const url = new URL(window.location.href);
                const searchValue = searchInput.value.trim();

                if (searchValue !== "") {
                    url.searchParams.set("search", searchValue);
                } else {
                    url.searchParams.delete("search");
                }

                url.searchParams.set("page", 1);

                window.location.href = url.toString();
            }, 500);
        });
    }
});