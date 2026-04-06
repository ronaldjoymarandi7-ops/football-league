// FootballHub — Main JavaScript

// Toggle mobile nav
function toggleMenu() {
    document.querySelector('.nav-links').classList.toggle('open');
}

// Modal helpers
function openModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.add('open');
}
function closeModal(id) {
    const m = document.getElementById(id);
    if (m) m.classList.remove('open');
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});

// Live search filter for tables
function filterTable(inputId, tableId, colIndex) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('keyup', function() {
        const val = this.value.toLowerCase();
        const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const col = colIndex !== undefined ? cells[colIndex] : cells[0];
            if (col) {
                row.style.display = col.textContent.toLowerCase().includes(val) ? '' : 'none';
            }
        });
    });
}

// Confirm delete
function confirmDelete(form, name) {
    return confirm('Delete "' + name + '"? This action cannot be undone.');
}

// Auto-dismiss flash after 4s
setTimeout(function() {
    const flash = document.querySelector('.flash');
    if (flash) { flash.style.opacity = '0'; flash.style.transition = 'opacity 0.5s'; setTimeout(() => flash.remove(), 500); }
}, 4000);

// Animate stat numbers on page load
document.addEventListener('DOMContentLoaded', function() {
    filterTable('playerSearch', 'playersTable', 0);
    filterTable('teamSearch', 'teamsTable', 0);
    filterTable('matchSearch', 'matchesTable', 0);
    
    // Animate stat values
    document.querySelectorAll('.stat-value').forEach(el => {
        const target = parseInt(el.textContent);
        if (!isNaN(target) && target > 0) {
            let cur = 0;
            const step = Math.max(1, Math.floor(target / 40));
            const timer = setInterval(() => {
                cur = Math.min(cur + step, target);
                el.textContent = cur;
                if (cur >= target) clearInterval(timer);
            }, 20);
        }
    });
});
