/**
 * Handles profile persistence, edit interactions, and post rendering for EcoGo profiles.
 */
(function () {
    const PROFILE_STORAGE_KEY = "ecogoUserProfile";
    const POSTS_STORAGE_KEY = "ecogoUserPosts";

    const defaultProfile = {
        username: "Jamie",
        fullName: "Jamie Lim",
        gender: "female",
        email: "abc@gmail.com",
        phone: "+60 12-345 6789",
        location: "Kuala Lumpur, Malaysia",
        bio: "Jamie coordinates neighbourhood clean-ups, rooftop garden builds, and resource swaps for EcoGo. Passionate about inclusive spaces, she mentors new volunteers and tracks project impact across the Klang Valley."
    };

    const defaultPosts = [
        {
            id: "seed-post-1",
            title: "5 Tips for Starting a Community Garden",
            body: "Sharing my top tips for launching a successful community garden project in your neighborhood. From planning to planting, let's grow together!",
            createdAt: "2025-09-07T09:00:00.000Z"
        },
        {
            id: "seed-post-2",
            title: "How to Reduce Household Waste",
            body: "Practical strategies for minimizing waste at home, including composting, recycling, and mindful consumption. Every little bit helps our planet!",
            createdAt: "2025-08-30T09:00:00.000Z"
        },
        {
            id: "seed-post-3",
            title: "Energy-Saving Tips for Your Home",
            body: "Simple changes you can make to reduce energy consumption and lower your utility bills. Let's make our homes more eco-friendly!",
            createdAt: "2025-08-23T09:00:00.000Z"
        }
    ];

    function safeParse(storageKey) {
        if (!storageKey) {
            return null;
        }

        try {
            const raw = localStorage.getItem(storageKey);
            if (!raw) {
                return null;
            }
            return JSON.parse(raw);
        } catch (error) {
            console.warn(`EcoGo profile: unable to parse data for ${storageKey}`, error);
            return null;
        }
    }

    function loadProfile() {
        const stored = safeParse(PROFILE_STORAGE_KEY);
        if (!stored || typeof stored !== "object") {
            return { ...defaultProfile };
        }
        return { ...defaultProfile, ...stored };
    }

    function saveProfile(profile) {
        if (!profile) {
            return;
        }
        localStorage.setItem(PROFILE_STORAGE_KEY, JSON.stringify(profile));
    }

    function loadPosts() {
        const stored = safeParse(POSTS_STORAGE_KEY);
        if (!stored) {
            return [...defaultPosts];
        }
        if (!Array.isArray(stored)) {
            return [...defaultPosts];
        }
        return stored.length === 0 ? [] : stored;
    }

    function savePosts(posts) {
        if (!Array.isArray(posts)) {
            return;
        }
        localStorage.setItem(POSTS_STORAGE_KEY, JSON.stringify(posts));
    }

    function computeInitials(name) {
        if (!name) {
            return "E";
        }
        const segments = name.trim().split(/\s+/).slice(0, 2);
        if (segments.length === 0) {
            return "E";
        }
        return segments
            .map((part) => part.charAt(0))
            .join("")
            .toUpperCase();
    }

    function formatGender(value) {
        if (!value) {
            return "";
        }
        const normalized = value.toString().trim().toLowerCase();
        if (normalized === "male") {
            return "Male";
        }
        if (normalized === "female") {
            return "Female";
        }
        if (normalized === "other") {
            return "Prefer Not to Say";
        }
        return value;
    }

    function formatRelativeTime(isoString) {
        const parsed = new Date(isoString);
        if (Number.isNaN(parsed.getTime())) {
            return "Recently";
        }

        const diffMs = Date.now() - parsed.getTime();
        const dayMs = 86_400_000;

        if (diffMs < dayMs) {
            return "Today";
        }
        const diffDays = Math.floor(diffMs / dayMs);
        if (diffDays === 1) {
            return "Yesterday";
        }
        if (diffDays < 7) {
            return `${diffDays} days ago`;
        }
        const diffWeeks = Math.floor(diffDays / 7);
        if (diffWeeks === 1) {
            return "Last week";
        }
        if (diffWeeks < 5) {
            return `${diffWeeks} weeks ago`;
        }
        const diffMonths = Math.floor(diffDays / 30);
        if (diffMonths === 1) {
            return "Last month";
        }
        if (diffMonths < 12) {
            return `${diffMonths} months ago`;
        }
        const diffYears = Math.floor(diffDays / 365);
        if (diffYears === 1) {
            return "Last year";
        }
        return `${diffYears} years ago`;
    }

    function renderProfile(profile) {
        const profileRoot = document.querySelector("[data-page='user-profile']");
        if (!profileRoot) {
            return;
        }

        const avatar = profileRoot.querySelector("[data-profile-avatar]");
        const username = profileRoot.querySelector("[data-profile-field='username']");
        const fullName = profileRoot.querySelector("[data-profile-field='fullName']");
        const gender = profileRoot.querySelector("[data-profile-field='gender']");
        const email = profileRoot.querySelector("[data-profile-field='email']");
        const phone = profileRoot.querySelector("[data-profile-field='phone']");
        const location = profileRoot.querySelector("[data-profile-field='location']");
        const bio = profileRoot.querySelector("[data-profile-field='bio']");

        if (avatar) {
            avatar.textContent = computeInitials(profile.fullName || profile.username);
        }
        if (username) {
            username.textContent = profile.username || "EcoGo member";
        }
        if (fullName) {
            fullName.textContent = profile.fullName || "";
        }
        if (gender) {
            gender.textContent = formatGender(profile.gender);
        }
        if (email) {
            email.textContent = profile.email || "";
        }
        if (phone) {
            phone.textContent = profile.phone || "";
        }
        if (location) {
            location.textContent = profile.location || "";
        }
        if (bio) {
            bio.textContent = profile.bio || "";
        }
    }

    function buildPostEntry(post) {
        const article = document.createElement("article");
        article.className = "activity-item";

        const time = document.createElement("span");
        time.className = "time";
        time.textContent = formatRelativeTime(post.createdAt);

        const title = document.createElement("strong");
        title.textContent = post.title;

        const body = document.createElement("p");
        body.textContent = post.body;

        article.append(time, title, body);
        return article;
    }

    function renderPosts(posts) {
        const postsContainer = document.querySelector("[data-profile-posts]");
        const emptyState = document.querySelector("[data-profile-posts-empty]");
        if (!postsContainer) {
            return;
        }

        postsContainer.innerHTML = "";
        if (!Array.isArray(posts) || posts.length === 0) {
            if (emptyState) {
                emptyState.hidden = false;
            }
            return;
        }
        if (emptyState) {
            emptyState.hidden = true;
        }

        posts
            .slice()
            .sort((a, b) => {
                const timeA = new Date(a.createdAt).getTime();
                const timeB = new Date(b.createdAt).getTime();
                return timeB - timeA;
            })
            .forEach((post) => {
                postsContainer.appendChild(buildPostEntry(post));
            });
    }

    function toggleModal(modal, isOpen) {
        if (!modal) {
            return;
        }
        if (isOpen) {
            modal.removeAttribute("hidden");
            document.body.classList.add("profile-modal-open");
            return;
        }
        modal.setAttribute("hidden", "hidden");
        document.body.classList.remove("profile-modal-open");
    }

    function initEditProfile() {
        const modal = document.querySelector("[data-profile-modal]");
        const editButton = document.querySelector("[data-action='edit-profile']");
        const closeButtons = modal ? modal.querySelectorAll("[data-action='close-modal']") : [];
        const form = modal ? modal.querySelector("form") : null;
        const errorTarget = modal ? modal.querySelector("[data-modal-error]") : null;

        if (!modal || !editButton || !form) {
            return;
        }

        function fillForm(nextProfile) {
            form.elements.username.value = nextProfile.username || "";
            form.elements.fullName.value = nextProfile.fullName || "";
            form.elements.gender.value = (nextProfile.gender || "").toString().toLowerCase();
            form.elements.email.value = nextProfile.email || "";
            form.elements.phone.value = nextProfile.phone || "";
            form.elements.location.value = nextProfile.location || "";
            form.elements.bio.value = nextProfile.bio || "";
        }

        function showError(message) {
            if (!errorTarget) {
                if (message) {
                    alert(message);
                }
                return;
            }
            errorTarget.textContent = message || "";
        }

        function handleOpen() {
            const latestProfile = loadProfile();
            fillForm(latestProfile);
            showError("");
            toggleModal(modal, true);
            const firstInput = form.querySelector("input, textarea, select");
            if (firstInput) {
                window.requestAnimationFrame(() => firstInput.focus());
            }
        }

        function handleClose() {
            toggleModal(modal, false);
            showError("");
        }

        editButton.addEventListener("click", (event) => {
            event.preventDefault();
            handleOpen();
        });

        closeButtons.forEach((button) => {
            button.addEventListener("click", (event) => {
                event.preventDefault();
                handleClose();
            });
        });

        modal.addEventListener("click", (event) => {
            if (event.target === modal) {
                handleClose();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && !modal.hasAttribute("hidden")) {
                handleClose();
            }
        });

        form.addEventListener("submit", (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const updatedProfile = {
                username: (formData.get("username") || "").toString().trim(),
                fullName: (formData.get("fullName") || "").toString().trim(),
                gender: (formData.get("gender") || "").toString().trim().toLowerCase(),
                email: (formData.get("email") || "").toString().trim(),
                phone: (formData.get("phone") || "").toString().trim(),
                location: (formData.get("location") || "").toString().trim(),
                bio: (formData.get("bio") || "").toString().trim()
            };

            if (!updatedProfile.username || !updatedProfile.fullName || !updatedProfile.email) {
                showError("Please complete the required fields before saving.");
                return;
            }

            saveProfile(updatedProfile);
            renderProfile(updatedProfile);
            handleClose();
        });
    }

    function initTabs() {
        const buttons = document.querySelectorAll(".tab-btn");
        const sections = document.querySelectorAll(".profile-content");
        if (!buttons.length || !sections.length) {
            return;
        }

        buttons.forEach((button) => {
            button.addEventListener("click", () => {
                const targetId = button.dataset.target;
                if (!targetId) {
                    return;
                }
                buttons.forEach((entry) => entry.classList.remove("active"));
                sections.forEach((section) => {
                    if (section.id === targetId) {
                        section.classList.add("active");
                    } else {
                        section.classList.remove("active");
                    }
                });
                button.classList.add("active");
            });
        });
    }

    function initLogout() {
        const logoutButton = document.querySelector("[data-action='logout']");
        if (!logoutButton) {
            return;
        }
        logoutButton.addEventListener("click", (event) => {
            event.preventDefault();
            const confirmed = window.confirm("Are you sure you want to log out of EcoGo?");
            if (confirmed) {
                window.location.href = "landingPage.html";
            }
        });
    }

    function hydrateProfilePage() {
        const profileRoot = document.querySelector("[data-page='user-profile']");
        if (!profileRoot) {
            return;
        }
        const profile = loadProfile();
        renderProfile(profile);
        initEditProfile();
        initTabs();
        initLogout();
        const posts = loadPosts();
        renderPosts(posts);
    }

    function initProfileSetup() {
        const form = document.getElementById("profileForm");
        if (!form) {
            return;
        }

        const profile = loadProfile();

        if (form.elements.username && profile.username) {
            form.elements.username.value = profile.username;
        }
        if (form.elements.fullName && profile.fullName) {
            form.elements.fullName.value = profile.fullName;
        }
        if (form.elements.gender && profile.gender) {
            form.elements.gender.value = profile.gender.toLowerCase();
        }
        if (form.elements.phoneNumber && profile.phone) {
            form.elements.phoneNumber.value = profile.phone;
        }
        if (form.elements.city && profile.location) {
            form.elements.city.value = profile.location;
        }
        if (form.elements.skills && profile.bio) {
            form.elements.skills.value = profile.bio;
        }

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            const formData = new FormData(form);
            const updatedProfile = {
                ...profile,
                username: (formData.get("username") || "").toString().trim() || profile.username,
                fullName: (formData.get("fullName") || "").toString().trim() || profile.fullName,
                gender: (formData.get("gender") || "").toString().trim() || profile.gender,
                phone: (formData.get("phoneNumber") || "").toString().trim() || profile.phone,
                location: (formData.get("city") || "").toString().trim() || profile.location,
                bio: (formData.get("skills") || "").toString().trim() || profile.bio
            };

            saveProfile(updatedProfile);
            window.location.href = "userProfile.html";
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        hydrateProfilePage();
        initProfileSetup();

        const storedPosts = safeParse(POSTS_STORAGE_KEY);
        if (!storedPosts) {
            savePosts(defaultPosts);
        }
    });
})();
