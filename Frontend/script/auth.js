(function () {
    function renderStatus(target, message, variant) {
        if (!target) {
            return;
        }

        var successClass = 'is-success';
        var errorClass = 'is-error';

        target.classList.remove(successClass, errorClass);
        target.textContent = message || '';

        if (!message) {
            return;
        }

        var className = variant === 'success' ? successClass : errorClass;
        target.classList.add(className);
    }

    function disableButton(button, state) {
        if (!button) {
            return;
        }
        button.disabled = state;
        if (state) {
            button.dataset.originalText = button.dataset.originalText || button.textContent;
            button.textContent = 'Please wait...';
        } else if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
    }

    function handleSignup(form) {
        var errorTarget = document.getElementById('signUpError');
        var submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var email = (form.email.value || '').trim().toLowerCase();
            var password = form.password.value || '';
            var confirmPassword = form.confirmPassword.value || '';
            var termsAccepted = form.terms && form.terms.checked;

            renderStatus(errorTarget, '', null);

            if (!email || !password || !confirmPassword) {
                renderStatus(errorTarget, 'Please complete all required fields.', 'error');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                renderStatus(errorTarget, 'Please enter a valid email address.', 'error');
                return;
            }

            if (password.length < 8) {
                renderStatus(errorTarget, 'Password must be at least 8 characters.', 'error');
                return;
            }

            if (password !== confirmPassword) {
                renderStatus(errorTarget, 'Passwords do not match. Please re-enter them.', 'error');
                return;
            }

            if (!termsAccepted) {
                renderStatus(errorTarget, 'Please accept the terms to continue.', 'error');
                return;
            }

            disableButton(submitButton, true);

            var payload = new FormData(form);
            payload.set('email', email);

            fetch('./php/signup.php', {
                method: 'POST',
                body: payload,
            })
                .then(function (response) {
                    return response.json().catch(function () {
                        throw new Error('Unable to parse server response.');
                    }).then(function (data) {
                        return { ok: response.ok, status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    if (!result.ok || !result.data || result.data.success !== true) {
                        var message = (result.data && result.data.message) || 'We could not create your account. Please try again.';
                        renderStatus(errorTarget, message, 'error');
                        disableButton(submitButton, false);
                        return;
                    }

                    renderStatus(errorTarget, result.data.message || 'Account created successfully.', 'success');

                    setTimeout(function () {
                        window.location.href = result.data.redirectTo || 'profileSetup.html';
                    }, 750);
                })
                .catch(function (error) {
                    console.error(error);
                    renderStatus(errorTarget, 'Something went wrong. Please try again in a moment.', 'error');
                    disableButton(submitButton, false);
                });
        });
    }

    function handleLogin(form) {
        var messageTarget = document.getElementById('msg');
        var submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var emailField = form.querySelector('#email');
            var passwordField = form.querySelector('#password');
            var email = emailField && emailField.value ? emailField.value.trim().toLowerCase() : '';
            var password = passwordField && passwordField.value ? passwordField.value : '';

            renderStatus(messageTarget, '', null);

            if (!email || !password) {
                renderStatus(messageTarget, 'Please enter both email and password.', 'error');
                return;
            }

            disableButton(submitButton, true);

            var payload = new FormData();
            payload.append('email', email);
            payload.append('password', password);

            fetch('./php/login.php', {
                method: 'POST',
                body: payload,
            })
                .then(function (response) {
                    return response.json().catch(function () {
                        throw new Error('Unable to parse server response.');
                    }).then(function (data) {
                        return { ok: response.ok, status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    if (!result.ok || !result.data || result.data.success !== true) {
                        var message = (result.data && result.data.message) || 'We could not sign you in. Please try again.';
                        renderStatus(messageTarget, message, 'error');
                        disableButton(submitButton, false);
                        return;
                    }

                    renderStatus(messageTarget, result.data.message || 'Signed in successfully.', 'success');

                    setTimeout(function () {
                        window.location.href = result.data.redirectTo || 'homePage.html';
                    }, 600);
                })
                .catch(function (error) {
                    console.error(error);
                    renderStatus(messageTarget, 'Something went wrong. Please try again shortly.', 'error');
                    disableButton(submitButton, false);
                });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var signUpForm = document.getElementById('signUpForm');
        if (signUpForm) {
            handleSignup(signUpForm);
        }

        var loginForm = document.getElementById('loginForm');
        if (loginForm) {
            handleLogin(loginForm);
        }
    });
})();
