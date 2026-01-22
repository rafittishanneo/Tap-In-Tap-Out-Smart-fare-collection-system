// Moderator Dashboard JS - Form validations & interactive features
document.addEventListener('DOMContentLoaded', function() {
    
    // Quick action confirmations
    const quickActions = document.querySelectorAll('.quick-actions .btn');
    quickActions.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#' || !href) {
                e.preventDefault();
                
                // Show confirmation for destructive actions
                const action = this.textContent.trim();
                if (action.includes('Update') || action.includes('Delete')) {
                    if (!confirm(`Are you sure you want to ${action.toLowerCase()}?`)) {
                        return;
                    }
                }
                
                // Simulate loading
                this.innerHTML = '⏳ Loading...';
                this.style.opacity = '0.7';
                this.disabled = true;
                
                // Redirect after delay or open modal
                setTimeout(() => {
                    alert(`${action} feature coming soon!`);
                    location.href = 'admin-dashboard.php'; // fallback
                }, 1500);
            }
        });
    });

    // Stats animation on scroll
    const observerOptions = {
        threshold: 0.3,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumber = entry.target.querySelector('.stat-number');
                const target = parseInt(statNumber.textContent);
                let current = 0;
                const increment = target / 50;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        statNumber.textContent = target;
                        clearInterval(timer);
                    } else {
                        statNumber.textContent = Math.floor(current);
                    }
                }, 30);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        observer.observe(card);
    });

    // Logout confirmation
    const logoutLink = document.querySelector('.logout');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + L for logout
        if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
            e.preventDefault();
            document.querySelector('.logout')?.click();
        }
    });
});
