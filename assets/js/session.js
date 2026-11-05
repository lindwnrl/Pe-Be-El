/**
 * session.js — Nina Rasa unified session helpers
 *
 * All state is stored in sessionStorage under the key "ninaRasaSession".
 * Shape of the session object:
 * {
 *   user: {
 *     isLoggedIn: boolean,
 *     username: string,
 *     nama: string,
 *     email: string,
 *     no_hp: string,
 *     jenis_kelamin: string,
 *     tanggal_lahir: string,
 *     profilePicture: string   // base-64 data URL or empty string
 *   },
 *   isProfileComplete: boolean,
 *   selectedSeat: {
 *     tableId: string,
 *     capacity: number,
 *     guests: number,
 *     date: string,
 *     time: string
 *   } | null,
 *   cart: [
 *     { name: string, price: number, quantity: number }
 *   ],
 *   lastOrder: object | null
 * }
 */

const SESSION_KEY = "ninaRasaSession";

/** Return the full session object (initialises if absent). */
function getSession() {
    const raw = sessionStorage.getItem(SESSION_KEY);
    if (raw) {
        try {
            return JSON.parse(raw);
        } catch (_) { /* fall through */ }
    }
    return _defaultSession();
}

/** Persist the full session object. */
function saveSession(session) {
    sessionStorage.setItem(SESSION_KEY, JSON.stringify(session));
}

/** Destroy the session (logout / full reset). */
function clearSession() {
    sessionStorage.removeItem(SESSION_KEY);
}

/** Merge a partial update into the session and persist. */
function updateSession(partial) {
    const session = getSession();
    const merged = _deepMerge(session, partial);
    saveSession(merged);
    return merged;
}

// ── Convenience accessors ──────────────────────────────────────────────────

function getUser()          { return getSession().user; }
function getSelectedSeat()  { return getSession().selectedSeat; }
function getCart()          { return getSession().cart || []; }
function isLoggedIn()       { return !!getSession().user.isLoggedIn; }
function isAdmin()          { return getSession().user.role === 'admin'; }
function isProfileComplete(){ return !!getSession().isProfileComplete; }
function hasSeat()          { return !!getSession().selectedSeat; }

// ── Guard helpers ──────────────────────────────────────────────────────────

/**
 * Call at the top of Menu.php and any other page that requires a seat.
 * Redirects to Kursi.php if no seat is booked.
 */
function requireSeat() {
    if (!hasSeat()) {
        sessionStorage.setItem(
            "ninaRasaFlash",
            "Silakan pilih kursi/meja terlebih dahulu sebelum memesan makanan."
        );
        window.location.href = "/Kursi.php";
        return false;
    }
    return true;
}

/**
 * Call before checkout to ensure the user is logged in with a complete profile.
 * Redirects to Profile.php if not.
 */
function requireProfile() {
    if (!isProfileComplete()) {
        sessionStorage.setItem(
            "ninaRasaFlash",
            "Silakan lengkapi data profil Anda sebelum menyelesaikan pesanan."
        );
        window.location.href = "/Profile.php";
        return false;
    }
    return true;
}

/**
 * Call before any page that requires the user to be logged in.
 * Redirects to Login.php if not.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        sessionStorage.setItem(
            "ninaRasaFlash",
            "Silakan masuk ke akun Anda terlebih dahulu."
        );
        window.location.href = "/Login.php";
        return false;
    }
    return true;
}

// ── Flash message ──────────────────────────────────────────────────────────

/**
 * Show a one-time notification banner at the top of the page if a flash
 * message is waiting.  Call this early in DOMContentLoaded on every page.
 */
function showFlashMessage() {
    const msg = sessionStorage.getItem("ninaRasaFlash");
    if (!msg) return;
    sessionStorage.removeItem("ninaRasaFlash");

    const banner = document.createElement("div");
    banner.setAttribute("role", "alert");
    banner.setAttribute("aria-live", "assertive");
    banner.className = "flash-banner";
    banner.textContent = msg;

    // Inject minimal inline style so the banner works without extra CSS.
    Object.assign(banner.style, {
        position:     "fixed",
        top:          "0",
        left:         "0",
        right:        "0",
        zIndex:       "9999",
        background:   "#b45309",
        color:        "#fff",
        padding:      "0.8rem 1.5rem",
        fontSize:     "0.95rem",
        textAlign:    "center",
        boxShadow:    "0 2px 8px rgba(0,0,0,.25)",
        cursor:       "pointer"
    });

    banner.addEventListener("click", () => banner.remove());
    document.body.prepend(banner);

    // Auto-dismiss after 5 seconds.
    setTimeout(() => { if (banner.parentNode) banner.remove(); }, 5000);
}

