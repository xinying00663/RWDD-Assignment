(function () {
    const storageKey = "ecogoProfile";

    function readProfile() {
        try {
            const raw = localStorage.getItem(storageKey);
            return raw ? JSON.parse(raw) : {};
        } catch (err) {
            console.warn("Unable to parse profile data", err);
            return {};
        }
    }

    function persistProfile(updates) {
        const current = readProfile();
        const merged = Object.assign({}, current, updates);
        localStorage.setItem(storageKey, JSON.stringify(merged));
        return merged;
    }

    function initialiseSignupForm() {
        const form = document.getElementById("signUpForm");
        if (!form) {
            return;
        }

        const errorNode = document.getElementById("signUpError");

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            errorNode.textContent = "";

            if (!form.reportValidity()) {
                return;
            }

            const password = form.password.value.trim();
            const confirmPassword = form.confirmPassword.value.trim();
            const termsAccepted = form.terms.checked;

            if (password !== confirmPassword) {
                errorNode.textContent = "Passwords do not match.";
                return;
            }

            if (!termsAccepted) {
                errorNode.textContent = "You must accept the community guidelines to continue.";
                return;
            }

            const record = {
                fullName: form.fullName.value.trim(),
                city: form.city.value.trim(),
                email: form.email.value.trim().toLowerCase(),
                interest: form.interest.value,
                createdAt: new Date().toISOString()
            };

            persistProfile({ signUp: record });
            window.location.href = "profileSetup.html";
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
        const sideCity = document.getElementById("sideCityLabel");

        const state = readProfile();
        if (!state.signUp) {
            window.location.href = "signup.html";
            return;
        }

        summaryName.textContent = state.signUp.fullName;
        summaryEmail.textContent = state.signUp.email;
        summaryCity.textContent = `Based in ${state.signUp.city}`;
        sideCity.textContent = state.signUp.city;

        if (state.profile) {
            form.householdSize.value = state.profile.householdSize || "";
            form.dwelling.value = state.profile.dwelling || "";
            const goals = new Set(state.profile.goals || []);
            form.querySelectorAll("input[name='goals']").forEach((input) => {
                input.checked = goals.has(input.value);
            });
            const contribution = state.profile.contribution || "";
            if (contribution) {
                const target = form.querySelector(`input[name='contribution'][value='${contribution}']`);
                if (target) {
                    target.checked = true;
                }
            }
            form.skills.value = state.profile.skills || "";
            form.energyAlerts.checked = Boolean(state.profile.energyAlerts);
        }

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            errorNode.textContent = "";

            if (!form.reportValidity()) {
                return;
            }

            const selectedGoals = Array.from(form.querySelectorAll("input[name='goals']:checked"), (input) => input.value);
            if (selectedGoals.length === 0) {
                errorNode.textContent = "Choose at least one goal so we can tailor your tips.";
                return;
            }

            const contributionField = form.querySelector("input[name='contribution']:checked");
            if (!contributionField) {
                errorNode.textContent = "Select how you would like to contribute.";
                return;
            }

            const profileRecord = {
                householdSize: form.householdSize.value,
                dwelling: form.dwelling.value,
                goals: selectedGoals,
                contribution: contributionField.value,
                skills: form.skills.value.trim(),
                energyAlerts: form.energyAlerts.checked,
                completedAt: new Date().toISOString()
            };

            persistProfile({ profile: profileRecord });
            window.location.href = "homePage.html";
        });
    }

    initialiseSignupForm();
    initialiseProfileForm();
})();
