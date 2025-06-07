CREATE DATABASE IF NOT EXISTS kebunku_db;
USE kebunku_db;

CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama_admin VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE petani (
    id_petani INT AUTO_INCREMENT PRIMARY KEY,
    nama_petani VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE distributor (
    id_distributor INT AUTO_INCREMENT PRIMARY KEY,
    nama_distributor VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE tanaman (
    id_tanaman INT AUTO_INCREMENT PRIMARY KEY,
    nama_tanaman VARCHAR(100) NOT NULL
);

CREATE TABLE data_lahan (
    id_lahan INT AUTO_INCREMENT PRIMARY KEY,
    id_petani INT NOT NULL,
    nama_lahan VARCHAR(100) NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    luas DECIMAL(10,2) NOT NULL,
    jenis_tanah VARCHAR(50) NOT NULL,
    panen_terakhir DATE,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_petani) REFERENCES petani(id_petani)
);

CREATE TABLE rencana_tanam (
    id_rencana INT AUTO_INCREMENT PRIMARY KEY,
    id_lahan INT NOT NULL,
    id_tanaman INT NOT NULL,
    luas_area DECIMAL(10,2) NOT NULL,
    tanggal_tanam DATE NOT NULL,
    perkiraan_panen DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_lahan) REFERENCES data_lahan(id_lahan),
    FOREIGN KEY (id_tanaman) REFERENCES tanaman(id_tanaman)
);

CREATE TABLE penggunaan_pupuk (
    id_penggunaan INT AUTO_INCREMENT PRIMARY KEY,
    id_lahan INT NOT NULL,
    jenis_pupuk VARCHAR(50) NOT NULL,
    jumlah INT NOT NULL,
    tanggal_penggunaan DATE NOT NULL,
    catatan TEXT,
    FOREIGN KEY (id_lahan) REFERENCES data_lahan(id_lahan)
);

CREATE TABLE data_hasil_panen (
    id_panen INT AUTO_INCREMENT PRIMARY KEY,
    id_tanaman INT NOT NULL,
    id_lahan INT NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    jumlah INT NOT NULL,
    tanggal_panen DATE NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_tanaman) REFERENCES tanaman(id_tanaman),
    FOREIGN KEY (id_lahan) REFERENCES data_lahan(id_lahan)
);

CREATE TABLE pembelian (
    id_pembelian INT AUTO_INCREMENT PRIMARY KEY,
    id_distributor INT NOT NULL,
    id_panen INT NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    id_tanaman INT NOT NULL,
    nama_tanaman VARCHAR(100) NOT NULL,
    jumlah_pembelian INT NOT NULL,
    lokasi_pengiriman VARCHAR(255) NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    tanggal_pembelian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL DEFAULT 'menunggu',
    FOREIGN KEY (id_distributor) REFERENCES distributor(id_distributor),
    FOREIGN KEY (id_panen) REFERENCES data_hasil_panen(id_panen),
    FOREIGN KEY (id_tanaman) REFERENCES tanaman(id_tanaman)
);

CREATE TABLE riwayat_pembelian (
    id_riwayat_pembelian INT AUTO_INCREMENT PRIMARY KEY,
    id_pembelian INT NOT NULL,
    id_panen INT NOT NULL,
    id_petani INT NOT NULL,
    jumlah INT NOT NULL,
    tanggal DATE NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_pembelian) REFERENCES pembelian(id_pembelian),
    FOREIGN KEY (id_panen) REFERENCES data_hasil_panen(id_panen),
    FOREIGN KEY (id_petani) REFERENCES petani(id_petani)
);

CREATE TABLE riwayat_pengiriman (
    id_riwayat_pengiriman INT AUTO_INCREMENT PRIMARY KEY,
    id_pembelian INT NOT NULL,
    id_panen INT NOT NULL,
    jumlah INT NOT NULL,
    tujuan VARCHAR(255) NOT NULL,
    tanggal_pengiriman DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_pembelian) REFERENCES pembelian(id_pembelian),
    FOREIGN KEY (id_panen) REFERENCES data_hasil_panen(id_panen)
);

CREATE TABLE stok_gudang (
    id_stok INT AUTO_INCREMENT PRIMARY KEY,
    id_panen INT NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    stok INT NOT NULL,
    lokasi_gudang VARCHAR(255) NOT NULL,
    update_terakhir DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_panen) REFERENCES data_hasil_panen(id_panen)
);

INSERT INTO admin (nama_admin, email, password) VALUES
('Admin', 'admin@kebunku.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO tanaman (nama_tanaman) VALUES
('Padi'),
('Jagung'),
('Kedelai'),
('Cabai'),
('Tomat'); 