(function () {
    var SELECTION_KEY = "ecogoSelectedSwap";
    var SELECTION_TTL_MS = 10 * 60 * 1000;
    var FALLBACK_IMAGE = "Pictures/landingPage/swap-item-pic.jpg";

    function loadStoredSelection() {
        try {
            var raw = localStorage.getItem(SELECTION_KEY);
            if (!raw) {
                return null;
            }
            var parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== "object") {
                localStorage.removeItem(SELECTION_KEY);
                return null;
            }
            if (
                typeof parsed.timestamp === "number" &&
                Date.now() - parsed.timestamp > SELECTION_TTL_MS
            ) {
                localStorage.removeItem(SELECTION_KEY);
                return null;
            }
            return {
                title: parsed.title || "",
                description: parsed.description || "",
                category: parsed.category || "",
                image: parsed.image || ""
            };
        } catch (error) {
            console.warn("EcoGo swap: unable to load stored selection", error);
            return null;
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        var stored = loadStoredSelection();
        var params = new URLSearchParams(window.location.search);

        var itemTitle = params.get("title");
        if (!itemTitle && stored && stored.title) {
            itemTitle = stored.title;
        }
        if (!itemTitle) {
            itemTitle = "Selected swap item";
        }

        var itemDescription = params.get("description");
        if (!itemDescription && stored && stored.description) {
            itemDescription = stored.description;
        }
        if (!itemDescription) {
            itemDescription = "Choose a listing from the swap feed to see the details here.";
        }

        var itemCategory = params.get("category");
        if (!itemCategory && stored && stored.category) {
            itemCategory = stored.category;
        }
        if (!itemCategory) {
            itemCategory = "Swap item";
        }

        var itemImage = params.get("image");
        if (!itemImage && stored && stored.image) {
            itemImage = stored.image;
        }
        if (!itemImage) {
            itemImage = FALLBACK_IMAGE;
        }

        var titleElement = document.getElementById("selectedItemTitle");
        var nameElement = document.getElementById("selectedItemName");
        var descriptionElement = document.getElementById("selectedItemDescription");
        var tagElement = document.getElementById("selectedItemTag");
        var inlineNameElement = document.getElementById("selectedItemNameInline");
        var imageElement = document.getElementById("selectedItemImage");

        if (titleElement) {
            titleElement.textContent = itemTitle;
        }
        if (nameElement) {
            nameElement.textContent = itemTitle;
        }
        if (descriptionElement) {
            descriptionElement.textContent = itemDescription;
        }
        if (tagElement) {
            tagElement.textContent = itemCategory;
        }
        if (inlineNameElement) {
            inlineNameElement.textContent = itemTitle;
        }
        if (imageElement) {
            imageElement.src = itemImage;
            imageElement.alt = "Photo of " + itemTitle;
        }

        document.title = "Confirm Swap - " + itemTitle;

        var offerForm = document.getElementById("swapOfferForm");
        if (!offerForm) {
            return;
        }

        var successBanner = document.getElementById("swapConfirmSuccess");
        var submitButton = offerForm.querySelector('button[type="submit"]');

        offerForm.addEventListener("submit", function (event) {
            event.preventDefault();
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = "Request sent";
            }
            if (successBanner) {
                successBanner.classList.add("swap-confirm__success--visible");
                if (typeof successBanner.focus === "function") {
                    successBanner.focus();
                }
            }
        });
    });
})();
