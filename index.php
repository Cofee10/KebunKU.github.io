<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KebunKU - Platform Manajemen Pertanian Modern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #4CAF50;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .hero {
            background: linear-gradient(rgba(46, 125, 50, 0.9), rgba(76, 175, 80, 0.9)), url('https://images.unsplash.com/photo-1523741543316-beb7fc7023d8?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        
        .feature-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            color: var(--primary-color);
            margin-bottom: 3rem;
            font-weight: bold;
        }
        
        .footer {
            background-color: #333;
            color: white;
            padding: 50px 0;
        }
        
        .footer-title {
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }
        
        .footer-link {
            color: white;
            text-decoration: none;
        }
        
        .footer-link:hover {
            color: var(--secondary-color);
        }
        
        .social-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: white;
        }
        
        .social-icon:hover {
            color: var(--secondary-color);
        }

        @media (max-width: 991.98px) {
            .navbar {
                padding: 1rem;
            }

            .hero {
                padding: 80px 0;
            }

            .section {
                padding: 60px 0;
            }
        }

        @media (max-width: 767.98px) {
            .navbar-brand {
                font-size: 1.25rem;
            }

            .hero {
                text-align: center;
                padding: 60px 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 2rem;
            }

            .feature-card {
                margin-bottom: 1.5rem;
            }

            .footer {
                text-align: center;
                padding: 30px 0;
            }

            .footer-links {
                margin-bottom: 2rem;
            }

            .social-icons {
                justify-content: center;
                margin-top: 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .navbar {
                padding: 0.5rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .btn-lg {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }

            .feature-icon {
                font-size: 2rem;
            }

            .section {
                padding: 40px 0;
            }

            .contact-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-white">
        <div class="container">
            <a class="navbar-brand" href="#">KebunKU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="dashboard/<?= $_SESSION['role'] ?>/index.php">Dashboard</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="login.php">Masuk</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 mb-4">Kelola Pertanian Anda dengan Lebih Efisien</h1>
                    <p class="lead mb-4">Platform manajemen pertanian modern yang membantu petani, distributor, dan admin dalam mengelola hasil panen dengan lebih baik.</p>
                    <a href="login.php" class="btn btn-light btn-lg" style="color: #2e7d32">Mulai Sekarang</a>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="section">
        <div class="container">
            <h2 class="text-center section-title">Fitur Utama</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100" onclick="location.href='login.php?role=admin'" style="cursor: pointer;">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield feature-icon"></i>
                            <h4 class="card-title">Admin</h4>
                            <p class="card-text">Manajemen data hasil panen dan pemantauan transaksi pembelian secara real-time.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100" onclick="location.href='login.php?role=petani'" style="cursor: pointer;">
                        <div class="card-body text-center">
                            <i class="fas fa-seedling feature-icon"></i>
                            <h4 class="card-title">Petani</h4>
                            <p class="card-text">Kelola data pribadi, rencana tanam, penggunaan pupuk, dan pencatatan hasil panen.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100" onclick="location.href='login.php?role=distributor'" style="cursor: pointer;">
                        <div class="card-body text-center">
                            <i class="fas fa-truck feature-icon"></i>
                            <h4 class="card-title">Distributor</h4>
                            <p class="card-text">Pemesanan hasil panen, pengelolaan stok gudang, dan manajemen pengiriman.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">Tentang KebunKU</h2>
            <div class="row align-items-center">
                <div class="col-md-6 mb-4">
                    <h3>Platform Pertanian Modern</h3>
                    <p>KebunKU adalah solusi manajemen pertanian terpadu yang menghubungkan petani, distributor, dan admin dalam satu platform yang mudah digunakan.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Manajemen hasil panen yang efisien</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Pemantauan rencana tanam</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Pengelolaan stok dan distribusi</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Pelaporan real-time</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="section">
        <div class="container">
            <h2 class="text-center section-title">Hubungi Kami</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
                        require_once 'includes/send_email.php';
                        
                        $name = trim($_POST['name']);
                        $email = trim($_POST['email']);
                        $message = trim($_POST['message']);
                        
                        $result = sendEmail($name, $email, $message);
                        
                        if ($result['success']) {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>' . $result['message'] . '
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                  </div>';
                        } else {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>' . $result['message'] . '
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                  </div>';
                        }
                    }
                    ?>
                    <div class="card feature-card">
                        <div class="card-body">
                            <form method="POST" action="#contact">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Pesan</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                    </div>
                                </div>
                                <button type="submit" name="contact_submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4 class="footer-title">KebunKU</h4>
                    <p>Platform manajemen pertanian modern untuk hasil panen yang lebih baik.</p>
                    <div class="mt-3">
                        <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h4 class="footer-title">Tautan</h4>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="footer-link">Fitur</a></li>
                        <li><a href="#about" class="footer-link">Tentang</a></li>
                        <li><a href="#contact" class="footer-link">Kontak</a></li>
                        <li><a href="login.php" class="footer-link">Masuk</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h4 class="footer-title">Kontak</h4>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> Jl. Soekarno-Hatta, Palembang</li>
                        <li><i class="fas fa-phone me-2"></i> (0813)7309-4784</li>
                        <li><i class="fas fa-envelope me-2"></i> kebunku4tid.com</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> KebunKU. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 