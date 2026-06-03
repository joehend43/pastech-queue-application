<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Display Monitor</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4e73df">

    <style>
        /* --- STYLE GENERAL & RESET --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #eef2f5; height: 100vh; overflow: hidden; padding: 20px; }
        .hidden { display: none !important; }
        
        .btn-base { padding: 12px 25px; font-size: 1rem; font-weight: bold; cursor: pointer; border-radius: 8px; transition: all 0.2s; border: none; }
        .btn-back { border: 2px solid #6c757d; background: #fff; color: #6c757d; max-width: 200px; width: 100%; }
        .btn-back:hover { background: #6c757d; color: #fff; }
        
        /* --- STYLE STATE 1 --- */
        .grid-user { display: flex; flex-direction: column; gap: 15px; max-width: 400px; width: 100%; margin-top: 20px; }
        .btn-user-s1 { padding: 18px 25px; font-size: 1.2rem; font-weight: bold; cursor: pointer; border: 2px solid #1cc88a; border-radius: 8px; background: #fff; color: #1cc88a; transition: all 0.2s; text-align: center; }
        .btn-user-s1:not(:disabled):hover { background: #1cc88a; color: #fff; transform: translateY(-2px); }
        .btn-user-s1:disabled { background: #eaecf4; border-color: #d1d3e2; color: #b7b9cc; cursor: not-allowed; }

        /* --- STYLE STATE 2 (KONFIGURASI) --- */
        .split-container-s2 { display: flex; min-height: 70vh; gap: 20px; margin-bottom: 20px; }
        .side-pane-s2 { flex: 1; background: #fff; border: 2px dashed #dddfeb; border-radius: 12px; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .list-selector { display: flex; flex-direction: column; gap: 10px; width: 100%; max-width: 300px; margin-top: 20px; }
        .btn-select-kasir { padding: 12px; font-size: 1rem; cursor: pointer; border: 1px solid #d1d3e2; background: #fff; border-radius: 6px; font-weight: bold; }
        .btn-select-kasir:hover:not(:disabled) { background: #4e73df; color: #fff; }
        .btn-select-kasir:disabled { background: #eaecf4; color: #b7b9cc; cursor: not-allowed; }
        .bottom-actions { display: flex; justify-content: center; gap: 20px; align-items: center; height: 10vh; }
        .btn-primary-action { background: #4e73df; color: #fff; }
        .btn-primary-action:disabled { background: #dddfeb; color: #b7b9cc; cursor: not-allowed; }
        .btn-danger-action { background: #e74a3b; color: #fff; }

        /* --- STYLE STATE 3 (NEW CLIENT DISPLAY DESIGNS) --- */
        .container-s3 { display: flex; height: 100vh; padding: 30px; gap: 30px; width: 100%; }
        .panel { flex: 1; border-radius: 25px; display: flex; flex-direction: column; justify-content: center; align-items: center; box-shadow: 0 12px 30px rgba(0,0,0,.08); position: relative; overflow: hidden; padding: 20px; }
        .panel.full-width { flex: none; width: 100%; }
        
        .panel-left-theme { background: #eaf7ef; }   /* Tema Sisi Kiri (Hijau) */
        .panel-right-theme { background: #fdeeee; }  /* Tema Sisi Kanan (Merah) */

        .counter { position: absolute; top: 40px; font-size: 4.5vw; font-weight: 900; color: #222; text-transform: uppercase; line-height: 1;}
        .service { font-size: 34px; font-weight: bold; margin-top: 8px; color: #666; }
        .number { font-size: 16vw; font-weight: 900; color: #111; line-height: 0.85; letter-spacing: -1.2vw; text-align: center; width: 100%; white-space: nowrap; }
        .status { font-size: 2.2vw; color: #555; background: rgba(255,255,255,.7); padding: 10px 25px; border-radius: 30px; margin-top: 20px; font-weight: bold; }

        .panel.full-width .number { font-size: 32vw; letter-spacing: -2vw;}

        /* Garis identitas warna atas pada panel */
        .panel::before { content: ''; width: 100%; height: 24px; position: absolute; top: 0; left: 0; }
        .panel-left-theme::before { background: #2eaf5d; }
        .panel-right-theme::before { background: #d9534f; }
        
        /* Indikator status Reverb di pojok layar */
        #connection-dot { position: fixed; bottom: 15px; right: 15px; width: 14px; height: 14px; background: #d9534f; border-radius: 50%; z-index: 99999; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    </style>
    <!-- <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script> -->
    <script src="{{ asset('js/pusher.min.js') }}"></script>
</head>
<body>

    <div id="state-1" class="hidden" style="text-align: center; padding-top: 40px; display: flex; flex-direction: column; align-items: center; min-height: 85vh; justify-content: space-between;">
        <div>
            <h2 style="color: #333; margin-bottom: 30px;">Pilih Display</h2>
            <div id="user-list" class="grid-user"></div>
        </div>
        <button class="btn-base btn-back" onclick="window.location.href='/'">Kembali</button>
    </div>

    <div id="state-2" class="hidden">
        <h2 style="text-align: center; color: #333; margin-bottom: 20px;">Konfigurasi Tampilan Monitor</h2>
        <div class="split-container-s2">
            <div class="side-pane-s2">
                <h3>Sisi Kiri</h3>
                <div id="left-selector-list" class="list-selector"></div>
            </div>
            <div class="side-pane-s2">
                <h3>Sisi Kanan</h3>
                <div id="right-selector-list" class="list-selector"></div>
            </div>
        </div>
        <div class="bottom-actions">
            <button id="btn-tampilkan" class="btn-base btn-primary-action" disabled onclick="submitConfiguration()">Tampilkan</button>
            <button class="btn-base btn-danger-action" onclick="logoutDisplay()">Keluar</button>
        </div>
    </div>

    <div id="state-3" class="hidden" style="margin: -20px;">
        <!-- <div id="audio-blocker-overlay" style="position: fixed; top:0; left:0; width:100vw; height:100vh; background: rgba(78, 115, 223, 0.95); z-index: 99999; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white;">
            <h1 style="font-size: 3rem; margin-bottom: 20px;">Monitor Display Belum Siap</h1>
            <p style="font-size: 1.5rem; margin-bottom: 30px;">Browser memblokir audio otomatis sebelum ada interaksi.</p>
            <button class="btn-base" style="background: #1cc88a; color: white; padding: 20px 40px; font-size: 1.8rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2);" onclick="unlockDisplayAudio()">
                AKTIFKAN SUARA & MONITOR
            </button>
        </div> -->

        <div id="connection-dot" title="Menghubungkan ke WebSocket..."></div>

        <div id="monitor-container" class="container-s3"></div>
    </div>

    <script>
        let indonesianVoice = null;
        let isAudioInitialized = false;
        let ttsQueue = [];          // Menampung antrean panggilan yang masuk
        let isTtsSpeaking = false;  // Flag penanda apakah speaker sedang sibuk berbicara
        let selectedLeftKasirId = null;
        let selectedRightKasirId = null;
        let globalKasirList = [];
        // const isBroadcastAll = {{ env('BROADCAST_DISPLAY') === 'ALL' ? 'true' : 'false' }};

        // === KONFIGURASI DINAMIS DARI LARAVEL .ENV ===
        const broadcastMode = "{{ env('BROADCAST_DISPLAY', 'DEFAULT') }}"; // 'ALL', 'GROUP', atau 'DEFAULT'
        
        // Mengonversi string "1,2,3" dari env menjadi array [1, 2, 3] di JavaScript
        const group1 = [{{ env('BROADCAST_GROUP_1', '') }}];
        const group2 = [{{ env('BROADCAST_GROUP_2', '') }}];

        // Fungsi untuk mengecek apakah Kasir yang memanggil berada dalam grup yang sama dengan monitor ini
        function isCallerInSameGroup(calledKasirId) {
            let monitorGroups = [];

            // 1. Deteksi loket ini masuk ke grup mana saja berdasarkan kasir terpilih di URL
            if (group1.includes(Number(selectedLeftKasirId)) || group1.includes(Number(selectedRightKasirId))) {
                monitorGroups.push(1);
            }
            if (group2.includes(Number(selectedLeftKasirId)) || group2.includes(Number(selectedRightKasirId))) {
                monitorGroups.push(2);
            }

            // 2. Cek apakah kasir yang memanggil (calledKasirId) terdaftar di dalam grup monitor tersebut
            if (monitorGroups.includes(1) && group1.includes(Number(calledKasirId))) {
                return true;
            }
            if (monitorGroups.includes(2) && group2.includes(Number(calledKasirId))) {
                return true;
            }

            return false;
        }

        document.addEventListener("DOMContentLoaded", function() {
            checkState();
            initSpeechSynthesis();
            setupReverbListener();
        });

        // ==========================================
        // SYSTEM ENGINE: TEXT TO SPEECH (TTS)
        // ==========================================
        function initSpeechSynthesis() {
            if ('speechSynthesis' in window) {
                const loadVoices = () => {
                    const voices = window.speechSynthesis.getVoices();
                    indonesianVoice = voices.find(voice => voice.lang === 'id-ID' || voice.lang.startsWith('id'));
                };
                loadVoices();
                if (window.speechSynthesis.onvoiceschanged !== undefined) {
                    window.speechSynthesis.onvoiceschanged = loadVoices;
                }
            }
        }

        function triggerAudioPermission() {
            isAudioInitialized = true;
            console.log("Izin TTS Aktif di Monitor Display.");
        }

        function unlockDisplayAudio() {
            isAudioInitialized = true;
            window.speechSynthesis.cancel();
            const welcomeUtterance = new SpeechSynthesisUtterance("Sistem suara antrean aktif.");
            welcomeUtterance.voice = indonesianVoice;
            window.speechSynthesis.speak(welcomeUtterance);

            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            }

            document.getElementById('audio-blocker-overlay').style.display = 'none';
        }

        function speakQueueV1(type, number, kasirName) {
            if (!isAudioInitialized || !indonesianVoice) {
                console.warn(`[TTS Skip]: initialized=${isAudioInitialized}`);
                return;
            }
            
            console.log(`Panggilan Antrian: ${type}.${String(number).padStart(3, '0')} untuk ${kasirName}`);
            const parsedNumber = parseInt(number, 10);
            const textToSpeak = `Nomor Antrian ${type}, ${parsedNumber}. Silahkan menuju ${kasirName}.`;

            const utterance = new SpeechSynthesisUtterance(textToSpeak);
            utterance.voice = indonesianVoice;
            utterance.rate = 0.90;

            utterance.onstart = () => console.log('%c[TTS Start]', 'color: #1cc88a; font-weight: bold;');
            utterance.onend = () => console.log('%c[TTS End]', 'color: #4e73df; font-weight: bold;');
            utterance.onerror = (e) => console.error('[TTS Error]:', e.error);

            window.speechSynthesis.cancel(); 
            window.speechSynthesis.speak(utterance);
        }

        function speakQueue(type, number, kasirName) {
            if (!isAudioInitialized || !indonesianVoice) {
                console.warn(`[TTS Skip]: initialized=${isAudioInitialized}`);
                return;
            }

            // Masukkan data panggilan baru ke barisan paling belakang
            ttsQueue.push({ type: type, number: number, kasirName: kasirName });
            console.log(`[TTS Queue] Masuk antrean: ${type}.${String(number).padStart(3, '0')}. Total antrean saat ini: ${ttsQueue.length}`);

            // Jalankan prosesor pengecek antrean
            processTtsQueue();
        }

        // 2. Fungsi Prosesor: Mengatur lalu lintas suara secara berurutan
        function processTtsQueue() {
            // Jika mesin TTS sedang berbicara, atau tidak ada antrean tersisa, kunci proses (tunggu)
            if (isTtsSpeaking || ttsQueue.length === 0) {
                return;
            }

            // Tandai mesin TTS mulai sibuk
            isTtsSpeaking = true;

            // Ambil data panggilan pertama di barisan paling depan (FIFO - First In First Out)
            const currentItem = ttsQueue.shift();

            console.log(`Panggilan Antrian: ${currentItem.type}.${String(currentItem.number).padStart(3, '0')} untuk ${currentItem.kasirName}`);
            const parsedNumber = parseInt(currentItem.number, 10);
            const textToSpeak = `Nomor Antrian ${currentItem.type}, ${parsedNumber}. Silahkan menuju ${currentItem.kasirName}.`;

            const utterance = new SpeechSynthesisUtterance(textToSpeak);
            utterance.voice = indonesianVoice;
            utterance.rate = 0.90;

            // Trigger saat suara MULAI diucapkan
            utterance.onstart = () => {
                console.log('%c[TTS Start]: Sedang menyuarakan...', 'color: #1cc88a; font-weight: bold;');
            };

            // Trigger saat suara SELESAI diucapkan secara tuntas
            utterance.onend = () => {
                console.log('%c[TTS End]: Selesai.', 'color: #4e73df; font-weight: bold;');
                
                // Lepas status sibuk
                isTtsSpeaking = false;
                
                // Berikan jeda napas 1 detik sebelum memanggil antrean berikutnya agar tidak terlalu rapat
                setTimeout(() => {
                    processTtsQueue(); 
                }, 1000);
            };

            // Trigger jika terjadi error di tengah jalan
            utterance.onerror = (e) => {
                console.error('[TTS Error]:', e.error);
                isTtsSpeaking = false;
                processTtsQueue(); // Tetap lanjutkan antrean berikutnya agar sistem tidak macet
            };

            // PENTING: Jangan gunakan window.speechSynthesis.cancel() di sini!
            // Biarkan browser mengeksekusi objek utterance tunggal ini secara alami
            window.speechSynthesis.speak(utterance);
        }

        // ==========================================
        // SYSTEM ENGINE: LARAVEL REVERB WEBSOCKET
        // ==========================================
        function setupReverbListener() {
            const pusher = new Pusher("{{ env('REVERB_APP_KEY') }}", {
                cluster: 'mt1',
                wsHost: "{{ env('REVERB_HOST', '127.0.0.1') }}",
                wsPort: {{ env('REVERB_PORT', 8080) }},
                forceTLS: false,
                encrypted: false,
                enabledTransports: ['ws', 'wss']
            });

            pusher.connection.bind('state_change', function(states) {
                const dot = document.getElementById('connection-dot');
                if (dot) {
                    if (states.current === 'connected') {
                        dot.style.background = '#2eaf5d'; // Berubah Hijau jika tersambung murni
                        dot.setAttribute('title', 'Koneksi Terhubung Realtime');
                    } else {
                        dot.style.background = '#d9534f'; // Merah jika terputus
                        dot.setAttribute('title', 'Koneksi Terputus / Reconnecting...');
                    }
                }
            });

            const channel = pusher.subscribe('queue-channel');
            
            channel.bind('queue.called', function(data) {
                console.log('[Reverb Sinyal Masuk]:', data);
                if(data && data.status != 'called') {
                    console.log('[Reverb Skip]: Status antrian bukan "called".', data);
                    if(data.status != 'created') return; // Hanya proses jika status bukan "called" maupun "created" (untuk update status Kasir)
                    const urlParams = new URLSearchParams(window.location.search);
                    const leftParam = urlParams.get('left');
                    const rightParam = urlParams.get('right');

                    fetchAndRenderMonitorCard(leftParam, 'pane-left');
                    fetchAndRenderMonitorCard(rightParam, 'pane-right');
                    return;
                }

                const calledKasirId = data.queue.user_id;

                // ------------------------------------------------------------
                // 1. LOGIKA VISUAL PANEL (Hanya berubah jika Kasir cocok dengan parameter URL)
                // ------------------------------------------------------------
                if (calledKasirId == selectedLeftKasirId || calledKasirId == selectedRightKasirId) {
                    const isLeft = (calledKasirId == selectedLeftKasirId);
                    const elementId = isLeft ? 'pane-left' : 'pane-right';
                    const pane = document.getElementById(elementId);
                    
                    if (pane) {
                        const formattedNumber = `${data.queue.type}.${String(data.queue.queue_number).padStart(3, '0')}`;
                        const formattedTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });

                        // Update DOM sesuai gaya template baru
                        pane.innerHTML = `
                            <div class="counter">${data.kasirName}</div>
                            <div class="number">${formattedNumber}</div>
                        `;
                    }
                }

                // ------------------------------------------------------------
                // 2. LOGIKA AUDIO TTS (Bypass langsung jika BROADCAST_DISPLAY="ALL")
                // ------------------------------------------------------------
                const isKasirTercatatDiMonitor = (calledKasirId == selectedLeftKasirId || calledKasirId == selectedRightKasirId);
                let shouldSpeak = false;

                if (broadcastMode === 'ALL') {
                    shouldSpeak = true; // Panggil semua tanpa pandang bulu
                } else if (broadcastMode === 'GROUP') {
                    // Jika mode GROUP aktif, bunyikan jika kasir tercatat di layar ATAU berada dalam satu ikatan grup
                    shouldSpeak = isKasirTercatatDiMonitor || isCallerInSameGroup(calledKasirId);
                } else {
                    shouldSpeak = isKasirTercatatDiMonitor; // Default: Hanya bunyikan yang tampil di monitor saja
                }

                if (shouldSpeak) {
                    console.log(`[TTS Queue] Memasukkan suara ${data.queue.type}.${data.queue.queue_number} ke antrian.`);
                    speakQueue(data.queue.type, data.queue.queue_number, data.kasirName);
                }
            });
        }

        // ==========================================
        // ROUTER STATE CHECKER
        // ==========================================
        function checkState() {
            const urlParams = new URLSearchParams(window.location.search);
            const leftParam = urlParams.get('left');
            const rightParam = urlParams.get('right');

            if (leftParam || rightParam) {
                selectedLeftKasirId = leftParam;
                selectedRightKasirId = rightParam;
                showState3(leftParam, rightParam);
                return;
            }

            const isLogin = localStorage.getItem('display_isLogin');
            const user = JSON.parse(localStorage.getItem('display_userLogin'));

            if (isLogin === 'true' && user && user.type === 'display') {
                showState2();
            } else {
                showState1();
            }
        }

        // ==========================================
        // LOGIC STATE 1: SELECTION DISPLAY LOGIN
        // ==========================================
        function showState1() {
            document.getElementById('state-1').classList.remove('hidden');
            document.getElementById('state-2').classList.add('hidden');
            document.getElementById('state-3').classList.add('hidden');
            
            fetch('/api/display')
                .then(res => res.json())
                .then(users => {
                    const container = document.getElementById('user-list');
                    container.innerHTML = '';
                    users.forEach(user => {
                        const btn = document.createElement('button');
                        btn.className = 'btn-user-s1';
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
            .then(res => { if(!res.ok) throw new Error('Display ini sedang aktif'); return res.json(); })
            .then(user => {
                localStorage.setItem('display_isLogin', 'true');
                localStorage.setItem('display_lastLogin', new Date().toISOString());
                localStorage.setItem('display_userLogin', JSON.stringify(user));
                showState2();
            })
            .catch(err => alert(err.message));
        }

        // ==========================================
        // LOGIC STATE 2: CONFIGURATION LAYOUT PANEL
        // ==========================================
        function showState2() {
            document.getElementById('state-1').classList.add('hidden');
            document.getElementById('state-2').classList.remove('hidden');
            document.getElementById('state-3').classList.add('hidden');

            fetch('/api/kasir')
                .then(res => res.json())
                .then(kasirs => {
                    globalKasirList = kasirs;
                    renderSelectorLists();
                });
        }

        function renderSelectorLists() {
            const leftContainer = document.getElementById('left-selector-list');
            const rightContainer = document.getElementById('right-selector-list');
            
            leftContainer.innerHTML = '';
            rightContainer.innerHTML = '';

            globalKasirList.forEach(kasir => {
                // Sisi Kiri
                const btnLeft = document.createElement('button');
                btnLeft.className = 'btn-select-kasir';
                if (selectedLeftKasirId == kasir.id) {
                    btnLeft.innerText = `${kasir.name} (Terpilih)`;
                    btnLeft.style.background = '#2eaf5d';
                    btnLeft.style.color = '#fff';
                } else {
                    btnLeft.innerText = kasir.name;
                    if (selectedRightKasirId == kasir.id) btnLeft.disabled = true;
                }
                btnLeft.onclick = () => {
                    selectedLeftKasirId = (selectedLeftKasirId == kasir.id) ? null : kasir.id;
                    renderSelectorLists();
                    validateTampilkanButton();
                };
                leftContainer.appendChild(btnLeft);

                // Sisi Kanan
                const btnRight = document.createElement('button');
                btnRight.className = 'btn-select-kasir';
                if (selectedRightKasirId == kasir.id) {
                    btnRight.innerText = `${kasir.name} (Terpilih)`;
                    btnRight.style.background = '#d9534f';
                    btnRight.style.color = '#fff';
                } else {
                    btnRight.innerText = kasir.name;
                    if (selectedLeftKasirId == kasir.id) btnRight.disabled = true;
                }
                btnRight.onclick = () => {
                    selectedRightKasirId = (selectedRightKasirId == kasir.id) ? null : kasir.id;
                    renderSelectorLists();
                    validateTampilkanButton();
                };
                rightContainer.appendChild(btnRight);
            });
        }

        function validateTampilkanButton() {
            const btnTampilkan = document.getElementById('btn-tampilkan');
            btnTampilkan.disabled = !(selectedLeftKasirId || selectedRightKasirId);
        }

        function submitConfiguration() {
            let url = '/display?';
            if (selectedLeftKasirId) url += `left=${selectedLeftKasirId}&`;
            if (selectedRightKasirId) url += `right=${selectedRightKasirId}`;
            window.location.href = url;
        }

        function logoutDisplay() {
            const user = JSON.parse(localStorage.getItem('display_userLogin'));
            if (!user) {
                localStorage.clear();
                showState1();
                return;
            }

            fetch('/api/logout-user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ id: user.id })
            })
            .then(() => {
                localStorage.clear();
                showState1();
            });
        }

        // ==========================================
        // LOGIC STATE 3: MONITOR ENGINE RENDERING
        // ==========================================
        function showState3(leftId, rightId) {
            document.getElementById('state-1').classList.add('hidden');
            document.getElementById('state-2').classList.add('hidden');
            document.getElementById('state-3').classList.remove('hidden');
            triggerAudioPermission();

            const container = document.getElementById('monitor-container');
            container.innerHTML = '';

            // Kasus Dua Loket Berdampingan (Split)
            if (leftId && rightId) {
                container.innerHTML = `
                    <div id="pane-left" class="panel panel-left-theme">Loading...</div>
                    <div id="pane-right" class="panel panel-right-theme">Loading...</div>
                `;
                fetchAndRenderMonitorCard(leftId, 'pane-left');
                fetchAndRenderMonitorCard(rightId, 'pane-right');
            } 
            // Kasus Hanya Mengisi Loket Kiri (Full Screen Hijau)
            else if (leftId) {
                container.innerHTML = `<div id="pane-left" class="panel panel-left-theme full-width">Loading...</div>`;
                fetchAndRenderMonitorCard(leftId, 'pane-left');
            } 
            // Kasus Hanya Mengisi Loket Kanan (Full Screen Merah)
            else if (rightId) {
                container.innerHTML = `<div id="pane-right" class="panel panel-right-theme full-width">Loading...</div>`;
                fetchAndRenderMonitorCard(rightId, 'pane-right');
            }
        }

        function fetchAndRenderMonitorCard(kasirId, elementId) {
            fetch('/api/kasir')
                .then(res => res.json())
                .then(kasirs => {
                    const currentKasir = kasirs.find(k => k.id == kasirId);
                    const pane = document.getElementById(elementId);
                    if (!pane || !currentKasir) return;

                    const name = currentKasir.name;
                    const statusLoket = currentKasir.status;

                    if (statusLoket === 'Offline') {
                        pane.innerHTML = `
                            <div class="counter">${name}</div>
                            <div class="number" style="color: #d9534f; font-size: 9vw; letter-spacing: -0.75vw;">OFFLINE</div>
                        `;
                        return;
                    }

                    if (statusLoket === 'Locked') {
                        pane.innerHTML = `
                            <div class="counter">${name}</div>
                            <div class="number" style="color: #f6c23e; font-size: 7vw; letter-spacing: -0.75vw;">ISTIRAHAT</div>
                            <div class="status" style="background: rgba(246, 194, 62, 0.2); color: #b58100;">Loket Sementara Tutup</div>
                        `;
                        return; // Berhenti di sini, jangan tampilkan nomor antrian dulu
                    }

                    fetch(`/api/queues/latest`)
                        .then(res => res.json())
                        .then(queues => {
                            const lastCalledQueue = queues.find(q => q.user_id == kasirId && q.called_at !== null);

                            if (lastCalledQueue) {
                                const type = lastCalledQueue.type;
                                const formattedNumber = `${type}.${String(lastCalledQueue.queue_number).padStart(3, '0')}`;
                                const formattedTime = new Date(lastCalledQueue.called_at).toLocaleTimeString('id-ID', {
                                    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
                                });

                                pane.innerHTML = `
                                    <div class="counter">${name}</div>
                                    <div class="number">${formattedNumber}</div>
                                `;
                            } else {
                                pane.innerHTML = `
                                    <div class="counter">${name}</div>
                                    <div class="number" style="font-size: 9vw; letter-spacing: -0.75vw; color: #777;">KOSONG</div>
                                    <div class="status" style="background: rgba(0, 0, 0, 0.1); color: #555;">Belum ada antrian</div>
                                `;
                            }
                        });
                });
        }
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('[PWA]: Service Worker Aktif'))
                    .catch(err => console.error('[PWA Error]:', err));
            });
        }
    </script>
</body>
</html>