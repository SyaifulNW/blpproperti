<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Terima data dari Google Form via Apps Script
     * POST /api/google-form
     */
    public function googleFormWebhook(Request $request)
    {
        try {
            // Ambil data dari payload JSON
            $payload = $request->json()->all();

            // Fallback ke form data biasa jika bukan JSON
            if (empty($payload)) {
                $payload = $request->all();
            }

            $nama       = $payload['nama']         ?? null;
            $noWa       = $payload['no_wa']         ?? null;
            $jenisProduk = $payload['jenis_produk'] ?? null;
            $source     = $payload['source']        ?? 'google_form';

            // Validasi field wajib
            if (empty($nama) || empty($noWa)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field nama dan no_wa wajib diisi.'
                ], 422);
            }

            // Cek duplikat berdasarkan no_wa (opsional, bisa dihilangkan)
            $existing = DB::table('data')
                ->where('no_wa', $noWa)
                ->whereIn('status_peserta', ['peserta_baru', 'sales_plan'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor WA sudah terdaftar di database.',
                    'id'      => $existing->id
                ], 200);
            }

            // Map jenis_produk ke kelas_id jika ada
            // Sesuaikan dengan nama kelas di database Bapak
            $kelasMap = [
                'Permata Botanical'       => null,
                'Permata Optima'          => null,
                'Permata Orchard'         => null,
                'Permata Orchard Residence' => null,
            ];

            // Cari kelas_id berdasarkan nama produk
            $kelasId = null;
            if ($jenisProduk) {
                $kelas = DB::table('kelas')
                    ->where('nama_kelas', 'LIKE', '%' . $jenisProduk . '%')
                    ->first();
                $kelasId = $kelas ? $kelas->id : null;
            }

            // Simpan ke tabel data
            // leads ENUM yang valid: Iklan, Referal, Marketing, Mandiri, Pameran, Sosmed, Canvasing
            $leadsSource = 'Mandiri'; // Google Form = calon pelanggan datang sendiri
            
            $dataId = DB::table('data')->insertGetId([
                'nama'           => $nama,
                'no_wa'          => $noWa,
                'leads'          => $leadsSource,
                'leads_custom'   => 'Google Form - ' . $source,
                'status_peserta' => 'peserta_baru',
                'kelas_id'       => $kelasId,
                'status'         => 'Cold',
                'created_by'     => 'Google Form',
                'created_by_role'=> 'api',
                'spin_b'         => 'Tidak',
                'spin_a'         => 'Tidak',
                'spin_t'         => 'Tidak',
                'survei_lokasi'  => 'Tidak',
                'spin1_wa'       => 0,
                'spin1_telp'     => 0,
                'spin2_wa'       => 0,
                'spin2_telp'     => 0,
                'spin3_wa'       => 0,
                'spin3_telp'     => 0,
                'spin4_wa'       => 0,
                'spin4_telp'     => 0,
                'spin5_wa'       => 0,
                'spin5_telp'     => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            Log::info("Google Form lead masuk: nama={$nama}, wa={$noWa}, produk={$jenisProduk}, id={$dataId}");

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan ke Database Calon Pelanggan.',
                'id'      => $dataId,
                'data'    => [
                    'nama'        => $nama,
                    'no_wa'       => $noWa,
                    'jenis_produk'=> $jenisProduk,
                ]
            ], 201);

        } catch (\Throwable $e) {
            Log::error("Google Form API Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
