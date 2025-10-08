/**
 * Injects saved swap listings into the swap feed.
 */
(function () {
    const STORAGE_KEY = "ecogoSwapListings";
    const FLASH_KEY = "ecogoSwapFlash";
    const FALLBACK_IMAGE = "Pictures/landingPage/swap-item-pic.jpg";
    const SEED_SWAP_LISTINGS = [
        {
            category: "home-grown",
            title: "Lemongrass bundles",
            description: "Freshly harvested stalks from this week’s balcony trim. Perfect for teas or curries.",
            imageSrc: "Pictures/landingPage/swap-item-pic.jpg",
            imageAlt: "Fresh lemongrass bundled with twine"
        },
        {
            category: "eco-friendly",
            title: "Reusable produce bags",
            description: "Set of six mesh bags sewn from leftover muslin. Trade for compost or herbs.",
            imageSrc: "Pictures/landingPage/swap-item-pic.jpg",
            imageAlt: "Stack of reusable produce bags on a table"
        },
        {
            category: "home-grown",
            title: "Curry leaf cuttings",
            description: "Rooted cuttings ready for potting. Looking for homemade snacks or glass jars.",
            imageSrc: "Pictures/landingPage/swap-item-pic.jpg",
            imageAlt: "Curry leaf plant cuttings in small pots"
        }
    ];

    function categoryLabel(value) {
        switch (value) {
            case "eco-friendly":
                return "Eco-friendly";
            case "home-grown":
            default:
                return "Home-grown";
        }
    }

    /**
     * Builds the media preview for swap cards.
     */
    function createMediaElement(entry) {
        const wrapper = document.createElement("div");
        wrapper.className = "card-media";

        if (entry.mediaType === "video") {
            const video = document.createElement("video");
            video.src = entry.mediaSrc;
            if (entry.poster) {
                video.poster = entry.poster;
            }
            video.muted = true;
            video.loop = true;
            video.playsInline = true;
            video.setAttribute("aria-label", entry.title || "Swap item video");
            wrapper.appendChild(video);
        } else {
            const image = document.createElement("img");
            image.src = entry.mediaSrc;
            image.alt = entry.imageAlt || entry.title || "Swap item image";
            wrapper.appendChild(image);
        }

        return wrapper;
    }

    function createCardBody(entry) {
        const body = document.createElement("div");
        body.className = "card-body";

        const tag = document.createElement("span");
        tag.className = "card-tag";
        tag.textContent = categoryLabel(entry.category);
        body.appendChild(tag);

        const title = document.createElement("h3");
        title.textContent = entry.title || "Swap listing";
        body.appendChild(title);

        const description = document.createElement("p");
        description.textContent = entry.details || "No description provided yet.";
        body.appendChild(description);

        if (entry.condition || entry.preferred) {
            const meta = document.createElement("div");
            meta.className = "swap-card__meta";
            if (entry.condition) {
                const condition = document.createElement("span");
                condition.textContent = `Condition: ${entry.condition}`;
                meta.appendChild(condition);
            }
            if (entry.preferred) {
                const wish = document.createElement("span");
                wish.textContent = `Hoping for: ${entry.preferred}`;
                meta.appendChild(wish);
            }
            body.appendChild(meta);
        }

        const cta = document.createElement("a");
        cta.className = "swap-card__cta";
        cta.href = "swapConfirm.html";
        cta.dataset.swapTrigger = "";
        cta.textContent = "Message to swap";
        body.appendChild(cta);

        return { body, link: cta };
    }

    function createRemoveButton(entry, article) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "card-remove";
        button.textContent = "Remove";
        button.setAttribute("aria-label", `Remove ${entry.title || "swap listing"}`);
        button.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            window.ecogoUploads.removeEntry(STORAGE_KEY, entry.id);
            article.remove();
            refreshFilters();
        });
        return button;
    }

    function createSwapCard(entry) {
        const article = document.createElement("article");
        article.className = "media-card swap-card media-card--upload";
        article.dataset.category = entry.category || "home-grown";
        article.dataset.itemTitle = entry.title || "";
        article.dataset.itemDescription = entry.details || "";
        article.dataset.itemImage =
            entry.mediaType === "image" ? entry.mediaSrc || FALLBACK_IMAGE : FALLBACK_IMAGE;

        article.appendChild(createRemoveButton(entry, article));
        article.appendChild(createMediaElement(entry));
        const { body, link } = createCardBody(entry);
        article.appendChild(body);

        if (window.ecogoSwap && typeof window.ecogoSwap.registerLink === "function") {
            window.ecogoSwap.registerLink(link);
        }

        return article;
    }

    /**
     * Creates a seeded swap listing so the board is populated on first load.
     */
    function createSeedSwapCard(seed) {
        const article = document.createElement("article");
        article.className = "media-card swap-card";
        article.dataset.category = seed.category || "home-grown";
        article.dataset.itemTitle = seed.title || "";
        article.dataset.itemDescription = seed.description || "";
        article.dataset.itemImage = seed.imageSrc || FALLBACK_IMAGE;

        article.appendChild(
            createMediaElement({
                mediaType: "image",
                mediaSrc: seed.imageSrc || FALLBACK_IMAGE,
                title: seed.title || "Swap listing",
                imageAlt: seed.imageAlt || seed.title || ""
            })
        );

        const { body, link } = createCardBody({
            category: seed.category || "home-grown",
            title: seed.title,
            details: seed.description
        });
        article.appendChild(body);

        if (window.ecogoSwap && typeof window.ecogoSwap.registerLink === "function") {
            window.ecogoSwap.registerLink(link);
        }

        return article;
    }

    function createFlashBanner(data) {
        const banner = document.createElement("div");
        banner.className = "feed-flash feed-flash--swap";
        const heading = document.createElement("strong");
        heading.textContent = "Listing posted!";
        const message = document.createElement("span");
        const title = data && data.title ? `"${data.title}"` : "Your listing";
        message.textContent = ` ${title} is now live in the swap feed.`;
        banner.append(heading, message);
        return banner;
    }

    function refreshFilters() {
        if (window.ecogoMediaFeed && typeof window.ecogoMediaFeed.refresh === "function") {
            window.ecogoMediaFeed.refresh();
        }
    }

    /**
     * Renders the seeded swap listings and returns true when entries were added.
     */
    function renderSeedEntries(grid) {
        const seeds = SEED_SWAP_LISTINGS;
        if (!Array.isArray(seeds) || !seeds.length) {
            return false;
        }
        const fragment = document.createDocumentFragment();
        seeds.forEach((seed) => {
            fragment.appendChild(createSeedSwapCard(seed));
        });
        grid.appendChild(fragment);
        return true;
    }

    document.addEventListener("DOMContentLoaded", () => {
        const grid = document.getElementById("swapItems");
        if (!grid) {
            return;
        }

        let shouldRefresh = renderSeedEntries(grid);

        const listings = window.ecogoUploads.loadEntries(STORAGE_KEY);
        if (listings.length) {
            const fragment = document.createDocumentFragment();
            listings.forEach((entry) => {
                fragment.appendChild(createSwapCard(entry));
            });
            grid.prepend(fragment);
            shouldRefresh = true;
        }

        if (shouldRefresh) {
            refreshFilters();
        }

        const flashData = window.ecogoUploads.consumeFlash(FLASH_KEY);
        if (flashData) {
            const section = document.querySelector(".tabs-card");
            if (section) {
                section.insertBefore(createFlashBanner(flashData), section.firstChild);
            }
        }
    });
})();
