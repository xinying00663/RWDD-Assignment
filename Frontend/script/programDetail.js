// Program detail renderer for both pre-seeded and community-submitted recycling programs.
// Seeded data is supplied by window.ecogoProgramCatalog; community uploads are read from localStorage.

// Retrieves a seeded program entry from the shared catalog, if available.
function loadSeedProgram(id) {
    if (!id || !window.ecogoProgramCatalog || typeof window.ecogoProgramCatalog.getProgram !== "function") {
        return null;
    }
    return window.ecogoProgramCatalog.getProgram(id);
}

// Storage key shared with the upload form so we can surface community submissions here.
const COMMUNITY_STORAGE_KEY = "ecogoRecyclingPrograms";
const COMMUNITY_BADGE = "Community submission";

// Reads the recycling program uploads from localStorage and locates the entry by id.
function loadCommunityProgram(id) {
    if (!id) {
        return null;
    }
    try {
        const raw = localStorage.getItem(COMMUNITY_STORAGE_KEY);
        if (!raw) {
            return null;
        }
        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) {
            return null;
        }
        return parsed.find((entry) => entry && entry.id === id) || null;
    } catch (error) {
        console.warn("Program detail: unable to read community submissions", error);
        return null;
    }
}

// Produces a friendly date label using the stored date range (if available).
function buildCommunityDateLabel(entry) {
    if (!entry) {
        return "";
    }
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
    return "";
}

// Normalises a community-submitted entry so the rest of the view logic can treat it like seed data.
function transformCommunityProgram(entry) {
    const dateLabel = buildCommunityDateLabel(entry);
    const summary = entry.description || "This organiser has not shared additional details yet.";
    const location = entry.location || "Location to be confirmed";
    const coordinatorDetails = entry.coordinator || {};
    const sections = Array.isArray(entry.sections) ? entry.sections : [];
    const customSections = sections
        .map((section, index) => {
            if (!section) {
                return null;
            }
            const heading = (section.heading || "").toString().trim();
            const details = (section.details || "").toString().trim();
            if (!heading && !details) {
                return null;
            }
            return {
                heading: heading || `Additional details ${index + 1}`,
                details
            };
        })
        .filter(Boolean);

    return {
        badge: COMMUNITY_BADGE,
        title: entry.name || "Community recycling program",
        summary,
        duration: dateLabel || "Schedule to be confirmed",
        commitment: dateLabel || "Check back for the confirmed timing.",
        location,
        outcomes: summary ? [summary] : [],
        schedule: [],
        resources: [
            { title: "When", detail: dateLabel || "To be confirmed" },
            { title: "Where", detail: location }
        ],
        coordinator: {
            name: coordinatorDetails.name || "Community organiser",
            role: "Community submission",
            email: coordinatorDetails.email || "",
            phone: coordinatorDetails.phone || ""
        },
        roles: ["Volunteer participant"],
        isCommunity: true,
        customSections
    };
}

// Hides the surrounding panel when no meaningful content is available.
function togglePanelVisibility(contentRoot, shouldShow) {
    if (!contentRoot) {
        return;
    }
    const panel = contentRoot.closest(".program-panel");
    if (!panel) {
        return;
    }
    panel.hidden = !shouldShow;
}

// Creates additional program panels for any custom sections supplied by community organisers.
function renderCustomSections(sections) {
    if (!Array.isArray(sections) || !sections.length) {
        return;
    }
    const body = document.querySelector(".program-body");
    if (!body) {
        return;
    }
    sections.forEach((section) => {
        const panel = document.createElement("article");
        panel.className = "program-panel program-panel--community";

        const heading = document.createElement("h2");
        heading.textContent = section.heading;

        const paragraph = document.createElement("p");
        paragraph.textContent = section.details || "More information coming soon.";

        panel.append(heading, paragraph);
        body.appendChild(panel);
    });
}

