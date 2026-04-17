<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Data;
use Rap2hpoutre\FastExcel\FastExcel;

class SalesPlanController extends Controller
{
    public function index(Request $request)
    {
        $orphans = \App\Models\SalesPlan::whereNull('data_id')->get();
        foreach ($orphans as $sp) {
            $data = \App\Models\Data::where('nama', $sp->nama)->first();
            if ($data) {
                $sp->update(['data_id' => $data->id]);
            }
        }
        $kelasFilter = $request->input('kelas');
        $csFilter = $request->input('created_by');

        $restrictedView = $request->input('restricted_view', false);
        // if (auth()->user()->name == 'Linda' && !empty($kelasFilter)) {
        //     $restrictedView = true;
        // }




        // ======================================
        // 🔥 AUTO UPDATE STATUS
        // Jika status 'tertarik' sudah > 5 hari tidak berubah -> ubah jadi 'no'
        // ======================================
        $cutoffDate = now()->subDays(5);
        $updatedCount = SalesPlan::where('status', 'tertarik')
            ->where('updated_at', '<', $cutoffDate)
            ->update(['status' => 'cold']);

        if ($updatedCount > 0) {
            $dateString = $cutoffDate->format('d M Y H:i');
            session()->flash('warning', "$updatedCount data peserta dengan status 'Tertarik' telah otomatis diubah menjadi 'Cold' (Tidak ada update sejak $dateString).");
        }

        $statusFilter = $request->input('status');
        $bulanFilter = $request->input('bulan');
        $tahunFilter = $request->input('tahun', date('Y'));
        if ($request->has('tahun') && $request->input('tahun') == '') {
            $tahunFilter = null;
        }

        $userId = auth()->id();
        $perPage = $request->get('per_page', 100);

        // Dropdown data
        $kelasList = Kelas::all();
        $csList = User::orderBy('name', 'asc')->get();

        // Filter CS List for Admin Dropdown (Specific Request)
        if (auth()->user()->role === 'administrator' || auth()->user()->role === 'manager') {
            $csList = User::where('role', 'sales')
                ->orWhereIn('name', ['Yasmin', 'Linda', 'Arifa', 'Putri', 'Puput', 'Gunawan'])
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $csList = User::orderBy('name', 'asc')->get();
        }


        // =====================================================
        // 🔥 UNIFIED VIEW BY DEFAULT
        // =====================================================
        $isAdmin = in_array($userId, [1]);


        // ======================================
        // 🔥 QUERY UTAMA SALESPLAN
        // ======================================




        // Determine exempt users (who can see all data)
        $exemptUsers = ['Agus Setyo', 'Fitra Jaya Saleh', 'Linda'];
        // Linda is exempt only if NOT in restricted view
        // if (auth()->user()->name == 'Linda' && !$restrictedView) {
        //     $exemptUsers[] = 'Linda';
        // }

        // Clone query logic untuk statistik agar menyertakan semua data (tidak terpotong pagination)
        $statsQuery = SalesPlan::query()
            ->when($kelasFilter, function ($query) use ($kelasFilter) {
                $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                    $sub->where('nama_kelas', $kelasFilter);
                });
            })
            ->when($csFilter, function ($query) use ($csFilter) {
                $query->where('created_by', $csFilter);
            })
            ->when($statusFilter, function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->when($bulanFilter, function ($query) use ($bulanFilter) {
                $query->whereMonth('updated_at', $bulanFilter);
            })
            ->when($tahunFilter, function ($query) use ($tahunFilter) {
                $query->whereYear('updated_at', $tahunFilter);
            })
            ->when(!$isAdmin && !in_array(auth()->user()->name, $exemptUsers), function ($query) use ($userId) {
                $query->where('created_by', $userId);
            });

        // Ambil data stats: group by CS, sum nominal
        $salesplanStats = $statsQuery->selectRaw('created_by, SUM(nominal) as total_nominal')
            ->groupBy('created_by')
            ->pluck('total_nominal', 'created_by');

        $salesplans = SalesPlan::with(['kelas', 'data'])
            ->when($kelasFilter, function ($query) use ($kelasFilter) {
                $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                    $sub->where('nama_kelas', $kelasFilter);
                });
            })
            ->when(!$kelasFilter, function ($query) {
                // Jika "Semua Produk", gabungkan baris berdasarkan data_id
                $aggregated = \DB::table('salesplans')
                    ->join('kelas', 'salesplans.kelas_id', '=', 'kelas.id')
                    ->select('data_id', 
                        \DB::raw('SUM(nominal) as total_nominal_aggregated'),
                        \DB::raw('GROUP_CONCAT(kelas.nama_kelas SEPARATOR ", ") as all_kelas_names')
                    )
                    ->whereNull('salesplans.deleted_at')
                    ->groupBy('data_id');
                
                $latestRows = \DB::table('salesplans')
                    ->select(\DB::raw('MAX(id) as latest_id'))
                    ->whereNull('deleted_at')
                    ->groupBy('data_id');

                $query->joinSub($latestRows, 'latest', 'salesplans.id', '=', 'latest.latest_id')
                      ->joinSub($aggregated, 'agg', 'salesplans.data_id', '=', 'agg.data_id')
                      ->select('salesplans.*', 'agg.total_nominal_aggregated', 'agg.all_kelas_names');
            })
            ->when($csFilter, function ($query) use ($csFilter) {
                $query->where('salesplans.created_by', $csFilter);
            })
            ->when($statusFilter, function ($query) use ($statusFilter) {
                $query->where('salesplans.status', $statusFilter);
            })
            ->when($bulanFilter, function ($query) use ($bulanFilter) {
                $query->whereMonth('salesplans.updated_at', $bulanFilter);
            })
            ->when($tahunFilter, function ($query) use ($tahunFilter) {
                $query->whereYear('salesplans.updated_at', $tahunFilter);
            })
            ->when(!$isAdmin && !in_array(auth()->user()->name, $exemptUsers), function ($query) use ($userId) {
                $query->where('salesplans.created_by', $userId);
            })
            ->orderBy('salesplans.created_at', 'desc')
            ->paginate($perPage);


        // ======================================
        // 🔥 PESERTA TRANSFER
        // ======================================
        $pesertaTransfer = SalesPlan::whereIn('status', ['sudah_transfer', 'mau_transfer'])
            ->with(['data', 'kelas', 'kpr'])

            ->when($kelasFilter, function ($query) use ($kelasFilter) {
                $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                    $sub->where('nama_kelas', $kelasFilter);
                });
            })

            ->when($csFilter, function ($query) use ($csFilter) {
                $query->where('created_by', $csFilter);
            })

            ->when(!$isAdmin && !in_array(auth()->user()->name, $exemptUsers), function ($query) use ($userId) {
                $query->where('created_by', $userId);
            })

            ->when($bulanFilter, function ($query) use ($bulanFilter) {
                $query->whereMonth('updated_at', $bulanFilter);
            })
            ->when($tahunFilter, function ($query) use ($tahunFilter) {
                $query->whereYear('updated_at', $tahunFilter);
            })
            ->paginate(100);


        $salesplansByCS = collect($salesplans->items())->groupBy('created_by');

        // Fallback: Ambil data berdasarkan nama jika data_id null (untuk data lama)
        $names = collect($salesplans->items())->pluck('nama')->filter()->toArray();
        $dataMap = Data::whereIn('nama', $names)->get()->keyBy('nama');


        // ======================================
        // 🔥 CALCULATE DYNAMIC TARGET PER CS
        // ======================================
        $csTargets = [];
        if (empty($kelasFilter) && !empty($bulanFilter)) {
            // Jika filter Bulan aktif & Semua Kelas -> Target adalah SUM dari target kelas yang diikuti CS
            // Ambil semua kelas yang ada salesplannya untuk filter ini (tanpa pagination)
            $distinctClasses = SalesPlan::with('kelas')
                ->select('created_by', 'kelas_id')
                ->when($csFilter, fn($q) => $q->where('created_by', $csFilter))
                ->when($bulanFilter, fn($q) => $q->whereMonth('updated_at', $bulanFilter))
                ->when($tahunFilter, fn($q) => $q->whereYear('updated_at', $tahunFilter))
                ->distinct()
                ->get()
                ->groupBy('created_by');

            foreach ($distinctClasses as $csId => $items) {
                $totalTargetCS = 0;
                foreach ($items as $item) {
                    if (!$item->kelas)
                        continue;
                    $namaKelas = $item->kelas->nama_kelas;

                    // Logic Target: 1 Miliar per kelas
                    $t = 1250000000;
                    $totalTargetCS += $t;
                }
                $csTargets[$csId] = $totalTargetCS;
            }
        }



        return view('admin.salesplan.index', [
            'salesplans' => $salesplans,
            'pesertaTransfer' => $pesertaTransfer,
            'kelasList' => $kelasList,
            'csList' => $csList,
            'kelasFilter' => $kelasFilter,
            'csFilter' => $csFilter,
            'statusFilter' => $statusFilter,
            'bulanFilter' => $bulanFilter,
            'tahunFilter' => $tahunFilter,
            'csTargets' => $csTargets,
            'salesplansByCS' => $salesplansByCS,
            'salesplanStats' => $salesplanStats,
            'dataMap' => $dataMap,
            'message' => null,
            'isRestrictedView' => $restrictedView
        ]);
    }




    /**
     * FILTER â€” sekarang tetep kirim variabel yang sama seperti index()
     */
    public function filter($kelas)
    {
        $request = new Request(['kelas' => $kelas, 'restricted_view' => true]);
        return $this->index($request);
    }


    /**
     * SEARCH â€” tetep kirim variabel view yang sama
     */
    public function search(Request $request)
    {
        $q = $request->input('q');

        $kelasList = Kelas::all();

        $salesplans = SalesPlan::with(['kelas', 'data'])
            ->where('nama', 'like', "%$q%")
            ->orWhereHas('kelas', fn($q2) => $q2->where('nama_kelas', 'like', "%$q%"))
            ->paginate(100);

        $kelasFilter = null;
        $pesertaTransfer = collect([]);
        $salesplansByCS = collect($salesplans->items())->groupBy('created_by');

        // Fallback: Ambil data berdasarkan nama
        $names = collect($salesplans->items())->pluck('nama')->filter()->toArray();
        $dataMap = Data::whereIn('nama', $names)->get()->keyBy('nama');

        return view('admin.salesplan.index', [
            'salesplans' => $salesplans,
            'kelasList' => $kelasList,
            'kelasFilter' => $kelasFilter,
            'pesertaTransfer' => $pesertaTransfer,
            'salesplansByCS' => $salesplansByCS,
            'dataMap' => $dataMap,
            'message' => "Hasil pencarian: $q"
        ]);
    }


    public function inlineUpdate(Request $request)
    {
        $plan = SalesPlan::findOrFail($request->id);

        $allowedFields = [
            'fu1_hasil',
            'fu1_tindak_lanjut',
            'fu2_hasil',
            'fu2_tindak_lanjut',
            'fu3_hasil',
            'fu3_tindak_lanjut',
            'fu4_hasil',
            'fu4_tindak_lanjut',
            'fu5_hasil',
            'fu5_tindak_lanjut',
            'nominal',
            'keterangan',
            'komentar_atasan',
            'kebutuhan',
            'kelas_id'
        ];

        if (!in_array($request->field, $allowedFields)) {
            return response()->json(['error' => 'Field tidak diizinkan'], 400);
        }

        $value = $request->value;
        if ($request->field === 'nominal') {
            // Strip dots and convert to numeric if it's nominal
            $value = str_replace('.', '', $value);
        }

        $plan->{$request->field} = $value;
        $plan->save();

        return response()->json(['success' => true]);
    }


    public function updateStatus(Request $request, $id)
    {
        $plan = SalesPlan::findOrFail($id);
        $plan->status = $request->status;
        $plan->save();

        return response()->json(['success' => true]);
    }


    public function export()
    {
        $sales = SalesPlan::all();
        return (new FastExcel($sales))->download('sales_plan.xlsx');
    }


    public function destroy($id)
    {
        $plan = SalesPlan::findOrFail($id);
        $plan->delete();

        return back()->with('success', 'Data berhasil dihapus');
    }

    public function dataPembeli(Request $request)
    {
        $kelasFilter = $request->input('kelas');
        $csFilter = $request->input('created_by');
        $bulanFilter = $request->input('bulan');
        $tahunFilter = $request->input('tahun', date('Y'));
        
        $userId = auth()->id();
        $isAdmin = in_array($userId, [1]);
        $exemptUsers = ['Agus Setyo', 'Fitra Jaya Saleh', 'Linda'];

        $pesertaTransfer = SalesPlan::whereIn('status', ['sudah_transfer', 'mau_transfer'])
            ->with(['data', 'kelas', 'kpr'])
            ->when($kelasFilter, function ($query) use ($kelasFilter) {
                $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                    $sub->where('nama_kelas', $kelasFilter);
                });
            })
            ->when($csFilter, function ($query) use ($csFilter) {
                $query->where('created_by', $csFilter);
            })
            ->when(!$isAdmin && !in_array(auth()->user()->name, $exemptUsers), function ($query) use ($userId) {
                $query->where('created_by', $userId);
            })
            ->when($bulanFilter, function ($query) use ($bulanFilter) {
                $query->whereMonth('updated_at', $bulanFilter);
            })
            ->when($tahunFilter, function ($query) use ($tahunFilter) {
                $query->whereYear('updated_at', $tahunFilter);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(100);

        $kelasList = Kelas::all();
        $csList = User::where('role', 'sales')
            ->orWhereIn('name', ['Yasmin', 'Linda', 'Arifa', 'Putri', 'Puput', 'Gunawan', 'Fitra Jaya Saleh', 'Agus Setyo'])
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.salesplan.data_pembeli', [
            'pesertaTransfer' => $pesertaTransfer,
            'kelasList' => $kelasList,
            'csList' => $csList,
            'kelasFilter' => $kelasFilter,
            'csFilter' => $csFilter,
            'bulanFilter' => $bulanFilter,
            'tahunFilter' => $tahunFilter,
        ]);
    }
}