// ── Guest login interception modal ────────────────────────────────────────

/**
 * Show the login-required modal for guests attempting to order/book.
 * Returns false if not logged in (modal shown), true if logged in.
 */
function requireLoginModal() {
    if (isLoggedIn()) return true;
    _showLoginModal();
    return false;
}

/** Internal: inject and show the login interception modal. */
function _showLoginModal() {
    // Remove any existing modal
    const existing = document.getElementById('nr-login-modal');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'nr-login-modal';
    Object.assign(overlay.style, {
        position: 'fixed', inset: '0', zIndex: '10000',
        background: 'rgba(0,0,0,0.5)',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        padding: '1rem'
    });

    const box = document.createElement('div');
    Object.assign(box.style, {
        background: '#fff', borderRadius: '16px', padding: '2rem',
        maxWidth: '400px', width: '100%', textAlign: 'center',
        boxShadow: '0 10px 40px rgba(0,0,0,0.2)'
    });

    box.innerHTML = `
        <div style="font-size:2.5rem;margin-bottom:0.5rem;">🔒</div>
        <h2 style="margin-bottom:0.75rem;font-size:1.25rem;color:#35231e;">Akun Diperlukan</h2>
        <p style="color:#685852;margin-bottom:1.5rem;font-size:0.95rem;">
            Kamu perlu login untuk melakukan pemesanan.
        </p>
        <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
            <button id="nr-modal-login" style="
                padding:0.6rem 1.5rem;background:#770900;color:#fff;
                border:none;border-radius:8px;font-size:1rem;cursor:pointer;font-weight:600;">
                Login
            </button>
            <button id="nr-modal-cancel" style="
                padding:0.6rem 1.5rem;background:#f5f5f5;color:#35231e;
                border:1px solid #ddd;border-radius:8px;font-size:1rem;cursor:pointer;">
                Lanjutkan sebagai Tamu
            </button>
        </div>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    document.getElementById('nr-modal-login').addEventListener('click', function() {
        window.location.href = '/Login.php';
    });
    document.getElementById('nr-modal-cancel').addEventListener('click', function() {
        overlay.remove();
    });
    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) overlay.remove();
    });
}

/**
 * Page-load guard for pages that require login + complete profile.
 * Call on Dashboard, Menu, Kursi.
 * If logged in but profile incomplete → redirect to Profile.php
 */
function enforceProfileGate() {
    if (isLoggedIn() && !isProfileComplete()) {
        sessionStorage.setItem(
            'ninaRasaFlash',
            'Silakan lengkapi profil Anda terlebih dahulu sebelum melanjutkan.'
        );
        window.location.href = '/Profile.php';
        return false;
    }
    return true;
}

// ── Internal helpers ───────────────────────────────────────────────────────

function _defaultSession() {
    return {
        user: {
            isLoggedIn:     false,
            username:       "",
            nama:           "",
            email:          "",
            no_hp:          "",
            jenis_kelamin:  "",
            tanggal_lahir:  "",
            profilePicture: ""
        },
        isProfileComplete: false,
        selectedSeat:      null,
        cart:              [],
        lastOrder:         null
    };
}

/** Shallow-merge objects one level deep (does not recurse into arrays). */
function _deepMerge(base, partial) {
    const result = Object.assign({}, base);
    for (const key of Object.keys(partial)) {
        if (
            partial[key] !== null &&
            typeof partial[key] === "object" &&
            !Array.isArray(partial[key]) &&
            typeof base[key] === "object" &&
            base[key] !== null &&
            !Array.isArray(base[key])
        ) {
            result[key] = Object.assign({}, base[key], partial[key]);
        } else {
            result[key] = partial[key];
        }
    }
    return result;
}
