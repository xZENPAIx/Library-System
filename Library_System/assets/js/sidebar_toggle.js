document.addEventListener('DOMContentLoaded', function () {
    const dropdownToggles = document.querySelectorAll('.sidebar-menu .dropdown > a.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parentLi = this.parentElement;
            // Close other open dropdowns
            document.querySelectorAll('.sidebar-menu .dropdown.open').forEach(openDropdown => {
                if (openDropdown !== parentLi) {
                    openDropdown.classList.remove('open');
                }
            });
            parentLi.classList.toggle('open');
        });
    });
});
