// Confirm delete
function confirmDelete() {
    return confirm('Are you sure you want to delete this item?');
}

// Show loading on form submit
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.innerHTML = 'Processing...';
                btn.disabled = true;
            }
        });
    });
});

// Auto-hide alerts after 3 seconds
const alerts = document.querySelectorAll('.alert');
alerts.forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
});