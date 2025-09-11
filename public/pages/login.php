<div class="login-container">
    <form method="POST" action="../../includes/login-redirect.php">

        <div id="login-header">
            <div style="display: flex; justify-content: flex-end">
                <img id="close-login-modal-button" class="close-button" src="/public/assets/images/icons/close_btn.png" alt="Close">
            </div>
        </div>
        
        <input type="text" class="login-input input-field" name="email" placeholder="Please enter your Email">
        
        <div class="password-wrapper">
            <input type="password" class="login-input input-field" name="password" placeholder="Please enter your password" id="password-input">
            <img id="toggle-password-visibility" src="/public/assets/images/icons/show_password_icon.png" alt="Toggle password visibility">
        </div>
        <button type="submit" name="login_submit">LOGIN</button>
    </form>
</div>

<script>
        const passwordInput = document.getElementById('password-input');
        const togglePasswordVisibility = document.getElementById('toggle-password-visibility');
        
        const eyeIconSrc = '/public/assets/images/icons/show_password_icon.png';
        const eyeSlashIconSrc = '/public/assets/images/icons/hide_password_icon.png';

        togglePasswordVisibility.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            
            passwordInput.type = isPassword ? 'text' : 'password';
            this.src = isPassword ? eyeSlashIconSrc : eyeIconSrc;
        });
    </script>