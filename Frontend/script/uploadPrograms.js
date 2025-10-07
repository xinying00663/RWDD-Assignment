/**
 * Handles recycling program submissions and stores them in localStorage.
 */
(function () {
    const STORAGE_KEY = "ecogoRecyclingPrograms";
    const FLASH_KEY = "ecogoRecyclingFlash";

    function toDisplayDate(value) {
        if (!value) {
            return "";
        }
        const parsed = new Date(value);
        if (Number.isNaN(parsed.getTime())) {
            return value;
        }
        return parsed.toLocaleDateString();
    }

    function formatDateRange(start, end) {
        const fromLabel = toDisplayDate(start);
        const toLabel = toDisplayDate(end);
        if (!start && !end) {
            return "";
        }
        if (start && !end) {
            return `Starts ${fromLabel || start}`;
        }
        if (!start && end) {
            return `Until ${toLabel || end}`;
        }
        if (fromLabel && toLabel) {
            return `${fromLabel} – ${toLabel}`;
        }
        return `${start} – ${end}`;
    }

    function buildEntry(formData) {
        const name = (formData.get("eventName") || "").toString().trim();
        const location = (formData.get("eventLocation") || "").toString().trim();
        const startDate = (formData.get("eventDate") || "").toString();
        const endDate = (formData.get("eventDateTo") || "").toString();
        const description = (formData.get("eventDescription") || "").toString().trim();

        return {
            id: window.ecogoUploads.createEntryId("recycling"),
            name,
            location,
            startDate,
            endDate,
            description,
            dateLabel: formatDateRange(startDate, endDate),
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

        form.addEventListener("submit", (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const entry = buildEntry(formData);

            if (!entry.name) {
                showUploadError("Please add a program name.");
                return;
            }
            if (!entry.location) {
                showUploadError("Please include the program location.");
                return;
            }
            if (!entry.startDate || !entry.endDate) {
                showUploadError("Please provide both start and end dates.");
                return;
            }
            if (!entry.description) {
                showUploadError("Please share a short description so neighbours know what to expect.");
                return;
            }

            const uploads = window.ecogoUploads.loadEntries(STORAGE_KEY);
            uploads.unshift(entry);
            window.ecogoUploads.saveEntries(STORAGE_KEY, uploads);

            window.ecogoUploads.setFlash(FLASH_KEY, { name: entry.name });
            window.location.href = "homePage.html";
        });
    });
})();
