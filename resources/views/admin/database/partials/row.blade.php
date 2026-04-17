   <tr 
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
                       class="form-control form-control-sm editable fw-bold text-dark me-2" 
                       data-id="{{ $item->id }}"
                       data-field="nama" 
                       value="{{ $item->nama }}" 

                       style="font-size: 0.95rem; border-radius: 6px; border: 1px solid #ced4da; flex-grow: 1;">
            </div>
            
            {{-- Baris WA & Interaksi --}}
            <div class="d-flex align-items-center" style="gap: 12px;">
                <div class="input-group input-group-sm" style="width: 170px;">
                    <input type="text" 
                           class="form-control editable text-muted wa-input" 
                           data-id="{{ $item->id }}"
                           data-field="no_wa" 
                           data-original="{{ $item->no_wa }}"
                           value="{{ $item->no_wa }}" 
                           placeholder="No WhatsApp"
                           style="font-size: 0.85rem; border-radius: 6px 0 0 6px; border: 1px solid #ced4da;">
                    
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
                    <i class="fa-solid fa-file-invoice me-2"></i>Interaksi
                </button>



            </div>
        </div>
    </td>

    {{-- Sumber Leads --}}
    <td>
         <select class="form-control form-control-sm select-inline" data-id="{{ $item->id }}" data-field="leads">
            <option value="">- Pilih Sumber Leads -</option>
            <option value="Marketing" {{ $item->leads == 'Marketing' ? 'selected' : '' }}>Marketing</option>
            <option value="Iklan" {{ $item->leads == 'Iklan' ? 'selected' : '' }}>Iklan</option>
            <option value="Alumni" {{ $item->leads == 'Alumni' ? 'selected' : '' }}>Referal</option>
            <option value="Mandiri" {{ $item->leads == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
        </select>
    </td>

    {{-- Common Columns --}}
    
    @if(strtolower(auth()->user()->role) !== 'marketing')
    {{-- Survei Lokasi --}}
    <td class="text-center">
        <input type="checkbox" 
               class="checkbox-inline" 
               data-id="{{ $item->id }}" 
               data-field="survei_lokasi"
               {{ $item->survei_lokasi == 'Ya' ? 'checked' : '' }}
               style="transform: scale(1.5); cursor: pointer;">
    </td>
    @endif



    {{-- B --}}
    <td class="text-center">
        <input type="checkbox" class="checkbox-inline" data-id="{{ $item->id }}" data-field="spin_b" {{ $item->spin_b == 'Ya' ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
    </td>

    {{-- A --}}
    <td class="text-center">
        <input type="checkbox" class="checkbox-inline" data-id="{{ $item->id }}" data-field="spin_a" {{ $item->spin_a == 'Ya' ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
    </td>

    {{-- T --}}
    <td class="text-center">
        <input type="checkbox" class="checkbox-inline" data-id="{{ $item->id }}" data-field="spin_t" {{ $item->spin_t == 'Ya' ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
    </td>



    
    @if(!in_array(strtolower(auth()->user()->role), ['administrator', 'marketing']))
    @php
        $showMoveBtn = ($item->spin_b == 'Ya' && $item->spin_a == 'Ya' && $item->spin_t == 'Ya');
    @endphp
    <td class="text-center">
        <button type="button" 
                class="btn btn-sm btn-primary btn-move-salesplan {{ $showMoveBtn ? '' : 'd-none' }}" 
                data-id="{{ $item->id }}" 
                data-nama="{{ $item->nama }}"
                data-existing-kelas="{{ $item->salesplan->pluck('kelas_id')->toJson() }}"
                title="Pindahkan ke Prospek">
            <i class="fa fa-arrow-right"></i>
        </button>
    </td>
    @endif

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
