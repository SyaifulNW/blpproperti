<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas; // Ensure you import the Kelas model
use App\Models\Data;
use App\Models\Alumni; // Ensure you import the Alumni model
use App\Models\SalesPlan; // Ensure you import the Salesplan model

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
            $newData->nama = '(Edit Nama)';
            $newData->status_peserta = 'peserta_baru';
            $newData->created_by = $user->name;
            $newData->created_by_role = $user->role;
            $newData->save();

            $kelas = Kelas::select('id', 'nama_kelas')->orderBy('nama_kelas')->get();
            // Gunakan view partial yang sama dengan loop utama untuk konsistensi
            $html = view('admin.database.partials.row', [
                'item' => $newData,
                'loop' => (object)['iteration' => 'New'], // Placeholder iteration
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
    $csFilter    = $request->input('cs_name');
    $bulanFilter = $request->input('bulan');
    $tahunFilter = $request->input('tahun'); // Tambah filter tahun
    $searchFilter = $request->input('search');
    $perPage     = $request->get('per_page', 100);

    // --- Query utama ---
    $sortByParam = $request->input('sort_by', 'created_at');
    $sortOrderParam = $request->input('order', 'desc');
    
    // Whitelist columns
    $allowedSorts = ['created_at', 'created_by', 'nama', 'status_peserta']; 
    if (!in_array($sortByParam, $allowedSorts)) {
        $sortByParam = 'created_at';
    }
    
    // Base Query: Only show "Calon Peserta" (Peserta Baru)
    $query = \App\Models\Data::where('status_peserta', 'peserta_baru');

    // Filter Search
    if (!empty($searchFilter)) {
        $query->where(function($q) use ($searchFilter) {
             $q->where('nama', 'LIKE', '%'.$searchFilter.'%')
               ->orWhere('leads', 'LIKE', '%'.$searchFilter.'%')
               ->orWhere('nama_bisnis', 'LIKE', '%'.$searchFilter.'%')
               ->orWhere('no_wa', 'LIKE', '%'.$searchFilter.'%');
        });
    }

    $query->orderBy($sortByParam, $sortOrderParam); // Order By must be after search conditions if any

    // Jika admin MBC → hanya 6 CS tertentu (DISABLED/ADJUSTED: User reported CS seeing shared data is undesirable)
    // if (in_array($userId, $adminMbcIds)) {
    //     $query->whereIn('created_by', $allowedCsNames);
    // }

    // Manager → hanya bisa lihat data Latifah & Tursia
    if ($userRole === 'manager') {
        $query->whereIn('created_by', ['Latifah', 'Tursia']);
    }

    // Filter User
    if (!empty($csFilter)) {
        $query->where('created_by', $csFilter);
    }

    // Filter kelas & bulan & tahun
    if (!empty($kelasFilter)) {
        $query->where('kelas_id', $kelasFilter);
    }

    if (!empty($bulanFilter)) {
        $query->whereMonth('created_at', $bulanFilter);
    }

    if (!empty($tahunFilter)) {
        $query->whereYear('created_at', $tahunFilter);
    }

    // New Filters (Server Side)
    $sumberFilter = $request->input('sumber');
    $kotaFilter = $request->input('kota');
    $provinsiFilter = $request->input('provinsi');

    if (!empty($sumberFilter)) {
        $query->where('leads', $sumberFilter);
    }

    if (!empty($kotaFilter)) {
        // Kota is stored as 'kota_nama' or linked via ID. Checking view logic, it seems to be 'kota_nama'.
        // Let's verify view usage. In row.blade.php it uses $item->kota_nama.
        $query->where('kota_nama', $kotaFilter);
    }


    if (!empty($provinsiFilter)) {
        $query->where('provinsi_nama', $provinsiFilter);
    }

    // Filter Spin
    $spinFilter = $request->input('spin');
    if ($spinFilter !== null && $spinFilter !== '') {
        $query->where('berhasil_spin', $spinFilter);
    }

    // Filter Zoom
    $zoomFilter = $request->input('zoom');
    if ($zoomFilter !== null && $zoomFilter !== '') {
        $query->where('ikut_zoom', $zoomFilter);
    }


    // CS biasa → hanya datanya sendiri
    // REMOVED !in_array($userId, $adminMbcIds) check so they fall into this logic
    $forceMyData = $request->input('view') === 'me';
    if (($user->name === 'Linda' && $forceMyData) || (!in_array($userRole, ['administrator', 'manager']) && $user->name !== 'Agus Setyo' && $user->name !== 'Linda')) {
        $query->where('created_by', $user->name);
    }

    // Khusus Agus Setyo: Hanya kelas Start-Up Muslim/Muda Indonesia
    if ($user->name === 'Agus Setyo') {
        $query->whereHas('kelas', function($q) {
             $q->where('nama_kelas', 'Start-Up Muda Indonesia')
               ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
        });
    }

    // --- Stats Calculation for Dashboard Headers ---
    // KPI Query: Targets ALL data input (ignoring status_peserta) to reflect Acquisition Performance
    $kpiQuery = \App\Models\Data::query();
    
    // Re-apply Permission/Ownership Logic to KPI Query
    // Manager
    if ($userRole === 'manager') {
        $kpiQuery->whereIn('created_by', ['Latifah', 'Tursia']);
    }
    // Filter User (Dropdown)
    if (!empty($csFilter)) {
        $kpiQuery->where('created_by', $csFilter);
    }
    // Strict CS View
    // Strict CS View
    if (($user->name === 'Linda' && $forceMyData) || (!in_array($userRole, ['administrator', 'manager']) && $user->name !== 'Agus Setyo' && $user->name !== 'Linda')) {
        $kpiQuery->where('created_by', $user->name);
    }
    // Agus Setyo
    if ($user->name === 'Agus Setyo') {
        $kpiQuery->whereHas('kelas', function($q) {
             $q->where('nama_kelas', 'Start-Up Muda Indonesia')
               ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
        });
    }

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
        $data->$field = $request->value;
        $data->save();

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
        $data->status_peserta = $request->input('status_peserta','peserta_baru');
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
        $data->jenisbisnis = $request->input('jenisbisnis');
        $data->nama_bisnis = $request->input('nama_bisnis');
        $data->no_wa = $request->input('no_wa');
        $data->situasi_bisnis = $request->input('situasi_bisnis');
        $data->kendala = $request->input('kendala');

        // Ya atau tidak
        // Enum Peserta Baru


        // Role
        $data->created_by = Auth::user()->name;
        $data->created_by_role = Auth::user()->role;
        $data->save();
        return redirect()->route('admin.database.database')->with('success', 'Data has been added successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the data by ID
        $data = data::findOrFail($id);

        $kelas = Kelas::all(); // Fetch all classes for the sidebar
        // Return a view to edit the data
        return view('admin.database.edit', compact('data', 'kelas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
        $data->jenisbisnis = $request->input('jenisbisnis');
        $data->nama_bisnis = $request->input('nama_bisnis');
        $data->no_wa = $request->input('no_wa');
        $data->situasi_bisnis = $request->input('situasi_bisnis');
        $data->kendala = $request->input('kendala');

        // Ya atau tidak

        $data->save();



        // Redirect to the index page with a success message
        return redirect()->route('admin.database.database')->with('success', 'Data has been updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Fetch the data by ID
        $data = Data::findOrFail($id);
        // Delete the data
        $data->delete();
        // Redirect to the index page with a success message
        return redirect()->route('admin.database.database')->with('success', 'Data has been deleted successfully.');
    }


    // app/Http/Controllers/DatabaseController.php

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

    public function pindahkesalesplan($id)
    {
        // Ambil data peserta dari tabel data
        $data = Data::findOrFail($id);
        $salesPlan = new SalesPlan();
        $salesPlan->nama = $data->nama;          // dari tabel peserta
        $salesPlan->situasi_bisnis      = $data->situasi_bisnis; // dari tabel peserta
        $salesPlan->kendala      = $data->kendala;       // dari tabel peserta
        $salesPlan->kelas_id     = $data->kelas_id;
       $salesPlan->data_id      = $data->id; // Link ke data asli 
        $salesPlan->created_by   = auth()->id();
        $salesPlan->status       = 'cold'; // default awal

        // Kolom tambahan biarkan kosong dulu, admin yang isi nant
        $salesPlan->save();


        // Kalau mau pindahkan (hapus dari tabel data) bisa tambahkan:
        // $data->delete();

          return redirect()->back()
            ->with('success', 'Peserta berhasil dipindahkan ke Sales Plan.');
        
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
            $query->whereHas('kelas', function($q) {
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
}
