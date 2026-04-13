<?php
require_once '../includes/header.php';

if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user = $auth->getCurrentUser();
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $bio = sanitize($_POST['bio']);
    
    // Handle profile image upload
    $profile_image = isset($user['profile_image']) ? $user['profile_image'] : null;
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload = uploadFile($_FILES['profile_image'], 'profiles');
        if (isset($upload['success'])) {
            // Delete old image if exists
            if ($profile_image && file_exists('../' . $profile_image)) {
                unlink('../' . $profile_image);
            }
            $profile_image = $upload['path'];
            $success_message = 'Profile updated successfully! Image uploaded.';
        } else {
            $error_message = $upload['error'];
        }
    }
    
    // Update user profile
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, bio = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $full_name, $phone, $bio, $profile_image, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        if (empty($success_message)) {
            $success_message = 'Profile updated successfully!';
        }
        // Refresh user data
        $user = $auth->getCurrentUser();
    } else {
        $error_message = 'Failed to update profile.';
    }
}

// Get user profile image path
$profile_image_path = isset($user['profile_image']) && !empty($user['profile_image']) 
    ? $user['profile_image'] 
    : 'uploads/profiles/default-avatar.png';

// Check if the file exists
$full_image_path = '../' . $profile_image_path;
if (!file_exists($full_image_path) && $profile_image_path != 'uploads/profiles/default-avatar.png') {
    $profile_image_path = 'uploads/profiles/default-avatar.png';
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <img src="../<?php echo $profile_image_path; ?>" 
                     class="profile-image mb-3" 
                     alt="Profile Image"
                     style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #3498db;"
                     onerror="this.src='../uploads/profiles/default-avatar.png'">
                <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><i class="fas fa-calendar"></i> Member since <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Edit Profile</h4>
            </div>
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo isset($user['bio']) ? htmlspecialchars($user['bio']) : ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        <small class="text-muted">Allowed: JPG, PNG, GIF. Max size: 5MB</small>
                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <img id="preview" src="#" style="max-width: 100px; max-height: 100px; border-radius: 50%;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview
document.getElementById('profile_image').onchange = function(evt) {
    var tgt = evt.target || window.event.srcElement,
        files = tgt.files;
    
    if (FileReader && files && files.length) {
        var fr = new FileReader();
        fr.onload = function () {
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('preview').src = fr.result;
        }
        fr.readAsDataURL(files[0]);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>