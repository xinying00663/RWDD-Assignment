(function () {
    const selectionKey = "ecogoSelectedMedia";

    function handleFilter(select) {
        const gridId = select.dataset.target;
        const grid = document.getElementById(gridId);
        if (!grid) {
            return;
        }

        function applyFilter(value) {
            const cards = Array.from(grid.querySelectorAll(".media-card"));
            cards.forEach((card) => {
                const wrapper = card.closest('.media-card-wrapper') || card;
                const matches = value === "all" || card.dataset.category === value;
                wrapper.style.display = matches ? "" : "none";
                wrapper.setAttribute("aria-hidden", matches ? "false" : "true");
            });
        }

        select.addEventListener("change", (event) => {
            applyFilter(event.target.value);
        });

        select.ecogoApplyFilter = applyFilter;
        applyFilter(select.value || "all");
    }

    function captureMediaSelection(link) {
        if (!link) {
            return;
        }
        link.addEventListener("click", () => {
            const payload = {
                page: link.dataset.page || "",
                title: link.dataset.title || "",
                description: link.dataset.description || "",
                category: link.dataset.category || "",
                mediaType: link.dataset.mediaType || "image",
                mediaSrc: link.dataset.mediaSrc || "",
                poster: link.dataset.poster || "",
                mediaMime: link.dataset.mediaMime || "",
                alt: link.dataset.alt || "",
                duration: link.dataset.duration || "",
                uploader: link.dataset.uploader || "EcoGo Community",
                tags: (link.dataset.tags || "").split(",").map((item) => item.trim()).filter(Boolean)
            };
            localStorage.setItem(selectionKey, JSON.stringify(payload));
        });
    }

    function refreshFilters() {
        document.querySelectorAll(".media-filter").forEach((select) => {
            if (typeof select.ecogoApplyFilter === "function") {
                select.ecogoApplyFilter(select.value || "all");
            }
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".media-filter").forEach(handleFilter);
        document.querySelectorAll(".media-card__link").forEach(captureMediaSelection);
    });

    window.ecogoMediaFeed = {
        refresh: refreshFilters,
        registerLink: captureMediaSelection
    };
})();
