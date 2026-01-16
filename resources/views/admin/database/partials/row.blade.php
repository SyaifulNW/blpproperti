   <tr 
    data-created-by="{{ strtolower($item->created_by) }}"
    data-bulan="{{ \Carbon\Carbon::parse($item->created_at)->month }}"
    data-year="{{ \Carbon\Carbon::parse($item->created_at)->year }}"
        data-kota="{{ $item->kota_nama }}"
    data-id="{{ $item->id }}"
>

    <td>{{ $loop->iteration ?? '-' }}</td>
    
    @php
        $userRole = strtolower(auth()->user()->role);
        $isCs = in_array($userRole, ['cs', 'cs-mbc', 'cs-smi', 'customer_service']);
    @endphp

    @if($userRole === 'administrator')
        {{-- === LAYOUT ADMINISTRATOR === --}}
        
        {{-- Merged: Nama + WA + CTA --}}
        <td>
            <div class="d-flex flex-column">
                <div contenteditable="true" class="editable fw-bold text-dark mb-1" data-field="nama" style="font-size: 0.95rem;">{{ $item->nama }}</div>
                
                  <div class="d-flex align-items-center text-muted small">
                    <span contenteditable="true" class="editable" data-field="no_wa">{{ $item->no_wa }}</span>
                    
                    @if($item->no_wa)
                        @php $waNumber = preg_replace('/^0/', '62', $item->no_wa); @endphp
                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="btn btn-success btn-sm wa-button ms-1" style="padding: 0px 5px; line-height: 1.2;" title="Chat WhatsApp">
                            <i class="bi bi-whatsapp" style="color:#fff; font-size: 0.9rem; vertical-align: middle;"></i>
                        </a>
                    @endif
                </div>
            </div>
        </td>
    
        {{-- Sumber Leads (Simple) --}}
        <td>
             <select class="form-control form-control-sm select-inline" data-id="{{ $item->id }}" data-field="leads">
                <option value="">- Pilih -</option>
                <option value="Marketing" {{ $item->leads == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                <option value="Iklan" {{ $item->leads == 'Iklan' ? 'selected' : '' }}>Iklan</option>
                <option value="Alumni" {{ $item->leads == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                <option value="Mandiri" {{ $item->leads == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
            </select>
        </td>

        {{-- Kota (Simple) --}}
        <!--<td>-->
        <!--    <select class="form-control form-control-sm select-kota" data-id="{{ $item->id }}" data-prov-id="{{ $item->provinsi_id }}" data-nama="{{ $item->kota_nama }}">-->
        <!--         <option value="">{{ $item->kota_nama ?: '-- Pilih Kota --' }}</option>-->
        <!--    </select>-->
        <!--</td>-->




    @else
        {{-- === LAYOUT NON-ADMIN (CS, Manager, Marketing, etc) === --}}

        {{-- Nama --}}
        <td contenteditable="true" class="editable" data-field="nama">{{ $item->nama }}</td>
        
        {{-- Sumber Leads --}}
        <td>
             <select class="form-control form-control-sm select-inline" data-id="{{ $item->id }}" data-field="leads">
                <option value="">- Pilih Sumber Leads -</option>
                <option value="Marketing" {{ $item->leads == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                <option value="Iklan" {{ $item->leads == 'Iklan' ? 'selected' : '' }}>Iklan</option>
                <option value="Alumni" {{ $item->leads == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                <option value="Mandiri" {{ $item->leads == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
            </select>
        </td>


        
        {{-- WA & CTA Terpisah --}}
        <td contenteditable="true" class="editable" data-field="no_wa">{{ $item->no_wa }}</td>
        <td>
            @php $waNumber = preg_replace('/^0/', '62', $item->no_wa); @endphp
            <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="btn btn-success btn-sm wa-button">
                <i class="bi bi-whatsapp" style="color:#fff;font-size:1.5rem;"></i>
            </a>
        </td>

    @endif

    {{-- Common Columns --}}


    

    @if(strtolower(auth()->user()->role) !== 'marketing')
    <td>
        <select class="form-control form-control-sm select-inline" data-id="{{ $item->id }}" data-field="kelas_id">
            <option value="">- Pilih Kelas -</option>
            @foreach($kelas as $k)
            <option value="{{ $k->id }}" {{ $item->kelas_id == $k->id ? 'selected' : '' }}>
                {{ $k->nama_kelas }}
            </option>
            @endforeach
        </select>
    </td>

    {{-- Berhasil Spin --}}
    <td>
        <select class="form-control form-control-sm select-inline" data-id="{{ $item->id }}" data-field="berhasil_spin">
            <option value="">- Pilih -</option>
            <option value="Ya" {{ $item->berhasil_spin == 'Ya' ? 'selected' : '' }}>Ya</option>
            <option value="Tidak" {{ $item->berhasil_spin == 'Tidak' ? 'selected' : '' }}>Tidak</option>
        </select>
    </td>

    {{-- Ikut Zoom --}}
    <td>
        <select class="form-control form-control-sm select-inline" data-id="{{ $item->id }}" data-field="ikut_zoom">
            <option value="">- Pilih -</option>
            <option value="Ya" {{ $item->ikut_zoom == 'Ya' ? 'selected' : '' }}>Ya</option>
            <option value="Tidak" {{ $item->ikut_zoom == 'Tidak' ? 'selected' : '' }}>Tidak</option>
        </select>
    </td>
    @endif
    
    @if(strtolower(auth()->user()->role) !== 'administrator'  && Auth::user()->role !== 'marketing')
    <td>
        <form action="{{ route('data.pindahKeSalesPlan', $item->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-arrow-right"></i></button>
        </form>
    </td>
    @endif

    @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) || auth()->user()->name === 'Agus Setyo')
    <td>{{ $item->created_by }}</td>
    
    {{-- Role Column: Hidden for Administrator (Rule 5), Shown for Others --}}
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