// Populate the program detail page once the DOM skeleton is ready.
document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    // Use 'id' from PHP-generated links, but keep 'program' as a fallback for old links.
    const requestedId = params.get("id") || params.get("program") || "rooftop-herb-lab";
    let program = loadSeedProgram(requestedId);

    if (!program) {
        // Prioritize program data passed directly from PHP
        if (window.ecogoCommunityProgram && window.ecogoCommunityProgram.id.toString() === requestedId) {
            program = transformCommunityProgram(window.ecogoCommunityProgram);
        } else {
            // Fallback to localStorage for programs not from the DB
            const communityProgram = loadCommunityProgram(requestedId);
            if (communityProgram) {
            program = transformCommunityProgram(communityProgram);
            }
        }
    }

    const programView = document.getElementById("programView");
    const fallback = document.getElementById("programFallback");

    if (!program) {
        if (programView) {
            programView.hidden = true;
        }
        if (fallback) {
            fallback.hidden = false;
        }
        document.title = "Program not found | EcoGo";
        return;
    }

    if (programView) {
        programView.hidden = false;
    }
    if (fallback) {
        fallback.hidden = true;
    }

    document.title = `${program.title} | EcoGo Program Details`;
    const badgeEl = document.getElementById("programBadge");
    const titleEl = document.getElementById("programTitle");
    const summaryEl = document.getElementById("programSummary");
    const durationEl = document.getElementById("programDuration");
    const commitmentEl = document.getElementById("programCommitment");
    const locationEl = document.getElementById("programLocation");
    const breadcrumbEl = document.getElementById("programBreadcrumb");
    const registerNameEl = document.getElementById("registerProgramName");

    if (badgeEl) badgeEl.textContent = program.badge;
    if (titleEl) titleEl.textContent = program.title;
    if (summaryEl) summaryEl.textContent = program.summary;
    if (durationEl) durationEl.textContent = program.duration;
    if (commitmentEl) commitmentEl.textContent = program.commitment;
    if (locationEl) locationEl.textContent = program.location;
    if (breadcrumbEl) breadcrumbEl.textContent = program.title;
    if (registerNameEl) registerNameEl.textContent = program.title;

    const outcomesEl = document.getElementById("programOutcomes");
    if (outcomesEl) {
        const hasOutcomes = Array.isArray(program.outcomes) && program.outcomes.length > 0;
        if (hasOutcomes) {
            outcomesEl.innerHTML = program.outcomes
                .map((item) => `<li>${item}</li>`)
                .join("");
        } else {
            togglePanelVisibility(outcomesEl, false);
        }
    }

    const resourcesEl = document.getElementById("programResources");
    if (resourcesEl) {
        const hasResources = Array.isArray(program.resources) && program.resources.length > 0;
        if (hasResources) {
            resourcesEl.innerHTML = program.resources
                .map(
                    (resource) =>
                        `<div class="info-card"><h3>${resource.title}</h3><p>${resource.detail}</p></div>`
                )
                .join("");
        } else {
            togglePanelVisibility(resourcesEl, false);
        }
    }

    renderCustomSections(program.customSections);

    const coordinatorName = document.getElementById("coordinatorName");
    const coordinatorRole = document.getElementById("coordinatorRole");
    const coordinatorEmail = document.getElementById("coordinatorEmail");
    const coordinatorPhone = document.getElementById("coordinatorPhone");

    if (coordinatorName) coordinatorName.textContent = program.coordinator.name;
    if (coordinatorRole) coordinatorRole.textContent = program.coordinator.role;
    if (coordinatorEmail) {
        if (program.coordinator.email) {
            coordinatorEmail.textContent = program.coordinator.email;
            coordinatorEmail.href = `mailto:${program.coordinator.email}`;
        } else {
            coordinatorEmail.textContent = "Email not provided";
            coordinatorEmail.removeAttribute("href");
        }
    }
    if (coordinatorPhone && program.coordinator.phone) {
        coordinatorPhone.textContent = program.coordinator.phone;
        const telValue = program.coordinator.phone.replace(/\s+/g, "");
        coordinatorPhone.href = `tel:${telValue}`;
    } else if (coordinatorPhone) {
        coordinatorPhone.textContent = "To be shared after confirmation";
        coordinatorPhone.removeAttribute("href");
    }

    // Prepare the role dropdown so volunteers can pick how they would like to help.
    const roleSelect = document.getElementById("preferredRole");
    if (roleSelect) {
        const options = [
            '<option value="" disabled selected>Select a role</option>',
            ...program.roles.map((role) => `<option value="${role}">${role}</option>`)
        ];
        roleSelect.innerHTML = options.join("");
    }

    const form = document.getElementById("programRegisterForm");
    const statusEl = document.getElementById("formStatus");

    if (form) {
        // Handle inline confirmation instead of posting to a backend.
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            const formData = new FormData(form);
            const name = (formData.get("participantName") || "").toString().trim();
            const email = (formData.get("participantEmail") || "").toString().trim();

            const firstName = name.split(" ")[0] || "there";

            if (statusEl) {
                const emailFragment = email ? email : "the email you provided";
                statusEl.textContent = `Thanks ${firstName}! We have recorded your interest in ${program.title}. ${program.coordinator.name} will email you at ${emailFragment} within two working days.`;
            }

            form.reset();
            if (roleSelect) {
                roleSelect.selectedIndex = 0;
            }
        });
    }
});
