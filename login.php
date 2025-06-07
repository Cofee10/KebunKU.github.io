<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($role)) {
        $error = "Silakan pilih peran Anda (Admin/Petani/Distributor)";
    } else if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi";
    } else {
        $table = '';
        $id_field = '';
        
        switch($role) {
            case 'admin':
                $table = 'admin';
                $id_field = 'id_admin';
                break;
            case 'petani':
                $table = 'petani';
                $id_field = 'id_petani';
                break;
            case 'distributor':
                $table = 'distributor';
                $id_field = 'id_distributor';
                break;
            default:
                $error = "Peran tidak valid";
                break;
        }

        if (!isset($error)) {
            try {
                $query = "SELECT * FROM $table WHERE email = ?";
                error_log("[Login Attempt] Query: $query with email: $email, role: $role");
                
                $stmt = $pdo->prepare($query);
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    error_log("[Login Debug] Found user with email: $email");
                    error_log("[Login Debug] Stored password hash: " . substr($user['password'], 0, 10) . "...");
                    
                    if (password_verify($password, $user['password'])) {
                        error_log("[Login Success] User authenticated successfully");
                        $_SESSION['user_id'] = $user[$id_field];
                        $_SESSION['role'] = $role;
                        $_SESSION['name'] = $user['nama_' . $role];
                        
                        header("Location: dashboard/$role/index.php");
                        exit();
                    } else {
                        error_log("[Login Failed] Password verification failed for user: $email");
                        $error = "Password yang Anda masukkan salah";
                    }
                } else {
                    error_log("[Login Failed] No user found with email: $email in table: $table");
                    $error = "Email tidak ditemukan untuk peran yang dipilih";
                }
            } catch (PDOException $e) {
                error_log("[Login Error] Database error: " . $e->getMessage());
                $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
            }
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();

    if ($adminCount == 0) {
        $defaultAdminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin (nama_admin, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['Admin', 'admin@kebunku.com', $defaultAdminPassword]);
        error_log("[Setup] Created default admin account: admin@kebunku.com");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM petani");
    $stmt->execute();
    $petaniCount = $stmt->fetchColumn();

    if ($petaniCount == 0) {
        $defaultPetaniPassword = password_hash("petani123", PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO petani (nama_petani, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['Petani', 'petani@kebunku.com', $defaultPetaniPassword]);
        error_log("[Setup] Created default petani account: petani@kebunku.com");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM distributor");
    $stmt->execute();
    $distributorCount = $stmt->fetchColumn();

    if ($distributorCount == 0) {
        $defaultDistributorPassword = password_hash("distributor123", PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO distributor (nama_distributor, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['Distributor', 'distributor@kebunku.com', $defaultDistributorPassword]);
        error_log("[Setup] Created default distributor account: distributor@kebunku.com");
    }
} catch (PDOException $e) {
    error_log("[Setup Error] Error creating default accounts: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KebunKU</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #4CAF50;
            --text-primary: #212121;
            --text-secondary: #757575;
            --white: #FFFFFF;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(46, 125, 50, 0.9), rgba(76, 175, 80, 0.9)), url('https://images.unsplash.com/photo-1523741543316-beb7fc7023d8?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .navbar-brand {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            color: #333;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            padding: 3rem;
            margin-top: 2rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-header img {
            height: 45px;
            width: 45px;
            margin-bottom: 1.5rem;
            object-fit: contain;
        }
        
        .login-header h4 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }
        
        .login-header p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .role-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .role-option {
            flex: 1;
            text-align: center;
            padding: 1.5rem 1rem;
            border: 2px solid #E0E0E0;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .role-option:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .role-option.active {
            border-color: var(--primary-color);
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .role-option.active i,
        .role-option.active span {
            color: white;
        }
        
        .role-option i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .role-option span {
            display: block;
            font-size: 1rem;
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-control {
            border-radius: 15px;
            padding: 0.8rem 1.2rem;
            border: 2px solid #E0E0E0;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.15);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 15px;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-back {
            color: var(--text-secondary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            color: var(--primary-color);
        }
        
        .alert {
            border-radius: 15px;
            font-weight: 500;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            border: none;
            background: linear-gradient(45deg, #f44336, #e57373);
            color: white;
        }
        
        /* Responsive styles */
        @media (max-width: 991.98px) {
            .login-container {
                max-width: 450px;
                padding: 2.5rem;
            }
        }

        @media (max-width: 767.98px) {
            body {
                padding: 1rem;
            }

            .login-container {
                padding: 2rem;
                margin-top: 1rem;
            }

            .login-header h4 {
                font-size: 1.5rem;
            }

            .login-header p {
                font-size: 1rem;
            }

            .role-selector {
                flex-direction: column;
                gap: 0.75rem;
            }

            .role-option {
                padding: 1rem;
            }

            .role-option i {
                font-size: 1.75rem;
                margin-bottom: 0.75rem;
            }

            .role-option span {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 575.98px) {
            .login-container {
                padding: 1.5rem;
            }

            .login-header {
                margin-bottom: 2rem;
            }

            .login-header img {
                height: 40px;
                width: 40px;
                margin-bottom: 1rem;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control {
                font-size: 1rem;
                padding: 0.5rem 0.75rem;
            }

            .btn {
                width: 100%;
                padding: 0.75rem;
            }

            .back-to-home {
                font-size: 0.9rem;
            }
        }

        /* Form improvements */
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .back-to-home {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
        }

        .back-to-home:hover {
            color: var(--secondary-color);
            transform: translateX(-5px);
        }

        .back-to-home i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">KebunKU</a>
        </div>
    </nav>

    <div class="login-container">
        <div class="login-header">
            <img src="assets/img/logo.png" alt="KebunKU Logo" class="mb-4">
            <h4>Selamat Datang di KebunKU</h4>
            <p>Silakan pilih peran dan masuk ke akun Anda</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="role-selector">
                <div class="role-option" data-role="admin">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin</span>
                </div>
                <div class="role-option" data-role="petani">
                    <i class="fas fa-seedling"></i>
                    <span>Petani</span>
                </div>
                <div class="role-option" data-role="distributor">
                    <i class="fas fa-truck"></i>
                    <span>Distributor</span>
                </div>
            </div>

            <input type="hidden" name="role" id="selected_role" value="">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>
                Masuk
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleOptions = document.querySelectorAll('.role-option');
            const selectedRoleInput = document.getElementById('selected_role');

            roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    roleOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    selectedRoleInput.value = this.dataset.role;
                });
            });

            const urlParams = new URLSearchParams(window.location.search);
            const role = urlParams.get('role');
            if (role) {
                const option = document.querySelector(`.role-option[data-role="${role}"]`);
                if (option) {
                    option.click();
                }
            }
        });
    </script>
</body>
</html> 