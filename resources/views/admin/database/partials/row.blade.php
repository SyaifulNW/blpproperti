    @php
        // Prioritaskan ambil status & nominal dari SalesPlan (Prospek) jika ada
        $latestPlan = $item->salesplan->sortByDesc('created_at')->first();
        
        $statusVal = strtolower(($latestPlan ? $latestPlan->status : $item->status) ?: 'cold');
        $nominalVal = $latestPlan ? $latestPlan->nominal : $item->nominal;
        
        $statusClass = 'status-cold';
        $bgStyle = 'background-color: #ffffff; color: #000;'; // Default white
        
        if ($statusVal == 'tunai' || $statusVal == 'sudah_transfer') {
            $statusClass = 'status-tunai';
            $bgStyle = 'background-color: #48e7ecff; color: #000;';
        } elseif ($statusVal == 'kpr' || $statusVal == 'mau_transfer') {
            $statusClass = 'status-kpr';
            $bgStyle = 'background-color: #1cc600; color: #fff;';
        } elseif ($statusVal == 'tertarik') {
            $statusClass = 'status-tertarik';
            $bgStyle = 'background-color: #ffd900ff; color: #000;';
        } elseif ($statusVal == 'no') {
            $statusClass = 'status-no';
            $bgStyle = 'background-color: #ff4d4d; color: #fff;';
        }
    @endphp

   <tr 
    class="{{ $statusClass }}"
    style="{{ $bgStyle }} font-weight: 700 !important;"
    data-created-by="{{ strtolower($item->created_by) }}"
    data-bulan="{{ \Carbon\Carbon::parse($item->created_at)->month }}"
    data-year="{{ \Carbon\Carbon::parse($item->created_at)->year }}"
    data-id="{{ $item->id }}"
