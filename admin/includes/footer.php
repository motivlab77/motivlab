            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar functionality
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const welcomeText = document.querySelector('.welcome-text');
        
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }
        
        function closeSidebar() {
            sidebar.classList.remove('open');
            document.body.style.overflow = '';
        }
        
        // Toggle sidebar
        sidebarToggle.addEventListener('click', toggleSidebar);
        
        // Close sidebar when clicking overlay
        mobileOverlay.addEventListener('click', closeSidebar);
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 768 && 
                sidebar.classList.contains('open') && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                closeSidebar();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.add('open');
                document.body.style.overflow = '';
            } else {
                sidebar.classList.remove('open');
            }
            
            // Hide welcome text on very small screens
            if (window.innerWidth < 400) {
                welcomeText.style.display = 'none';
            } else {
                welcomeText.style.display = 'block';
            }
        });
        
        // Initialize on load
        window.addEventListener('load', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.add('open');
            }
            
            if (window.innerWidth < 400) {
                welcomeText.style.display = 'none';
            }
        });
    </script>
</body>
</html>
