<?php
require_once '../includes/header.php';

if (!$auth->isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (int)$_POST['user_id'];
    if (isset($_POST['approve'])) {
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            addNotification($user_id, 'Account Approved', 'Your account has been approved. You can now login.', 'success');
            $success = "User approved.";
        } else {
            $error = "Failed to approve user.";
        }
    } elseif (isset($_POST['reject'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success = "User rejected and removed.";
        } else {
            $error = "Failed to reject user.";
        }
    } elseif (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success = "User deleted.";
        } else {
            $error = "Failed to delete user.";
        }
    } elseif (isset($_POST['deactivate'])) {
        $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success = "User deactivated.";
        } else {
            $error = "Failed to deactivate user.";
        }
    } elseif (isset($_POST['activate'])) {
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success = "User activated.";
        } else {
            $error = "Failed to activate user.";
        }
    }
}

// Get users grouped by status
$pending = $conn->query("SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC");
$active = $conn->query("SELECT * FROM users WHERE status = 'active' AND role = 'member' ORDER BY created_at DESC");
$inactive = $conn->query("SELECT * FROM users WHERE status = 'inactive' ORDER BY created_at DESC");
?>

<div class="row">
    <div class="col-12">
        <h2>Member Management</h2>
        <?php if (isset($success)) echo '<div class="alert alert-success">'.$success.'</div>'; ?>
        <?php if (isset($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>

        <!-- Pending Members -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Pending Approval (<?php echo $pending->num_rows; ?>)</h5>
            </div>
            <div class="card-body">
                <?php if ($pending->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                32<th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Actions</th>  </>
                            </thead>
                            <tbody>
                                <?php while ($user = $pending->fetch_assoc()): ?>
                                    32
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="approve" class="btn btn-sm btn-success">Approve</button>
                                                <button type="submit" name="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject and delete this user?')">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No pending members.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Members -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Active Members (<?php echo $active->num_rows; ?>)</h5>
            </div>
            <div class="card-body">
                <?php if ($active->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                32<th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Actions</th> </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $active->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="deactivate" class="btn btn-sm btn-warning">Deactivate</button>
                                                <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user permanently?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No active members.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Inactive Members -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Inactive Members (<?php echo $inactive->num_rows; ?>)</h5>
            </div>
            <div class="card-body">
                <?php if ($inactive->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                32<th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Actions</th> </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $inactive->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="activate" class="btn btn-sm btn-success">Activate</button>
                                                <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user permanently?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No inactive members.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>