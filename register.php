<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'username'   => sanitize($_POST['username']),
        'email'      => sanitize($_POST['email']),
        'password'   => $_POST['password'],
        'full_name'  => sanitize($_POST['full_name']),
        'phone'      => sanitize($_POST['phone'])
    ];

    if (strlen($data['password']) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $user_id = $auth->register($data);
        if ($user_id) {
            $success = 'Registration successful! Please wait for admin approval.';
        } else {
            $error = 'Registration failed. Username or email may already exist.';
        }
    }
}
?>

<style>
    body {
        background: url('https://images.unsplash.com/photo-1438032945739-8d7f1e5caa59?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') no-repeat center center fixed;
        background-size: cover;
        position: relative;
        min-height: 100vh;
    }
    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 0;
    }
    body::after {
        content: "✝";
        font-size: 200px;
        color: rgba(255, 255, 255, 0.05);
        position: fixed;
        bottom: 20px;
        right: 20px;
        pointer-events: none;
        font-family: serif;
        z-index: 1;
    }
    .register-container {
        max-width: 500px;
        margin: 2rem auto;
        position: relative;
        z-index: 2;
    }
    .card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 45px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .card-header {
        background: linear-gradient(135deg, #2c3e50, #1a252f);
        border-radius: 24px 24px 0 0 !important;
        padding: 30px 20px;
        text-align: center;
        border: none;
    }
    .card-header h3 {
        color: #ffd966;
        font-weight: 700;
        margin: 0;
    }
    .card-header p {
        color: #e0e0e0;
        margin-top: 5px;
    }
    .input-group {
        border-bottom: 2px solid #ddd;
        transition: border-color 0.3s;
        margin-bottom: 20px;
    }
    .input-group:focus-within {
        border-bottom-color: #f39c12;
    }
    .input-group-text {
        background: transparent;
        border: none;
        color: #f39c12;
        font-size: 1.2rem;
        padding-left: 0;
        padding-right: 12px;
    }
    .form-control {
        border: none;
        background: transparent;
        padding: 12px 0;
        font-size: 1rem;
        box-shadow: none;
    }
    .form-control:focus {
        background: transparent;
        box-shadow: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, #f39c12, #e67e22);
        border: none;
        padding: 12px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s;
        width: 100%;
        color: #fff;
    }
    .btn-primary:hover {
        transform: scale(1.02);
        background: linear-gradient(135deg, #e67e22, #d35400);
        box-shadow: 0 10px 20px rgba(243,156,18,0.3);
    }
    .alert {
        border-radius: 15px;
        border: none;
    }
    .small-note {
        font-size: 12px;
        color: #555;
        margin-top: -10px;
        margin-bottom: 15px;
        display: block;
    }
    .footer-links a {
        color: #f39c12;
        text-decoration: none;
        font-weight: 500;
    }
    .footer-links a:hover {
        text-decoration: underline;
    }
    @media (max-width: 480px) {
        .register-container { margin: 1rem auto; }
        .card-header h3 { font-size: 1.8rem; }
    }
</style>

<main class="container">
    <div class="register-container">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-plus me-2"></i> Join Our Church</h3>
                <p>Create an account to participate in events</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="full_name" placeholder="Full Name *" required>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="Username *" required>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" name="email" placeholder="Email Address *" required>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                        <input type="tel" class="form-control" name="phone" placeholder="Phone Number (optional)">
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Password *" required>
                    </div>
                    <small class="small-note"><i class="fas fa-info-circle"></i> Minimum 6 characters</small>
                    <button type="submit" class="btn btn-primary">Register <i class="fas fa-user-check ms-2"></i></button>
                </form>
                <hr>
                <div class="footer-links text-center">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>