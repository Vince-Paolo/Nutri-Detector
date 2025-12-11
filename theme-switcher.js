/* =====================================================
   DARK MODE & THEME CUSTOMIZER LOGIC
===================================================== */
const toggleSwitch = document.getElementById('themeToggle'); // Corrected ID from 'themeSwitch'
const themeLabel = document.getElementById('themeLabel');
const body = document.body;
const root = document.documentElement;

// Smooth background glow animation
function animateTheme() {
    body.classList.add("theme-changing");
    setTimeout(() => {
        body.classList.remove("theme-changing");
    }, 500);
}

// --- INITIAL LOAD ---

function applyThemeState(isDark) {
    if (isDark) {
        body.classList.add('dark');
        if (toggleSwitch) toggleSwitch.checked = true;
        if (themeLabel) themeLabel.textContent = "Dark Mode";
    } else {
        body.classList.remove('dark');
        if (toggleSwitch) toggleSwitch.checked = false;
        if (themeLabel) themeLabel.textContent = "Default";
    }
}

// Load saved theme (dark/light)
const savedTheme = localStorage.getItem('nutri_theme'); 
applyThemeState(savedTheme === 'dark');

// Load saved custom colors
if (localStorage.getItem("custom_bg")) {
    root.style.setProperty("--bg", localStorage.getItem("custom_bg"));
    root.style.setProperty("--bg2", localStorage.getItem("custom_bg")); // Sync gradient
}
if (localStorage.getItem("custom_card")) {
    root.style.setProperty("--card", localStorage.getItem("custom_card"));
}
if (localStorage.getItem("custom_primary")) {
    root.style.setProperty("--accent", localStorage.getItem("custom_primary"));
}


// --- THEME TOGGLE EVENT ---
if (toggleSwitch) {
    toggleSwitch.addEventListener('change', () => {
        const isDark = toggleSwitch.checked;
        applyThemeState(isDark);
        localStorage.setItem('nutri_theme', isDark ? 'dark' : 'light');
        animateTheme();
    });
}


/* =====================================================
   THEME CUSTOMIZER PANEL (OPEN/CLOSE)
===================================================== */
const customBtn = document.getElementById('openCustomizer');
const customPanel = document.getElementById('themeCustomizer');
const customizerClose = document.getElementById("customizerClose");

if (customBtn && customPanel) {
    customBtn.addEventListener("click", () => {
        customPanel.classList.toggle("open");
    });
}

if (customizerClose && customPanel) {
    customizerClose.addEventListener("click", () => {
        customPanel.classList.remove("open");
    });
}

/* =====================================================
   COLOR PICKERS WITH ANIMATIONS
===================================================== */

// Background color picker
const bgPicker = document.getElementById("bgColorPicker");
if (bgPicker) {
    bgPicker.addEventListener("input", (e) => {
        root.style.setProperty("--bg", e.target.value);
        root.style.setProperty("--bg2", e.target.value); 
        localStorage.setItem("custom_bg", e.target.value);
        animateTheme();
    });
}

// Card color picker
const cardPicker = document.getElementById("cardColorPicker");
if (cardPicker) {
    cardPicker.addEventListener("input", (e) => {
        root.style.setProperty("--card", e.target.value);
        localStorage.setItem("custom_card", e.target.value);

        document.querySelectorAll('.card')
            .forEach(el => {
                el.classList.add('card-animate');
                setTimeout(() => el.classList.remove('card-animate'), 350);
            });
    });
}

// Accent color picker
const accentPicker = document.getElementById("accentColorPicker");
if (accentPicker && customBtn) {
    accentPicker.addEventListener("input", (e) => {
        root.style.setProperty("--accent", e.target.value);
        localStorage.setItem("custom_primary", e.target.value);

        customBtn.classList.add('accent-animate');
        setTimeout(() => customBtn.classList.remove('accent-animate'), 350);
    });
}

/* =====================================================
   RESET CUSTOM THEME
===================================================== */
const resetBtn = document.getElementById("resetTheme");
if (resetBtn) {
    resetBtn.addEventListener("click", () => {
        localStorage.removeItem("custom_bg");
        localStorage.removeItem("custom_card");
        localStorage.removeItem("custom_primary");
        location.reload();
    });
}
