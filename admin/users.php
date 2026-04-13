<?php
require_once '../includes/header.php';
if (!$auth->isAdmin()) { header('Location: ../index.php'); exit; }

$users = getAllUsers();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $auth->isOwner()) {
    $user_id = (int)$_POST['user_id'];
    if (isset($_POST['make_admin'])) updateUserRole($user_id, 'admin') and $success = "User promoted to admin.";
    elseif (isset($_POST['remove_admin'])) updateUserRole($user_id, 'member') and $success = "Admin removed.";
    elseif (isset($_POST['deactivate'])) deactivateUser($user_id) and $success = "User deactivated.";
    elseif (isset($_POST['activate'])) activateUser($user_id) and $success = "User activated.";
    $users = getAllUsers();
}
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">User Management</h4>
            </div>
            <div class="card-body">
                <?php if (!$auth->isOwner()): ?>
                    <div class="alert alert-info">Only the system owner can manage admin privileges.</div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td>
                                        <?php
                                        // Build absolute URL for profile image
                                        $profile_img = BASE_URL . 'uploads/profiles/default-avatar.png';
                                        if (!empty($u['profile_image']) && $u['profile_image'] != 'default-avatar.png') {
                                            $full_path = dirname(__DIR__) . '/' . $u['profile_image'];
                                            if (file_exists($full_path)) {
                                                $profile_img = BASE_URL . $u['profile_image'];
                                            }
                                        }
                                        ?>
                                        <img src="<?php echo $profile_img; ?>" width="40" height="40" class="rounded-circle" style="object-fit: cover;" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>uploads/profiles/default-avatar.png'">
                                    </td>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><span class="badge bg-<?php echo $u['role']=='owner'?'danger':($u['role']=='admin'?'warning':'secondary'); ?>"><?php echo strtoupper($u['role']); ?></span></td>
                                    <td><span class="badge bg-<?php echo $u['status']=='active'?'success':'danger'; ?>"><?php echo strtoupper($u['status']); ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <?php if ($auth->isOwner() && $u['role'] != 'owner'): ?>
                                            <?php if ($u['role'] == 'member'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" name="make_admin" class="btn btn-sm btn-warning">Make Admin</button>
                                                </form>
                                            <?php elseif ($u['role'] == 'admin'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" name="remove_admin" class="btn btn-sm btn-secondary">Remove Admin</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($u['status'] == 'active'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" name="deactivate" class="btn btn-sm btn-danger">Deactivate</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" name="activate" class="btn btn-sm btn-success">Activate</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>