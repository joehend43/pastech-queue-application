<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Kasir</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 20px; background: #f8f9fc; }
        .hidden { display: none !important; }
        
        /* Style State 1 */
        .grid-user { 
            display: flex; 
            flex-direction: column; 
            gap: 15px; 
            max-width: 400px; 
            margin: 20px auto 0 auto; 
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
            transition: all 0.2s ease-in-out; 
            text-align: center;
        }

        .btn-user:not(:disabled):hover { 
            background: #4e73df; 
            color: #fff;
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.25);
            transform: translateY(-2px); 
        }

        .btn-user:not(:disabled):active {
            transform: translateY(0);
            box-shadow: none;
        }

        .btn-user:disabled { 
            background: #eaecf4; 
            border-color: #d1d3e2;
            color: #b7b9cc;
            cursor: not-allowed; 
        }

        /* Utility Header Menu di State 2 (Lock & Logout) */
        .dashboard-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
        }
        .header-utils { 
            display: flex; 
            gap: 10px; 
        }
        .btn-util { 
            padding: 10px 18px; 
            font-weight: bold; 
            font-size: 0.95rem;
            border-radius: 8px; 
            cursor: pointer; 
            border: none; 
            transition: all 0.2s; 
        }
        .btn-lock { background: #36b9cc; color: white; }
        .btn-lock:hover { background: #2c9faf; transform: translateY(-1px); }
        .btn-logout { background: #e74a3b; color: white; }
        .btn-logout:hover { background: #be2617; transform: translateY(-1px); }

        /* Style State 2 */
        .top-half { height: 40vh; display: flex; gap: 30px; justify-content: center; align-items: center; border-bottom: 2px solid #e3e6f0; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.03); padding: 20px; }
        .right-actions { display: flex; flex-direction: column; gap: 15px; } 
        
        .btn-call { padding: 25px 45px; font-size: 1.4rem; font-weight: bold; cursor: pointer; color: white; border: none; border-radius: 10px; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,.05); }
        .btn-call:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,.1); }
        .btn-call:active { transform: translateY(0); }
        
        .btn-current { background: #f6c23e; color: #fff; height: 135px; } 
        .btn-order-next { background: #a3e2bc; color: #2c6e49; border: 2px solid #71cd94; } 
        .btn-pickup-next { background: #b2d7ff; color: #1a4473; border: 2px solid #80bfff; } 
        
        /* Layout Judul & Fitur Pencarian */
        .table-header-container { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; }
        .search-box { padding: 10px 15px; font-size: 1rem; border: 2px solid #d1d3e2; border-radius: 8px; width: 100%; max-width: 300px; outline: none; transition: all 0.2s; }
        .search-box:focus { border-color: #4e73df; box-shadow: 0 0 8px rgba(78, 115, 223, 0.15); }

        .bottom-half { height: 50vh; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.03); }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid #e3e6f0; }
        th { background-color: #f8f9fc; color: #4e73df; font-weight: bold; }
        tr:hover { background-color: #f8f9fc; }
        
        .btn-table-recall { background: #4e73df; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .btn-table-recall:hover { background: #2e59d9; }

        .btn-back {
            margin-top: 30px; 
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

        /* Style State 3 (Layar Kunci / Lock Screen) */
        .lock-screen { 
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; 
            background: #2c3e50; color: white; display: flex; flex-direction: column; 
            justify-content: center; align-items: center; z-index: 9999; 
        }
        .btn-unlock { 
            padding: 20px 50px; font-size: 1.5rem; font-weight: bold; 
            background: #1cc88a; color: white; border: none; border-radius: 10px; 
            cursor: pointer; transition: all 0.2s; margin-top: 25px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
        }
        .btn-unlock:hover { background: #17a673; transform: scale(1.05); }
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
        <div class="dashboard-header">
            <h2 id="txt-welcome-kasir" style="color: #333; margin: 0;">Dashboard Kasir</h2>
            <div class="header-utils">
                <button class="btn-util btn-lock" onclick="lockKasir()">🔒 Kunci Layar</button>
                <button class="btn-util btn-logout" onclick="logoutKasir()">🚪 Keluar</button>
            </div>
        </div>

        <div class="top-half">
            <button class="btn-call btn-current" onclick="callCurrent()">Panggil Antrian<br>Saat Ini</button>
            
            <div class="right-actions">
                <button class="btn-call btn-order-next" onclick="callNext('next_order')">Panggil Antrian Pembelian Selanjutnya</button>
                <button class="btn-call btn-pickup-next" onclick="callNext('next_pickup')">Panggil Antrian Pengambilan Selanjutnya</button>
            </div>
        </div>
        
        <div class="bottom-half">
            <div class="table-header-container">
                <h3 style="margin: 0;">Daftar Antrian Berjalan</h3>
                <input type="text" id="queue-search-input" class="search-box" placeholder="Cari nomor antrian (misal: A.001 atau 1)..." oninput="filterQueueTable()">
            </div>

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
                <tbody id="queue-table-body"></tbody>
            </table>
        </div>
    </div>

    <div id="state-3" class="hidden lock-screen">
        <h1 style="font-size: 3.5rem; margin-bottom: 10px; letter-spacing: 1px;">Layar Terkunci</h1>
        <p id="txt-lock-info" style="font-size: 1.5rem; color: #bdc3c7;">Kasir sedang beristirahat/AFK.</p>
        <button class="btn-unlock" onclick="unlockKasir()">🔓 Buka Kunci</button>
    </div>

    <script>
        // Array global untuk menampung data mentah antrian agar filter terasa instan
        let rawQueuesData = [];

        document.addEventListener("DOMContentLoaded", function() {
            checkState();
        });

        function checkState() {
            const isLogin = localStorage.getItem('kasir_isLogin');
            const lastLogin = localStorage.getItem('kasir_lastLogin');
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            let isLocked = localStorage.getItem('kasir_isLocked');
            const today = new Date().toISOString().split('T')[0];

            if(lastLogin && lastLogin.split(' ')[0] !== today){
                localStorage.setItem('kasir_isLocked', 'true');
                isLocked = 'true';
            }

            if (isLogin === 'true' && user && user.type === 'kasir') {
                if (isLocked === 'true') {
                    showState3();
                } else {
                    showState2();
                }
            } else {
                clearLocalStorageSessi();
                showState1();
            }
        }

        // --- LOGIC STATE 1 ---
        function showState1() {
            document.getElementById('state-1').classList.remove('hidden');
            document.getElementById('state-2').classList.add('hidden');
            document.getElementById('state-3').classList.add('hidden');
            
            fetch('/api/kasir')
                .then(res => res.json())
                .then(users => {
                    const container = document.getElementById('user-list');
                    container.innerHTML = '';
                    users.forEach(user => {
                        const btn = document.createElement('button');
                        btn.className = 'btn-user';
                        btn.innerText = user.status === 'Offline' ? user.name : `${user.name} (${user.status})`;
                        
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
                
                localStorage.setItem('kasir_isLogin', 'true');
                localStorage.setItem('kasir_lastLogin', dateTimeStr);
                localStorage.setItem('kasir_userLogin', JSON.stringify(user));
                localStorage.setItem('kasir_isLocked', 'false');

                document.getElementById('state-1').classList.add('hidden');
                showState2();
            })
            .catch(err => alert(err.message));
        }

        // --- LOGIC STATE 2 ---
        function showState2() {
            document.getElementById('state-1').classList.add('hidden');
            document.getElementById('state-2').classList.remove('hidden');
            document.getElementById('state-3').classList.add('hidden');
            
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            document.getElementById('txt-welcome-kasir').innerText = `Dashboard ${user.name}`;
            loadQueueTable();
        }

        function loadQueueTable() {
            fetch('/api/queues')
                .then(res => res.json())
                .then(queues => {
                    // Simpan data mentah ke array global
                    rawQueuesData = queues;
                    
                    // Render isi tabel dengan fungsi filter bawaan
                    filterQueueTable();
                });
        }

        // FUNGSI UTAMA PENCARIAN & FILTER REALTIME
        function filterQueueTable() {
            const tbody = document.getElementById('queue-table-body');
            const keyword = document.getElementById('queue-search-input').value.trim().toLowerCase();
            
            tbody.innerHTML = '';
            
            if (rawQueuesData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Belum ada antrian hari ini</td></tr>';
                return;
            }

            // Saring data berdasarkan keyword pencarian
            const filteredData = rawQueuesData.filter(q => {
                const formattedNumber = `${q.type}.${String(q.queue_number).padStart(3, '0')}`.toLowerCase(); // Format: a.001
                const rawNumberStr = String(q.queue_number); // Format angka mentah: 1

                // Mencocokkan keyword dengan nomor berformat (A.001) ATAU angka mentah saja (1)
                return formattedNumber.includes(keyword) || rawNumberStr.includes(keyword);
            });

            if (filteredData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#858796; font-style:italic;">Tidak ada nomor antrian yang cocok dengan pencarian Anda</td></tr>';
                return;
            }

            // Render baris data hasil penyaringan ke dalam tabel HTML
            filteredData.forEach(q => {
                const formattedNumber = `${q.type}.${String(q.queue_number).padStart(3, '0')}`;
                const row = `<tr>
                    <td><b>${formattedNumber}</b></td>
                    <td>${q.type === 'A' ? 'Order (Pembelian)' : 'Pickup (Pengambilan)'}</td>
                    <td>${new Date(q.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit', second:'2-digit'})}</td>
                    <td>${q.called_at ? new Date(q.called_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit', second:'2-digit'}) : '-'}</td>
                    <td>${q.caller ? q.caller : '-'}</td>
                    <td>${q.called_at ? '<button class="btn-table-recall" onclick="recall(\'' + q.id + '\')">Panggil Ulang</button>' : ''}</td>
                </tr>`;
                tbody.innerHTML += row;
            });
        }

        function callCurrent() {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            
            fetch(`/api/queues/recall-current?user_id=${user.id}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => {
                if (!res.ok) return res.json().then(err => { throw new Error(err.message) });
                return res.json();
            })
            .then(queue => {
                console.log('Berhasil memanggil ulang antrian:', queue.queue_number);
                loadQueueTable(); 
            })
            .catch(err => alert(err.message));
        }

        function callNext(typeParam) { 
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            
            fetch(`/api/queues/call?user_id=${user.id}&type=${typeParam}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => {
                if (!res.ok) return res.json().then(err => { throw new Error(err.message) });
                return res.json();
            })
            .then(queue => {
                loadQueueTable(); 
            })
            .catch(err => alert(err.message));
        }
        
        function recall(queueId) {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            if (!user) {
                alert("Sesi kasir tidak ditemukan. Silahkan login ulang.");
                return;
            }

            fetch(`/api/queues/call/${queueId}?user_id=${user.id}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal melakukan panggilan ulang antrian.');
                return res.json();
            })
            .then(response => {
                loadQueueTable();
            })
            .catch(err => alert(err.message));
        }

        // --- LOCK & UNLOCK SCREEN LOGIC ---
        function lockKasir() {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            
            fetch('/api/toggle-lock-kasir', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: user.id, action: 'lock' })
            })
            .then(res => {
                if(res.ok) {
                    localStorage.setItem('kasir_isLocked', 'true');
                    showState3();
                } else {
                    alert('Gagal mengunci layar dari server.');
                }
            });
        }

        function showState3() {
            document.getElementById('state-1').classList.add('hidden');
            document.getElementById('state-2').classList.add('hidden');
            document.getElementById('state-3').classList.remove('hidden');
            
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            document.getElementById('txt-lock-info').innerText = `${user.name} sedang beristirahat/AFK.`;
        }

        function unlockKasir() {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            
            fetch('/api/toggle-lock-kasir', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: user.id, action: 'unlock' })
            })
            .then(res => {
                if(res.ok) {
                    localStorage.setItem('kasir_isLocked', 'false');
                    showState2();
                } else {
                    alert('Gagal membuka kunci layar.');
                }
            });
        }

        // --- CLEAN UP SELECTION MANAGEMENT ---
        function clearLocalStorageSessi() {
            localStorage.removeItem('kasir_isLogin');
            localStorage.removeItem('kasir_lastLogin');
            localStorage.removeItem('kasir_userLogin');
            localStorage.removeItem('kasir_isLocked');
        }

        function logoutKasir() {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            if (!user) {
                clearLocalStorageSessi();
                window.location.href = '/';
                return;
            }

            fetch('/api/logout-user', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: user.id })
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal memperbarui status di server');
                return res.json();
            })
            .then(() => {
                clearLocalStorageSessi();
                window.location.href = '/'; 
            })
            .catch(err => {
                console.error('Logout error fallback:', err.message);
                clearLocalStorageSessi();
                window.location.href = '/';
            });
        }
    </script>
</body>
</html>