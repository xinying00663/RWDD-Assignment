(function () {
    const storageKey = "ecogoUsers";

    function defaultState() {
        return { accounts: [], activeEmail: null };
    }

    function readState() {
        try {
            const raw = localStorage.getItem(storageKey);
            if (!raw) {
                return defaultState();
            }
            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed.accounts)) {
                parsed.accounts = [];
            }
            if (typeof parsed.activeEmail !== "string") {
                parsed.activeEmail = null;
            }
            return parsed;
        } catch (err) {
            console.warn("Unable to parse stored state", err);
            return defaultState();
        }
    }

    function writeState(nextState) {
        localStorage.setItem(storageKey, JSON.stringify(nextState));
        return nextState;
    }

    function findAccount(state, email) {
        return state.accounts.find((account) => account.email === email) || null;
    }

    function setText(node, text) {
        if (node) {
            node.textContent = text;
        }
    }

    function clearText(node) {
        setText(node, "");
    }

    function initialiseSignupForm() {
        const form = document.getElementById("signUpForm");
        if (!form) {
            return;
        }

        const errorNode = document.getElementById("signUpError");

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            clearText(errorNode);

            if (!form.reportValidity()) {
                return;
            }

            const email = form.email.value.trim().toLowerCase();
            const password = form.password.value.trim();
            const confirmPassword = form.confirmPassword.value.trim();
            const termsAccepted = form.terms.checked;

            if (password !== confirmPassword) {
                setText(errorNode, "Passwords do not match.");
                return;
            }

            if (!termsAccepted) {
                setText(errorNode, "You must accept the community guidelines to continue.");
                return;
            }

            const state = readState();

            if (findAccount(state, email)) {
                setText(errorNode, "An account with this email already exists.");
                return;
            }

            const account = {
                email,
                password,
                createdAt: new Date().toISOString(),
                profile: null
            };

            state.accounts.push(account);
            state.activeEmail = email;

            try {
                writeState(state);
                window.location.href = "profileSetup.html";
            } catch (err) {
                console.error("Unable to save signup information", err);
                setText(errorNode, "We couldn't save your details. Please try again.");
            }
        });
    }

    function initialiseProfileForm() {
        const form = document.getElementById("profileForm");
        if (!form) {
            return;
        }

        const errorNode = document.getElementById("profileError");
        const summaryName = document.getElementById("profileSummaryName");
        const summaryEmail = document.getElementById("profileSummaryEmail");
        const summaryCity = document.getElementById("profileSummaryCity");

        const state = readState();
        const activeEmail = state.activeEmail;

        if (!activeEmail) {
            window.location.href = "signup.html";
            return;
        }

        const account = findAccount(state, activeEmail);

        if (!account) {
            state.activeEmail = null;
            writeState(state);
            window.location.href = "signup.html";
            return;
        }

        const existingProfile = account.profile || {};

        setText(summaryEmail, account.email || "");
        setText(summaryName, existingProfile.fullName || "Add your name below");
        setText(summaryCity, existingProfile.city ? `Based in ${existingProfile.city}` : "Tell us where you are based");

        if (existingProfile.fullName) {
            form.fullName.value = existingProfile.fullName;
        }

        if (existingProfile.city) {
            form.city.value = existingProfile.city;
        }

        if (typeof existingProfile.skills === "string") {
            form.skills.value = existingProfile.skills;
        }

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            clearText(errorNode);

            if (!form.reportValidity()) {
                return;
            }

            const profileRecord = {
                fullName: form.fullName.value.trim(),
                city: form.city.value.trim(),
                skills: form.skills.value.trim(),
                updatedAt: new Date().toISOString()
            };

            account.profile = profileRecord;

            try {
                writeState(state);
                window.location.href = "homePage.html";
            } catch (err) {
                console.error("Unable to save profile", err);
                setText(errorNode, "We couldn't save your profile. Please try again.");
            }
        });
    }

    initialiseSignupForm();
    initialiseProfileForm();
})();
