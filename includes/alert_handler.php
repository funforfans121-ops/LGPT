
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to fade out and remove alerts
    function handleAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            // Add transition effect
            alert.style.transition = 'opacity 0.5s ease-in-out';
            
            // Set timeout to fade out after 3 seconds
            setTimeout(() => {
                alert.style.opacity = '0';
                
                // Remove the alert after fade out
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 3000);
        });
    }

    // Run when page loads
    handleAlerts();
});
</script>

<style>
.alert {
    opacity: 1;
    transition: opacity 0.5s ease-in-out;
}
</style>