<?php
// =========================================================================
// MotivLab Admin Panel Footer (admin/includes/footer.php)
// Closes HTML structure and includes main JavaScript file.
// =========================================================================

// Determine the path to assets. If running from /admin/pages, it needs another ../
$asset_path = (strpos($_SERVER['PHP_SELF'], '/admin/pages/') !== false) ? '../../admin/assets/' : '../admin/assets/';

// Get current year for the copyright
$current_year = date('Y');
?>

            </div><footer class="admin-footer">
                <p>&copy; <?php echo $current_year; ?> MotivLab CMS. All Rights Reserved. | Version 1.0.0</p>
                <p>Designed and Built by MotivLab Team</p>
            </footer>

    </main></div><script src="<?php echo $asset_path; ?>js/admin.js"></script>

</body>
</html>
