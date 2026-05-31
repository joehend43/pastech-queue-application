<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Peran Aplikasi</title>
    <style>
        body { margin: 0; display: flex; height: 100vh; font-family: sans-serif; text-align: center; }
        .half { flex: 1; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: bold; cursor: pointer; transition: 0.3s; text-decoration: none;}
        .kasir { background: #4e73df; color: white; }
        .kasir:hover { background: #2e59d9; }
        .display { background: #1cc88a; color: white; }
        .display:hover { background: #17a673; }
    </style>
</head>
<body>

    <a href="/kasir" class="half kasir">KASIR</a>
    <a href="/display" class="half display">DISPLAY</a>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const today = new Date().toISOString().split('T')[0];

            // 1. Cek Sesi Kasir
            const kasirIsLogin = localStorage.getItem('kasir_isLogin');
            const kasirLastLogin = localStorage.getItem('kasir_lastLogin');
            
            // 2. Cek Sesi Display
            const displayIsLogin = localStorage.getItem('display_isLogin');
            const displayLastLogin = localStorage.getItem('display_lastLogin');

            // Bersihkan sesi kasir jika sudah berganti hari
            if (kasirLastLogin && kasirLastLogin.split(' ')[0] !== today) {
                localStorage.removeItem('kasir_isLogin');
                localStorage.removeItem('kasir_lastLogin');
                localStorage.removeItem('kasir_userLogin');
            }
        });
    </script>
</body>
</html>