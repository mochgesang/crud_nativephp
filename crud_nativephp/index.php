<?php
session_start();
require 'functions.php';

// Cek cookie untuk login otomatis
if (isset($_COOKIE["login"]) && $_COOKIE["login"] === "true") {
    $_SESSION["login"] = true;
    $_SESSION["username"] = $_COOKIE["username"];
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

// Konfigurasi Pagination dan Pencarian
$jumlahDataPerHalaman = 5;
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$pagination = getPagination($jumlahDataPerHalaman, $keyword);
$mahasiswa = getDataWithPagination($pagination['halamanAktif'], $jumlahDataPerHalaman, $keyword);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mahasiswa</title>
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #3b82f6;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --gray: #6c757d;
            --radius: 8px;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.5;
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 1.5rem;
            color: white;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .search-container {
            margin-bottom: 1.5rem;
        }

        .search-container form {
            display: flex;
            gap: 0.75rem;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .action-buttons-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .add-button {
            background: var(--success);
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: var(--radius);
            transition: all 0.2s;
        }

        .add-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .search-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .logout-button {
            background: var(--danger);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .logout-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .edit {
            background: var(--warning);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .delete {
            background: var(--danger);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .edit:hover, .delete:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            color: var(--primary);
            text-decoration: none;
            transition: all 0.2s;
        }

        .pagination a:hover,
        .pagination a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .halaman-info {
            text-align: center;
            color: var(--gray);
            margin: 1rem 0;
            font-size: 0.9rem;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            background: white;
            border-radius: var(--radius);
            margin: 2rem 0;
        }

        img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }

        @media (max-width: 768px) {
            .search-container form {
                flex-direction: column;
            }

            .action-buttons-container {
                flex-direction: column;
            }

            .table-responsive {
                overflow-x: auto;
            }

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 0.75rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .pagination {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>Daftar Mahasiswa</h1>
            <a href="logout.php" class="logout-button">Keluar</a>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="search-container">
                <form action="" method="get">
                    <input type="text" 
                           name="keyword" 
                           class="search-input" 
                           placeholder="Cari berdasarkan nama, NRP, email, atau jurusan..." 
                           value="<?= htmlspecialchars($keyword) ?>">
                    <button type="submit" class="search-button">Cari</button>
                </form>
            </div>

            <div class="action-buttons-container">
                <a href="tambah.php" class="add-button">Tambah Mahasiswa</a>
                <?php if (!empty($keyword)): ?>
                    <a href="index.php" class="btn" style="background: var(--gray); color: white;">Reset</a>
                <?php endif; ?>
            </div>

            <?php if (!empty($keyword)): ?>
                <div class="halaman-info">
                    Hasil pencarian untuk: "<?= htmlspecialchars($keyword) ?>"
                </div>
            <?php endif; ?>

            <?php if (empty($mahasiswa)): ?>
                <div class="no-results">
                    <?= empty($keyword) ? 'Belum ada data mahasiswa.' : 'Tidak ada hasil yang ditemukan.' ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>NRP</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Jurusan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = ($pagination['halamanAktif'] - 1) * $jumlahDataPerHalaman + 1; ?>
                            <?php foreach ($mahasiswa as $row): ?>
                            <tr>
                                <td><?= $i; ?></td>
                                <td>
                                    <img src="img/<?= htmlspecialchars($row["gambar"]); ?>" 
                                         alt="<?= htmlspecialchars($row["nama"]); ?>">
                                </td>
                                <td><?= htmlspecialchars($row["nrp"]); ?></td>
                                <td><?= htmlspecialchars($row["nama"]); ?></td>
                                <td><?= htmlspecialchars($row["email"]); ?></td>
                                <td><?= htmlspecialchars($row["jurusan"]); ?></td>
                                <td class="action-buttons">
                                    <a href="ubah.php?id=<?= $row["id"]; ?>" class="edit">Ubah</a>
                                    <a href="hapus.php?id=<?= $row["id"]; ?>" 
                                       class="delete" 
                                       onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                                </td>
                            </tr>
                            <?php $i++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <?php if($pagination['halamanAktif'] > 1): ?>
                        <a href="?halaman=<?= $pagination['halamanAktif'] - 1 ?>&keyword=<?= urlencode($keyword) ?>">
                            &laquo; Sebelumnya
                        </a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $pagination['jumlahHalaman']; $i++): ?>
                        <a href="?halaman=<?= $i ?>&keyword=<?= urlencode($keyword) ?>" 
                           class="<?= $i == $pagination['halamanAktif'] ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($pagination['halamanAktif'] < $pagination['jumlahHalaman']): ?>
                        <a href="?halaman=<?= $pagination['halamanAktif'] + 1 ?>&keyword=<?= urlencode($keyword) ?>">
                            Selanjutnya &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>