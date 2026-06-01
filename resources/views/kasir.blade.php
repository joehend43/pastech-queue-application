<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Kasir</title>
    <style>
        /* --- RESET & LAYOUT GENERAL --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f7fb; color: #0f172a; padding: 30px; }
        .hidden { display: none !important; }
        
        /* --- STYLE STATE 1 (PILIH KASIR) --- */
        .grid-user { display: flex; flex-direction: column; gap: 15px; max-width: 400px; margin: 40px auto 0 auto; }
        .btn-user { padding: 18px 25px; font-size: 1.2rem; font-weight: bold; cursor: pointer; border: 2px solid #4e73df; border-radius: 8px; background: #fff; color: #4e73df; transition: all 0.2s ease-in-out; text-align: center; }
        .btn-user:not(:disabled):hover { background: #4e73df; color: #fff; box-shadow: 0 4px 12px rgba(78, 115, 223, 0.25); transform: translateY(-2px); }
        .btn-user:disabled { background: #eaecf4; border-color: #d1d3e2; color: #b7b9cc; cursor: not-allowed; }

        /* --- HEADER ACTIONS --- */
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .page-title { font-size: 36px; font-weight: 700; margin: 0; }
        .header-utils { display: flex; gap: 10px; }
        .header-btn { padding: 12px 20px; font-weight: 600; font-size: 0.95rem; border-radius: 16px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; background: transparent; }
        /* --- TOMBOL UTILITY HEADER (MANUAL PREMIUM STYLING) --- */
        .btn-util-lock { 
            border: 1px solid #ffc107; 
            color: #b58100; 
            background: #fff8e1; /* Soft Amber/Warning Background */
        }
        .btn-util-lock:hover { 
            background: #ffc107; 
            color: #fff; 
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
            transform: translateY(-1px);
        }
        .btn-util-lock:active {
            transform: translateY(0);
        }

        .btn-util-logout { 
            border: 1px solid #dc3545; 
            color: #dc3545; 
            background: #fff5f5; /* Soft Red/Danger Background */
        }
        .btn-util-logout:hover { 
            background: #dc3545; 
            color: #fff; 
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
            transform: translateY(-1px);
        }
        .btn-util-logout:active {
            transform: translateY(0);
        }

        /* --- TOP GRID AREA (MANUAL REPLICA) --- */
        .top-card { background: #fff; border-radius: 28px; padding: 32px; border: 1px solid #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,.02); display: flex; gap: 24px; margin-bottom: 30px; }
        
        /* Kartu Kiri (Antrian Saat Ini) */
        .queue-card-wrapper { flex: 1; max-width: 33.333%; }
        .queue-card { height: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; padding: 28px; display: flex; flex-direction: column; justify-content: space-between; text-align: center; }
        .queue-card h5 { color: #64748b; font-size: 1.1rem; font-weight: 600; margin-bottom: 10px; }
        .queue-number { font-size: 76px; line-height: 1; font-weight: 800; margin: 15px 0; color: #0f172a; }
        .queue-time { color: #64748b; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-repeat-current { width: 100%; border-radius: 18px; height: 58px; font-size: 18px; font-weight: 700; background: transparent; border: 1px solid #4e73df; color: #4e73df; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; margin-top: 20px; }
        .btn-repeat-current:hover { background: #4e73df; color: #fff; }

        /* Kartu Kanan (Dua Tombol Panggil) */
        .call-btn-container { flex: 2; display: flex; flex-direction: column; gap: 20px; }
        .call-btn { width: 100%; border: none; border-radius: 24px; padding: 26px 30px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s; }
        .call-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.05); }
        .call-green { background: #ecfdf3; border: 1px solid #bbf7d0; color: #15803d; }
        .call-blue { background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; }
        
        .call-left { display: flex; align-items: center; gap: 18px; }
        .call-icon { width: 72px; height: 72px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .icon-green { background: #dcfce7; }
        .icon-blue { background: #dbeafe; }
        .call-title { font-size: 24px; font-weight: 700; text-align: left; }

        /* --- BOTTOM AREA (TABEL & PENCARIAN) --- */
        .bottom-half { background: #fff; border-radius: 28px; padding: 32px; border: 1px solid #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,.02); }
        .table-header-container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; }
        .table-title { font-size: 24px; font-weight: 700; margin: 0; }
        .search-box { padding: 0 15px; font-size: 1rem; border: 2px solid #d1d3e2; border-radius: 16px; width: 100%; max-width: 320px; height: 50px; outline: none; transition: all 0.2s; }
        .search-box:focus { border-color: #4e73df; }

        /* Table CSS */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid #edf2f7; }
        th { background-color: #f8fafc; color: #4e73df; font-weight: bold; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover { background-color: #f8fafc; }
        .btn-table-recall { background: transparent; border: 1px solid #4e73df; color: #4e73df; padding: 8px 16px; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-table-recall:hover { background: #4e73df; color: white; }

        .btn-back { margin-top: 30px; padding: 12px 25px; font-size: 1rem; font-weight: bold; cursor: pointer; border: 2px solid #6c757d; border-radius: 8px; background: #fff; color: #6c757d; transition: all 0.2s; max-width: 200px; width: 100%; }
        .btn-back:hover { background: #6c757d; color: #fff; }

        /* --- STYLE STATE 3 (LOCK SCREEN) --- */
        .lock-screen { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #2c3e50; color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 9999; }
        .btn-unlock { padding: 20px 50px; font-size: 1.5rem; font-weight: bold; background: #1cc88a; color: white; border: none; border-radius: 10px; cursor: pointer; transition: all 0.2s; margin-top: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .btn-unlock:hover { background: #17a673; transform: scale(1.05); }

        /* Responsive Breakpoints */
        @media(max-width: 992px) {
            .top-card { flex-direction: column; }
            .queue-card-wrapper { max-width: 100%; }
            .page-title { font-size: 28px; }
            .queue-number { font-size: 62px; }
            .call-title { font-size: 20px; }
            .search-box { max-width: 100%; }
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
        
        <div class="dashboard-header">
            <h1 id="txt-welcome-kasir" class="page-title">Dashboard Kasir</h1>
            <div class="header-utils">
                <button class="header-btn btn-util-lock" onclick="lockKasir()">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg>
                    Kunci Layar
                </button>
                <button class="header-btn btn-util-logout" onclick="logoutKasir()">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v2a.5.5 0 0 1-1 0v-2A1.5 1.5 0 0 1 6.5 2h8A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1 1.5h-8A1.5 1.5 0 0 1 5 12.5v-2a.5.5 0 0 1 1 0v2z"/><path fill-rule="evenodd" d="M.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L1.707 7.5H10.5a.5.5 0 0 1 0 1H1.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3z"/></svg>
                    Keluar
                </button>
            </div>
        </div>

        <div class="top-card">
            
            <div class="queue-card-wrapper">
                <div class="queue-card">
                    <div>
                        <h5>Antrian Saat Ini</h5>
                        <div id="active-queue-box" class="queue-number">-</div>
                        <div class="queue-time">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 1 1 14 0z"/></svg>
                            <span id="active-queue-time">--.--.--</span>
                        </div>
                    </div>
                    <button class="btn-repeat-current" onclick="callCurrent()">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658a.25.25 0 0 1-.41-.192z"/></svg>
                        Panggil Ulang
                    </button>
                </div>
            </div>

            <div class="call-btn-container">
                
                <button class="call-btn call-green" onclick="callNext('next_order')">
                    <div class="call-left">
                        <div class="call-icon icon-green">
                            <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                        </div>
                        <div class="call-title">Panggil Antrian Pembelian Selanjutnya</div>
                    </div>
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>
                </button>

                <button class="call-btn call-blue" onclick="callNext('next_pickup')">
                    <div class="call-left">
                        <div class="call-icon icon-blue">
                            <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5 8.186 1.113zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l6.5 2.6a1 1 0 0 1 0 1.854l-6.5 2.6a1.5 1.5 0 0 1-1.114 0l-6.5-2.6a1 1 0 0 1 0-1.854l6.5-2.6z"/></svg>
                        </div>
                        <div class="call-title">Panggil Antrian Pengambilan Selanjutnya</div>
                    </div>
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>
                </button>

            </div>
        </div>

        <div class="bottom-half">
            <div class="table-header-container">
                <h3 class="table-title">Daftar Antrian Berjalan</h3>
                <input type="text" id="queue-search-input" class="search-box" placeholder="Cari nomor antrian..." oninput="filterQueueTable()">
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
                        if(user.status !== 'Offline') btn.disabled = true;
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
                    rawQueuesData = queues;
                    
                    const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
                    const currentActive = queues.find(q => q.user_id == user.id && q.called_at !== null);
                    
                    const boxNumber = document.getElementById('active-queue-box');
                    const boxTime = document.getElementById('active-queue-time');
                    
                    if(currentActive) {
                        boxNumber.innerText = `${currentActive.type}.${String(currentActive.queue_number).padStart(3, '0')}`;
                        boxTime.innerText = new Date(currentActive.called_at).toLocaleTimeString('id-ID');
                    } else {
                        boxNumber.innerText = "-";
                        boxTime.innerText = "--.--.--";
                    }

                    filterQueueTable();
                });
        }

        function filterQueueTable() {
            const tbody = document.getElementById('queue-table-body');
            const keyword = document.getElementById('queue-search-input').value.trim().toLowerCase();
            tbody.innerHTML = '';
            
            if (rawQueuesData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">Belum ada antrian hari ini</td></tr>';
                return;
            }

            const filteredData = rawQueuesData.filter(q => {
                const formattedNumber = `${q.type}.${String(q.queue_number).padStart(3, '0')}`.toLowerCase();
                const rawNumberStr = String(q.queue_number);
                return formattedNumber.includes(keyword) || rawNumberStr.includes(keyword);
            });

            if (filteredData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#858796; font-style:italic; padding: 20px;">Tidak ada nomor antrian yang cocok dengan pencarian Anda</td></tr>';
                return;
            }

            filteredData.forEach(q => {
                const formattedNumber = `${q.type}.${String(q.queue_number).padStart(3, '0')}`;
                const row = `<tr>
                    <td><b>${formattedNumber}</b></td>
                    <td>${q.type === 'A' ? 'Pembelian' : 'Pengambilan Barang'}</td>
                    <td>${new Date(q.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit', second:'2-digit'})}</td>
                    <td>${q.called_at ? new Date(q.called_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit', second:'2-digit'}) : '-'}</td>
                    <td>${q.caller ? q.caller : '-'}</td>
                    <td>${q.called_at ? '<button class="btn-table-recall" onclick="recall(\'' + q.id + '\')"><svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="margin-right:2px;"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658a.25.25 0 0 1-.41-.192z"/></svg> Panggil Ulang</button>' : ''}</td>
                </tr>`;
                tbody.innerHTML += row;
            });
        }

        function callCurrent() {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            fetch(`/api/queues/recall-current?user_id=${user.id}`, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
            .then(res => { if (!res.ok) return res.json().then(err => { throw new Error(err.message) }); return res.json(); })
            .then(queue => { loadQueueTable(); })
            .catch(err => alert(err.message));
        }

        // Modifikasi callNext agar menerima string slug tipe 'next_order' / 'next_pickup' bawaan Anda
        function callNext(typeParam) { 
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            
            fetch(`/api/queues/call?user_id=${user.id}&type=${typeParam}`, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
            .then(res => { if (!res.ok) return res.json().then(err => { throw new Error(err.message) }); return res.json(); })
            .then(queue => { loadQueueTable(); })
            .catch(err => alert(err.message));
        }
        
        function recall(queueId) {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            if (!user) return;
            fetch(`/api/queues/call/${queueId}?user_id=${user.id}`, { method: 'GET', headers: { 'Content-Type': 'application/json' } })
            .then(res => { if (!res.ok) throw new Error('Gagal melakukan panggilan ulang.'); return res.json(); })
            .then(() => loadQueueTable())
            .catch(err => alert(err.message));
        }

        function lockKasir() {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            fetch('/api/toggle-lock-kasir', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: user.id, action: 'lock' }) })
            .then(res => { if(res.ok) { localStorage.setItem('kasir_isLocked', 'true'); showState3(); } });
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
            fetch('/api/toggle-lock-kasir', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: user.id, action: 'unlock' }) })
            .then(res => { if(res.ok) { localStorage.setItem('kasir_isLocked', 'false'); showState2(); } });
        }

        function clearLocalStorageSessi() {
            localStorage.removeItem('kasir_isLogin');
            localStorage.removeItem('kasir_lastLogin');
            localStorage.removeItem('kasir_userLogin');
            localStorage.removeItem('kasir_isLocked');
        }

        function logoutKasir() {
            const user = JSON.parse(localStorage.getItem('kasir_userLogin'));
            if (!user) { clearLocalStorageSessi(); window.location.href = '/'; return; }
            fetch('/api/logout-user', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: user.id }) })
            .then(() => { clearLocalStorageSessi(); window.location.href = '/'; })
            .catch(() => { clearLocalStorageSessi(); window.location.href = '/'; });
        }
    </script>
</body>
</html>