// ============================================
// Navbar Dropdown Akun — Portal PPKS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const account = document.querySelector('.nav-account');
    if (!account) return;

    const btn = account.querySelector('.nav-account-btn');
    if (!btn) return;

    // Toggle dropdown saat tombol diklik
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        account.classList.toggle('open');
    });

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(e) {
        if (!account.contains(e.target)) {
            account.classList.remove('open');
        }
    });

    // Tutup dropdown saat tekan Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            account.classList.remove('open');
        }
    });
});