//ALL HARDCODED DATA FOR PROGRAMS IS STORED IN THIS FILE
// This is a simple approach for a static site without a backend or CMS
const programData = {
    "rooftop-herb-lab": {
        badge: "Urban farming",
        title: "Rooftop Herb Lab",
        summary: "Co-create a rooftop herb garden, learn soil-free irrigation tricks, and send home fresh bundles every week.",
        duration: "4-week series",
        commitment: "Thursdays - 6:30 PM to 8:30 PM",
        location: "Solaris Dutamas, Level 7 rooftop garden",
        outcomes: [
            "Install and maintain wicking beds that stay hydrated even through hot spells.",
            "Experiment with heat-tolerant herb varieties and interchangeable soil mixes.",
            "Package weekly herb bundles for 18 surrounding households and a community pantry."
        ],
        schedule: [
            { title: "Week 1 - Setup and systems", detail: "Assemble the beds, lay the irrigation, and assign plant care rotations." },
            { title: "Week 2 - Planting lab", detail: "Trial herb pairings, companion plants, and organic pest deterrents." },
            { title: "Week 3 - Harvest and prep", detail: "Learn quick-harvest techniques and packaging for pantry deliveries." },
            { title: "Week 4 - Share and celebrate", detail: "Host an open evening to share tips with neighbours and recruit new caretakers." }
        ],
        resources: [
            { title: "What to bring", detail: "Reusable produce bags, gardening gloves, hat, and drinking water." },
            { title: "Group size", detail: "Up to 12 volunteers per session with rotating care teams." },
            { title: "Impact goal", detail: "Supply fresh herbs to 18 households and a local food bank each month." }
        ],
        coordinator: {
            name: "Asha Kaur",
            role: "Rooftop garden lead volunteer",
            email: "asha.kaur@ecogo.my",
            phone: "+60 19-555-0182"
        },
        roles: [
            "Plant care rotation",
            "Workshop facilitator",
            "Harvest packaging and delivery support"
        ]
    },
    "compost-cooperative": {
        badge: "Community composting",
        title: "Compost Cooperative",
        summary: "Turn neighbourhood scraps into nutrient-rich compost while learning low-odor systems you can replicate at home.",
        duration: "Monthly meetups",
        commitment: "First Saturday - 8:30 AM to 11:30 AM",
        location: "Bangsar South community kitchen",
        outcomes: [
            "Build and maintain hot compost piles that balance greens and browns year-round.",
            "Run a community drop-off station with clear signage, sorting, and contamination checks.",
            "Measure compost maturity and bag finished compost for community gardens."
        ],
        schedule: [
            { title: "08:30 - Drop-off crew", detail: "Guide residents, weigh incoming scraps, and log participation." },
            { title: "09:30 - Turning team", detail: "Layer browns, monitor temperature, and manage moisture levels." },
            { title: "10:30 - Skill share", detail: "Mini workshops on bokashi, vermicomposting, and balcony bins." }
        ],
        resources: [
            { title: "What to wear", detail: "Closed shoes and clothes you do not mind getting compost on." },
            { title: "Tools ready", detail: "Pitchforks, moisture meters, weigh scales, and cured compost sifters provided." },
            { title: "Community reach", detail: "Serving 42 households with free compost refills every quarter." }
        ],
        coordinator: {
            name: "Farid Rahman",
            role: "Zero-waste program coordinator",
            email: "farid.rahman@ecogo.my",
            phone: "+60 17-880-4421"
        },
        roles: [
            "Drop-off concierge",
            "Temperature tracker",
            "Workshop note taker"
        ]
    },
    "riverbank-cleanup": {
        badge: "Zero waste",
        title: "Riverbank Cleanup Sprint",
        summary: "Restore the Sungai Batu riverbank with targeted waste audits, sorting stations, and citizen science sampling.",
        duration: "Weekend blitz",
        commitment: "Saturday - 7:30 AM to 12:30 PM",
        location: "Taman Desa jetty meetup point",
        outcomes: [
            "Collect, sort, and catalogue riverbank litter for the city council data portal.",
            "Set up hydration, safety, and waste segregation stations for fellow volunteers.",
            "Map hotspots that require ongoing monitoring and habitat restoration."
        ],
        schedule: [
            { title: "07:30 - Briefing", detail: "Safety overview, team assignments, and equipment handoff." },
            { title: "08:00 - Cleanup waves", detail: "Three 45-minute cleanup sprints with rapid-sort stations." },
            { title: "11:00 - Waste audit", detail: "Log findings, prep council report, and plan follow-up actions." }
        ],
        resources: [
            { title: "Provided gear", detail: "Grabbers, gloves, waders, buckets, first-aid, and sunscreen." },
            { title: "Fitness level", detail: "Suited for active volunteers comfortable walking uneven ground." },
            { title: "Impact goal", detail: "Clear 180 kg of waste and submit a hotspot report by end of day." }
        ],
        coordinator: {
            name: "Melissa Tan",
            role: "River stewardship lead",
            email: "melissa.tan@ecogo.my",
            phone: "+60 16-220-9420"
        },
        roles: [
            "Sorting station lead",
            "Safety marshal",
            "Data logger"
        ]
    },
    "plastic-free-market": {
        badge: "Circular economy",
        title: "Plastic-Free Market Day",
        summary: "Host interactive booths that help weekend shoppers swap single-use plastics for reusable habits and tools.",
        duration: "One-day activation",
        commitment: "Sunday - 8:00 AM to 4:00 PM",
        location: "TTDI Community Hall forecourt",
        outcomes: [
            "Design engaging booths that let shoppers try refill and reuse systems.",
            "Facilitate micro-workshops on upcycling, food storage, and zero-waste shopping.",
            "Collect pledges from visitors to reduce plastics and track progress post-event."
        ],
        schedule: [
            { title: "08:00 - Setup crew", detail: "Assemble booths, signage, refill taps, and zero-waste toolkit." },
            { title: "10:00 - Market open", detail: "Engage shoppers, demo reuse swaps, and capture pledge stories." },
            { title: "15:00 - Wrap and debrief", detail: "Pack down stations, tally pledges, and plan follow-up support." }
        ],
        resources: [
            { title: "Booth support", detail: "All signage, refill dispensers, and sample products supplied." },
            { title: "Volunteer perks", detail: "Lunch, zero-waste starter kit, and transport stipend provided." },
            { title: "Impact goal", detail: "Sign up 120 shoppers to commit to a reusable habit for 30 days." }
        ],
        coordinator: {
            name: "Nadia Chong",
            role: "Circular economy campaigner",
            email: "nadia.chong@ecogo.my",
            phone: "+60 11-777-9034"
        },
        roles: [
            "Workshop facilitator",
            "Story collector",
            "Booth logistics"
        ]
    }
};

