/**
 * Persists community story uploads in localStorage so they can be rendered on the feed.
 */
(function () {
    const STORAGE_KEY = "ecogoCommunityUploads";
    const FLASH_KEY = "ecogoCommunityUploadFlash";

    function buildEntry(formData, mediaSrc, mediaType, fileName, mimeType) {
        const title = (formData.get("communityTitle") || "").toString().trim();
        const category = (formData.get("communityCategory") || "projects").toString();
        const uploader = (formData.get("communityContact") || "").toString().trim();
        const location = (formData.get("communityLocation") || "").toString().trim();
        const description = (formData.get("communitySummary") || "").toString().trim();
        const link = (formData.get("communityLink") || "").toString().trim();

        return {
            id: window.ecogoUploads.createEntryId("community"),
            title,
            category,
            uploader: uploader || "EcoGo neighbour",
            location,
            description,
            link,
            mediaType,
            mediaSrc,
            fileName,
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
            const fileInput = form.querySelector("#communityMedia");
            const file = fileInput && fileInput.files ? fileInput.files[0] : null;

            if (!file) {
                showUploadError("Please choose an image or video to share with the community.");
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
            const entry = buildEntry(
                formData,
                mediaSrc,
                mediaType,
                file.name || "",
                file.type || ""
            );
            const uploads = window.ecogoUploads.loadEntries(STORAGE_KEY);
            uploads.unshift(entry);
            window.ecogoUploads.saveEntries(STORAGE_KEY, uploads);

            window.ecogoUploads.setFlash(FLASH_KEY, { title: entry.title });
            window.location.href = "communityPage.html";
        });
    });
})();
