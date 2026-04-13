// Smart Church Event Scheduler JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Date validation for event creation
    const eventDateInput = document.querySelector('#event_date');
    if (eventDateInput) {
        const today = new Date().toISOString().split('T')[0];
        eventDateInput.setAttribute('min', today);
    }
    
    // Time validation
    const startTimeInput = document.querySelector('#start_time');
    const endTimeInput = document.querySelector('#end_time');
    
    if (startTimeInput && endTimeInput) {
        startTimeInput.addEventListener('change', function() {
            endTimeInput.setAttribute('min', this.value);
        });
    }
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Image preview for uploads
    const imageInput = document.querySelector('#event_image, #profile_image');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('#image-preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Search functionality
    const searchInput = document.querySelector('#search-events');
    const eventCards = document.querySelectorAll('.event-card');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            eventCards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const description = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Mobile responsive menu toggle
    const navbarToggler = document.querySelector('.navbar-toggler');
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            document.querySelector('.navbar-collapse').classList.toggle('show');
        });
    }
});

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// AJAX functions
async function registerForEvent(eventId) {
    try {
        const response = await fetch(`ajax/register-event.php?id=${eventId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        const data = await response.json();
        if (data.success) {
            showNotification('Successfully registered for event!', 'success');
            location.reload();
        } else {
            showNotification(data.error || 'Registration failed', 'danger');
        }
    } catch (error) {
        showNotification('An error occurred', 'danger');
    }
}

async function addComment(eventId, comment) {
    try {
        const response = await fetch('ajax/add-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ event_id: eventId, comment: comment })
        });
        const data = await response.json();
        if (data.success) {
            showNotification('Comment added!', 'success');
            location.reload();
        } else {
            showNotification('Failed to add comment', 'danger');
        }
    } catch (error) {
        showNotification('An error occurred', 'danger');
    }
}