/**
 * =========================================================================
 * MotivLab Admin Panel Core JavaScript (admin/assets/js/admin.js)
 * Handles UI interactions, navigation, and other front-end logic.
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', function() {

    // --------------------------------------------------
    // 1. Sidebar Toggle Logic (Mobile Responsiveness)
    // --------------------------------------------------
    const sidebar = document.getElementById('sidebar');
    const appWrapper = document.querySelector('.app-wrapper');
    const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    const topbarToggleBtn = document.getElementById('topbar-toggle-btn');

    if (sidebar && appWrapper) {
        // Function to toggle the sidebar state
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            appWrapper.classList.toggle('sidebar-open');
        }

        // Event listener for the toggle buttons
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', toggleSidebar);
        }
        if (topbarToggleBtn) {
            topbarToggleBtn.addEventListener('click', toggleSidebar);
        }
        
        // Close sidebar if a link is clicked (useful on mobile)
        const navLinks = sidebar.querySelectorAll('.nav-item a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    // Timeout allows navigation to happen before closing sidebar
                    setTimeout(toggleSidebar, 300); 
                }
            });
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992 && sidebar.classList.contains('open') && 
                !sidebar.contains(event.target) && 
                !topbarToggleBtn.contains(event.target)) {
                
                toggleSidebar();
            }
        });
    }

    // --------------------------------------------------
    // 2. User Profile Dropdown
    // --------------------------------------------------
    const userDropdownContainer = document.querySelector('.user-dropdown-container');
    const userDropdownMenu = document.querySelector('.user-dropdown-menu');

    if (userDropdownContainer && userDropdownMenu) {
        userDropdownContainer.addEventListener('click', function(event) {
            // Toggle the 'show' class
            userDropdownMenu.classList.toggle('show');
            event.stopPropagation(); // Prevents click from bubbling up to document
        });

        // Close the dropdown if the user clicks anywhere else on the page
        document.addEventListener('click', function() {
            if (userDropdownMenu.classList.contains('show')) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }

    // --------------------------------------------------
    // 3. Sidebar Dropdown Menu Logic
    // --------------------------------------------------
    const dropdownToggles = document.querySelectorAll('.nav-dropdown .dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            const parentLi = this.closest('.nav-dropdown');
            const dropdownMenu = parentLi.querySelector('.dropdown-menu');
            
            // Toggle the 'open' class on the parent list item
            parentLi.classList.toggle('open');
            
            // Toggle the 'show' class on the dropdown menu
            if (dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
                // Set max-height to 0 to trigger CSS transition for closing
                dropdownMenu.style.maxHeight = '0';
            } else {
                dropdownMenu.classList.add('show');
                // Set max-height to its scroll height to allow CSS transition for opening
                dropdownMenu.style.maxHeight = dropdownMenu.scrollHeight + "px";
            }
        });
    });

    // --------------------------------------------------
    // 4. Input File Name Display
    // This is useful for file upload fields (e.g., in Media Manager)
    // --------------------------------------------------
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            let fileName = 'No file chosen';
            if (this.files && this.files.length > 0) {
                fileName = this.files[0].name;
            }
            
            // Find a sibling element to display the file name (e.g., a next span/div)
            const displayElement = this.closest('.form-group').querySelector('.file-name-display');
            if (displayElement) {
                displayElement.textContent = fileName;
            } else {
                // If a specific display element doesn't exist, create one dynamically
                const span = document.createElement('span');
                span.classList.add('file-name-display');
                span.textContent = fileName;
                this.parentNode.insertBefore(span, this.nextSibling);
            }
        });
    });


    // --------------------------------------------------
    // 5. Delete Confirmation
    // Prevents accidental deletion by requiring user confirmation
    // Apply this class to any delete button/link: <a href="delete.php?id=1" class="confirm-delete">
    // --------------------------------------------------
    const deleteLinks = document.querySelectorAll('.confirm-delete');

    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you absolutely sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
});
