<div class="login-container">
    <div id="login-header">
        <div style="display: flex; justify-content: flex-end">
            <img id="close-login-modal-button" class="close-button" src="/public/assets/images/icons/close_btn.png" alt="Close">
        </div>
    </div>
    <div id="login-form">
        <span class="hidden" style="text-align: center; color: red">Invalid Email or Password</span>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
        <input type="text" class="login-input input-field" name="email" placeholder="Please enter your Email">
        
        <div class="password-wrapper">
            <input type="password" class="login-input input-field" name="password" placeholder="Please enter your password" id="password-input">
            <img id="toggle-password-visibility" src="/public/assets/images/icons/show_password_icon.png" alt="Toggle password visibility">
        </div>
        <button type="submit" name="login_submit">LOGIN</button>
    </div>
</div>

<script>
    //Password toggle
    const passwordInput = document.getElementById('password-input');
    const togglePasswordVisibility = document.getElementById('toggle-password-visibility');

    const eyeIconSrc = '/public/assets/images/icons/show_password_icon.png';
    const eyeSlashIconSrc = '/public/assets/images/icons/hide_password_icon.png';

    togglePasswordVisibility.addEventListener('click', function () {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        this.src = isPassword ? eyeSlashIconSrc : eyeIconSrc;
    });

    //Modal open/close logic
    const isLoggedIn = <?= json_encode($isLoggedIn) ?>;
    const modal = document.getElementById('login-modal');
    const openButton = document.getElementById('open-login-modal-button');
    const closeButton = document.getElementById('close-login-modal-button');
    const sellButton = document.getElementById('sell-button');

    if (openButton) {
        openButton.addEventListener('click', () => modal.showModal());
    }

    function closeDialogWithAnimation() {
        modal.classList.add('closing');
        modal.addEventListener('animationend', () => {
            modal.classList.remove('closing');
            modal.close();
        }, { once: true });
    }

    if (closeButton) {
        closeButton.addEventListener('click', closeDialogWithAnimation);
    }

    modal.addEventListener('click', (event) => {
        const dialogDimensions = modal.getBoundingClientRect();
        if (
            event.clientX < dialogDimensions.left ||
            event.clientX > dialogDimensions.right ||
            event.clientY < dialogDimensions.top ||
            event.clientY > dialogDimensions.bottom
        ) {
            closeDialogWithAnimation();
        }
    });

    sellButton.addEventListener('click', () => {
        if (!isLoggedIn) {
            modal.showModal();
        } else {
            window.location.href = '/sell.php';
        }
    });

    const loginForm = document.getElementById('login-form');
    const loginBtn  = loginForm.querySelector('button[type="submit"]');
    const errorMsg  = loginForm.querySelector('.hidden');

    loginBtn.addEventListener('click', (e) => {
        e.preventDefault();

        const email    = loginForm.querySelector('input[name="email"]').value.trim();
        const password = loginForm.querySelector('input[name="password"]').value.trim();
        const redirect = loginForm.querySelector('input[name="redirect"]').value;

        if (!email || !password) {
            errorMsg.textContent = 'Please fill in both fields';
            errorMsg.classList.remove('hidden');
            return;
        }

        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);
        formData.append('redirect', redirect);

        fetch('/includes/login-redirect.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '/';
            } else {
                errorMsg.textContent = data.message || 'Invalid Email or Password';
                errorMsg.classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error('Login request failed:', err);
            errorMsg.textContent = 'An error occurred. Please try again.';
            errorMsg.classList.remove('hidden');
        });
    });

</script>
