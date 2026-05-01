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
                <a class="nav-link" id="kelas-tab" data-toggle="tab" href="#kelas" role="tab">Manajemen Produk</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reward-tab" data-toggle="tab" href="#reward" role="tab">Setting Reward</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="leads-tab" data-toggle="tab" href="#leads" role="tab">Sumber Leads</a>
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

            {{-- TAB 2: MANAJEMEN PRODUK --}}
            <div class="tab-pane fade p-3 bg-white border border-top-0" id="kelas" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Daftar Produk</h5>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambahKelas">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Nama Produk</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelas as $index => $k)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold text-dark">{{ $k->nama_kelas }}</td>
                                    <td class="text-left">{{ $k->deskripsi ?? '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit-kelas" 
                                            data-id="{{ $k->id }}"
                                            data-nama="{{ $k->nama_kelas }}"
                                            data-deskripsi="{{ $k->deskripsi }}"
                                            data-toggle="modal" 
                                            data-target="#modalEditKelas">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted italic">Belum ada data produk</td>
                                </tr>
                            @endforelse
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

        {{-- TAB 6: SUMBER LEADS --}}
        <div class="tab-pane fade p-3 bg-white border border-top-0" id="leads" role="tabpanel">
            <div class="row">
                <div class="col-md-5">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white font-weight-bold">
                            <i class="fas fa-plus-circle mr-1"></i> Tambah Sumber Leads
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.lead-sources.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="font-weight-bold">Nama Sumber Leads</label>
                                    <input type="text" name="name" class="form-control" placeholder="Contoh: TikTok, Instagram, dsb" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-block mt-3">
                                    <i class="fas fa-save mr-1"></i> Simpan Sumber Leads
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white font-weight-bold">
                            <i class="fas fa-list mr-1"></i> Daftar Sumber Leads
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-3">No</th>
                                            <th>Nama Sumber</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($leadSources as $index => $ls)
                                            <tr>
                                                <td class="px-3">{{ $index + 1 }}</td>
                                                <td class="fw-bold">{{ $ls->name }}</td>
                                                <td class="text-center">
                                                    <form action="{{ route('admin.settings.lead-sources.destroy', $ls->id) }}" method="POST" onsubmit="return confirm('Hapus sumber leads ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted italic">Belum ada data sumber leads</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>

    {{-- Modal Tambah Kelas --}}
    <div class="modal fade" id="modalTambahKelas" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" action="{{ route('admin.kelas.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Tambah Produk</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Produk</label>
                            <input type="text" name="nama_kelas" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Kelas --}}
    <div class="modal fade" id="modalEditKelas" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form method="POST" id="formEditKelas">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit Produk</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Produk</label>
                            <input type="text" name="nama_kelas" id="edit_nama_kelas" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Deskripsi</label>
                            <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-save mr-1"></i> Update Produk</button>
                    </div>
                </div>
            </form>
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

        // Script Edit Kelas
        document.querySelectorAll('.btn-edit-kelas').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                document.getElementById('edit_nama_kelas').value = this.dataset.nama;
                document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
                document.getElementById('formEditKelas').action = `/admin/kelas/${id}`;
            });
        });
    </script>
@endsection