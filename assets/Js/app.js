// assets/js/app.js

document.addEventListener('DOMContentLoaded', function () {
    // ---------- Toggle thème sombre / clair ----------
    const body = document.body;
    const btnMode = document.getElementById('btn-mode');

    // Récupérer le thème sauvegardé
    const savedTheme = localStorage.getItem('parc_theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    } else if (savedTheme === 'light') {
        body.classList.remove('dark-mode');
    }

    function updateModeButton() {
        if (!btnMode) return;
        const icon = btnMode.querySelector('i');
        const label = btnMode.querySelector('span');
        const isDark = body.classList.contains('dark-mode');

        if (isDark) {
            if (icon) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
            if (label) {
                label.textContent = 'Mode clair';
            }
        } else {
            if (icon) {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
            if (label) {
                label.textContent = 'Mode sombre';
            }
        }
    }

    updateModeButton();

    if (btnMode) {
        btnMode.addEventListener('click', function () {
            const isDark = body.classList.toggle('dark-mode');
            localStorage.setItem('parc_theme', isDark ? 'dark' : 'light');
            updateModeButton();
        });
    }

    // ---------- Toggle sidebar (mobile) ----------
    const btnSidebar = document.getElementById('btn-toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');

    if (btnSidebar && sidebar) {
        btnSidebar.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }
});
