// App Interactions - AniLog

// Tab Switching 
function initTabs() {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.getAttribute('data-tab');


            tabs.forEach(t => t.classList.remove('active'));


            this.classList.add('active');


            tabContents.forEach(content => {
                content.style.display = 'none';
            });


            const targetContent = document.getElementById(target);
            if (targetContent) {
                targetContent.style.display = 'block';
            }
        });
    });
}

// Initialize progress bars animation

function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');

    progressBars.forEach(bar => {
        const width = bar.getAttribute('data-progress');
        setTimeout(() => {
            bar.style.width = width + '%';
        }, 100);
    });
}

// Quick update episode

function updateEpisode(userAnimeId, increment = true) {
    const episodeInput = document.querySelector(`input[data-id="${userAnimeId}"]`);
    if (!episodeInput) return;

    let current = parseInt(episodeInput.value) || 0;
    const max = parseInt(episodeInput.getAttribute('max')) || 999;

    if (increment && current < max) {
        current++;
    } else if (!increment && current > 0) {
        current--;
    }

    episodeInput.value = current;

    // Submit via AJAX
    updateProgress(userAnimeId, current);
}

// Update progress via AJAX

function updateProgress(userAnimeId, episode) {
    const formData = new FormData();
    formData.append('action', 'update_episode');
    formData.append('user_anime_id', userAnimeId);
    formData.append('current_episode', episode);

    fetch('api/user_anime_crud.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update progress bar
                const progressBar = document.querySelector(`[data-id="${userAnimeId}"] .progress-bar`);
                if (progressBar) {
                    const percentage = (episode / progressBar.getAttribute('data-max')) * 100;
                    progressBar.style.width = percentage + '%';
                }
                showToast('Progress updated!', 'success');
            } else {
                showToast('Failed to update progress', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
}

// Show toast notification

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#14B8A6' : '#EF4444'};
        color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Search/Filter functionality

function initSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const cards = document.querySelectorAll('.anime-card');

        cards.forEach(card => {
            const title = card.querySelector('.anime-title').textContent.toLowerCase();
            if (title.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

// Watch Status Listener (Auto-fill episodes when completed)

function initWatchStatusListener() {
    const watchStatus = document.getElementById('watch_status');
    const currentEpisode = document.getElementById('current_episode');

    if (!watchStatus || !currentEpisode) return;

    watchStatus.addEventListener('change', function () {
        if (this.value === 'completed') {
            const totalEpisodes = currentEpisode.getAttribute('max');
            if (totalEpisodes) {
                currentEpisode.value = totalEpisodes;
            }
        }
    });
}

// Initialize on page load

document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    animateProgressBars();
    initSearch();
    initWatchStatusListener();
});

// Add anime to list via AJAX

function addToMyList(event, form) {
    event.preventDefault();

    // Disable button to prevent double submit
    const button = form.querySelector('button');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.textContent = 'Adding...';

    const formData = new FormData(form);
    formData.append('ajax', 'true');

    fetch('api/user_anime_crud.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');

                // Create status badge div
                const statusDiv = document.createElement('div');
                statusDiv.style.margin = '1rem 0';
                statusDiv.innerHTML = '<span class="status-badge status-plan-to-watch">Plan to Watch</span>';

                // Create View Details button
                const viewDetailsBtn = document.createElement('a');
                viewDetailsBtn.href = `view_anime.php?id=${data.anime_id}`;
                viewDetailsBtn.className = 'btn btn-primary btn-sm btn-block';
                viewDetailsBtn.textContent = 'View Details';

                // Insert new elements
                form.parentNode.insertBefore(statusDiv, form);
                form.replaceWith(viewDetailsBtn);

            } else {
                showToast(data.message || 'Failed to add anime', 'error');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        });
}

// Delete review reply
function deleteReply(replyId) {
    if (!confirm('Are you sure you want to delete this reply? This will also delete all sub-replies.')) {
        return;
    }

    const formData = new FormData();
    formData.append('reply_id', replyId);

    fetch('api/delete_reply.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Reload page to reflect changes (simplest for nested structures)
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Failed to delete reply', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
