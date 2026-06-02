<?php

namespace App\Http\Controllers;

use App\Events\QueueCalled;
use DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Queue;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ApiController extends Controller
{

    private function autoOfflineExpiredUsers($type="kasir")
    {
        // Cari semua user yang login-nya bukan hari ini, tapi statusnya bukan Offline
        User::whereDate('last_login', '<', now()->toDateString())
            ->where('status', '!=', 'Offline')
            ->where('type', $type)
            ->update([
                'status' => 'Locked'
            ]);
    }
    public function getKasir() {
        $this->autoOfflineExpiredUsers();
        return response()->json(User::where('is_active', true)->where('type', 'kasir')->get());
    }

    public function getDisplay() {
        return response()->json(User::where('is_active', true)->where('type', 'display')->get());
    }

    public function loginUser(Request $request) {
        $user = User::findOrFail($request->id);
        
        if ($user->status !== 'Offline') {
            return response()->json(['message' => 'User sedang digunakan'], 400);
        }

        $user->update([
            'status' => 'Online',
            'last_login' => now()
        ]);

        $dummy = Queue::withTrashed()->first();
        broadcast(new QueueCalled($dummy, $user->name, $user->status))->toOthers();

        return response()->json($user);
    }

    public function logoutUser(Request $request) {
        $user = User::findOrFail($request->id);
        
        $user->update([
            'status' => 'Offline'
        ]);

        $dummy = Queue::withTrashed()->first();
        broadcast(new QueueCalled($dummy, $user->name, $user->status))->toOthers();

        return response()->json($user);
    }

    public function toggleLockKasir(Request $request)
    {
        $user = User::findOrFail($request->id);
        $action = $request->input('action'); // 'lock' atau 'unlock'

        if ($action === 'lock') {
            $user->update(['status' => 'Locked']);
        } else {
            $user->update(['status' => 'Online']);
        }
        $dummy = Queue::withTrashed()->first();
        broadcast(new QueueCalled($dummy, $user->name, $user->status))->toOthers();

        return response()->json($user);
    }

    public function getQueues() {
        // Mengambil antrian hari ini yang berjalan
        return DB::transaction(function (){
            $queues = Queue::whereDate('created_at', now()->toDateString())
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json($queues);
        });
    }

    public function getLatestQueues() {
        // Mengambil antrian hari ini yang berjalan
        return DB::transaction(function (){
            $queues = Queue::whereDate('created_at', now()->toDateString())
                ->orderBy('called_at', 'desc')
                ->get();
                
            return response()->json($queues);
        });
    }

    public function callDynamic(Request $request){
        $type = $request->query('type'); // 'next_order', 'next_pickup', atau 'current'
        switch ($type) {
            case 'next_order':
                return $this->callNext($request, 'A'); // Asumsi type A untuk order
            case 'next_pickup':
                return $this->callNext($request, 'B'); // Asumsi type B untuk pickup
            case 'current':
                return $this->recallCurrent($request);
            default:
                return response()->json(['message' => 'Tipe panggilan tidak valid'], 400);
        }
    }

    public function callNext(Request $request, $type='A')
    {
        $userId = $request->user_id;
        return DB::transaction(function () use($userId, $type) {
            $userKasir = User::findOrFail($userId);

            $nextQueue = Queue::whereDate('created_at', now()->toDateString())
                ->whereNull('called_at')
                ->where('type', $type)
                ->orderBy('created_at', 'asc')
                ->first();
            $typeDisplay = ($type === 'A') ? 'Pembelian' : 'Pengambilan';
            if (!$nextQueue) {
                return response()->json(['message' => 'Antrian ' . $typeDisplay . ' saat ini sudah habis'], 404);
            }

            $nextQueue->update([
                'called_at' => now(),
                'user_id' => $userId,
                'caller' => $userKasir->name
            ]);

            // AMBIL SIKNAL & LEMPAR KE REVERB
            broadcast(new QueueCalled($nextQueue, $userKasir->name))->toOthers();

            return response()->json($nextQueue);
        });
    }

    public function recallCurrent(Request $request)
    {
        // 1. Ambil user_id kasir dari query parameter GET
        $userId = $request->query('user_id');
        return DB::transaction(function () use($userId) {
            $userKasir = User::findOrFail($userId);

            // 2. Cari antrian terakhir hari ini yang SUDAH dipanggil oleh kasir ini
            $currentQueue = Queue::whereDate('created_at', now()->toDateString())
                ->whereNotNull('called_at')
                ->where('user_id', $userId)
                ->orderBy('called_at', 'desc') // Mengambil panggilan paling terbaru
                ->first();

            // 3. Jika kasir belum pernah memanggil antrian sama sekali hari ini
            if (!$currentQueue) {
                return response()->json([
                    'message' => 'Anda belum memanggil antrian apa pun hari ini.'
                ], 404);
            }

            // 4. Update kembali kolom called_at ke waktu sekarang (untuk menyegarkan urutan di display)
            $currentQueue->update([
                'called_at' => now(),
            ]);

            // 5. Broadcast ke Reverb agar Monitor Display memicu suara TTS ulang
            broadcast(new QueueCalled($currentQueue, $userKasir->name))->toOthers();

            return response()->json($currentQueue);
        });
    }

    public function callQueue(Request $request, $id)
    {
        return DB::transaction(function () use($request, $id) {
            $queue = Queue::findOrFail($id);
            $userId = $request->query('user_id');
            $kasir = User::findOrFail($userId);
            // Update waktu panggil terakhir dan siapa yang memanggil
            $queue->update([
                'called_at' => now(),
                'user_id' => $kasir->id,
                'caller' => $kasir->name
            ]);

            // Broadcast ke Reverb agar Monitor Display memicu suara TTS
            broadcast(new QueueCalled($queue, $kasir->name))->toOthers();

            return response()->json([
                'status' => 'Success',
                'message' => 'Antrian berhasil dipanggil ulang',
                'data' => $queue
            ]);
        });
    }



    public function countRemainingByType()
    {
        return DB::transaction(function () {
            $countA = Queue::whereDate('created_at', now()->toDateString())
                ->whereNull('called_at')
                ->where('type', 'A')
                ->count();

            $countB = Queue::whereDate('created_at', now()->toDateString())
                ->whereNull('called_at')
                ->where('type', 'B')
                ->count();

            return response()->json(['A' => $countA, 'B' => $countB]);
        });
    }

    public function generateQueue(Request $request)
    {
        $type = $request->input('type'); // Menerima 'A' atau 'B'
        
        if (!in_array($type, ['A', 'B'])) {
            return response()->json(['message' => 'Tipe antrian tidak valid'], 400);
        }

        return DB::transaction(function () use ($type) {
            // 1. Ambil nomor antrian selanjutnya (Increment)
            $lastQueue = Queue::whereDate('created_at', now()->toDateString())
                ->where('type', $type)
                ->orderBy('queue_number', 'desc')
                ->first();

            $nextNumber = $lastQueue ? ($lastQueue->queue_number + 1) : 1;

            // 2. Simpan data antrian ke DB
            $newQueue = Queue::create([
                'type' => $type,
                'queue_number' => $nextNumber,
                'called_at' => null,
                'user_id' => null,
                'created_at' => now(),
            ]);

            // Format nomor (Contoh: A.005)
            $formattedNumber = $newQueue->type . '.' . str_pad($newQueue->queue_number, 3, '0', STR_PAD_LEFT);
            $waktuCetak = now()->format('d/m/Y H:i:s');

            // 3. PROSES CETAK LANGSUNG (HEADLESS PRINTING)
            try {
                // Tentukan konektor berdasarkan konfigurasi .env
                if (env('PRINTER_CONNECTION_TYPE') === 'network') {
                    $connector = new NetworkPrintConnector(env('PRINTER_IP'), env('PRINTER_PORT', 9100));
                } else {
                    // Standar Windows USB Sharing
                    $connector = new WindowsPrintConnector(env('PRINTER_NAME'));
                }

                $printer = new Printer($connector);

                // --- Mulai Desain Struk Thermal ---
                $printer->setJustification(Printer::JUSTIFY_CENTER);

                // Cetak Tipe Antrian
                $printer->text(($type === 'A' ? "PEMBELIAN " : "PENGAMBILAN BARANG "));
                $printer->text($waktuCetak ."\n");
                $printer->feed();
                // Cetak Nomor Besar
                $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
                $printer->text($formattedNumber . "\n");
                $printer->selectPrintMode(); // Reset
                
                $printer->feed();
                $printer->text("MOHON TIKET DISERTAKAN SAAT NOMER \n DIPANGGIL (PASTECH01).\n");
                
                // $printer->feed(2); // Kasih jarak potongan kertas
                
                // Perintah potong kertas (Auto-cutter) jika printer mendukung
                $printer->cut();
                
                // Tutup koneksi printer
                $printer->close();

            } catch (\Exception $e) {
                // Log error jika printer mati / tidak terhubung agar API tidak crash sepenuhnya
                \Log::error("Gagal mencetak struk antrian: " . $e->getMessage());
                return response()->json([
                    'status' => 'Warning',
                    'message' => 'Antrian tersimpan di DB, tapi gagal cetak fisik.',
                    'error' => $e->getMessage()
                ], 500);
            }

            // Kembalikan respons sukses ke device pengirim hit API
            return response()->json([
                'status' => 'Success',
                'data' => [
                    'id' => $newQueue->id,
                    'formatted' => $formattedNumber
                ]
            ]);
        });
    }

    public function createQueue(Request $request)
    {
        $type = $request->input('type'); // Menerima 'A' atau 'B'
        
        if (!in_array($type, ['A', 'B'])) {
            return response()->json(['message' => 'Tipe antrian tidak valid'], 400);
        }
        return DB::transaction(function () use ($type) {
            // 1. Ambil nomor antrian selanjutnya (Increment)
            $lastQueue = Queue::whereDate('created_at', now()->toDateString())
                ->where('type', $type)
                ->orderBy('queue_number', 'desc')
                ->first();

            $nextNumber = $lastQueue ? ($lastQueue->queue_number + 1) : 1;

            // 2. Simpan data antrian ke DB
            $newQueue = Queue::create([
                'type' => $type,
                'queue_number' => $nextNumber,
                'called_at' => null,
                'user_id' => null,
                'created_at' => now(),
            ]);
            // Format nomor (Contoh: A.005)
            $formattedNumber = $newQueue->type . '.' . str_pad($newQueue->queue_number, 3, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'Success',
                'data' => [
                    'id' => $newQueue->id,
                    'formatted' => $formattedNumber
                ]
            ]);
        });
    }
}