@extends('layouts.masteradmin')

@section('content')
    <div class="container-fluid">

        {{-- HEADER WITH FILTERS --}}

        {{-- HEADER WITH FILTERS AND EXPORT --}}
        <div class="mb-4">
            <!-- <h1 class="h3 font-weight-bold text-primary mb-3">
                <i class="far fa-calendar-alt mr-2"></i> DAILY ACTIVITY
            </h1> -->

            <form method="GET" action="{{ route($routeAction ?? 'manager.penilaian-cs.index') }}" class="d-inline-block">
                <div class="form-group mb-2">
                    <label class="mb-1 text-gray-800">Tanggal:</label>
                    <input type="date" name="tanggal" 
                           class="form-control" 
                           value="{{ request('tanggal', date('Y-m-d')) }}" 
                           onchange="this.form.submit()" 
                           style="width: 250px;">
                </div>
            </form>

            <form method="POST" action="{{ route('admin.penilaian-cs.exportPdf') }}" target="_blank" class="d-inline-block ml-3" id="pdfForm">
                @csrf
                <input type="hidden" name="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}">
                <input type="hidden" name="pdf_data" id="pdfDataInput">
                <div style="margin-top: 29px;">
                     <button type="button" class="btn btn-danger" style="width: 250px;" onclick="submitPdf()">
                        <i class="fas fa-file-pdf mr-2"></i> Export PDF
                    </button>
                </div>
            </form>

            <script>
                function submitPdf() {
                    const data = {
                        weekly: [],
                        monthly: [],
                        daily_spp_checked: [],
                        daily_networking_checked: [],
                        daily_coaching_checked: [],
                        daily_scheduling_checked: false, // New Item 4
                        // Also capture total scores to be safe?
                        total_daily: document.getElementById('totalDailyScore')?.textContent || '0%',
                        total_weekly: document.getElementById('totalWeeklyScore')?.textContent || '0%',
                        total_monthly: document.getElementById('totalMonthlyScore')?.textContent || '0%',
                        grand_total: document.getElementById('grandTotal')?.textContent || '0%'
                    };

                    // Collect Weekly
                    document.querySelectorAll('.weekly-checkbox:checked').forEach(cb => {
                        const id = cb.id.replace('checkWeekly_', ''); // 1, 2...
                        data.weekly.push(id);
                    });

                    // Collect Monthly
                    document.querySelectorAll('.monthly-checkbox:checked').forEach(cb => {
                        const id = cb.id.replace('checkMonthly_', '');
                        data.monthly.push(id);
                    });

                    // Collect Daily Dynamic Checklists
                    // SPP
                    document.querySelectorAll('.spp-checkbox:checked').forEach(cb => {
                         data.daily_spp_checked.push(cb.id.replace('spp_check_', ''));
                    });
                    // Networking
                    document.querySelectorAll('.networking-checkbox:checked').forEach(cb => {
                         data.daily_networking_checked.push(cb.id.replace('networking_check_', ''));
                    });
                    // Coaching
                    document.querySelectorAll('.coaching-checkbox:checked').forEach(cb => {
                         data.daily_coaching_checked.push(cb.id.replace('coaching_check_', ''));
                    });
                    // Scheduling (Single checkbox)
                    const scheduleCb = document.getElementById('checkDaily_4');
                    if(scheduleCb && scheduleCb.checked) {
                        data.daily_scheduling_checked = true;
                    }

                    document.getElementById('pdfDataInput').value = JSON.stringify(data);
                    document.getElementById('pdfForm').submit();
                }
            </script>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body p-5">

                {{-- DOCUMENT HEADER --}}
                <div class="text-center mb-5">
                    <h4 class="font-weight-bold text-uppercase text-dark">Daily Activity</h4>
                    <h5 class="text-uppercase text-secondary">Staff Operasional & Keuangan</h5>
                </div>

                <h5 class="font-weight-bold text-primary mb-3">A. CHECKLIST TO DO LIST</h5>

                {{-- HARIAN --}}
                <h6 class="font-weight-bold text-dark bg-light p-2 border-left-primary">Harian (35%)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 50%;">Aktivitas</th>
                                <th style="width: 25%;" class="text-center">Checklist</th>
                                <th style="width: 20%;" class="text-center">Poin</th>
                            </tr>
                        </thead>
                        <tbody id="activityTableBody">
                            <!-- Define weights: 35% / 4 items = 8.75% -->
                            <!-- Define weights: 35% / 4 items = 8.75% -->
                            <script>const MAX_SCORE_ACTIVITY = 8.75;</script>
                            
                            <!-- Item 1: SPP (Integrated, No Dropdown) -->
                            <tr>
                                <td class="text-center">1</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="mb-2">Penagihan SPP Mahasiswa SMI</span>
                                        <form method="GET" action="{{ url()->current() }}" class="form-inline">
                                            <!-- Keep existing params -->
                                            @foreach(request()->except(['spp_bulan', 'spp_tahun', 'spp_status']) as $key => $value)
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endforeach

                                            <select name="spp_bulan" class="form-control form-control-sm mr-1 mb-1" style="width: 100px;">
                                                @php $sppBulan = request('spp_bulan', date('m')); @endphp
                                                @foreach(range(1, 12) as $m)
                                                    <option value="{{ $m }}" {{ $sppBulan == $m ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <select name="spp_status" class="form-control form-control-sm mr-1 mb-1" style="width: 100px;">
                                                <option value="">- Status -</option>
                                                <option value="lunas" {{ request('spp_status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                                <option value="belum" {{ request('spp_status') == 'belum' ? 'selected' : '' }}>Belum</option>
                                            </select>

                                            <select name="spp_tahun" class="form-control form-control-sm mr-1 mb-1" style="width: 80px;">
                                                @php $sppTahun = request('spp_tahun', date('Y')); @endphp
                                                @foreach(range(date('Y')-1, date('Y')+1) as $y)
                                                    <option value="{{ $y }}" {{ $sppTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endforeach
                                            </select>

                                            <button type="submit" class="btn btn-primary btn-sm mb-1 px-2">
                                                <i class="fas fa-filter"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        // Parameters
                                        $reqBulan = request('spp_bulan', date('m'));
                                        $reqStatus = request('spp_status');
                                        $reqTahun = request('spp_tahun', date('Y'));
                                        
                                        // Normalize month index (1 vs 01) - DB columns usually spp_1 etc.
                                        $monthIdx = (int) $reqBulan; 

                                        $filteredSMI = isset($listPesertaSMI) ? $listPesertaSMI : collect([]);
                                        
                                        // Filter by Year (if applicable column exists, else ignore or assuming active students)
                                        // Assuming listPesertaSMI is all(). We can filter by created_at year or similar if needed.
                                        // For now, simpler to just calculate payments for the given month.

                                        // Filter by Status if requested
                                        if($reqStatus === 'lunas') {
                                            $filteredSMI = $filteredSMI->filter(function($p) use ($monthIdx) {
                                                return $p->{'spp_'.$monthIdx} == 1;
                                            });
                                        } elseif($reqStatus === 'belum') {
                                            $filteredSMI = $filteredSMI->filter(function($p) use ($monthIdx) {
                                                return $p->{'spp_'.$monthIdx} == 0;
                                            });
                                        }

                                        $sppTotal = $filteredSMI->count();
                                        
                                        // Count Paid in the filtered set
                                        // If status='lunas', Paid=Total. If 'belum', Paid=0.
                                        $sppPaid = $filteredSMI->filter(function($p) use ($monthIdx) {
                                                return $p->{'spp_'.$monthIdx} == 1;
                                        })->count();

                                        $sppScore = $sppTotal > 0 ? ($sppPaid / $sppTotal) * 8.75 : 0;
                                    @endphp
                                    <span class="text-dark font-weight-bold">
                                        ({{ $sppPaid }}/{{ $sppTotal }} Mhs)
                                    </span>
                                </td>
                                <td id="poinSPP" class="text-center align-middle font-weight-bold">{{ number_format($sppScore, 2) }}</td>
                            </tr>

                            <!-- Item 2: Networking (Simple Checklist) -->
                            <tr>
                                <td class="text-center">2</td>
                                <td>Networking dengan Mahasiswa</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static daily-simple-checkbox" type="checkbox" id="checkDaily_2">
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const cb = document.getElementById('checkDaily_2');
                                            const poinCell = document.getElementById('poinDaily_2');
                                            
                                            cb.addEventListener('change', function() {
                                                const score = this.checked ? MAX_SCORE_ACTIVITY : 0;
                                                if(poinCell) poinCell.textContent = score.toFixed(2);
                                                if(typeof updateTotalScore === 'function') updateTotalScore();
                                            });
                                        });
                                    </script>
                                </td>
                                <td id="poinDaily_2" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>

                            <!-- Item 3: Coaching -->
                            <tr>
                                <td class="text-center">3</td>
                                <td>Jalankan 1 on 1 Coaching</td>
                                <td class="text-center">
                                    <div style="position: relative;">
                                        <!-- Search Input -->
                                        <input type="text" id="searchCoaching" class="form-control form-control-sm" placeholder="Cari nama..." autocomplete="off">
                                        
                                        <!-- List Container (Autocomplete Dropdown) -->
                                        <div id="listCoachingContainer" class="card shadow-sm" style="display: none; position: absolute; top: 100%; left: 0; width: 100%; z-index: 999; max-height: 200px; overflow-y: auto; text-align: left;">
                                            <ul class="list-group list-group-flush" id="ulCoaching">
                                                @if(isset($listPesertaSMI) && $listPesertaSMI->count() > 0)
                                                    @foreach($listPesertaSMI as $peserta)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center p-2 border-bottom-0 coaching-item">
                                                        <label class="form-check-label small mb-0 w-100" for="coaching_check_{{ $peserta->id }}" style="cursor: pointer;">
                                                            {{ $peserta->nama }}
                                                        </label>
                                                        <div class="custom-control custom-checkbox ml-2">
                                                            <input type="checkbox" class="custom-control-input coaching-checkbox" id="coaching_check_{{ $peserta->id }}">
                                                            <label class="custom-control-label" for="coaching_check_{{ $peserta->id }}"></label>
                                                        </div>
                                                    </li>
                                                    @endforeach
                                                @else
                                                    <li class="list-group-item text-muted small text-center p-2">Belum ada data.</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- Selected Summary -->
                                    <div class="mt-1 small text-muted text-left">
                                        <span id="counterCoaching" style="display: none;"></span> 
                                    </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const searchInput = document.getElementById('searchCoaching');
                                            const listContainer = document.getElementById('listCoachingContainer');
                                            const listItems = document.querySelectorAll('.coaching-item');
                                            const checkboxes = document.querySelectorAll('.coaching-checkbox');
                                            const poinCell = document.getElementById('poinCoaching');
                                            const total = {{ isset($listPesertaSMI) ? $listPesertaSMI->count() : 0 }};

                                            // Toggle List on Focus
                                            searchInput.addEventListener('focus', () => {
                                                listContainer.style.display = 'block';
                                            });
                                            
                                            // Close list when clicking outside
                                            document.addEventListener('click', function(event) {
                                                // Prevent closing if clicking inside the search box or the list
                                                if (!searchInput.contains(event.target) && !listContainer.contains(event.target)) {
                                                    listContainer.style.display = 'none';
                                                }
                                            });

                                            // Search Filter
                                            searchInput.addEventListener('input', function(e) {
                                                const term = e.target.value.toLowerCase();
                                                listContainer.style.display = 'block'; // Ensure visible when typing
                                                
                                                listItems.forEach(item => {
                                                    const name = item.querySelector('label').textContent.toLowerCase();
                                                    if(name.includes(term)) {
                                                        item.style.removeProperty('display'); // Show (revert to d-flex)
                                                    } else {
                                                        item.style.setProperty('display', 'none', 'important'); // Force hide
                                                    }
                                                });
                                            });

                                            // Scoring Logic
                                            function updateCounter() {
                                                const checked = document.querySelectorAll('.coaching-checkbox:checked').length;
                                                let score = 0;
                                                if(total > 0) {
                                                    score = (checked / total) * MAX_SCORE_ACTIVITY;
                                                }
                                                if(poinCell) poinCell.textContent = score.toFixed(2);
                                                if(typeof updateTotalScore === 'function') updateTotalScore();
                                            }

                                            checkboxes.forEach(cb => {
                                                cb.addEventListener('change', updateCounter);
                                            });
                                            
                                            // Init
                                            updateCounter();
                                        });
                                    </script>
                                </td>
                                <td id="poinCoaching" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>

                            <tr>
                                <td class="text-center">4</td>
                                <td>Jadwalkan sesi 1 on 1</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static daily-simple-checkbox" type="checkbox" id="checkDaily_4">
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const cb = document.getElementById('checkDaily_4');
                                            const poinCell = document.getElementById('poinDaily_4');
                                            
                                            cb.addEventListener('change', function() {
                                                const score = this.checked ? MAX_SCORE_ACTIVITY : 0;
                                                if(poinCell) poinCell.textContent = score.toFixed(2);
                                                if(typeof updateTotalScore === 'function') updateTotalScore();
                                            });
                                        });
                                    </script>
                                </td>
                                <td id="poinDaily_4" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Total Row -->
                            <tr class="font-weight-bold bg-light">
                                <td colspan="3" class="text-right">Total Harian</td>
                                <td id="totalDailyScore" class="text-center font-weight-bold">0.00%</td>
                            </tr>
                        </tbody>
                        <script>
                            function updateTotalScore() {
                                const p1 = parseFloat(document.getElementById('poinSPP').textContent) || 0;
                                const p2 = parseFloat(document.getElementById('poinDaily_2').textContent) || 0; // Networking
                                const p3 = parseFloat(document.getElementById('poinCoaching').textContent) || 0;
                                const p4 = parseFloat(document.getElementById('poinDaily_4').textContent) || 0;
                                
                                const total = p1 + p2 + p3 + p4;
                                const totalCell = document.getElementById('totalDailyScore');
                                if(totalCell) totalCell.textContent = total.toFixed(2) + '%';
                            }
                        </script>
                    </table>
                </div>

                {{-- MINGGUAN --}}
                <h6 class="font-weight-bold text-dark bg-light p-2 border-left-success mt-4">Mingguan (40%)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 50%;">Aktivitas</th>
                                <th style="width: 25%;" class="text-center">Checklist</th>
                                <th style="width: 20%;" class="text-center">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Item 1 -->
                            <tr>
                                <td class="text-center">1</td>
                                <td>Pemeriksaan kebersihan dan kelayakan semua ruang kantor</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static weekly-checkbox" type="checkbox" id="checkWeekly_1" data-target="poinWeekly_1">
                                    </div>
                                </td>
                                <td id="poinWeekly_1" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Item 2 -->
                            <tr>
                                <td class="text-center">2</td>
                                <td>Cek perlengkapan kantor</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static weekly-checkbox" type="checkbox" id="checkWeekly_2" data-target="poinWeekly_2">
                                    </div>
                                </td>
                                <td id="poinWeekly_2" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Item 3 (Merged SMI & MBC) -->
                            <tr>
                                <td class="text-center">3</td>
                                <td>Menyiapkan Kelas SMI dan MBC</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="custom-control custom-checkbox mr-3">
                                            <input type="checkbox" class="custom-control-input weekly-checkbox" id="checkWeekly_3" data-target="poinWeekly_3">
                                            <label class="custom-control-label" for="checkWeekly_3">SMI</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input weekly-checkbox" id="checkWeekly_6" data-target="poinWeekly_3">
                                            <label class="custom-control-label" for="checkWeekly_6">MBC</label>
                                        </div>
                                    </div>
                                </td>
                                <td id="poinWeekly_3" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Item 4 (Previously 4) -->
                            <tr>
                                <td class="text-center">4</td>
                                <td>Rapat evaluasi internal</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static weekly-checkbox" type="checkbox" id="checkWeekly_4" data-target="poinWeekly_4">
                                    </div>
                                </td>
                                <td id="poinWeekly_4" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Item 5 (Previously 5) -->
                            <tr>
                                <td class="text-center">5</td>
                                <td>Menyiapkan Kelas EF</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static weekly-checkbox" type="checkbox" id="checkWeekly_5" data-target="poinWeekly_5">
                                    </div>
                                </td>
                                <td id="poinWeekly_5" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Total Row Weekly -->
                            <tr class="font-weight-bold bg-light">
                                <td colspan="3" class="text-right">Total Mingguan</td>
                                <td id="totalWeeklyScore" class="text-center font-weight-bold">0.00%</td>
                            </tr>
                        </tbody>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const weeklyCheckboxes = document.querySelectorAll('.weekly-checkbox');
                                const totalWeeklyCell = document.getElementById('totalWeeklyScore');
                                const SCORE_PER_ITEM = 40 / 6; // ~6.67% per checkbox

                                function updateWeekly() {
                                    let totalScore = 0;
                                    
                                    // Reset all poin cells first
                                    const distinctTargets = new Set();
                                    weeklyCheckboxes.forEach(cb => {
                                        if(cb.dataset.target) distinctTargets.add(cb.dataset.target);
                                    });
                                    distinctTargets.forEach(id => {
                                        const cell = document.getElementById(id);
                                        if(cell) cell.textContent = '0.00';
                                    });

                                    // Calculate per checkbox
                                    weeklyCheckboxes.forEach(cb => {
                                        if(cb.checked) {
                                            totalScore += SCORE_PER_ITEM;
                                            const targetId = cb.dataset.target;
                                            const cell = document.getElementById(targetId);
                                            if(cell) {
                                                const currentVal = parseFloat(cell.textContent) || 0;
                                                cell.textContent = (currentVal + SCORE_PER_ITEM).toFixed(2);
                                            }
                                        }
                                    });

                                    if(totalWeeklyCell) totalWeeklyCell.textContent = totalScore.toFixed(2) + '%';
                                }

                                weeklyCheckboxes.forEach(cb => {
                                    cb.addEventListener('change', updateWeekly);
                                });

                                // Init
                                updateWeekly();
                            });
                        </script>
                    </table>
                </div>

                {{-- BULANAN --}}
                <h6 class="font-weight-bold text-dark bg-light p-2 border-left-info mt-4">Bulanan (25%)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 50%;">Aktivitas</th>
                                <th style="width: 25%;" class="text-center">Checklist</th>
                                <th style="width: 20%;" class="text-center">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Item 1 -->
                            <tr>
                                <td class="text-center">1</td>
                                <td>Update status Progres mahasiswa</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static monthly-checkbox" type="checkbox" id="checkMonthly_1">
                                    </div>
                                </td>
                                <td id="poinMonthly_1" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Item 2 -->
                            <tr>
                                <td class="text-center">2</td>
                                <td>Rekap sesi 1-on-1</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static monthly-checkbox" type="checkbox" id="checkMonthly_2">
                                    </div>
                                </td>
                                <td id="poinMonthly_2" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Item 3 -->
                            <tr>
                                <td class="text-center">3</td>
                                <td>Cek inventaris aset</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static monthly-checkbox" type="checkbox" id="checkMonthly_3">
                                    </div>
                                </td>
                                <td id="poinMonthly_3" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Item 4 -->
                            <tr>
                                <td class="text-center">4</td>
                                <td>Laporan operasional dan kemahasiswaan SMI</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static monthly-checkbox" type="checkbox" id="checkMonthly_4">
                                    </div>
                                </td>
                                <td id="poinMonthly_4" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Item 5 -->
                            <tr>
                                <td class="text-center">5</td>
                                <td>Rekap feedback mahasiswa</td>
                                <td class="text-center">
                                    <div class="form-check text-center">
                                        <input class="form-check-input position-static monthly-checkbox" type="checkbox" id="checkMonthly_5">
                                    </div>
                                </td>
                                <td id="poinMonthly_5" class="text-center align-middle font-weight-bold">0.00</td>
                            </tr>
                            <!-- Total Row Monthly -->
                            <tr class="font-weight-bold bg-light">
                                <td colspan="3" class="text-right">Total Bulanan</td>
                                <td id="totalMonthlyScore" class="text-center font-weight-bold">0.00%</td>
                            </tr>
                        </tbody>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const monthlyCheckboxes = document.querySelectorAll('.monthly-checkbox');
                                const totalMonthlyCell = document.getElementById('totalMonthlyScore');
                                const SCORE_PER_ITEM = 25 / 5; // 5.0%

                                function updateMonthly() {
                                    let totalScore = 0;
                                    monthlyCheckboxes.forEach((cb, index) => {
                                        const poinId = 'poinMonthly_' + (index + 1);
                                        const poinCell = document.getElementById(poinId);
                                        if(cb.checked) {
                                            totalScore += SCORE_PER_ITEM;
                                            if(poinCell) poinCell.textContent = SCORE_PER_ITEM.toFixed(2);
                                        } else {
                                            if(poinCell) poinCell.textContent = '0.00';
                                        }
                                    });
                                    if(totalMonthlyCell) totalMonthlyCell.textContent = totalScore.toFixed(2) + '%';
                                }

                                monthlyCheckboxes.forEach(cb => {
                                    cb.addEventListener('change', updateMonthly);
                                });

                                // Init
                                updateMonthly();
                            });
                        </script>
                    </table>
                </div>

                <!-- <div class="alert alert-info small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Halaman ini adalah form checklist monitoring kinerja. Untuk pengisian skor dan catatan, silakan lakukan
                    secara manual pada dokumen fisik atau hubungi Administrator untuk pengembangan fitur input digital.
                </div> -->

                <!-- REKAPITULASI -->
                <h6 class="font-weight-bold text-dark bg-light p-2 border-left-danger mt-5">REKAPITULASI PENILAIAN</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 70%;">Kategori Penilaian</th>
                                <th style="width: 30%;" class="text-center">Total Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Harian (35%)</strong></td>
                                <td id="rekapDaily" class="text-center font-weight-bold">0.00%</td>
                            </tr>
                            <tr>
                                <td><strong>Mingguan (40%)</strong></td>
                                <td id="rekapWeekly" class="text-center font-weight-bold">0.00%</td>
                            </tr>
                            <tr>
                                <td><strong>Bulanan (25%)</strong></td>
                                <td id="rekapMonthly" class="text-center font-weight-bold">0.00%</td>
                            </tr>
                            <tr class="bg-primary text-white">
                                <td class="text-right"><strong>TOTAL SKOR AKHIR</strong></td>
                                <td id="grandTotal" class="text-center font-weight-bold" style="font-size: 1.2em;">0.00%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- KATEGORI KUALITAS KERJA --}}
                <h6 class="font-weight-bold text-dark mt-5">4. KATEGORI KUALITAS KERJA (4 LEVEL)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 15%;" class="text-center">Nilai Akhir</th>
                                <th style="width: 20%;">Kategori</th>
                                <th style="width: 65%;">Makna Manajerial</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- SANGAT BAIK -->
                            <tr>
                                <td class="text-center">90 – 100</td>
                                <td style="background-color: #1e7e34; color: white;">SANGAT BAIK</td>
                                <td>Sangat disiplin, mandiri, layak ditambah tanggung jawab</td>
                            </tr>
                            <!-- BAIK -->
                            <tr>
                                <td class="text-center">75 – 89</td>
                                <td style="background-color: #90ee90;">BAIK</td>
                                <td>Konsisten, hanya perlu penguatan kecil</td>
                            </tr>
                            <!-- CUKUP -->
                            <tr>
                                <td class="text-center">60 – 74</td>
                                <td class="bg-warning text-dark">CUKUP</td>
                                <td>Banyak bolong, perlu pembinaan rutin</td>
                            </tr>
                            <!-- KURANG -->
                            <tr>
                                <td class="text-center">&lt; 60</td>
                                <td class="bg-danger text-white">KURANG</td>
                                <td>Tidak disiplin, perlu evaluasi serius</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Elements to watch
                        const srcDaily = document.getElementById('totalDailyScore');
                        const srcWeekly = document.getElementById('totalWeeklyScore');
                        const srcMonthly = document.getElementById('totalMonthlyScore');

                        // Elements to update
                        const destDaily = document.getElementById('rekapDaily');
                        const destWeekly = document.getElementById('rekapWeekly');
                        const destMonthly = document.getElementById('rekapMonthly');
                        const destGrand = document.getElementById('grandTotal');

                        function updateGrandTotal() {
                            const valDaily = parseFloat(srcDaily.textContent) || 0;
                            const valWeekly = parseFloat(srcWeekly.textContent) || 0;
                            const valMonthly = parseFloat(srcMonthly.textContent) || 0;

                            // Update individual rekap cells
                            destDaily.textContent = valDaily.toFixed(2) + '%';
                            destWeekly.textContent = valWeekly.toFixed(2) + '%';
                            destMonthly.textContent = valMonthly.toFixed(2) + '%';

                            // Update Grand Total
                            const total = valDaily + valWeekly + valMonthly;
                            destGrand.textContent = total.toFixed(2) + '%';
                        }

                        // Create an observer instance linked to the callback function
                        const observer = new MutationObserver(function(mutationsList) {
                            updateGrandTotal();
                        });

                        // Start observing the target nodes for configured mutations
                        if(srcDaily) observer.observe(srcDaily, { childList: true, subtree: true, characterData: true });
                        if(srcWeekly) observer.observe(srcWeekly, { childList: true, subtree: true, characterData: true });
                        if(srcMonthly) observer.observe(srcMonthly, { childList: true, subtree: true, characterData: true });

                        // Initial run
                        // Delay slightly to ensure other scripts have initialized their 0.00 values if needed
                        setTimeout(updateGrandTotal, 500);
                    });
                </script>

            </div>
        </div>
    </div>

@endsection