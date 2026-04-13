    </main>
    
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Smart Church Event Scheduler</h5>
                    <p>Efficiently coordinate and manage church events with our intelligent scheduling system.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Home</a></li>
                        <li><a href="events.php" class="text-white-50">Events</a></li>
                        <?php if (isset($auth) && $auth->isLoggedIn()): ?>
                            <li><a href="member/dashboard.php" class="text-white-50">Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope"></i> info@churchscheduler.com</li>
                        <li><i class="fas fa-phone"></i> +256 752611682</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Smart Church Event Scheduler. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>

<?php
// Close database connection if it exists
if (isset($conn)) {
    $conn->close();
}
?>