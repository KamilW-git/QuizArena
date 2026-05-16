document.addEventListener('DOMContentLoaded', () => {
    const bellBtn = document.querySelector('.nav-bell');
    if (!bellBtn) return; // Brak ikony na stronie (np. niezalogowany)

    // Owiń .nav-bell w kontener do łatwego pozycjonowania dropdownu
    const bellContainer = document.createElement('div');
    bellContainer.className = 'nav-bell-container';
    bellBtn.parentNode.insertBefore(bellContainer, bellBtn);
    bellContainer.appendChild(bellBtn);

    const badge = document.createElement('span');
    badge.className = 'nav-bell-badge';
    badge.style.display = 'none';
    bellContainer.appendChild(badge);

    const dropdown = document.createElement('div');
    dropdown.className = 'notifications-dropdown';
    dropdown.style.display = 'none';
    
    dropdown.innerHTML = `
        <div class="notifications-header">
            <h4>Notifications</h4>
            <button class="mark-read-btn">Mark all as read</button>
        </div>
        <div class="notifications-list">
            <!-- Items via JS -->
        </div>
    `;
    bellContainer.appendChild(dropdown);

    const listEl = dropdown.querySelector('.notifications-list');
    const markReadBtn = dropdown.querySelector('.mark-read-btn');

    const loadNotifications = async () => {
        try {
            const res = await fetch('/api/notifications.php');
            if (!res.ok) return;
            const data = await res.json();
            
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }

            if (data.notifications.length === 0) {
                listEl.innerHTML = '<div class="no-notifications">No new notifications.</div>';
            } else {
                listEl.innerHTML = '';
                data.notifications.forEach(notif => {
                    const item = document.createElement('div');
                    item.className = 'notification-item' + (notif.is_read ? ' read' : '');
                    
                    let icon = '🔔';
                    if (notif.type === 'level_up') icon = '⭐';
                    if (notif.type === 'new_quiz') icon = '📝';

                    item.innerHTML = `
                        <div class="notif-icon">${icon}</div>
                        <div class="notif-content">
                            <div class="notif-title">${notif.title}</div>
                            <div class="notif-message">${notif.message}</div>
                        </div>
                    `;
                    listEl.appendChild(item);
                });
            }
        } catch (e) {
            console.error('Failed to load notifications', e);
        }
    };

    bellBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isVisible = dropdown.style.display === 'block';
        dropdown.style.display = isVisible ? 'none' : 'block';
        if (!isVisible) loadNotifications();
    });

    document.addEventListener('click', (e) => {
        if (!bellContainer.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    markReadBtn.addEventListener('click', async () => {
        try {
            const res = await fetch('/api/notifications/read.php', { method: 'POST' });
            if (res.ok) {
                badge.style.display = 'none';
                loadNotifications();
            }
        } catch (e) {
            console.error('Failed to mark read', e);
        }
    });

    // Inicjalne załadowanie stanu
    loadNotifications();
    
    // Auto-odświeżanie co 30 sekund
    setInterval(loadNotifications, 30000);
});
