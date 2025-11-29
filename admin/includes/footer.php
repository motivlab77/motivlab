        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success" id="successAlert">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $_SESSION['success_message']; ?></span>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error" id="errorAlert">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $_SESSION['error_message']; ?></span>
        <button class="alert-close" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <script src="assets/js/admin.js"></script>
</body>
</html>