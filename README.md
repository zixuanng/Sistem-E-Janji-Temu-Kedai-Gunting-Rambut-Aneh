# Sistem E-Janji Temu – Kedai Gunting Rambut Aneh

Sistem tempahan temu janji atas talian untuk **Kedai Gunting Rambut Aneh**, dibangunkan sebagai Kerja Projek Sains Komputer SPM.

## 🛠 Stack Teknologi

| Komponen      | Teknologi          |
|---------------|--------------------|
| Backend       | PHP 8.x            |
| Pangkalan Data | SQLite (via PDO)  |
| Frontend      | HTML5, CSS3, JavaScript (Vanilla) |
| Hosting       | InfinityFree       |
| Version Control | GitHub           |

## 🎨 Palet Warna

- **Champagne** `#E8D5B7`
- **Warm Ivory** `#FAF7F0`
- **Espresso**   `#3C2415`

> Quiet Luxury aesthetic — elegan dan minimalis.

## 📂 Struktur Projek

```
salon/
├── index.php                  # Halaman Utama
├── log-masuk.php              # Log Masuk Pelanggan / Admin
├── daftar.php                 # Pendaftaran Pelanggan Baru
├── dashboard.php              # Dashboard Pelanggan
├── buat-temu-janji.php        # Borang Temu Janji
├── sejarah-temu-janji.php     # Sejarah Temu Janji
├── profil.php                 # Profil Pengguna
├── log-keluar.php             # Log Keluar
├── admin/
│   └── laporan.php            # Laporan Admin
├── includes/
│   ├── db.php                 # Sambungan SQLite + Init
│   └── functions.php         # Helper: session, auth, CSRF
├── css/
│   └── style.css              # Stail utama (Quiet Luxury)
└── data/
    └── salon.db               # SQLite database (auto-created)
```

## 🔑 Kelayakan Admin (Lalai)

| Field       | Nilai      |
|-------------|------------|
| ID Pengguna | `admin`    |
| Kata Laluan | `admin123` |

> ⚠️ Tukar kata laluan admin dalam `includes/functions.php` sebelum deploy!

## 👥 Peranan Pengguna

### Pelanggan
- Daftar menggunakan No. Kad Pengenalan (12 digit)
- Log masuk dan log keluar
- Buat temu janji (1 slot / hari)
- Lihat sejarah temu janji
- Kemaskini profil

### Admin
- Log masuk dengan ID khas (`admin`)
- Lihat semua temu janji
- Tapis mengikut tarikh
- Kemaskini status temu janji (Menunggu / Selesai / Tidak Selesai)

## ⏰ Slot Masa Tersedia

09:00 | 10:00 | 11:00 | 12:00 | 13:00 | 14:00 | 15:00 | 16:00 | 17:00

## 💇 Perkhidmatan

- Gunting Rambut (A001)
- Gaya Rambut (A002)
- Cuci Rambut (A003)

## 🗄️ Skema Pangkalan Data (3NF)

```sql
PELANGGAN   (idPelanggan PK, namaPelanggan, noTel, kataLaluan)
PERKHIDMATAN (idPerkhidmatan PK, namaPerkhidmatan)
JANJITEMU   (id PK, idPelanggan FK, tarikh, idPerkhidmatan FK, masa, catatan, status)
```

## 🚀 Cara Deploy ke InfinityFree

1. Daftar akaun di [infinityfree.com](https://infinityfree.com)
2. Buat hosting baru → dapatkan cPanel
3. Muat naik semua fail ke folder `htdocs/` melalui File Manager atau FTP
4. Pastikan folder `data/` boleh ditulis (chmod 755)
5. Lawati URL yang diberikan — pangkalan data SQLite dicipta secara automatik

## 📋 Ciri Keselamatan

- Kata laluan di-hash menggunakan `password_hash()` (bcrypt)
- CSRF token pada semua borang
- Sanitasi input dengan `htmlspecialchars()`
- PDO prepared statements (cegah SQL injection)
- Kawalan sesi PHP

---

**Dibina oleh:** Ng Zi Xuan | Angka Giliran: 100125070415  
**Sekolah:** SMJK Chung Ling Pulau Pinang  
**Guru:** Cikgu Low Wei Zi
