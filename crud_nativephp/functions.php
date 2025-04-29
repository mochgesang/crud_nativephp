<?php
// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "phpdasar");

// Periksa koneksi database
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// Function untuk login
function login($data) {
    global $conn;
    
    $username = mysqli_real_escape_string($conn, $data["username"]);
    $password = $data["password"];
    
    // Cek username
    $result = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek password
        if (password_verify($password, $row["password"])) {
            // Set session
            $_SESSION["login"] = true;
            $_SESSION["username"] = $row["username"];
            
            // Cek remember me
            if (isset($data["remember"])) {
                // Buat cookie yang lebih aman
                setcookie('id', $row['id'], time() + 604800, '/'); // 7 hari
                setcookie('key', hash('sha256', $row['username']), time() + 604800, '/');
            }
            
            return true;
        }
    }
    
    return false;
}

// Function untuk cek cookie
function checkCookie() {
    global $conn;
    
    if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
        $id = $_COOKIE['id'];
        $key = $_COOKIE['key'];
        
        // Ambil username berdasarkan id
        $result = mysqli_query($conn, "SELECT username FROM user WHERE id = '$id'");
        $row = mysqli_fetch_assoc($result);
        
        // Cek cookie dan username
        if ($key === hash('sha256', $row['username'])) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $row['username'];
            return true;
        }
    }
    return false;
}

function tambah($data) {
    global $conn;

    $nrp = htmlspecialchars($data["nrp"]);
    $nama = htmlspecialchars($data["nama"]);
    $email = htmlspecialchars($data["email"]);
    $jurusan = htmlspecialchars($data["jurusan"]);

    // Upload gambar
    $gambar = upload();
    if (!$gambar) return false;

    $query = "INSERT INTO mahasiswa
              VALUES ('', '$nrp', '$nama', '$email', '$jurusan', '$gambar')";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

function upload() {
    $namaFile = $_FILES['gambar']['name'];
    $ukuranFile = $_FILES['gambar']['size'];
    $tmpName = $_FILES['gambar']['tmp_name'];
    $error = $_FILES['gambar']['error'];

    // Cek apakah tidak ada gambar yang diupload
    if ($error === 4) {
        echo "<script>alert('Pilih gambar terlebih dahulu!');</script>";
        return false;
    }

    // Cek apakah yang diupload adalah gambar
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    if (!in_array($ekstensiGambar, $ekstensiGambarValid)) {
        echo "<script>alert('Yang diupload bukan gambar!');</script>";
        return false;
    }

    // Cek ukuran file
    if ($ukuranFile > 1000000) {
        echo "<script>alert('Ukuran gambar terlalu besar!');</script>";
        return false;
    }

    // Generate nama file baru
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.' . $ekstensiGambar;

    // Pindahkan file
    move_uploaded_file($tmpName, 'img/' . $namaFileBaru);

    return $namaFileBaru;
}

function hapus($id) {
    global $conn;
    
    // Ambil nama file gambar
    $result = mysqli_query($conn, "SELECT gambar FROM mahasiswa WHERE id = $id");
    $file = mysqli_fetch_assoc($result);
    
    // Hapus file gambar jika ada
    if (file_exists('img/' . $file['gambar'])) {
        unlink('img/' . $file['gambar']);
    }
    
    mysqli_query($conn, "DELETE FROM mahasiswa WHERE id = $id");
    return mysqli_affected_rows($conn);
}

function ubah($data) {
    global $conn;

    $id = $data["id"];
    $nrp = htmlspecialchars($data["nrp"]);
    $nama = htmlspecialchars($data["nama"]);
    $email = htmlspecialchars($data["email"]);
    $jurusan = htmlspecialchars($data["jurusan"]);
    $gambarLama = htmlspecialchars($data["gambarLama"]);

    // Cek apakah user upload gambar baru
    if ($_FILES['gambar']['error'] === 4) {
        $gambar = $gambarLama;
    } else {
        $gambar = upload();
        // Hapus gambar lama
        if ($gambar && $gambarLama != 'default.jpg') {
            unlink('img/' . $gambarLama);
        }
    }

    $query = "UPDATE mahasiswa SET
              nrp = '$nrp',
              nama = '$nama',
              email = '$email',
              jurusan = '$jurusan',
              gambar = '$gambar'
              WHERE id = $id";

    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

function cari($keyword) {
    $query = "SELECT * FROM mahasiswa
              WHERE
              nrp LIKE '%$keyword%' OR
              nama LIKE '%$keyword%' OR
              email LIKE '%$keyword%' OR
              jurusan LIKE '%$keyword%'";

    return query($query);
}

function registrasi($data) {
    global $conn;

    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);

    // Validasi username (hanya huruf dan angka)
    if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        echo "<script>alert('Username hanya boleh mengandung huruf dan angka!');</script>";
        return false;
    }

    // Cek username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Username sudah terdaftar!');</script>";
        return false;
    }

    // Cek konfirmasi password
    if ($password !== $password2) {
        echo "<script>alert('Konfirmasi password tidak sesuai!');</script>";
        return false;
    }

    // Minimal panjang password
    if (strlen($password) < 6) {
        echo "<script>alert('Password minimal 6 karakter!');</script>";
        return false;
    }

    // Enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Tambahkan user baru ke database
    mysqli_query($conn, "INSERT INTO user VALUES('', '$username', '$password')");

    return mysqli_affected_rows($conn);
}

// Fungsi untuk pagination
function getPagination($jumlahDataPerHalaman = 5) {
    global $conn;
    
    // Hitung total data
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM mahasiswa");
    $jumlahData = mysqli_fetch_assoc($result)['total'];
    
    // Hitung jumlah halaman
    $jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);
    
    // Tentukan halaman aktif
    $halamanAktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
    $halamanAktif = max(1, min($halamanAktif, $jumlahHalaman));
    
    // Hitung awal data per halaman
    $awalData = ($halamanAktif - 1) * $jumlahDataPerHalaman;
    
    return [
        'jumlahData' => $jumlahData,
        'jumlahHalaman' => $jumlahHalaman,
        'halamanAktif' => $halamanAktif,
        'awalData' => $awalData,
        'jumlahDataPerHalaman' => $jumlahDataPerHalaman
    ];
}

// Fungsi untuk mengambil data dengan pagination
function getDataWithPagination($halaman = 1, $jumlahDataPerHalaman = 5) {
    $mulai = ($halaman - 1) * $jumlahDataPerHalaman;
    return query("SELECT * FROM mahasiswa LIMIT $mulai, $jumlahDataPerHalaman");
}
?>