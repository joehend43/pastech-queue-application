<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Kasir</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 20px; }
        .hidden { display: none !important; }
        /* Style State 1 */
        .grid-user { 
            display: flex; 
            flex-direction: column; /* Mengubah susunan menjadi menurun */
            gap: 15px; 
            max-width: 400px; /* Membatasi lebar agar tidak terlalu melar ke samping */
            margin: 20px auto 0 auto; /* Tengah secara horizontal */
        }

        .btn-user { 
            padding: 18px 25px; 
            font-size: 1.2rem; 
            font-weight: bold;
            cursor: pointer; 
            border: 2px solid #4e73df; 
            border-radius: 8px;
            background: #fff; 
            color: #4e73df;
            transition: all 0.2s ease-in-out; /* Animasi halus saat hover */
            text-align: center;
        }

        /* Feedback Hover untuk tombol yang aktif */
        .btn-user:not(:disabled):hover { 
            background: #4e73df; 
            color: #fff;
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.25);
            transform: translateY(-2px); /* Efek sedikit terangkat saat di-hover */
        }

        /* Feedback saat tombol ditekan (Click) */
        .btn-user:not(:disabled):active {
            transform: translateY(0);
            box-shadow: none;
        }

        /* Style untuk Kasir yang sedang Online / Terkunci */
        .btn-user:disabled { 
            background: #eaecf4; 
            border-color: #d1d3e2;
            color: #b7b9cc;
            cursor: not-allowed; 
        }
        /* Style State 2 */
        .top-half { height: 40vh; display: flex; gap: 20px; justify-content: center; align-items: center; border-bottom: 2px solid #ccc; }
        .btn-call { padding: 30px 50px; font-size: 1.5rem; cursor: pointer; color: white; border: none; border-radius: 5px; }
        .btn-current { background: #f6c23e; }
        .btn-next { background: #1cc88a; }
        .bottom-half { height: 50vh; padding-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn-back {
            margin-top: 30px; /* Memberi jarak aman dari list kasir */
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            border: 2px solid #6c757d;
            border-radius: 8px;
            background: #fff;
            color: #6c757d;
            transition: all 0.2s ease-in-out;
            max-width: 200px;
            width: 100%;
        }
        .btn-back:hover {
            background: #6c757d;
            color: #fff;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.25);
        }
    </style>
</head>
<body>

    <div id="state-1" class="hidden" style="text-align: center; padding-top: 40px; display: flex; flex-direction: column; align-items: center; min-height: 80vh; justify-content: space-between;">
        <div>
            <h2 style="color: #333; margin-bottom: 30px;">Pilih Kasir</h2>
            <div id="user-list" class="grid-user"></div>
        </div>
        
        <button class="btn-back" onclick="window.location.href='/'">Kembali</button>
    </div>

    <div id="state-2" class="hidden">
        <div class="top-half">
            <button class="btn-call btn-current" onclick="callCurrent()">Panggil Antrian Saat Ini</button>
            <button class="btn-call btn-next" onclick="callNext()">Panggil Antrian Selanjutnya</button>
        </div>
        <div class="bottom-half">
            <h3>Daftar Antrian Berjalan</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nomor Antrian</th>
                        <th>Jenis Antrian</th>
                        <th>Waktu Cetak</th>
                        <th>Panggilan Terakhir</th>
                        <th>Dipanggil Oleh</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="queue-table-body">
                    </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            checkState();
        });

        function checkState() {
            const isLogin = localStorage.getItem('kasir_isLogin');
            const lastLogin = localStorage.getItem('kasir_lastLogin');
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            const today = new Date().toISOString().split('T')[0];

            // Validasi: Harus login hari ini dan bertipe kasir
            if (isLogin === 'true' && lastLogin && user && lastLogin.split(' ')[0] === today && user.type === 'kasir') {
                showState2();
            } else {
                // Jika sudah berganti hari, hapus data lama yang usang
                localStorage.removeItem('kasir_isLogin');
                localStorage.removeItem('kasir_lastLogin');
                localStorage.removeItem('kasir_userLogin');
                showState1();
            }
        }

        // --- LOGIC STATE 1 ---
        function showState1() {
            document.getElementById('state-1').classList.remove('hidden');
            document.getElementById('state-2').classList.add('hidden');
            
            fetch('/api/kasir')
                .then(res => res.json())
                .then(users => {
                    const container = document.getElementById('user-list');
                    container.innerHTML = '';
                    users.forEach(user => {
                        const btn = document.createElement('button');
                        btn.className = 'btn-user';
                        if (user.status === 'Offline') {
                            btn.innerText = user.name;
                        } else {
                            btn.innerText = `${user.name} (${user.status})`;
                        }
                        
                        // Disable jika statusnya tidak Offline
                        if(user.status !== 'Offline') {
                            btn.disabled = true;
                        }

                        btn.onclick = () => loginProcess(user.id);
                        container.appendChild(btn);
                    });
                });
        }

        function loginProcess(userId) {
            fetch('/api/login-user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
            })
            .then(res => {
                if(!res.ok) throw new Error('Kasir ini sedang digunakan di komputer lain');
                return res.json();
            })
            .then(user => {
                const now = new Date();
                const dateTimeStr = now.toISOString().split('T')[0] + ' ' + now.toTimeString().split(' ')[0];
                
                // Simpan dengan prefix kasir_
                localStorage.setItem('kasir_isLogin', 'true');
                localStorage.setItem('kasir_lastLogin', dateTimeStr);
                localStorage.setItem('kasir_userLogin', JSON.stringify(user));

                document.getElementById('state-1').classList.add('hidden');
                showState2();
            })
            .catch(err => alert(err.message));
        }

        // --- LOGIC STATE 2 ---
        function showState2() {
            document.getElementById('state-2').classList.remove('hidden');
            loadQueueTable();
        }

        function loadQueueTable() {
            fetch('/api/queues')
                .then(res => res.json())
                .then(queues => {
                    const tbody = document.getElementById('queue-table-body');
                    tbody.innerHTML = '';
                    if(queues.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Belum ada antrian hari ini</td></tr>';
                        return;
                    }
                    queues.forEach(q => {
                        const row = `<tr>
                            <td><b>${q.queue_number}</b></td>
                            <td>Antrian ${q.type}</td>
                            <td>${new Date(q.created_at).toLocaleTimeString()}</td>
                            <td>${q.called_at ? new Date(q.called_at).toLocaleTimeString() : '-'}</td>
                            <td>${q.caller ? q.caller : '-'}</td>
                            <td><button onclick="recall('${q.id}')">Panggil Ulang</button></td>
                        </tr>`;
                        tbody.innerHTML += row;
                    });
                });
        }

        function callCurrent() {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            
            // Hit API dengan method GET dan membawa query parameter ?user_id=X
            fetch(`/api/queues/recall-current?user_id=${user.id}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => {
                // Jika backend melempar error 404 (belum ada antrian)
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.message) });
                }
                return res.json();
            })
            .then(queue => {
                // Berhasil memicu panggilan ulang ke server & reverb
                console.log('Berhasil memanggil ulang antrian:', queue.queue_number);
                
                // Segarkan tabel antrian berjalan di bagian bawah kasir
                loadQueueTable(); 
            })
            .catch(err => {
                alert(err.message);
            });
        }

        function callNext() { 
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            
            fetch('/api/queues/call-next?user_id=' + user.id, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => {
                if (!res.ok) return res.json().then(err => { throw new Error(err.message) });
                return res.json();
            })
            .then(queue => {
                currentActiveQueue = queue;
                loadQueueTable(); // Hanya refresh tabel, suara diurus monitor display
            })
            .catch(err => alert(err.message));
        }
        
        function recall(queueId) {
            // 1. Ambil data kasir yang sedang login saat ini dari localStorage
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            if (!user) {
                alert("Sesi kasir tidak ditemukan. Silahkan login ulang.");
                return;
            }

            console.log('Memanggil Ulang Antrian ID:', queueId, 'Oleh Kasir:', user.id);

            // 2. Hit API sesuai format: api/queues/call/{queue_id}?user_id={user_id}
            fetch(`/api/queues/call/${queueId}?user_id=${user.id}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Gagal melakukan panggilan ulang antrian.');
                }
                return res.json();
            })
            .then(response => {
                // 3. Update state internal kasir jika diperlukan
                currentActiveQueue = response.data;
                
                // 4. Refresh data tabel antrian di bawah agar kolom 'Panggilan Terakhir' ter-update waktunya
                loadQueueTable();
            })
            .catch(err => {
                alert(err.message);
            });
        }
    </script>
</body>
</html>