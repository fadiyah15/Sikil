@extends('adminlte::page')
@section('title', ' Perizinan')
@section('content_header')
<h1 class="m-0 text-dark"> Perizinan</h1>
@stop
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('ajuanperizinan.index') }}" method="GET" class="form-inline mb-3">
                    
                    <div class="form-group mb-2">    
                        <label for="kode_finger" >Nama Pegawai :</label> &nbsp;
                        <select class="form-control" name="kode_finger" id="kode_finger" class="form-select" >
                            <option value="all">Semua Pegawai</option>
                            @foreach ($users as $user)
                            <option value="{{ $user->kode_finger }}" @if(request()->input('kode_finger') == $user->kode_finger) selected @endif>{{ $user->nama_pegawai }}</option>
                            @endforeach
                        </select>&nbsp;&nbsp;&nbsp;&nbsp;
                    </div> 
                    <br>
                    <div class="form-group mb-2 ">
                        <label for="jenis_perizinan" >Jenis Perizinan :</label>&nbsp;
                        <select class="form-control" id="jenis_perizinan" name="jenis_perizinan">
                            <option value="all">Semua Jenis Perizinan</option>
                            <option value="I" @if(request()->input('jenis_perizinan')=='I' )selected
                                @endif>Izin</option>
                            <option value="DL" @if(request()->input('jenis_perizinan')=='DL' )selected
                                @endif>Dinas Luar</option>
                            <option value="S" @if(request()->input('jenis_perizinan')=='S' )selected
                                @endif>Sakit</option>
                            <option value="CS" @if(request()->input('jenis_perizinan')=='CS' )selected
                                @endif>Cuti Sakit</option>
                            <option value="Prajab" @if(request()->input('jenis_perizinan')=='Prajab'
                                )selected @endif>Prajab</option>
                            <option value="CT" @if(request()->input('jenis_perizinan')=='CT' )selected
                                @endif>Cuti Tahunan</option>
                            <option value="CM" @if(request()->input('jenis_perizinan')=='CM' )selected
                                @endif>Cuti Melahirkan</option>
                            <option value="CAP" @if(request()->input('jenis_perizinan')=='CAP' )selected
                                @endif>CAP</option>
                            <option value="CH" @if(request()->input('jenis_perizinan')=='CH' )selected
                                @endif>Cuti Haji</option>
                            <option value="CB" @if(request()->input('jenis_perizinan')=='CB' )selected
                                @endif>Cuti Bersama</option>
                            <option value="A" @if(request()->input('jenis_perizinan')=='A' )selected
                                @endif>Alpha</option>
                            <option value="TB" @if(request()->input('jenis_perizinan')=='TB' )selected
                                @endif>Tugas Belajar</option>
                        </select>

                    </div>
                    <div class="form-group mb-2">
                        <label for="tgl_absen_awal" class="my-label mr-2">Tanggal Awal: </label>
                        <input type="date" id="tgl_absen_awal" name="tgl_absen_awal" required class="form-control" value="{{request()->input('tgl_absen_awal')}}">&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                        <label for="tgl_absen_akhir" class="form-label">Tanggal Akhir: </label>&nbsp;&nbsp;
                        <input type="date" id="tgl_absen_akhir" name="tgl_absen_akhir" required class="form-control" value="{{request()->input('tgl_absen_akhir')}}"> &nbsp;&nbsp;&nbsp;
                        <button type="submit" class="btn btn-primary"> Tampilkan</button>
                    </div>  

                </form>
                @can('isAdmin')
                <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#modal_form"
                    role="dialog">
                    Tambah
                </button>
                @endcan
                                                    
                            
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Pegawai</th>
                                <th>Jenis Ajuan</th>
                                <th>Tanggal Ajuan</th>
                                <th>Tanggal Pelaksanaan</th>
                                <th>Keterangan</th>
                                <th>Lampiran</th>
                                <th>Persetujuan Atasan</th>
                                @if(auth()->user()->level=='ppk' or auth()->user()->level=='admin')
                                <th>Persetujuan PPK</th>
                                @endif
                                <th>Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ajuanperizinan as $key => $ap)
                            <tr>
                                <td id={{$key+1}}>{{$key+1}}</td>
                                <td id={{$key+1}}>{{$ap->user->nama_pegawai}}</td>
                                <td id={{$key+1}}>{{$ap->jenis_perizinan}}</td>
                                <td id={{$key+1}}>{{date_format( new DateTime($ap->tgl_ajuan), 'd F Y')}}</td>
                                <td id={{$key+1}}>{{date_format( new DateTime($ap->tgl_absen_awal), 'd F Y')}} s.d.
                                    {{ date_format( new DateTime($ap->tgl_absen_akhir), 'd F Y')}}</td>
                                <td id={{$key+1}}>{{$ap->keterangan}}</td>
                                <td id="{{ $key + 1 }}" style="text-align: center; vertical-align: middle;">
                                    <a href="{{ asset('storage/file_perizinan/' . $ap->file_perizinan) }}" download>
                                        <i class="fas fa-download" style="display: inline-block; line-height: normal; vertical-align: middle;"></i>
                                    </a>
                                </td>
                                
                                <td id={{$key+1}}>
                                    @if($ap->id_atasan == auth()->user()->id_users or auth()->user()->level=='admin')

                                        @if ($ap->status_izin_atasan === null)
                                        Menunggu Persetujuan
                                        @elseif ($ap->status_izin_atasan === '1')
                                        Disetujui
                                        @else
                                        Ditolak
                                        @endif
                                    @else
                                        <b>Bukan Atasan Persetujuan</b>
                                    @endif

                                </td>
                                <td id="{{ $key + 1 }}">
                                    @if (auth()->user()->level == 'ppk' || auth()->user()->level == 'admin')
                                        @if ($ap->jenis_perizinan === 'I')
                                        <b>Bukan PPK Persetujuan</b>
                                        @elseif ($ap->status_izin_ppk === null)
                                            Menunggu Persetujuan
                                        @elseif ($ap->status_izin_ppk === '1')
                                            Disetujui
                                        @else
                                            Ditolak
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="#" class="btn btn-primary btn-xs edit-button" data-toggle="modal"
                                            data-target="#editModal{{$ap->id_perizinan}}" data-id="{{$ap->id_perizinan}}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if(auth()->user()->level=='admin' )
                                        <a href="{{ route('ajuanperizinan' . '.destroy', $ap->id_perizinan) }}"
                                            onclick="notificationBeforeDelete(event, this, {{$key+1}})" class="btn btn-danger btn-xs mx-1">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <div class="modal fade" id="editModal{{$ap->id_perizinan}}" tabindex="-1" role="dialog"
                                aria-labelledby="editModalLabel{{$ap->id_perizinan}}" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel">Edit Diklat</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('ajuanperizinan.update', $ap->id_perizinan) }}"
                                                enctype="multipart/form-data" method="post">
                                                @csrf
                                                @method('PUT')
                                                @can('isAdmin')
                                                <div class="form-group">
                                                    <label for="tgl_absen_awal" class='form-label'>Tanggal Awal
                                                        Izin</label>
                                                    <div class="form-input">
                                                        <input type="date" class="form-control"
                                                            class="form-control @error('tgl_absen_awal') is-invalid @enderror"
                                                            id="tgl_absen_awal" placeholder="Nama Diklat"
                                                            name="tgl_absen_awal"
                                                            value="{{$ap -> tgl_absen_awal ?? old('tgl_absen_awal')}}">
                                                        @error('tgl_absen_awal') <span
                                                            class="textdanger">{{$message}}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="tgl_absen_akhir" class='form-label'>Tanggal Akhir
                                                        Izin</label>
                                                    <div class="form-input">
                                                        <input type="date" class="form-control"
                                                            class="form-control @error('tgl_absen_akhir') is-invalid @enderror"
                                                            id="tgl_absen_akhir" placeholder="Nama Diklat"
                                                            name="tgl_absen_akhir"
                                                            value="{{$ap -> tgl_absen_akhir ?? old('tgl_absen_akhir')}}">
                                                        @error('tgl_absen_akhir') <span
                                                            class="textdanger">{{$message}}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="jenis_perizinan">Jenis Perizinan</label>
                                                    <select
                                                        class="form-control  @error('jenis_perizinan') is-invalid @enderror"
                                                        id="jenis_perizinan" name="jenis_perizinan">
                                                        <option value="I" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='I')selected
                                                            @endif>Izin</option>
                                                        <option value="DL" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='DL' )selected
                                                            @endif>Dinas Luar</option>
                                                        <option value="S" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='S' )selected
                                                            @endif>Sakit</option>
                                                        <option value="CS" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='CS' )selected
                                                            @endif>Cuti Sakit</option>
                                                        <option value="Prajab" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='Prajab'
                                                            )selected @endif>Prajab</option>
                                                        <option value="CT" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='CT' )selected
                                                            @endif>Cuti Tahunan</option>
                                                        <option value="CM" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='CM' )selected
                                                            @endif>Cuti Melahirkan</option>
                                                        <option value="CAP" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='CAP' )selected
                                                            @endif>CAP</option>
                                                        <option value="CH" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='CH' )selected
                                                            @endif>Cuti Haji</option>
                                                        <option value="CB" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='CB' )selected
                                                            @endif>Cuti Bersama</option>
                                                        <option value="A" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='A' )selected
                                                            @endif>Alpha</option>
                                                        <option value="TB" @if(old('jenis_perizinan', $ap->jenis_perizinan)=='TB' )selected
                                                            @endif>Tugas Belajar</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="keterangan" class="form-label">Keterangan</label>
                                                    <textarea rows="5"
                                                        class="form-control"
                                                        id="keterangan"
                                                        name="keterangan">{{$ap -> keterangan ?? old('keterangan')}}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label class="id_atasan" for="id_atasan">Atasan Langsung</label>
                                                    <select id="id_atasan" name="id_atasan"
                                                        class="form-control @error('id_atasan') is-invalid @enderror">
                                                        @foreach ($users as $us)
                                                        <option value="{{ $us->id_users }}" @if( $ap->id_atasan === old('id_atasan', $us->id_users) ) selected @endif>
                                                            {{ $us->nama_pegawai }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="file_perizinan">Unggah Lampiran</label>
                                                    @if ($ap->file_perizinan)
                                                    <p>Previous File: <a
                                                            href="{{ asset('/storage/file_perizinan/' . $ap->file_perizinan) }}"
                                                            target="_blank">{{ $ap->file_perizinan }}</a></p>
                                                    @endif
                                                    <small class="form-text text-muted">Allow file extensions :.jpeg .jpg .png .pdf .docx
                                                        .docx</small>
                                                    <input type="file" class="form-control" id="file_perizinan"
                                                        name="file_perizinan"
                                                        @error('file_perizinan') <span class="invalid"
                                                        role="alert">{{$message}}</span>
                                                    @enderror
                                                </div>
                                                @endcan
                                                @if($ap->id_atasan == auth()->user()->id_users or auth()->user()->level=='admin')

                                                <div class="form-group">
                                                    <label for="status_izin_atasan">Persetujuan Atasan</label>
                                                    <div class="input">
                                                        <input type="radio" name="status_izin_atasan" value="1" @if ($ap->status_izin_atasan === '1') checked @endif>  Disetujui<br>
                                                        <input type="radio" name="status_izin_atasan" value="0" @if ($ap->status_izin_atasan === '0') checked @endif> Ditolak<br> 
                                                    </div>
                                                </div>
                                                <div id="alasan_ditolak_atasan" style="display: none;" class="form-group">
                                                    <label for="alasan_ditolak_atasan">Alasan Ditolak</label>
                                                    <textarea name="alasan_ditolak_atasan" id="alasan_ditolak_atasan" cols="30" rows="3" class="form-control">{{ $ap->alasan_ditolak_atasan }}</textarea>
                                                </div>

                                                @endif
                                                @if(auth()->user()->level === 'admin' || auth()->user()->level === 'ppk')
                                                @if ($ap->jenis_perizinan !== 'I')
                                                <div class="form-group">
                                                    <label for="status_izin_ppk">Persetujuan PPK</label>
                                                    <div class="input">
                                                        <input type="radio" name="status_izin_ppk" value="1" @if ($ap->status_izin_ppk === '1') checked @endif>
                                                        Disetujui<br>
                                                        <input type="radio" name="status_izin_ppk" value="0" @if ($ap->status_izin_ppk === '0') checked @endif>
                                                        Ditolak<br>
                                                    </div>
                                                </div>
                                                @endif
                                                <div id="alasan_ditolak_ppk" style="display: none;" class="form-group">
                                                    <label for="alasan_ditolak_ppk">Alasan Ditolak</label>
                                                    <textarea name="alasan_ditolak_ppk" id="alasan_ditolak_ppk"
                                                        cols="30" rows="3" class="form-control">{{ $ap->alasan_ditolak_ppk }}</textarea>
                                                </div>
                                                @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                            <a href="{{route('ajuanperizinan.index')}}" class="btn btn-danger">
                                                Batal
                                            </a>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                </div>
                @endforeach
                <!-- Edit modal -->
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<!-- Modal -->
<!-- Bootstrap modal Create -->
<div class="modal fade" id="modal_form" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Tambah Perizinan</h4>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('ajuanperizinan.store') }}" method="POST" id="form" class="form-horizontal"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-6" for="kode_finger">Nama Pegawai</label>
                                    <select id="kode_finger" name="kode_finger" class="form-control ">
                                        @foreach ($users as $us)
                                        <option value="{{ $us->kode_finger }}" @if( old('kode_finger')==$us->id_users
                                            )selected @endif>
                                            {{ $us->nama_pegawai }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tgl_absen_awal">Tanggal Awal Izin</label>
                                <input type="date" class="form-control" id="tgl_absen_awal" name="tgl_absen_awal"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="tgl_absen_akhir">Tanggal Akhir Izin</label>
                                <input type="date" class="form-control" id="tgl_absen_akhir" name="tgl_absen_akhir"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="jenis_perizinan">Jenis Perizinan</label>
                                <select class="form-control  @error('jenis_perizinan') is-invalid @enderror"
                                    id="jenis_perizinan" name="jenis_perizinan">
                                    <option value="I">Izin</option>
                                    <option value="DL">Dinas Luar</option>
                                    <option value="S">Sakit</option>
                                    <option value="CS">Cuti Sakit</option>
                                    <option value="Prajab">Prajab</option>
                                    <option value="CT">Cuti Tahunan</option>
                                    <option value="CM">Cuti Melahirkan</option>
                                    <option value="CAP">CAP</option>
                                    <option value="CH">Cuti Haji</option>
                                    <option value="CB">Cuti Bersama</option>
                                    <option value="A">Alpha</option>
                                    <option value="TB">Tugas Belajar</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea rows="5" class="form-control" id="keterangan" name="keterangan"
                                    required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="id_atasan">Atasan Langsung</label>
                                <select id="id_atasan" name="id_atasan" class="form-control ">
                                    @foreach ($users as $us)
                                    <option value="{{ $us->id_users }}" @if( old('id_users')==$us->
                                        id_users )selected
                                        @endif>
                                        {{ $us->nama_pegawai }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- <div class="form-group">
                                <label for="ppk">PPK</label>
                                <input type="text" class="form-control" id="ppk" name="ppk" required>
                            </div>
 -->
                            <div class="form-group">
                                <label for="file_perizinan">Unggah Lampiran</label>
                                <small class="form-text text-muted">Allow file extensions :
                                    .jpeg .jpg .png .pdf .docx</small>
                                <input type="file" class="form-control" id="file_perizinan"
                                    enctype="multipart/form-data" name="file_perizinan" @error('file_perizinan') <span
                                    class="invalid" role="alert">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@stop



@push('js')
<form action="" id="delete-form" method="post">
    @method('delete')
    @csrf
</form>
<script>

document.querySelectorAll('input[type=radio][name=status_izin_atasan]').forEach(input => input.addEventListener('change', function() {
    if (this.value == '0') {
        this.parentNode.parentNode.parentNode.querySelector('#alasan_ditolak_atasan').style.display = 'block';
    } else {
        this.parentNode.parentNode.parentNode.querySelector('#alasan_ditolak_atasan').style.display = 'none';
    }
}));
document.querySelectorAll('input[type=radio][name=status_izin_ppk]').forEach(input => input.addEventListener('change', function() {
    if (this.value == '0') {
        this.parentNode.parentNode.parentNode.querySelector('#alasan_ditolak_ppk').style.display = 'block';
    } else {
        this.parentNode.parentNode.parentNode.querySelector('#alasan_ditolak_ppk').style.display = 'none';
    }
}));
</script>
<script>
$('#example2').DataTable({
    "responsive": true,
});
</script>
@endpush