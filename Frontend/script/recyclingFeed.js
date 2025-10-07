/**
 * Injects saved recycling programs onto the home page.
 */
(function () {
    const STORAGE_KEY = "ecogoRecyclingPrograms";
    const FLASH_KEY = "ecogoRecyclingFlash";

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

    function createActionsRow(entry) {
        const actions = document.createElement("div");
        actions.className = "program-card__actions";

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

    document.addEventListener("DOMContentLoaded", () => {
        const grid = document.querySelector(".program-grid");
        if (!grid) {
            return;
        }

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
