<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$admin_id = intval($_SESSION['user_id'] ?? 0);
$query = "SELECT * FROM admins WHERE id = {$admin_id} LIMIT 1";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

// Fallback if record not found
if (!$admin) {
  // return to login page
  header("Location: login.php");
  exit;
}
?>

<?php require_once __DIR__ . "/partials/header.php" ?>

<body>

  <div class="admin-wrapper">

    <!-- Admin Sidebar -->
    <?php require_once __DIR__ . "/partials/sidebar.php"; ?>


    <div class="admin-main">

      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link text-dark p-0 d-lg-none" id="sidebarToggleBtn" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-3"></i>
          </button>
          <div class="topbar-search">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="form-control" id="tableSearchInput" placeholder="Quick search inventory, orders...">
          </div>
        </div>

        <div class="topbar-actions">
          <a href="../front-site/index.html" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1">
            <i class="bi bi-eye"></i> View Site
          </a>

          <div class="dropdown">
            <a href="#" class="topbar-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
              <i class="bi bi-bell"></i>
              <span class="topbar-badge-dot"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width: 280px;">
              <li>
                <h6 class="dropdown-header text-uppercase small fw-bold">Recent Alerts</h6>
              </li>
              <li><a class="dropdown-item small py-2" href="orders.html"><i class="bi bi-bag-check text-success me-2"></i> New Order #NF-88291 received (PKR 14,999)</a></li>
              <li><a class="dropdown-item small py-2" href="inventory.php"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Low stock alerts</a></li>
              <li><a class="dropdown-item small py-2" href="reviews.html"><i class="bi bi-star text-primary me-2"></i> New 5-star review pending approval</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-center small text-primary fw-semibold" href="orders.html">View All Notifications</a></li>
            </ul>
          </div>

          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop" class="admin-avatar me-2" alt="Admin">
              <div class="d-none d-sm-block text-start">
                <div class="fw-semibold small leading-none"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></div>
                <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($admin['role'] ?? 'Store Director') ?></div>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2 text-muted"></i> My Profile</a></li>
              <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2 text-muted"></i> Store Settings</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="actions/logout_action.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </header>


      <main class="admin-content">

        <?php if (!empty($errors)) : ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $error) : ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="page-title">Administrator Profile</h1>
            <p class="page-subtitle">Manage personal credentials and security access stored in <code>admins</code> table.</p>
          </div>
        </div>

        <form method="POST" action="actions/update_profile_action.php">
          <div class="row g-4">

            <div class="col-lg-8">
              <div class="admin-card mb-4">
                <div class="admin-card-header">
                  <h5 class="admin-card-title">Account Details</h5>
                </div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Full Name *</label>
                      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($admin['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Administrative Role</label>
                      <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($admin['role'] ?? 'Admin') ?>" readonly>
                    </div>
                    <div class="col-md-12">
                      <label class="form-label small fw-bold">Email Address *</label>
                      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-card mb-4">
                <div class="admin-card-header">
                  <h5 class="admin-card-title">Security &amp; Password</h5>
                </div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">New Password</label>
                      <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Confirm New Password</label>
                      <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                    </div>
                    <div class="col-12">
                      <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Only enter a new password if you wish to change your current password (minimum 6 characters).</small>
                    </div>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-dark px-4 py-2">
                <i class="bi bi-check2-circle me-1"></i> Update Profile
              </button>
            </div>

            <div class="col-lg-4">
              <div class="admin-card p-4 text-center">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop" class="admin-avatar mb-3" style="width: 80px; height: 80px;" alt="Admin Avatar">
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></h5>
                <div class="text-muted small mb-2"><?= htmlspecialchars($admin['role'] ?? 'Store Director') ?></div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 mb-3"><?= htmlspecialchars($admin['status'] ?? 'Active') ?></span>

                <hr class="my-3">
                <div class="small text-muted text-start">
                  <div class="d-flex justify-content-between mb-1">
                    <span>Admin ID:</span>
                    <strong>#<?= intval($admin['id']) ?></strong>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span>Email:</span>
                    <span class="text-dark fw-semibold"><?= htmlspecialchars($admin['email'] ?? '') ?></span>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span>Role:</span>
                    <strong><?= htmlspecialchars($admin['role'] ?? 'Admin') ?></strong>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span>Account Status:</span>
                    <strong class="text-success"><?= htmlspecialchars($admin['status'] ?? 'Active') ?></strong>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </form>

      </main>
    </div>
  </div>

  <!-- Delete Confirmation Modal (Shared) -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow">
        <div class="modal-body text-center p-4">
          <div class="text-danger fs-1 mb-2"><i class="bi bi-exclamation-circle"></i></div>
          <h5 class="fw-bold mb-2">Confirm Delete?</h5>
          <p class="text-muted small mb-4">Are you sure you want to remove <strong id="deleteTargetName">this item</strong> from the database? This action is simulated for the prototype.</p>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-sm btn-danger px-3" id="btnConfirmDelete">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
</body>

</html>