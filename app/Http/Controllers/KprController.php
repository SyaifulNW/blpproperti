<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kpr;
use App\Models\SalesPlan;
use Illuminate\Support\Facades\Auth;

class KprController extends Controller
{
    public function index(Request $request)
    {
        $kprs = Kpr::orderBy('updated_at', 'desc')->paginate(100);
        return view('admin.kpr.index', compact('kprs'));
    }

    public function moveToKpr($salesplanId)
    {
        $plan = SalesPlan::with('data')->findOrFail($salesplanId);

        $kpr = Kpr::where('salesplan_id', $plan->id)->first();
        if ($kpr) {
            return redirect()->route('admin.kpr.show', $kpr->id)->with('info', 'Data ini sudah ada di monitoring KPR.');
        }

        $newKpr = Kpr::create([
            'salesplan_id' => $plan->id,
            'nama' => $plan->nama ?? ($plan->data->nama ?? '-'),
            'phone' => $plan->data->no_wa ?? '-',
            'created_by' => Auth::user()->name,
            'tahap_posisi' => 'Booking Fee',
            'status_global' => 'Ongoing'
        ]);

        return redirect()->route('admin.kpr.show', $newKpr->id)->with('success', 'Berhasil memasukkan data ke monitoring KPR.');
    }

    public function show($id)
    {
        $kpr = Kpr::findOrFail($id);
        return view('admin.kpr.show', compact('kpr'));
    }

    public function update(Request $request, $id)
    {
        $kpr = Kpr::findOrFail($id);

        $data = $request->all();

        // Bersihkan titik ribuan sebelum simpan ke DB
        $moneyFields = ['bf_nominal', 'appraisal_hasil_nilai', 'sp3k_plafon', 'sp3k_cicilan'];
        foreach ($moneyFields as $field) {
            if ($request->has($field)) {
                $data[$field] = str_replace('.', '', $request->get($field));
            }
        }

        $kpr->update($data);

        return back()->with('success', 'Data KPR berhasil diperbarui.');
    }

    public function updateStage(Request $request, $id)
    {
        $kpr = Kpr::findOrFail($id);
        $kpr->tahap_posisi = $request->tahap;

        // Auto-change status global to Success if last stage
        if ($request->tahap == 'Pencairan/Final') {
            $kpr->status_global = 'Success';
        } else {
            $kpr->status_global = 'Ongoing';
        }

        $kpr->save();
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $kpr = Kpr::findOrFail($id);
        $kpr->delete();
        return back()->with('success', 'Data KPR berhasil dihapus.');
    }
}
