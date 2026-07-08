
// Theme Manager
const ThemeManager = {
    init() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        this.updateTogglerIcon(savedTheme);
        
        const toggler = document.getElementById('theme-toggle');
        if (toggler) {
            toggler.addEventListener('click', () => this.toggle());
        }
    },
    toggle() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        this.updateTogglerIcon(newTheme);
        window.dispatchEvent(new CustomEvent('themechanged', { detail: { theme: newTheme } }));
    },
    updateTogglerIcon(theme) {
        const icon = document.querySelector('#theme-toggle i');
        if (icon) {
            icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
    }
};

// Loader Manager
const hideLoader = () => {
    const loader = document.getElementById('page-loader');
    if (loader) {
        loader.classList.add('loader-hidden');
    }
};


document.addEventListener('DOMContentLoaded', hideLoader);


window.addEventListener('load', hideLoader);


setTimeout(hideLoader, 2000);


document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
    
    // Initialize DataTables 
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.datatable-custom').DataTable({
            responsive: true,
            pageLength: 10,
            ordering: true,
            lengthMenu: [5, 10, 25, 50],
            language: {
                search: "",
                searchPlaceholder: "Search records...",
                lengthMenu: "Show _MENU_",
                paginate: {
                    next: '<i class="fa-solid fa-chevron-right"></i>',
                    previous: '<i class="fa-solid fa-chevron-left"></i>'
                }
            },
            drawCallback: function() {
                $('.dataTables_filter input').addClass('form-control form-control-custom d-inline-block w-auto ms-2');
                $('.dataTables_length select').addClass('form-select form-control-custom d-inline-block w-auto ms-2 me-2');
            }
        });
    }

    //Alert2 Confirmation Hook
    window.confirmAction = function(title, text, confirmBtnText, callbackUrl) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#ef4444',
            confirmButtonText: confirmBtnText,
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#1e293b',
            customClass: { popup: 'glass-card' }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = callbackUrl;
            }
        });
    };
});
