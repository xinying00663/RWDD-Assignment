(function () {
    const selectionKey = "ecogoSelectedMedia";

    function handleFilter(select) {
        const gridId = select.dataset.target;
        const grid = document.getElementById(gridId);
        if (!grid) {
            return;
        }

        const cards = Array.from(grid.querySelectorAll(".media-card"));

        function applyFilter(value) {
            cards.forEach((card) => {
                const matches = value === "all" || card.dataset.category === value;
                card.style.display = matches ? "" : "none";
                card.setAttribute("aria-hidden", matches ? "false" : "true");
            });
        }

        select.addEventListener("change", (event) => {
            applyFilter(event.target.value);
        });

        applyFilter(select.value || "all");
    }

    function captureMediaSelection(link) {
        link.addEventListener("click", () => {
            const payload = {
                page: link.dataset.page || "",
                title: link.dataset.title || "",
                description: link.dataset.description || "",
                category: link.dataset.category || "",
                mediaType: link.dataset.mediaType || "image",
                mediaSrc: link.dataset.mediaSrc || "",
                poster: link.dataset.poster || "",
                alt: link.dataset.alt || "",
                duration: link.dataset.duration || "",
                uploader: link.dataset.uploader || "EcoGo Community",
                tags: (link.dataset.tags || "").split(",").map((item) => item.trim()).filter(Boolean)
            };
            localStorage.setItem(selectionKey, JSON.stringify(payload));
        });
    }

    document.querySelectorAll(".media-filter").forEach(handleFilter);
    document.querySelectorAll(".media-card__link").forEach(captureMediaSelection);
})();
