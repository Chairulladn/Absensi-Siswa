
document.addEventListener("DOMContentLoaded", function () {
    const tombol = document.getElementById("sidebarToggle");
    const layout = document.querySelector(".layout");

    if (!tombol || !layout) return;

    tombol.addEventListener("click", function () {
        layout.classList.toggle("sidebar-collapsed");

        const terbuka = !layout.classList.contains("sidebar-collapsed");

        tombol.setAttribute("aria-expanded", String(terbuka));
    });
});
