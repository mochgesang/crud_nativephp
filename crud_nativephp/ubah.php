<?php
session_start();

// Cek session
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

require 'functions.php';

// Ambil data dari URL
$id = $_GET["id"];

// Query data mahasiswa berdasarkan id
$mhs = query("SELECT * FROM mahasiswa WHERE id = $id")[0];

// Cek apakah tombol submit sudah ditekan
if (isset($_POST["submit"])) {
    // Cek apakah data berhasil diubah
    if (ubah($_POST) > 0) {
        echo "<script>
                alert('Data berhasil diubah!');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Data gagal diubah!');
                document.location.href = 'index.php';
              </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="file"] {
            margin-top: 5px;
        }

        .img-preview {
            max-width: 100px;
            margin: 10px 0;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .kembali {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }

        .kembali:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Ubah Data Mahasiswa</h1>

    <div class="container">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $mhs["id"]; ?>">
            <input type="hidden" name="gambarLama" value="<?= $mhs["gambar"]; ?>">
            
            <div class="form-group">
                <label for="nrp">NRP:</label>
                <input type="text" name="nrp" id="nrp" required 
                       value="<?= $mhs["nrp"]; ?>">
            </div>

            <div class="form-group">
                <label for="nama">Nama:</label>
                <input type="text" name="nama" id="nama" required 
                       value="<?= $mhs["nama"]; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required 
                       value="<?= $mhs["email"]; ?>">
            </div>

            <div class="form-group">
                <label for="jurusan">Jurusan:</label>
                <input type="text" name="jurusan" id="jurusan" required 
                       value="<?= $mhs["jurusan"]; ?>">
            </div>

            <div class="form-group">
                <label for="gambar">Gambar:</label>
                <img src="img/<?= $mhs['gambar']; ?>" class="img-preview">
                <input type="file" name="gambar" id="gambar">
            </div>

            <button type="submit" name="submit">Ubah Data</button>
        </form>
        
        <a href="index.php" class="kembali">Kembali ke Daftar Mahasiswa</a>
    </div>
</body>
</html>