>

    <td style="padding-right: 25px !important; text-align: center; font-weight: bold;">{{ $loop->iteration ?? '-' }}</td>
    
    @php
        $userRole = strtolower(auth()->user()->role);
        $isCs = in_array($userRole, ['cs', 'cs-mbc', 'cs-smi', 'customer_service']);
    @endphp

    {{-- Merged: Nama + WA + CTA --}}
    <td>
        <div class="d-flex flex-column py-1">
            {{-- Baris Nama --}}
            <div class="d-flex align-items-center mb-2">
                <input type="text" 
                       class="form-control form-control-sm editable" 
                       data-id="{{ $item->id }}"
                       data-field="nama" 
                       value="{{ $item->nama }}" 
                       style="font-size: 1.05rem; border-radius: 6px; border: 2px solid #000 !important; background: transparent !important; color: #000 !important; font-weight: 900; flex-grow: 1; padding: 5px 10px;">
            </div>
            
            {{-- Baris WA & Interaksi --}}
            <div class="d-flex align-items-center" style="gap: 12px;">
                <div class="input-group input-group-sm" style="width: 190px;">
                    <input type="text" 
                           class="form-control editable wa-input" 
                           data-id="{{ $item->id }}"
                           data-field="no_wa" 
                           data-original="{{ $item->no_wa }}"
                           value="{{ $item->no_wa }}" 
                           placeholder="No WhatsApp"
                           style="font-size: 1rem; border-radius: 6px; border: 2px solid #000 !important; background: transparent !important; color: #000 !important; font-weight: 900; padding: 5px 10px;">
                    
                    @php $waNumber = preg_replace('/^0/', '62', $item->no_wa); @endphp
                    <a href="{{ $item->no_wa ? 'https://wa.me/'.$waNumber : '#' }}" 
                       target="{{ $item->no_wa ? '_blank' : '' }}" 
                       class="input-group-text btn btn-success wa-button {{ !$item->no_wa ? 'disabled opacity-50' : '' }}" 
                       style="border-radius: 0 6px 6px 0; border: 1px solid #28a745; background-color: #28a745; width: 40px; display: flex; justify-content: center;" 
                       title="{{ $item->no_wa ? 'Chat WhatsApp' : 'No WA Belum Diisi' }}">
                        <i class="fa-brands fa-whatsapp" style="color:#fff; font-size: 1.1rem;"></i>
                    </a>
                </div>

                {{-- Tombol Interaksi Riwayat SPIN --}}
                <button type="button" 
                        class="btn btn-primary btn-sm btn-spin-history border-0 d-flex align-items-center px-3" 
                        data-id="{{ $item->id }}" 
                        data-nama="{{ $item->nama }}"
                        style="border-radius: 50px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); font-size: 0.75rem; font-weight: 600; height: 32px; white-space: nowrap;">
                    <i class="fa-solid fa-file-invoice me-2"></i>Follow Up
                </button>



            </div>
        </div>
    </td>

    {{-- Sumber Leads --}}
    <td>
         <select class="form-control form-control-sm select-inline" 
                 data-id="{{ $item->id }}" 
                 data-field="leads" 
                 style="background: transparent !important; color: #000 !important; font-weight: 800; border: 2px solid #000 !important; border-radius: 6px;">
            <option value="">- Pilih Sumber Leads -</option>
            @foreach($leadSources as $ls)
                <option value="{{ $ls->name }}" {{ $item->leads == $ls->name ? 'selected' : '' }}>{{ $ls->name }}</option>
            @endforeach
        </select>
    </td>

    {{-- Common Columns --}}
    
    @if(strtolower(auth()->user()->role) !== 'marketing')
    {{-- Survei Lokasi --}}
    <td class="text-center" style="background: transparent !important;">
        <input type="checkbox" 
               class="checkbox-inline" 
               data-id="{{ $item->id }}" 
               data-field="survei_lokasi"
               {{ $item->survei_lokasi == 'Ya' ? 'checked' : '' }}
               style="transform: scale(1.5); cursor: pointer; background: transparent !important;">
    </td>
    @endif



    {{-- B --}}
    <td class="text-center" style="background: transparent !important;">
        <input type="checkbox" class="checkbox-inline" data-id="{{ $item->id }}" data-field="spin_b" {{ $item->spin_b == 'Ya' ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer; background: transparent !important;">
    </td>

    {{-- A --}}
    <td class="text-center">
        <input type="checkbox" class="checkbox-inline" data-id="{{ $item->id }}" data-field="spin_a" {{ $item->spin_a == 'Ya' ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
    </td>

    {{-- T --}}
    <td class="text-center">
        <input type="checkbox" class="checkbox-inline" data-id="{{ $item->id }}" data-field="spin_t" {{ $item->spin_t == 'Ya' ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
    </td>

    {{-- Pilih Produk --}}
    <td class="text-center">
        <select class="form-control form-control-sm select-inline" 
                data-id="{{ $item->id }}" 
                data-field="kelas_id"
                style="min-width: 160px; border-radius: 6px; font-weight: 800; background: transparent !important; color: #000 !important; border: 2px solid #000 !important;">
            <option value="">- Pilih Produk -</option>
            @foreach($kelas as $k)
                <option value="{{ $k->id }}" {{ $item->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
    </td>



    
    {{-- Status Dropdown --}}
    <td class="text-center">
        <select class="form-control form-control-sm select-inline status-select-dynamic" 
                data-id="{{ $item->id }}" 
                data-field="status"
                style="min-width: 120px; border-radius: 6px; font-weight: 900; background: transparent !important; color: #000 !important; border: 2px solid #000 !important;">
            <option value="Cold" style="color: #000;">Cold</option>
            <option value="Tunai" {{ ($statusVal == 'tunai' || $statusVal == 'sudah_transfer') ? 'selected' : '' }}>Tunai</option>
            <option value="KPR" {{ ($statusVal == 'kpr' || $statusVal == 'mau_transfer') ? 'selected' : '' }}>KPR</option>
            <option value="Tertarik" {{ $statusVal == 'tertarik' ? 'selected' : '' }}>Tertarik</option>
            <option value="No" {{ $statusVal == 'no' ? 'selected' : '' }}>No</option>
        </select>
        
        {{-- Hidden trigger for Move Modal when KPR is selected --}}
        <button type="button" 
                class="btn-move-salesplan d-none" 
                data-id="{{ $item->id }}" 
                data-nama="{{ $item->nama }}"
                data-existing-kelas="{{ $item->salesplan->pluck('kelas_id')->toJson() }}">
        </button>
    </td>

    {{-- Nominal --}}
    <td>
        <input type="text" 
               class="form-control form-control-sm editable currency-input text-end fw-bold" 
               data-id="{{ $item->id }}"
               data-field="nominal" 
               value="{{ number_format((float)$nominalVal, 0, ',', '.') }}" 
               placeholder="0"
               style="min-width: 140px; border-radius: 6px; border: 2px solid #000 !important; background: transparent !important; color: #000 !important; font-weight: 800; padding: 5px 10px;">
    </td>

    {{-- Pindahkan ke Data Pelanggan --}}
    <td class="text-center">
        <button type="button" 
                class="btn btn-primary btn-sm btn-direct-alumni" 
                data-id="{{ $item->id }}" 
                data-nama="{{ $item->nama }}"
                title="Pindahkan ke Data Pelanggan"
                style="border-radius: 6px; border: 2px solid #000 !important; font-weight: 800;">
            <i class="fas fa-arrow-right"></i>
        </button>
    </td>

    @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) || auth()->user()->name === 'Agus Setyo')
    <td>{{ $item->created_by }}</td>
    @endif
    
    @if(!in_array(strtolower(auth()->user()->role), ['administrator']))
        <td>
            <a href="{{ route('admin.database.show', $item->id) }}" class="btn btn-info btn-sm">
                <i class="fa-solid fa-eye" style="color:#fff;"></i>
            </a>
            <form action="{{ route('delete-database', $item->id) }}" method="POST" style="display:inline;" class="delete-form">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm btn-delete">
                    <i class="fa-solid fa-trash" style="color:#fff;"></i>
                </button>
            </form>
        </td>
    @endif

</tr>
