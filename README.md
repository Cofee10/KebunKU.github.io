# KebunKU - Platform Manajemen Pertanian

## Pengaturan Email untuk Form Kontak

Untuk mengaktifkan fitur pengiriman email pada form kontak, ikuti langkah-langkah berikut:

1. Install dependencies menggunakan Composer:
```bash
composer install
```

2. Buat App Password untuk Gmail:
   - Login ke akun Gmail kebunku4tid@gmail.com
   - Buka [Google Account Settings](https://myaccount.google.com/)
   - Pilih "Security"
   - Aktifkan "2-Step Verification" jika belum
   - Kembali ke Security, scroll ke bawah dan pilih "App Passwords"
   - Pilih "Select App" -> "Other (Custom Name)" -> tulis "KebunKU Contact Form"
   - Klik "Generate"
   - Copy 16-digit password yang muncul

3. Update file `includes/send_email.php`:
   - Ganti baris yang berisi `$mail->Password = '';`
   - Paste 16-digit App Password yang sudah di-generate di dalam tanda kutip

4. Pastikan file `composer.json` dan `vendor/` ada di root folder website

5. Test form kontak dengan mengirim pesan test

### Format Email yang Diterima

Email yang dikirim melalui form kontak akan memiliki format berikut:
- Subject: "Pesan Baru dari Form Kontak KebunKU"
- Pengirim akan terlihat sebagai nama dan email yang diisi di form
- Email akan dikirim ke kebunku4tid@gmail.com
- Isi email akan ditampilkan dalam format yang rapi dengan template HTML
- Terdapat versi teks biasa (plain text) untuk klien email yang tidak mendukung HTML

### Troubleshooting

Jika email tidak terkirim, cek:
1. Pastikan App Password sudah di-set dengan benar
2. Pastikan ekstensi PHP yang dibutuhkan sudah terinstall:
   - openssl
   - php_mail
   - php_smtp
3. Cek error log PHP untuk detail error yang mungkin muncul 