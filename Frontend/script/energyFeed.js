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

    function createMediaElement(entry) {
        const wrapper = document.createElement("div");
        wrapper.className = "card-media";

        if (entry.mediaType === "video") {
            const video = document.createElement("video");
            video.src = entry.mediaSrc;
            video.muted = true;
            video.loop = true;
            video.playsInline = true;
            video.setAttribute("aria-label", entry.title || "Energy tip video");
            wrapper.appendChild(video);
            const indicator = document.createElement("span");
            indicator.className = "media-indicator";
            indicator.textContent = "Video";
            wrapper.appendChild(indicator);
        } else {
            const image = document.createElement("img");
            image.src = entry.mediaSrc;
            image.alt = entry.title || "Energy tip image";
            wrapper.appendChild(image);
            const indicator = document.createElement("span");
            indicator.className = "media-indicator";
            indicator.textContent = "Idea";
            wrapper.appendChild(indicator);
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

    document.addEventListener("DOMContentLoaded", () => {
        const grid = document.getElementById("energyGrid");
        if (!grid) {
            return;
        }

        const uploads = window.ecogoUploads.loadEntries(STORAGE_KEY);
        if (uploads.length) {
            const fragment = document.createDocumentFragment();
            uploads.forEach((entry) => {
                fragment.appendChild(createMediaCard(entry));
            });
            grid.prepend(fragment);

            if (window.ecogoMediaFeed && typeof window.ecogoMediaFeed.refresh === "function") {
                window.ecogoMediaFeed.refresh();
            }
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
