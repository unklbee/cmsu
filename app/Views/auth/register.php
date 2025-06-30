
<!DOCTYPE html>
<html lang="<?= config('App')->defaultLocale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= cms_setting('site_name') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        /* Same styles as login page */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .login-container {
            max-width: 450px;
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
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card card">
        <div class="login-header text-center p-4">
            <div class="site-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3 class="mb-1"><?= cms_setting('site_name') ?></h3>
            <p class="text-muted mb-0">Create your account</p>
        </div>

        <div class="login-body p-4">
            <?= view('partials/flash_messages') ?>

            <form action="<?= url_to('register') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control"
                           placeholder="Choose a username"
                           value="<?= old('username') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control"
                           placeholder="your@email.com"
                           value="<?= old('email') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Min. 8 characters" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirm" class="form-control"
                           placeholder="Confirm your password" required>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" name="agree" class="form-check-input" id="agree" required>
                    <label class="form-check-label" for="agree">
                        I agree to the terms and conditions
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i> Create Account
                </button>

                <div class="text-center">
                    <span class="text-muted">Already have an account?</span>
                    <a href="<?= site_url('login') ?>" class="text-decoration-none ms-1">
                        Login here
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