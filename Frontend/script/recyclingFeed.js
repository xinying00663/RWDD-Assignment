/**
 * Injects saved recycling programs onto the home page.
 */
(function () {
    const STORAGE_KEY = "ecogoRecyclingPrograms";
    const FLASH_KEY = "ecogoRecyclingFlash";

    // Builds the label that summarises when the program happens.
    function formatDateLabel(entry) {
        if (entry.dateLabel) {
            return entry.dateLabel;
        }
        const start = entry.startDate;
        const end = entry.endDate;
        if (start && end) {
            return `${start} - ${end}`;
        }
        if (start) {
            return `Starts ${start}`;
        }
        if (end) {
            return `Until ${end}`;
        }
        return "Date TBC";
    }

    function formatSubmittedAt(createdAt) {
        if (!createdAt) {
            return "";
        }
        const submittedDate = new Date(createdAt);
        if (Number.isNaN(submittedDate.getTime())) {
            return "";
        }
        return `Submitted on ${submittedDate.toLocaleDateString()}`;
    }

    function createHighlightsList(entry) {
        const list = document.createElement("ul");

        const locationItem = document.createElement("li");
        locationItem.innerHTML = `<strong>Location:</strong> ${entry.location || "TBC"}`;
        list.appendChild(locationItem);

        const dateItem = document.createElement("li");
        dateItem.innerHTML = `<strong>When:</strong> ${formatDateLabel(entry)}`;
        list.appendChild(dateItem);

        return list;
    }

    // Builds a highlight list for pre-seeded programs using the metadata in the catalog.
    function createSeedHighlights(program) {
        const list = document.createElement("ul");
        const highlights = Array.isArray(program.highlights) && program.highlights.length
            ? program.highlights
            : [
                { label: "Location", value: program.location || "TBC" },
                { label: "Meet-ups", value: program.commitment || "TBC" }
            ];

        highlights.slice(0, 2).forEach((item) => {
            const listItem = document.createElement("li");
            listItem.innerHTML = `<strong>${item.label}:</strong> ${item.value || "TBC"}`;
            list.appendChild(listItem);
        });

        return list;
    }

    function createActionsRow(entry) {
        const actions = document.createElement("div");
        actions.className = "program-card__actions";

        if (entry.id) {
            const viewLink = document.createElement("a");
            viewLink.className = "program-card__link";
            viewLink.href = `programDetail.html?program=${encodeURIComponent(entry.id)}`;
            viewLink.textContent = "View details & register";
            actions.appendChild(viewLink);
        }

        const posted = document.createElement("span");
        posted.className = "program-card__posted";
        posted.textContent = "Community submission";
        actions.appendChild(posted);

        const timestamp = formatSubmittedAt(entry.createdAt);
        if (timestamp) {
            const postedTime = document.createElement("span");
            postedTime.className = "program-card__posted-time";
            postedTime.textContent = timestamp;
            actions.appendChild(postedTime);
        }

        return actions;
    }

    // Generates the call-to-action row for seeded programs.
    function createSeedActions(program) {
        const actions = document.createElement("div");
        actions.className = "program-card__actions";

        const viewLink = document.createElement("a");
        viewLink.className = "program-card__link";
        viewLink.href = `programDetail.html?program=${encodeURIComponent(program.id)}`;
        viewLink.textContent = "View details & register";
        actions.appendChild(viewLink);

        if (program.spots) {
            const spots = document.createElement("span");
            spots.className = "program-card__spots";
            spots.textContent = program.spots;
            actions.appendChild(spots);
        }

        return actions;
    }

    function createRemoveButton(entry, article) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "card-remove";
        button.textContent = "Remove";
        button.setAttribute("aria-label", `Remove ${entry.name || "recycling program"}`);
        button.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            window.ecogoUploads.removeEntry(STORAGE_KEY, entry.id);
            article.remove();
        });
        return button;
    }

    function createProgramCard(entry) {
        const article = document.createElement("article");
        article.className = "highlight-card program-card program-card--upload";
        article.dataset.programId = entry.id || "";

        // Expose the remove button so the uploader can clear their submission from the feed.
        article.appendChild(createRemoveButton(entry, article));

        const meta = document.createElement("div");
        meta.className = "program-card__meta";

        const badge = document.createElement("span");
        badge.className = "program-card__tag";
        badge.textContent = "Community submission";
        meta.appendChild(badge);

        const duration = document.createElement("span");
        duration.className = "program-card__duration";
        duration.textContent = formatDateLabel(entry);
        meta.appendChild(duration);

        article.appendChild(meta);

        const heading = document.createElement("h3");
        heading.textContent = entry.name || "Neighbourhood program";
        article.appendChild(heading);

        const description = document.createElement("p");
        description.textContent = entry.description || "No additional details provided yet.";
        article.appendChild(description);

        article.appendChild(createHighlightsList(entry));
        article.appendChild(createActionsRow(entry));

        return article;
    }

    // Builds the full card for a seeded program entry sourced from the catalog.
    function createSeedProgramCard(program) {
        const article = document.createElement("article");
        article.className = "highlight-card program-card";
        article.dataset.programId = program.id;

        const meta = document.createElement("div");
        meta.className = "program-card__meta";

        const badge = document.createElement("span");
        badge.className = "program-card__tag";
        badge.textContent = program.badge || "EcoGo program";
        meta.appendChild(badge);

        if (program.duration) {
            const duration = document.createElement("span");
            duration.className = "program-card__duration";
            duration.textContent = program.duration;
            meta.appendChild(duration);
        }

        article.appendChild(meta);

        const heading = document.createElement("h3");
        heading.textContent = program.title || "EcoGo program";
        article.appendChild(heading);

        const description = document.createElement("p");
        description.textContent = program.summary || "";
        article.appendChild(description);

        article.appendChild(createSeedHighlights(program));
        article.appendChild(createSeedActions(program));

        return article;
    }

    function createFlashBanner(data) {
        const banner = document.createElement("div");
        banner.className = "feed-flash feed-flash--recycling";
        const heading = document.createElement("strong");
        heading.textContent = "Program submitted!";
        const message = document.createElement("span");
        const name = data && data.name ? `"${data.name}"` : "Your program";
        message.textContent = ` ${name} is now visible on the Recycling Programs board.`;
        banner.append(heading, message);
        return banner;
    }

    function renderSeedPrograms(grid) {
        if (!window.ecogoProgramCatalog || typeof window.ecogoProgramCatalog.listHomePrograms !== "function") {
            return;
        }
        const catalogPrograms = window.ecogoProgramCatalog.listHomePrograms();
        if (!Array.isArray(catalogPrograms) || !catalogPrograms.length) {
            return;
        }
        const fragment = document.createDocumentFragment();
        catalogPrograms.forEach((program) => {
            fragment.appendChild(createSeedProgramCard(program));
        });
        grid.appendChild(fragment);
    }

    // Once the feed container exists, inject seeded programs and any saved community submissions.
    document.addEventListener("DOMContentLoaded", () => {
        const grid = document.querySelector(".program-grid");
        if (!grid) {
            return;
        }

        renderSeedPrograms(grid);

        const submissions = window.ecogoUploads.loadEntries(STORAGE_KEY);
        if (submissions.length) {
            const fragment = document.createDocumentFragment();
            submissions.forEach((entry) => {
                fragment.appendChild(createProgramCard(entry));
            });
            grid.prepend(fragment);
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
