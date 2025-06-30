<!DOCTYPE html>
<html lang="<?= config('App')->defaultLocale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= cms_setting('site_name') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .login-header {
            text-align: center;
            padding: 40px 40px 30px;
            border-bottom: 1px solid #f0f0f0;
        }

        .login-body {
            padding: 40px;
        }

        .site-logo {
            width: 80px;
            height: 80px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 36px;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 12px 20px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card card">
        <div class="login-header">
            <div class="site-logo">
                <i class="fas fa-lock"></i>
            </div>
            <h3 class="mb-1"><?= cms_setting('site_name') ?></h3>
            <p class="text-muted mb-0">Login to your account</p>
        </div>

        <div class="login-body">
            <?= view('partials/flash_messages') ?>

            <form action="<?= url_to('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                        <input type="email" name="email" class="form-control"
                               placeholder="admin@example.com"
                               value="<?= old('email') ?>" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        <input type="password" name="password" class="form-control"
                               placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a href="<?= site_url('forgot-password') ?>" class="text-decoration-none">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>

                <div class="text-center">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="<?= site_url('register') ?>" class="text-decoration-none ms-1">
                        Register here
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?= site_url() ?>" class="text-white text-decoration-none">
            <i class="fas fa-arrow-left me-2"></i> Back to Home
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>