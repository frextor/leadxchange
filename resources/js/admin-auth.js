/**
 * LeadXchange · Admin Auth — admin-auth.js
 * Alpine component : adminLogin()
 */
function adminLogin() {
    return {
        showPassword: false,
        submitting: false,

        togglePassword() {
            this.showPassword = !this.showPassword;
            this.$refs.passwordInput.focus();
        },

        handleSubmit() {
            this.submitting = true;
        },
    };
}