document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const requestedId = params.get("program") || "rooftop-herb-lab";
    const program = programData[requestedId];

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
        outcomesEl.innerHTML = program.outcomes
            .map((item) => `<li>${item}</li>`)
            .join("");
    }

    const scheduleEl = document.getElementById("programSchedule");
    if (scheduleEl) {
        scheduleEl.innerHTML = program.schedule
            .map(
                (entry) =>
                    `<div class="schedule-card"><h3>${entry.title}</h3><p>${entry.detail}</p></div>`
            )
            .join("");
    }

    const resourcesEl = document.getElementById("programResources");
    if (resourcesEl) {
        resourcesEl.innerHTML = program.resources
            .map(
                (resource) =>
                    `<div class="info-card"><h3>${resource.title}</h3><p>${resource.detail}</p></div>`
            )
            .join("");
    }

    const coordinatorName = document.getElementById("coordinatorName");
    const coordinatorRole = document.getElementById("coordinatorRole");
    const coordinatorEmail = document.getElementById("coordinatorEmail");
    const coordinatorPhone = document.getElementById("coordinatorPhone");

    if (coordinatorName) coordinatorName.textContent = program.coordinator.name;
    if (coordinatorRole) coordinatorRole.textContent = program.coordinator.role;
    if (coordinatorEmail) {
        coordinatorEmail.textContent = program.coordinator.email;
        coordinatorEmail.href = `mailto:${program.coordinator.email}`;
    }
    if (coordinatorPhone) {
        coordinatorPhone.textContent = program.coordinator.phone;
        const telValue = program.coordinator.phone.replace(/\s+/g, "");
        coordinatorPhone.href = `tel:${telValue}`;
    }

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
