   <tr 
    data-created-by="{{ strtolower($item->created_by) }}"
    data-bulan="{{ \Carbon\Carbon::parse($item->created_at)->month }}"
    data-year="{{ \Carbon\Carbon::parse($item->created_at)->year }}"
        data-id="{{ $item->id }}"
>

    <td>{{ $loop->iteration ?? '-' }}</td>
    
    @php
        $userRole = strtolower(auth()->user()->role);
        $isCs = in_array($userRole, ['cs', 'cs-mbc', 'cs-smi', 'customer_service']);
    @endphp

    {{-- Merged: Nama + WA + CTA --}}
    <td>
        <div class="d-flex flex-column">
            <div contenteditable="true" class="editable fw-bold text-dark mb-1" data-field="nama" style="font-size: 0.95rem;">{{ $item->nama }}</div>
            
            <div class="d-flex align-items-center mt-1">
                <span contenteditable="true" 
                      class="editable border-bottom" 
                      data-field="no_wa" 
                      style="font-size: 0.85rem; min-width: 100px; display: inline-block; color: #6c757d;" 
                      placeholder="Masukkan No WA">{{ $item->no_wa }}</span>
                
                @php $waNumber = preg_replace('/^0/', '62', $item->no_wa); @endphp
                <a href="{{ $item->no_wa ? 'https://wa.me/'.$waNumber : '#' }}" 
                   target="{{ $item->no_wa ? '_blank' : '' }}" 
                   class="btn btn-success btn-sm wa-button ms-2 {{ !$item->no_wa ? 'disabled opacity-50' : '' }}" 
                   style="padding: 0px 6px; line-height: 1.2; border-radius: 4px;" 
                   title="{{ $item->no_wa ? 'Chat WhatsApp' : 'No WA Belum Diisi' }}">
                    <i class="fa-brands fa-whatsapp" style="color:#fff; font-size: 0.9rem; vertical-align: middle;"></i>
                </a>
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



    
    @if(strtolower(auth()->user()->role) !== 'administrator'  && Auth::user()->role !== 'marketing')
    <td>
        <button type="button" 
                class="btn btn-sm btn-primary btn-move-salesplan" 
                data-id="{{ $item->id }}" 
                data-nama="{{ $item->nama }}"
                title="Pindahkan ke Sales Plan">
            <i class="fa fa-arrow-right"></i>
        </button>
    </td>
    @endif

    @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) || auth()->user()->name === 'Agus Setyo')
    <td>{{ $item->created_by }}</td>
    
    {{-- Role Column --}}
    @if(strtolower(auth()->user()->role) !== 'administrator')
        <td>{{ $item->created_by_role }}</td>
    @endif
    @endif
    
    @if(strtolower(auth()->user()->role) !== 'administrator')
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
