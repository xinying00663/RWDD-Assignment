/**
 * Handles recycling program submissions and stores them in localStorage.
 * Each helper includes inline documentation to make the flow easier to follow.
 */
(function () {
    const STORAGE_KEY = "ecogoRecyclingPrograms";
    const FLASH_KEY = "ecogoRecyclingFlash";

    // Converts a YYYY-MM-DD string into the user's locale, or returns the raw value if parsing fails.
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

    // Converts the raw date inputs into a single readable label.
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
            return `${fromLabel} - ${toLabel}`;
        }
        return `${start} - ${end}`;
    }

    // Shapes the form submission into the structure expected by the feed.
    function buildEntry(formData, sections) {
        const name = (formData.get("eventName") || "").toString().trim();
        const location = (formData.get("eventLocation") || "").toString().trim();
        const startDate = (formData.get("eventDate") || "").toString();
        const endDate = (formData.get("eventDateTo") || "").toString();
        const description = (formData.get("eventDescription") || "").toString().trim();
        const coordinatorName = (formData.get("coordinatorName") || "").toString().trim();
        const coordinatorEmail = (formData.get("coordinatorEmail") || "").toString().trim();
        const coordinatorPhone = (formData.get("coordinatorPhone") || "").toString().trim();

        return {
            id: window.ecogoUploads.createEntryId("recycling"),
            name,
            location,
            startDate,
            endDate,
            description,
            dateLabel: formatDateRange(startDate, endDate),
            createdAt: new Date().toISOString(),
            coordinator: {
                name: coordinatorName,
                email: coordinatorEmail,
                phone: coordinatorPhone
            },
            sections
        };
    }

    // Uses a simple alert to surface validation issues to the organiser.
    function showUploadError(message) {
        alert(message);
    }

    // Reads every custom section block from the page.
    function collectSections(sectionContainer) {
        if (!sectionContainer) {
            return [];
        }
        const sections = [];
        sectionContainer.querySelectorAll("[data-section]").forEach((sectionEl) => {
            const titleInput = sectionEl.querySelector('input[name="sectionTitle"]');
            const descriptionInput = sectionEl.querySelector('textarea[name="sectionDescription"]');
            const heading = titleInput ? titleInput.value.trim() : "";
            const details = descriptionInput ? descriptionInput.value.trim() : "";
            if (heading || details) {
                sections.push({
                    heading,
                    details
                });
            }
        });
        return sections;
    }

    // Creates a new editable section card inside the container.
    function addSection(sectionContainer, template) {
        if (!sectionContainer || !template) {
            return;
        }
        const clone = template.content.firstElementChild.cloneNode(true);
        sectionContainer.appendChild(clone);
    }

    document.addEventListener("DOMContentLoaded", () => {
        const form = document.querySelector(".upload-form");
        if (!form) {
            return;
        }

        const sectionContainer = form.querySelector("[data-section-list]");
        const addSectionButton = form.querySelector("[data-section-add]");
        const sectionTemplate = document.getElementById("program-section-template");

        if (addSectionButton) {
            addSectionButton.addEventListener("click", () => {
                addSection(sectionContainer, sectionTemplate);
            });
        }

        if (sectionContainer && sectionTemplate) {
            // Start with a single blank section so users see how the feature works.
            addSection(sectionContainer, sectionTemplate);
        }

        if (sectionContainer) {
            sectionContainer.addEventListener("click", (event) => {
                const target = event.target;
                if (!(target instanceof HTMLElement)) {
                    return;
                }
                if (target.matches("[data-section-remove]")) {
                    event.preventDefault();
                    const section = target.closest("[data-section]");
                    if (section) {
                        section.remove();
                    }
                }
            });
        }

        // Persist the submission into localStorage so it shows up on the home page.
        form.addEventListener("submit", (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const sections = collectSections(sectionContainer);
            const entry = buildEntry(formData, sections);

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

            if (!entry.coordinator.name && !entry.coordinator.email && !entry.coordinator.phone) {
                showUploadError("Please share at least one coordinator contact so volunteers can reach you.");
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
