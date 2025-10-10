/**
 * Handles swap listing submissions and stores them in localStorage.
 */
(function () {
    const STORAGE_KEY = "ecogoSwapListings";
    const FLASH_KEY = "ecogoSwapFlash";

    function buildEntry(formData, mediaSrc, mediaType, file, mimeType) {
        const title = (formData.get("swapTitle") || "").toString().trim();
        const category = (formData.get("swapCategory") || "home-grown").toString();
        const condition = (formData.get("swapCondition") || "").toString().trim();
        const preferred = (formData.get("swapPreferred") || "").toString().trim();
        const details = (formData.get("swapDetails") || "").toString().trim();

        return {
            id: window.ecogoUploads.createEntryId("swap"),
            title,
            category,
            condition,
            preferred,
            details,
            mediaType,
            mediaSrc,
            fileName: file.name || "",
            mimeType,
            createdAt: new Date().toISOString()
        };
    }

    function showUploadError(message) {
        alert(message);
    }

    document.addEventListener("DOMContentLoaded", () => {
        const form = document.querySelector(".upload-form");
        if (!form) {
            return;
        }

        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const fileInput = form.querySelector("#swapMedia");
            const file = fileInput && fileInput.files ? fileInput.files[0] : null;

            if (!file) {
                showUploadError("Please add a photo or short clip of your swap item.");
                return;
            }

            let mediaSrc;
            try {
                mediaSrc = await window.ecogoUploads.readFileAsDataURL(file);
            } catch (error) {
                console.error("Failed to read uploaded file", error);
                showUploadError("We could not read the selected file. Please try again with a different one.");
                return;
            }

            const mediaType = window.ecogoUploads.determineMediaType(file);
            const entry = buildEntry(formData, mediaSrc, mediaType, file, file.type || "");

            if (!entry.title) {
                showUploadError("Please name your item so neighbours can find it.");
                return;
            }
            if (!entry.details) {
                showUploadError("Please add a few details so others know what to expect.");
                return;
            }

            const uploads = window.ecogoUploads.loadEntries(STORAGE_KEY);
            uploads.unshift(entry);
            window.ecogoUploads.saveEntries(STORAGE_KEY, uploads);

            window.ecogoUploads.setFlash(FLASH_KEY, { title: entry.title });
            window.location.href = "swapPage.html";
        });
    });
})();
