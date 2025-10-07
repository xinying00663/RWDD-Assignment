/**
 * Handles energy tip submissions and stores them in localStorage.
 */
(function () {
    const STORAGE_KEY = "ecogoEnergyUploads";
    const FLASH_KEY = "ecogoEnergyUploadFlash";

    function buildEntry(formData, mediaSrc, mediaType, file, mimeType) {
        const title = (formData.get("energyTitle") || "").toString().trim();
        const category = (formData.get("energyCategory") || "tutorial").toString();
        const contributor = (formData.get("energyContributor") || "").toString().trim();
        const duration = (formData.get("energyDuration") || "").toString().trim();
        const summary = (formData.get("energySummary") || "").toString().trim();
        const link = (formData.get("energyLink") || "").toString().trim();

        return {
            id: window.ecogoUploads.createEntryId("energy"),
            title,
            category,
            contributor: contributor || "EcoGo neighbour",
            duration,
            summary,
            link,
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
            const fileInput = form.querySelector("#energyMedia");
            const file = fileInput && fileInput.files ? fileInput.files[0] : null;

            if (!file) {
                showUploadError("Please add an image or video so others can follow your tip.");
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
            const uploads = window.ecogoUploads.loadEntries(STORAGE_KEY);
            uploads.unshift(entry);
            window.ecogoUploads.saveEntries(STORAGE_KEY, uploads);

            window.ecogoUploads.setFlash(FLASH_KEY, { title: entry.title });
            window.location.href = "energyPage.html";
        });
    });
})();
