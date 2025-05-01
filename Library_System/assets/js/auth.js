// Authentication-specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strengthIndicator = document.getElementById('password-strength');
            if (!strengthIndicator) return;
            
            const strength = calculatePasswordStrength(this.value);
            strengthIndicator.textContent = strength.text;
            strengthIndicator.className = 'password-strength ' + strength.class;
        });
    }
    
    // Confirm password match
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const matchIndicator = document.getElementById('password-match');
            
            if (this.value === password) {
                matchIndicator.textContent = 'Passwords match';
                matchIndicator.className = 'password-match text-success';
            } else {
                matchIndicator.textContent = 'Passwords do not match';
                matchIndicator.className = 'password-match text-danger';
            }
        });
    }
});

function calculatePasswordStrength(password) {
    let strength = 0;
    
    // Length check
    if (password.length > 7) strength++;
    if (password.length > 11) strength++;
    
    // Character variety
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Return result
    if (strength < 2) return { text: 'Weak', class: 'weak' };
    if (strength < 4) return { text: 'Moderate', class: 'moderate' };
    return { text: 'Strong', class: 'strong' };
}