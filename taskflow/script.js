// تفعيل الثيم المخزن فور تحميل الصفحة وتحديث الأيقونة إن وجدت
document.addEventListener("DOMContentLoaded", () => {
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        updateThemeUI('dark');
    } else {
        updateThemeUI('light');
    }
});

// دالة تبديل الثيم
function toggleTheme() {
    let theme = document.documentElement.getAttribute('data-theme');
    if (theme === 'dark') {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
        updateThemeUI('light');
    } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        updateThemeUI('dark');
    }
}

// دالة لتحديث شكل الأيقونة والنص في الزر بكل الصفحات تلقائياً
function updateThemeUI(theme) {
    const icon = document.getElementById('theme-icon');
    const text = document.getElementById('theme-text');
    if (icon && text) {
        if (theme === 'dark') {
            icon.className = 'fa-solid fa-sun';
            text.innerText = 'Light Mode';
        } else {
            icon.className = 'fa-solid fa-moon';
            text.innerText = 'Dark Mode';
        }
    }
}