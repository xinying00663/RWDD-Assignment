/**
 * Handles swap card interactions and navigation to the confirm page.
 */
(function () {
    var SELECTION_KEY = "ecogoSelectedSwap";

    function buildDestination(link) {
        var rawHref = link.getAttribute("href") || "swapConfirm.html";
        var questionIndex = rawHref.indexOf("?");
        return questionIndex === -1 ? rawHref : rawHref.slice(0, questionIndex);
    }

    function persistSelection(payload) {
        if (!payload) {
            return false;
        }
        try {
            var record = {
                title: payload.title || "",
                description: payload.description || "",
                category: payload.category || "",
                image: payload.image || "",
                timestamp: Date.now()
            };
            localStorage.setItem(SELECTION_KEY, JSON.stringify(record));
            return true;
        } catch (error) {
            console.warn("EcoGo swap: unable to store selection", error);
            return false;
        }
    }

    function redirectWithCardData(link) {
        var card = link.closest(".swap-card");
        if (!card) {
            window.location.href = link.getAttribute("href") || "swapConfirm.html";
            return;
        }

        var titleElement = card.querySelector("h3");
        var descriptionElement = card.querySelector("p");
        var imageEl = card.querySelector("img");
        var tag = card.querySelector(".card-tag");

        var title =
            card.dataset.itemTitle ||
            (titleElement ? titleElement.textContent.trim() : "Swap item");
        var description =
            card.dataset.itemDescription ||
            (descriptionElement ? descriptionElement.textContent.trim() : "");
        var category = tag ? tag.textContent.trim() : card.dataset.category || "";
        var image =
            card.dataset.itemImage || (imageEl ? imageEl.getAttribute("src") : "");

        var destination = buildDestination(link);
        if (persistSelection({ title: title, description: description, category: category, image: image })) {
            window.location.href = destination;
            return;
        }

        var params = new URLSearchParams();
        params.set("title", title);
        if (description) {
            params.set("description", description);
        }
        if (category) {
            params.set("category", category);
        }
        if (image && image.length < 1024) {
            params.set("image", image);
        }

        var query = params.toString();
        window.location.href = query ? destination + "?" + query : destination;
    }

    function enhanceLink(link) {
        if (!link || link.dataset.ecogoSwapReady === "1") {
            return;
        }

        link.addEventListener("click", function (event) {
            event.preventDefault();
            redirectWithCardData(link);
        });

        var card = link.closest(".swap-card");
        if (card && card.dataset.itemTitle) {
            link.setAttribute("aria-label", "Swap for " + card.dataset.itemTitle);
        }

        link.dataset.ecogoSwapReady = "1";
    }

    document.addEventListener("DOMContentLoaded", function () {
        var swapLinks = document.querySelectorAll("[data-swap-trigger]");
        if (!swapLinks.length) {
            return;
        }
        swapLinks.forEach(enhanceLink);
    });

    window.ecogoSwap = window.ecogoSwap || {};
    window.ecogoSwap.registerLink = enhanceLink;
})();
