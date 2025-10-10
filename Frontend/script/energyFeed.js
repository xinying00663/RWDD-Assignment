/**
 * Injects saved energy uploads into the energy feed.
 */
(function () {
    const STORAGE_KEY = "ecogoEnergyUploads";
    const FLASH_KEY = "ecogoEnergyUploadFlash";

    function categoryLabel(value) {
        switch (value) {
            case "habit":
                return "Daily habit";
            case "planning":
                return "Planning";
            case "tutorial":
            default:
                return "Tutorial";
        }
    }

    /**
     * Creates the thumbnail element for an energy card.
     * The indicator text can be overridden for seeded items so we can label guides/playbooks.
     */
    function createMediaElement(entry, indicatorOverride) {
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
            video.setAttribute("aria-label", entry.title || "Energy tip video");
            wrapper.appendChild(video);
            const indicator = document.createElement("span");
            indicator.className = "media-indicator";
            indicator.textContent = indicatorOverride || "Video";
            wrapper.appendChild(indicator);
        } else {
            const image = document.createElement("img");
            image.src = entry.mediaSrc;
            image.alt = entry.imageAlt || entry.title || "Energy tip image";
            wrapper.appendChild(image);
            const indicator = document.createElement("span");
            indicator.className = "media-indicator";
            indicator.textContent = indicatorOverride || "Idea";
            wrapper.appendChild(indicator);
        }

        return wrapper;
    }

    /**
     * Builds the body content for a seeded energy card.
     */
    function createSeedCardBody(seed) {
        const body = document.createElement("div");
        body.className = "card-body";

        const tag = document.createElement("span");
        tag.className = "card-tag";
        tag.textContent = seed.tagLabel || categoryLabel(seed.category);
        body.appendChild(tag);

        const title = document.createElement("h3");
        title.textContent = seed.title || "Energy tip";
        body.appendChild(title);

        const summary = document.createElement("p");
        summary.textContent = seed.summary || "No summary provided.";
        body.appendChild(summary);

        const meta = document.createElement("div");
        meta.className = "card-meta";

        const notes = Array.isArray(seed.metaNotes) ? seed.metaNotes : [];
        notes.forEach((note) => {
            const isObject = note && typeof note === "object";
            const text = isObject ? note.text : note;
            if (!text) {
                return;
            }
            const span = document.createElement("span");
            if (isObject && note.className) {
                span.className = note.className;
            }
            span.textContent = text;
            meta.appendChild(span);
        });

        if (meta.children.length) {
            body.appendChild(meta);
        }

        return body;
    }

    /**
     * Injects one seeded entry into the energy grid.
     */
    function createSeedCard(seed) {
        const article = document.createElement("article");
        article.className = "media-card";
        article.dataset.category = seed.category || "tutorial";

        const link = document.createElement("a");
        link.className = "media-card__link";
        link.href = "mediaDetail.html";
        link.dataset.page = "energy";
        link.dataset.title = seed.title || "";
        link.dataset.description = seed.detailDescription || seed.summary || "";
        link.dataset.category = seed.category || "";
        link.dataset.mediaType = seed.mediaType || "image";
        link.dataset.mediaSrc = seed.mediaSrc || "";
        if (seed.poster) {
            link.dataset.poster = seed.poster;
        }
        link.dataset.alt = seed.imageAlt || seed.title || "";
        link.dataset.uploader = seed.uploader || "EcoGo neighbour";
        link.dataset.mediaMime = seed.mediaMime || "";
        link.dataset.duration = seed.duration || "";

        link.appendChild(createMediaElement(seed, seed.mediaIndicator));
        link.appendChild(createSeedCardBody(seed));

        if (window.ecogoMediaFeed && typeof window.ecogoMediaFeed.registerLink === "function") {
            window.ecogoMediaFeed.registerLink(link);
        }

        article.appendChild(link);
        return article;
    }

    function createCardBody(entry) {
        const body = document.createElement("div");
        body.className = "card-body";

        const tag = document.createElement("span");
        tag.className = "card-tag";
        tag.textContent = categoryLabel(entry.category);
        body.appendChild(tag);

        const title = document.createElement("h3");
        title.textContent = entry.title || "Energy tip";
        body.appendChild(title);

        const summary = document.createElement("p");
        summary.textContent = entry.summary || "No summary provided.";
        body.appendChild(summary);

        const meta = document.createElement("div");
        meta.className = "card-meta";

        if (entry.duration) {
            const duration = document.createElement("span");
            duration.className = "duration";
            duration.textContent = entry.duration;
            meta.appendChild(duration);
        }

        const contributor = document.createElement("span");
        contributor.textContent = entry.contributor || "EcoGo neighbour";
        meta.appendChild(contributor);

        if (entry.link) {
            const resource = document.createElement("a");
            resource.href = entry.link;
            resource.target = "_blank";
            resource.rel = "noopener";
            resource.textContent = "View resource";
            meta.appendChild(resource);
        }

        body.appendChild(meta);
        return body;
    }

    function createRemoveButton(entry, article) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "card-remove";
        button.textContent = "Remove";
        button.setAttribute("aria-label", `Remove ${entry.title || "energy tip"}`);
        button.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            window.ecogoUploads.removeEntry(STORAGE_KEY, entry.id);
            article.remove();
            if (window.ecogoMediaFeed && typeof window.ecogoMediaFeed.refresh === "function") {
                window.ecogoMediaFeed.refresh();
            }
        });
        return button;
    }

    function createMediaCard(entry) {
        const article = document.createElement("article");
        article.className = "media-card media-card--upload";
        article.dataset.category = entry.category || "tutorial";

        const link = document.createElement("a");
        link.className = "media-card__link";
        link.href = "mediaDetail.html";
        link.dataset.page = "energy";
        link.dataset.title = entry.title || "";
        link.dataset.description = entry.summary || "";
        link.dataset.category = entry.category || "";
        link.dataset.mediaType = entry.mediaType || "image";
        link.dataset.mediaSrc = entry.mediaSrc || "";
        link.dataset.poster = entry.mediaType === "video" ? entry.mediaSrc || "" : "";
        link.dataset.alt = entry.title || "";
        link.dataset.uploader = entry.contributor || "EcoGo neighbour";
        link.dataset.mediaMime = entry.mimeType || "";
        link.dataset.duration = entry.duration || "";

        link.appendChild(createMediaElement(entry));
        link.appendChild(createCardBody(entry));

        if (window.ecogoMediaFeed && typeof window.ecogoMediaFeed.registerLink === "function") {
            window.ecogoMediaFeed.registerLink(link);
        }

        article.appendChild(createRemoveButton(entry, article));
        article.appendChild(link);
        return article;
    }

    function createFlashBanner(data) {
        const banner = document.createElement("div");
        banner.className = "feed-flash feed-flash--energy";
        const heading = document.createElement("strong");
        heading.textContent = "Tip shared!";
        const message = document.createElement("span");
        const title = data && data.title ? `"${data.title}"` : "Your tip";
        message.textContent = ` ${title} is now live on the Energy hub.`;
        banner.append(heading, message);
        return banner;
    }

    /**
     * Renders the seeded catalogue entries, if available.
     */
    function renderSeedEntries(grid) {
        if (!window.ecogoContentCatalog || typeof window.ecogoContentCatalog.listEnergyTips !== "function") {
            return false;
        }
        const seeds = window.ecogoContentCatalog.listEnergyTips();
        if (!Array.isArray(seeds) || !seeds.length) {
            return false;
        }
        const fragment = document.createDocumentFragment();
        seeds.forEach((seed) => {
            fragment.appendChild(createSeedCard(seed));
        });
        grid.appendChild(fragment);
        return true;
    }

    document.addEventListener("DOMContentLoaded", () => {
        const grid = document.getElementById("energyGrid");
        if (!grid) {
            return;
        }

        let shouldRefresh = renderSeedEntries(grid);

        const uploads = window.ecogoUploads.loadEntries(STORAGE_KEY);
        if (uploads.length) {
            const fragment = document.createDocumentFragment();
            uploads.forEach((entry) => {
                fragment.appendChild(createMediaCard(entry));
            });
            grid.prepend(fragment);

            shouldRefresh = true;
        }

        if (shouldRefresh && window.ecogoMediaFeed && typeof window.ecogoMediaFeed.refresh === "function") {
            window.ecogoMediaFeed.refresh();
        }

        const flashData = window.ecogoUploads.consumeFlash(FLASH_KEY);
        if (flashData) {
            const tabsCard = document.querySelector(".tabs-card");
            if (tabsCard) {
                tabsCard.insertBefore(createFlashBanner(flashData), tabsCard.firstChild);
            }
        }
    });
})();
