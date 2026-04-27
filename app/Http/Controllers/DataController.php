<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas; // Ensure you import the Kelas model
use App\Models\Data;
use App\Models\Alumni; // Ensure you import the Alumni model
use App\Models\SalesPlan; // Ensure you import the Salesplan model
use App\Models\SpinInteraction;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\Imports\DataImport;

class DataController extends Controller
{
    public function createDraft()
    {
        try {
            $user = Auth::user();
            $newData = new Data();
            $newData->nama = '';
            $newData->status_peserta = 'peserta_baru';
            $newData->created_by = $user->name;
            $newData->created_by_role = $user->role;
            $newData->save();

            $kelas = Kelas::select('id', 'nama_kelas')->orderBy('nama_kelas')->get();
            // Gunakan view partial yang sama dengan loop utama untuk konsistensi
            $html = view('admin.database.partials.row', [
                'item' => $newData,
                'loop' => (object) ['iteration' => 'New'], // Placeholder iteration
                'kelas' => $kelas
            ])->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $userRole = strtolower($user->role);

        // --- Admin MBC Khusus ---
        $adminMbcIds = [2, 3, 6, 10, 4, 12];
        $allowedCsNames = ['Linda', 'Yasmin', 'Shafa', 'Arifa', 'Tursia', 'Latifah'];

        // --- Ambil daftar CS sesuai role ---
        $csQuery = \App\Models\User::query();

        if (in_array($userId, $adminMbcIds)) {
            // Admin MBC hanya bisa lihat CS tertentu
            $csQuery->whereIn('name', $allowedCsNames);
        } elseif ($userRole === 'manager') {
            // Manager hanya boleh lihat Latifah & Tursia
            $csQuery->whereIn('name', ['Latifah', 'Tursia']);
        } elseif ($userRole === 'administrator' || $user->name === 'Agus Setyo' || $user->name === 'Linda') {
            // Administrator & Agus Setyo & Linda boleh lihat semua CS
            $csQuery->whereIn('role', ['cs', 'CS', 'customer_service', 'cs-mbc', 'cs-smi']);
        } else {
            // CS biasa hanya bisa lihat dirinya sendiri
            $csQuery->where('id', $userId);
        }

        $csList = $csQuery->select('id', 'name')->orderBy('name')->get();

        // --- Ambil filter ---
        $kelasFilter = $request->input('kelas');
        $csFilter = $request->input('cs_name');
        $bulanFilter = $request->input('bulan');
        $tahunFilter = $request->input('tahun'); // Tambah filter tahun
        $searchFilter = $request->input('search');
        $perPage = $request->get('per_page', 100);

        // --- Query utama ---
        $sortByParam = $request->input('sort_by', 'created_at');
        $sortOrderParam = $request->input('order', 'desc');

        // Whitelist columns
        $allowedSorts = ['created_at', 'created_by', 'nama', 'status_peserta'];
        if (!in_array($sortByParam, $allowedSorts)) {
            $sortByParam = 'created_at';
        }

        $query = \App\Models\Data::with('salesplan')->whereIn('status_peserta', ['peserta_baru', 'sales_plan']);
        $this->applyFilters($request, $query);

        $sortByParam = $request->input('sort_by', 'created_at');
        $sortOrderParam = $request->input('order', 'desc');
        $allowedSorts = ['created_at', 'created_by', 'nama', 'status_peserta'];
        if (in_array($sortByParam, $allowedSorts)) {
            $query->orderBy($sortByParam, $sortOrderParam);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // --- Stats Calculation for Dashboard Headers ---
        // KPI Query: Targets ALL data input (ignoring status_peserta) to reflect Acquisition Performance
        $kpiQuery = \App\Models\Data::query();
        $this->applyFilters($request, $kpiQuery);


        $now = \Carbon\Carbon::now();
        $statsYear = $tahunFilter ? $tahunFilter : $now->year;
        $statsMonth = $bulanFilter ? $bulanFilter : $now->month;

        $bulanLabel = \Carbon\Carbon::createFromDate($statsYear, $statsMonth, 1)->isoFormat('MMMM YYYY');

        // Total Database: Count of current table (Queue Size)
        // We use the original $query which has 'status' & 'time' & 'search' filters applied.
        $totalDatabase = (clone $query)->count();

        // Database Baru: Performance Metric (Count of ALL inputs in period)
        $databaseBaru = $kpiQuery
            ->whereYear('created_at', $statsYear)
            ->whereMonth('created_at', $statsMonth)
            ->count();

        $target = 100;
        $kurang = max($target - $databaseBaru, 0);

        $data = $query->paginate($perPage);
        $kelas = \App\Models\Kelas::select('id', 'nama_kelas')->orderBy('nama_kelas')->get();

        // Fetch lists for filters
        $provinsiList = \App\Models\Data::select('provinsi_nama')
            ->whereNotNull('provinsi_nama')
            ->where('provinsi_nama', '!=', '')
            ->distinct()
            ->orderBy('provinsi_nama')
            ->pluck('provinsi_nama');

        $kotaQuery = \App\Models\Data::select('kota_nama')
            ->whereNotNull('kota_nama')
            ->where('kota_nama', '!=', '')
            ->distinct()
            ->orderBy('kota_nama');

        if (!empty($provinsiFilter)) {
            $kotaQuery->where('provinsi_nama', $provinsiFilter);
        }

        $kotaList = $kotaQuery->pluck('kota_nama');

        return view('admin.database.database', [
            'data' => $data,
            'kelas' => $kelas,
            'csList' => $csList,
            'provinsiList' => $provinsiList,
            'kotaList' => $kotaList,
            'databaseBaru' => $databaseBaru,
            'totalDatabase' => $totalDatabase,
            'target' => $target,
            'kurang' => $kurang,
            'bulanLabel' => $bulanLabel,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Return a view to create a new resource
        return view('admin.database.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateInline(Request $request)
    {
        $data = Data::findOrFail($request->id);
        $field = $request->field;
        $value = $request->value;
        $data->$field = $value;

        // Auto update spin_updated_at if SPIN/BAT fields change
        if (in_array($field, ['spin', 'spin_b', 'spin_a', 'spin_t'])) {
            $data->spin_updated_at = now();
        }

        $data->save();

        if ($field === 'nama') {
            SalesPlan::where('data_id', $data->id)->update(['nama' => $value]);
        }

        // Sinkronisasi status dan nominal ke SalesPlan (Prospek)
        if (in_array($field, ['status', 'nominal'])) {
            $v = $value;
            if ($field === 'status') {
                $m = ['Tunai' => 'sudah_transfer', 'KPR' => 'mau_transfer'];
                $v = $m[$value] ?? strtolower($value);
            }
            SalesPlan::where('data_id', $data->id)->update([$field => $v]);
        }

        return response()->json(['success' => true]);
    }

    public function updateLocation(Request $request)
    {
        $data = Data::findOrFail($request->id);

        if ($request->has('provinsi_id')) {
            $data->provinsi_id = $request->provinsi_id;
            $data->provinsi_nama = $request->provinsi_nama;
            // Reset kota jika provinsi berubah
            $data->kota_id = null;
            $data->kota_nama = null;
        }

        if ($request->has('kota_id')) {
            $data->kota_id = $request->kota_id;
            $data->kota_nama = $request->kota_nama;
        }

        $data->save();

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $data = new Data();
        $data->nama = $request->input('nama');
        $data->status_peserta = $request->input('status_peserta', 'peserta_baru');
        // Enum field
        $data->leads = $request->input('leads'); // Assuming 'leads' is an enum field
        // Custom field
        if ($request->input('leads_custom') === null) {
            $data->leads_custom = ''; // Set to empty string if null
        } else {
            $data->leads_custom = $request->input('leads_custom');
        }
        $data->provinsi_id = $request->input('provinsi_id');
        $data->provinsi_nama = $request->input('provinsi_nama');
        $data->kota_id = $request->input('kota_id');
        $data->kota_nama = $request->input('kota_nama');
        $data->jenisbisnis = $request->input('jenisbisnis') ?? $request->input('jenis_bisnis') ?? '';
        $data->nama_bisnis = $request->input('nama_bisnis') ?? '';
        $data->no_wa = $request->input('no_wa');
        $data->situasi_bisnis = $request->input('situasi_bisnis');
        $data->kendala = $request->input('kendala');

        // Role
        $data->created_by = Auth::user()->name;
        $data->created_by_role = Auth::user()->role;
        $data->save();
        return redirect()->route('admin.database.database')->with('success', 'Data has been added successfully.');
    }

    public function updatePotensi(Request $request, $id)
    {
        $data = data::findOrFail($id);
        $data->kelas_id = $request->kelas_id;
        $data->save();

        return response()->json(['success' => true]);
    }

    public function updateSumberLeads(Request $request, $id)
    {
        $data = data::findOrFail($id);
        $data->leads = $request->leads;
        $data->save();

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        // Fetch the data by ID
        $data = data::findOrFail($id);
        $kelas = Kelas::all(); // Fetch all classes for the sidebar
        // Return a view to show the data
        return view('admin.database.show', compact('data', 'kelas'));
    }

    public function edit($id)
    {
        // Fetch the data by ID
        $data = data::findOrFail($id);

        $kelas = Kelas::all(); // Fetch all classes for the sidebar
        // Return a view to edit the data
        return view('admin.database.edit', compact('data', 'kelas'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $data = data::findOrFail($id);
        $data->nama = $request->input('nama');
        $data->status_peserta = $request->input('status_peserta', 'Peserta Baru');
        // Enum field
        $data->leads = $request->input('leads'); // Assuming 'leads' is an enum field
        // Custom field
        if ($request->input('leads_custom') === null) {
            $data->leads_custom = ''; // Set to empty string if null
        } else {
            $data->leads_custom = $request->input('leads_custom');
        }
        $data->provinsi_id = $request->input('provinsi_id');

        $data->kota_nama = $request->input('kota_nama');
        $data->jenisbisnis = $request->input('jenisbisnis') ?? $request->input('jenis_bisnis') ?? '';
        $data->nama_bisnis = $request->input('nama_bisnis') ?? '';
        $data->no_wa = $request->input('no_wa');
        $data->situasi_bisnis = $request->input('situasi_bisnis');
        $data->kendala = $request->input('kendala');

        $data->save();

        // 🔥 SYNC NAMA TO SALES PLAN
        SalesPlan::where('data_id', $data->id)->update(['nama' => $data->nama]);

        // Redirect to the index page with a success message
        return redirect()->route('admin.database.database')->with('success', 'Data has been updated successfully.');
    }

    public function destroy($id)
    {
        // Fetch the data by ID
        $data = Data::findOrFail($id);
        // Delete the data
        $data->delete();
        // Redirect to the index page with a success message
        return redirect()->route('admin.database.database')->with('success', 'Data has been deleted successfully.');
    }

    public function peserta_baru()
    {
        if (Auth::user()->email === 'mbchamasah@gmail.com') {
            $data = data::where('status_peserta', 'peserta_baru')->get();
        } else {
            $data = data::where('status_peserta', 'peserta_baru')
                ->where('created_by', Auth::user()->name)
                ->get();
        }
        return view('admin.database.database', compact('data'));
    }

    public function alumni()
    {
        if (Auth::user()->email === 'mbchamasah@gmail.com') {
            $data = data::where('status_peserta', 'alumni')->get();
        } else {
            $data = data::where('status_peserta', 'alumni')
                ->where('created_by', Auth::user()->name)
                ->get();
        }
        return view('admin.database.database', compact('data'));
    }

    private function filterKelasByUser($user)
    {
        // Jika Administrator atau Fitra Jaya Saleh: tampil semua
        if (strtolower($user->role) == 'administrator' || $user->name == 'Fitra Jaya Saleh') {
            return Kelas::all();
        }

        // Jika Tursia atau Latifah â†’ hanya Start-Up Muda Indonesia
        if (in_array($user->name, ['Tursia', 'Latifah'])) {
            return Kelas::where('nama_kelas', 'Start-Up Muda Indonesia')->get();
        }

        // Jika Mutiah â†’ hanya Sekolah Kaya
        if ($user->name == 'Mutiah') {
            return Kelas::where('nama_kelas', 'Sekolah Kaya')->get();
        }

        // Jika Shafa â†’ semua kecuali Start-Up Muda Indonesia
        if ($user->name == 'Shafa') {
            return Kelas::where('nama_kelas', '!=', 'Start-Up Muda Indonesia')->get();
        }

        // Selain itu â†’ semua kecuali Sekolah Kaya dan Start-Up Muda Indonesia
        return Kelas::whereNotIn('nama_kelas', ['Sekolah Kaya', 'Start-Up Muda Indonesia'])->get();
    }

    public function pindahkesalesplan(Request $request, $id)
    {
        $data = Data::findOrFail($id);
        $kelasIds = $request->input('kelas_ids', []);

        if (empty($kelasIds)) {
            return redirect()->back()->with('error', 'Silakan pilih minimal satu produk.');
        }

        foreach ($kelasIds as $kelasId) {
            // Cek apakah sudah ada di Sales Plan untuk produk ini agar tidak duplikat
            $exists = SalesPlan::where('data_id', $data->id)
                ->where('kelas_id', $kelasId)
                ->exists();

            if (!$exists) {
                $salesPlan = new SalesPlan();
                $salesPlan->nama = $data->nama;
                $salesPlan->situasi_bisnis = $data->situasi_bisnis;
                $salesPlan->kendala = $data->kendala;
                $salesPlan->kelas_id = $kelasId;
                $salesPlan->data_id = $data->id;
                $salesPlan->created_by = auth()->id();
                $salesPlan->status = 'cold';
                $salesPlan->save();
            }
        }

        // Update status_peserta agar hilang dari view Database
        $data->status_peserta = 'sales_plan';
        $data->save();

        return redirect()->route('admin.salesplan.index')->with('success', 'Peserta berhasil dipindahkan ke Sales Plan.');
    }

    public function getStatistik(Request $request)
    {
        $user = Auth::user();
        $userRole = strtolower($user->role);
        $filterUser = $request->input('user');

        $query = Data::query();

        // Admin & Manager Logic
        if (in_array($userRole, ['administrator', 'manager']) || $user->name === 'Agus Setyo') {
            if (!empty($filterUser)) {
                $query->where('created_by', $filterUser);
            }
        } else {
            // CS Biasa
            $query->where('created_by', $user->name);
        }

        // Agus Setyo Filter
        if ($user->name === 'Agus Setyo') {
            $query->whereHas('kelas', function ($q) {
                $q->where('nama_kelas', 'Start-Up Muda Indonesia')
                    ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
            });
        }

        // Calculate Stats
        $now = \Carbon\Carbon::now();
        $bulanLabel = $now->isoFormat('MMMM YYYY');

        $databaseBaru = (clone $query)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();

        $totalDatabase = $query->count();
        $target = 100;
        $kurang = max($target - $databaseBaru, 0);

        return response()->json([
            'bulanLabel' => $bulanLabel,
            'databaseBaru' => $databaseBaru,
            'totalDatabase' => $totalDatabase,
            'target' => $target,
            'kurang' => $kurang
        ]);
    }

    public function getSpinInteractions($id)
    {
        $data = Data::with('spinInteractions')->findOrFail($id);
        return response()->json([
            'success' => true,
            'interactions' => $data->spinInteractions
        ]);
    }

    public function saveSpinInteractions(Request $request, $id)
    {
        $data = Data::findOrFail($id);
        $interactions = $request->input('interactions', []);

        $collectedIds = [];
        foreach ($interactions as $index => $item) {
            $spin = $data->spinInteractions()->updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'spin_number' => $index + 1,
                    'wa' => (isset($item['wa']) && $item['wa']) ? 1 : 0,
                    'telp' => (isset($item['telp']) && $item['telp']) ? 1 : 0,
                    'hasil_fu' => $item['hasil_fu'] ?? '',
                    'tindak_lanjut' => $item['tindak_lanjut'] ?? '',
                ]
            );
            $collectedIds[] = $spin->id;
        }

        return response()->json(['success' => true]);
    }

    public function cetakInteraksiPdf($id)
    {
        $data = Data::with('spinInteractions')->findOrFail($id);
        $pdf = \PDF::loadView('admin.database.pdf-rekap', compact('data'));
        return $pdf->stream('Rekap-Interaksi-' . $data->nama . '.pdf');
    }

    public function cetakInteraksiPdfAll(Request $request)
    {
        $user = Auth::user();
        $query = Data::with('spinInteractions')->whereIn('status_peserta', ['peserta_baru', 'sales_plan']);
        $this->applyFilters($request, $query);
        
        $data = $query->orderBy('created_at', 'desc')->get();
        
        $bulanFilter = $request->input('bulan');
        $tahunFilter = $request->input('tahun');
        $periode = 'Semua Periode';
        if ($bulanFilter && $tahunFilter) {
            $periode = \Carbon\Carbon::create()->month($bulanFilter)->year($tahunFilter)->translatedFormat('F Y');
        } elseif ($tahunFilter) {
            $periode = $tahunFilter;
        }

        $pdf = \PDF::loadView('admin.database.pdf-rekap-bulanan', [
            'data' => $data,
            'periode' => $periode,
            'csName' => $request->input('cs_name') ?: $user->name
        ])->setPaper([0, 0, 609.4488, 935.433], 'landscape'); // F4 Landscape
        
        return $pdf->stream('Rekap-Interaksi-' . $periode . '.pdf');
    }

    public function pindahKeAlumni(Request $request, $id)
    {
        try {
            $data = Data::findOrFail($id);
            
            // Ambil status yang dipilih user (KPR atau Tunai)
            $statusPilihan = $request->input('status', $data->status ?? 'Tunai');
            
            // Cek apakah sudah pernah dipindahkan dengan PRODUK YANG SAMA
            $duplicate = \DB::table('salesplans')
                ->where('data_id', $data->id)
                ->where('kelas_id', $data->kelas_id)
                ->whereNull('deleted_at')
                ->first();

            if ($duplicate) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Data ini sudah pernah dipindahkan ke Data Pelanggan untuk produk/kelas tersebut.'
                ], 422);
            }
            
            // Map status ke salesplan status
            // KPR → mau_transfer, Tunai → sudah_transfer
            $salesplanStatus = (strtolower($statusPilihan) === 'kpr') ? 'mau_transfer' : 'sudah_transfer';
            $nominal = (strtolower($statusPilihan) === 'kpr') ? 0 : ($data->nominal ?: 0);

            // Karena kita sudah memvalidasi duplikasi (data_id + kelas_id) di atas,
            // maka jika sampai ke sini, berarti ini adalah produk BARU untuk pelanggan ini.
            // Langsung insert sebagai record baru agar pelanggan yang sama bisa punya banyak produk.
            \DB::table('salesplans')->insert([
                'data_id'    => $data->id,
                'nama'       => $data->nama ?: '-',
                'kelas_id'   => $data->kelas_id,
                'status'     => $salesplanStatus,
                'nominal'    => $nominal,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // TIDAK menghapus dari Database Calon — data tetap ada
            // Hanya update status_peserta ke 'sales_plan' agar bisa ditracking
            // tapi TIDAK dihilangkan dari view karena user ingin data tetap ada
            // (Jangan update status_peserta)

            $redirectUrl = route('admin.pembeli.index');

            return response()->json([
                'success'      => true,
                'redirect_url' => $redirectUrl,
            ]);
        } catch (\Throwable $e) {
            \Log::error("Error pindahKeAlumni: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function applyFilters(Request $request, $query)
    {
        $user = Auth::user();
        $userRole = strtolower($user->role);
        
        $kelasFilter = $request->input('kelas');
        $csFilter = $request->input('cs_name');
        $bulanFilter = $request->input('bulan');
        $tahunFilter = $request->input('tahun'); 
        $searchFilter = $request->input('search');
        $sumberFilter = $request->input('sumber');
        $kotaFilter = $request->input('kota');
        $provinsiFilter = $request->input('provinsi');
        $surveiFilter = $request->input('survei');
        $spinFilter = $request->input('spin');
        $zoomFilter = $request->input('zoom');
        $view = $request->input('view');

        if (!empty($searchFilter)) {
            $query->where(function ($q) use ($searchFilter) {
                $q->where('nama', 'LIKE', '%' . $searchFilter . '%')
                    ->orWhere('leads', 'LIKE', '%' . $searchFilter . '%')
                    ->orWhere('nama_bisnis', 'LIKE', '%' . $searchFilter . '%')
                    ->orWhere('no_wa', 'LIKE', '%' . $searchFilter . '%');
            });
        }

        if ($userRole === 'manager') {
            $query->whereIn('created_by', ['Latifah', 'Tursia']);
        }

        if (!empty($csFilter)) {
            $query->where('created_by', $csFilter);
        }

        if (!empty($kelasFilter)) {
            $query->where('kelas_id', $kelasFilter);
        }

        if (!empty($bulanFilter)) {
            $query->whereMonth('created_at', $bulanFilter);
        }

        if (!empty($tahunFilter)) {
            $query->whereYear('created_at', $tahunFilter);
        }

        if (!empty($sumberFilter)) {
            $query->where('leads', $sumberFilter);
        }

        if (!empty($kotaFilter)) {
            $query->where('kota_nama', $kotaFilter);
        }

        if (!empty($provinsiFilter)) {
            $query->where('provinsi_nama', $provinsiFilter);
        }

        if ($surveiFilter !== null && $surveiFilter !== '') {
            $query->where('survei_lokasi', $surveiFilter);
        }

        if ($spinFilter !== null && $spinFilter !== '') {
            $query->where('spin', $spinFilter);
        }

        if ($zoomFilter !== null && $zoomFilter !== '') {
            $query->where('ikut_zoom', $zoomFilter);
        }

        $forceMyData = $view === 'me';
        if (($user->name === 'Linda' && $forceMyData) || (!in_array($userRole, ['administrator', 'manager']) && $user->name !== 'Agus Setyo' && $user->name !== 'Linda')) {
            $query->where('created_by', $user->name);
        }

        if ($user->name === 'Agus Setyo') {
            $query->whereHas('kelas', function ($q) {
                $q->where('nama_kelas', 'Start-Up Muda Indonesia')
                    ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
            });
        }
        
        return $query;
    }
}
