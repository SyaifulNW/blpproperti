@extends('layouts.masteradmin')

@section('content')
    <div class="container-fluid">
        <h3 class="fw-bold mb-4">Pengaturan Administrator</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div>
        @endif

        <ul class="nav nav-tabs" id="settingTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="users-tab" data-toggle="tab" href="#users" role="tab">Users & Roles</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="target-tab" data-toggle="tab" href="#target" role="tab">Target Omset</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="menus-tab" data-toggle="tab" href="#menus" role="tab">Menu Global</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rolemenu-tab" data-toggle="tab" href="#rolemenu" role="tab">Akses Menu Role</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reward-tab" data-toggle="tab" href="#reward" role="tab">Setting Reward</a>
            </li>
        </ul>

        <div class="tab-content" id="settingTabContent">

            {{-- TAB 1: USER MANAGEMENT --}}
            <div class="tab-pane fade show active p-3 bg-white border border-top-0" id="users" role="tabpanel">
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addUserModal">
                    <i class="fas fa-plus"></i> Tambah User Baru
                </button>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $u)
                                <tr>
                                    <td>{{ $u->id }}</td>
                                    <td>{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td><span class="badge badge-info">{{ ucfirst($u->role) }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#editUserModal{{ $u->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.settings.users.destroy', $u->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Yakin hapus user ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit User - {{ $u->name }}</h5>
                                                <button type="button" class="close"
                                                    data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <form action="{{ route('admin.settings.users.update', $u->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Nama</label>
                                                        <input type="text" name="name" class="form-control"
                                                            value="{{ $u->name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email" name="email" class="form-control"
                                                            value="{{ $u->email }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Role</label>
                                                        <select name="role" class="form-control" required>
                                                            @foreach(['administrator', 'marketing', 'cs-smi', 'manager', 'hrd', 'user'] as $r)
                                                                <option value="{{ $r }}" {{ $u->role == $r ? 'selected' : '' }}>
                                                                    {{ ucfirst($r) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Password (Kosongkan jika tidak diganti)</label>
                                                        <input type="password" name="password" class="form-control"
                                                            placeholder="Min. 6 karakter">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 2: TARGET OMSET --}}
            <div class="tab-pane fade p-3 bg-white border border-top-0" id="target" role="tabpanel">
                <form action="{{ route('admin.settings.target.update') }}" method="POST" class="col-md-6">
                    @csrf
                    <div class="form-group">
                        <label class="fw-bold">Target Omset Saat Ini (Rp)</label>
                        <input type="number" name="target_omset" class="form-control" value="{{ $targetOmset }}" required>
                        <small class="text-muted">Target ini akan digunakan untuk perhitungan bonus semua CS secara default
                            kecuali diatur lain.</small>
                    </div>

                    <div class="form-group mt-3">
                        <label class="fw-bold">Target Omset Start-Up Muda Indonesia (Rp)</label>
                        <input type="number" name="target_omset_smi" class="form-control" value="{{ $targetOmsetSmi ?? 0 }}"
                            required>
                        <small class="text-muted">Target khusus untuk Start-Up Muda Indonesia (SMI).</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Target</button>
                </form>
            </div>

            {{-- TAB 3: MENUS MANAGEMENT (GLOBAL) --}}
            <div class="tab-pane fade p-3 bg-white border border-top-0" id="menus" role="tabpanel">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Pengaturan ini akan mengaktifkan/menonaktifkan menu secara
                    <strong>GLOBAL</strong> untuk semua user.
                </div>
                <table class="table table-bordered">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Label Menu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $m)
                            <tr>
                                <td>{{ $m->label }}</td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input menu-toggle" id="switch{{ $m->id }}"
                                            data-id="{{ $m->id }}" {{ $m->is_active ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="switch{{ $m->id }}">
                                            {{ $m->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAB 4: ROLE MENU ACCESS --}}
            <div class="tab-pane fade p-3 bg-white border border-top-0" id="rolemenu" role="tabpanel">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Atur akses menu spesifik per Role. Jika Menu Global
                    non-aktif, maka menu tetap tidak muncul meski di sini aktif.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-left">Role / Menu</th>
                                @foreach($menus as $m)
                                    <th>{{ $m->label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td class="text-left font-weight-bold">{{ ucfirst($role) }}</td>
                                    @foreach($menus as $m)
                                        @php
                                            $canAccess = \App\Models\Menu::hasRoleAccess($m->name, $role);
                                        @endphp
                                        <td>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input role-menu-toggle"
                                                    id="role-{{ $role }}-{{ $m->id }}" data-role="{{ $role }}"
                                                    data-menuid="{{ $m->id }}" {{ $canAccess ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="role-{{ $role }}-{{ $m->id }}"></label>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>

        {{-- TAB 5: SETTING REWARD --}}
        <div class="tab-pane fade p-3 bg-white border border-top-0" id="reward" role="tabpanel">
            <form action="{{ route('admin.settings.reward.update') }}" method="POST" class="col-md-7">
                @csrf
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white font-weight-bold">
                        <i class="fas fa-trophy mr-1"></i> Reward Bulanan & Konsistensi
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Bonus Konsistensi 3 Bulan (Rp)</label>
                            <input type="number" name="bonus_3_bulanan_amount" class="form-control"
                                value="{{ $bonus3BulananAmount }}" required>
                            <small class="text-muted">Bonus yang diberikan jika sales mencapai target 1.25M selama 3 bulan
                                berturut-turut.</small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white font-weight-bold">
                        <i class="fas fa-motorcycle mr-1"></i> Reward Tahunan (Apresiasi)
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Target Omset Tahunan (Rp)</label>
                            <input type="number" name="target_omset_tahunan" class="form-control"
                                value="{{ $targetOmsetTahunan }}" required>
                            <small class="text-muted">Target akumulasi omset selama 1 tahun untuk mendapatkan hadiah utama
                                (Default: 12 Miliar).</small>
                        </div>
                        <div class="form-group mt-3">
                            <label class="font-weight-bold">Nama Hadiah / Reward Tahunan</label>
                            <input type="text" name="reward_tahunan_nama" class="form-control"
                                value="{{ $rewardTahunanNama }}" required placeholder="Contoh: Motor Yamaha NMAX">
                            <small class="text-muted">Nama barang atau reward yang ditampilkan di dashboard sales.</small>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-lg btn-success">
                        <i class="fas fa-save mr-1"></i> Simpan Pengaturan Reward
                    </button>
                </div>
            </form>
        </div>

    </div>
    </div>

    {{-- Add User Modal --}}
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User Baru</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('admin.settings.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="user">User</option>
                                <option value="marketing">Marketing</option>
                                <option value="cs-smi">CS SMI</option>
                                <option value="manager">Manager</option>
                                <option value="hrd">HRD</option>
                                <option value="administrator">Administrator</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan User</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // AJAX Toggle Menu Global
        document.querySelectorAll('.menu-toggle').forEach(item => {
            item.addEventListener('change', event => {
                const id = event.target.dataset.id;
                const active = event.target.checked ? 1 : 0;
                const label = event.target.nextElementSibling;

                label.textContent = active ? 'Aktif' : 'Non-Aktif';

                fetch('{{ route('admin.settings.menus.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id: id, active: active })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) alert('Gagal mengubah status menu');
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

        // AJAX Toggle Role Menu Access
        document.querySelectorAll('.role-menu-toggle').forEach(item => {
            item.addEventListener('change', event => {
                const role = event.target.dataset.role;
                const menu_id = event.target.dataset.menuid;
                const active = event.target.checked ? 1 : 0;

                console.log(`Updating role ${role} menu ${menu_id} access to ${active}`);

                fetch('{{ route('admin.settings.role-menus.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ role: role, menu_id: menu_id, active: active })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Update success:', data);
                        if (!data.success) alert('Gagal mengubah akses menu role');
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
@endsection