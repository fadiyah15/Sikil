<?php

namespace App\Http\Controllers;

use App\Models\Perizinan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AjuanPerizinanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->input('tgl_absen_awal') == null && $request->input('tgl_absen_akhir') == null){
            $user = Auth::user();
            if ($user->level == 'admin' or $user->level == 'ppk' ) {
                $ajuanperizinan = Perizinan::where('is_deleted', '0')->get();
            }elseif( $user->level == 'bod' or $user->level == 'kadiv'){
                $ajuanperizinan = Perizinan::where('is_deleted', '0')->where('id_atasan', auth()->user()->id_users)->get();
            }
        }else{
            $user = Auth::user();
            if ($user->level == 'admin' or $user->level == 'ppk' ) {
                $ajuanperizinan = Perizinan::where('is_deleted', '0')->where('tgl_absen_awal', '>=', $request->input('tgl_absen_awal'))->where('tgl_absen_akhir', '<=', $request->input('tgl_absen_akhir'))->get();
            }elseif( $user->level == 'bod' or $user->level == 'kadiv'){
                $ajuanperizinan = Perizinan::where('is_deleted', '0')->where('id_atasan', auth()->user()->id_users)->where('tgl_absen_awal', '>=', $request->tgl_absen_awal)->where('tgl_absen_akhir', '<=', $request->tgl_absen_akhir)->get();
            }
        }

        if($request->input('kode_finger') != null){
            if($request->input('kode_finger') != 'all'){
                $ajuanperizinan = $ajuanperizinan->where('kode_finger', '=' , $request->input('kode_finger'));
            }
        }

        if($request->input('jenis_perizinan') != null){
            if($request->input('jenis_perizinan') != 'all'){
                $ajuanperizinan = $ajuanperizinan->where('jenis_perizinan', '=' , $request->input('jenis_perizinan'));
            }
        }


        return view('izin.index', [
            'ajuanperizinan' => $ajuanperizinan,
            'users' => User::where('is_deleted', '0')->get(),
            'settingperizinan' => User::with(['setting'])->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'id_atasan' => 'required',
            'kode_finger' => 'required',
            'jenis_perizinan' => 'required',
            'tgl_absen_awal' => 'required',
            'tgl_absen_akhir' => 'required',
            'keterangan' => 'required',
            'file_perizinan' => 'mimes:pdf,doc,docx,png,jpg,jpeg',
        ]);
        
        
        $ajuanperizinan = new Perizinan();   
        if ($request->jenis_perizinan === 'CT') {
            // Menggunakan kode_finger untuk mencari pengguna dalam tabel Perizinan
            $perizinanUser = User::with('cuti')->where('kode_finger', $request->kode_finger)->first();
        
            if ($perizinanUser) {
                if ($perizinanUser->cuti == null) {
                    return redirect()->back()->with('error', 'Anda belum memiliki cuti tahunan.');
                }
                
                // Hitung jumlah hari pengajuan cuti menggunakan fungsi hitungJumlahHariPengajuan
                $jumlah_hari_pengajuan = $ajuanperizinan->hitungJumlahHariPengajuan(
                    $request->tgl_absen_awal,
                    $request->tgl_absen_akhir
                );
        
                // Check if the user has enough jatah cuti tahunan
                $jatahCutiTahunan = $perizinanUser->cuti->jatah_cuti;
        
                if ($jatahCutiTahunan < $jumlah_hari_pengajuan) {
                    return redirect()->back()->with('error', 'Anda tidak memiliki jatah cuti tahunan yang cukup.');
                }
        
                // Update the user's jatah_cuti in the cuti record
                if ($perizinanUser->cuti) {
                    $perizinanUser->cuti->jatah_cuti -= $jumlah_hari_pengajuan;
                    if ($perizinanUser->cuti->save()) {
                        // Proceed with creating Perizinan record
                    } else {
                        return redirect()->back()->with('error', 'Gagal mengurangi jatah cuti pengguna.');
                    }
                } else {
                    return redirect()->back()->with('error', 'Tidak ada data cuti yang sesuai.');
                }
            } else {
                return redirect()->back()->with('error', 'Pengguna dengan kode finger tersebut tidak ditemukan.');
            }
        }
        
        if ($request->hasFile('file_perizinan')) {
            // Upload dan simpan file jika ada
            $file = $request->file('file_perizinan');
            $allowedExtensions = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];
            $fileExtension = $file->getClientOriginalExtension();
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                return redirect()->back()->with('error', 'Tipe data file tidak sesuai.');
            }
            
            $fileName = Str::random(20) . '.' . $fileExtension;
            $file->storeAs('file_perizinan', $fileName, 'public');
            
            // Update the file_perizinan attribute in the $perizinan object
            $ajuanperizinan->file_perizinan = $fileName;
        } else {
            // If no file is uploaded, set the file_perizinan attribute to NULL
            $ajuanperizinan->file_perizinan = null;
        }
        
        $ajuanperizinan->id_atasan = $request->id_atasan;
        $ajuanperizinan->kode_finger = $request->kode_finger;
        $ajuanperizinan->jenis_perizinan = $request->jenis_perizinan;
        $ajuanperizinan->tgl_ajuan = $request->tgl_ajuan;
        $ajuanperizinan->tgl_absen_awal = $request->tgl_absen_awal;
        $ajuanperizinan->tgl_absen_akhir = $request->tgl_absen_akhir;
        $jumlah_hari_pengajuan = $ajuanperizinan->hitungJumlahHariPengajuan(
            $request->tgl_absen_awal,
            $request->tgl_absen_akhir
        );
        $ajuanperizinan->keterangan = $request->keterangan;
        // $ajuanperizinan->file_perizinan = $fileName;
        $ajuanperizinan->status_izin_atasan = null; // Default menunggu persetujuan
        $ajuanperizinan->status_izin_ppk = null; // Default menunggu persetujuan
        $ajuanperizinan->save();
        
        return redirect()->back()->with('success_message', 'Data telah tersimpan.');
        
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Perizinan $perizinan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Perizinan $perizinan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_perizinan)
    {
        if (auth()->user()->level === 'bod' || auth()->user()->level === 'kadiv') {
            $rules = [
                'status_izin_atasan' => 'required',
            ];
            
            $ajuanperizinan = Perizinan::find($id_perizinan);
            $request->validate($rules);
            $ajuanperizinan->status_izin_atasan = $request->status_izin_atasan;
            
            if ($request->status_izin_atasan === '0') {
                $ajuanperizinan->alasan_ditolak_atasan = $request->alasan_ditolak_atasan;
            }
            
            $ajuanperizinan->save();
            
            return redirect()->route('ajuanperizinan.index')->with('success_message', 'Data telah tersimpan');
        }
        elseif(auth()->user()->level === 'ppk'){
            $ajuanperizinan = Perizinan::find($id_perizinan);
            
            $rules = [
                'status_izin_ppk' => 'required',
            ];

            if($ajuanperizinan) {
                if($ajuanperizinan->id_atasan === auth()->user()->id_users){
                    $rules['status_izin_atasan'] = 'required'; 
                }
            }
            
            $request->validate($rules);

            $ajuanperizinan->status_izin_ppk = $request->status_izin_ppk;
            $ajuanperizinan->status_izin_atasan = $request->status_izin_atasan;

            if($request->status_izin_ppk === '0'){
                $ajuanperizinan->alasan_ditolak_ppk = $request->alasan_ditolak_ppk;
            }
            if($request->status_izin_atasan === '0'){
                $ajuanperizinan->alasan_ditolak_atasan = $request->alasan_ditolak_atasan;
            }
            $ajuanperizinan->save();
            return redirect()->route('ajuanperizinan.index')->with('success_message', 'Data telah tersimpan');
        }
        else
        {
            $rules = [
                'id_atasan' => 'required',
                'kode_finger' => 'required',
                'jenis_perizinan' => 'required',
                // 'tgl_ajuan' => 'required',
                'tgl_absen_awal' => 'required',
                'tgl_absen_akhir' => 'required',
                'keterangan' => 'required',
                // 'status_izin_atasan' => 'required',
                // 'status_izin_ppk' => 'required',
            ];
    
            if($request->file('file_perizinan')){
                $rules['file_perizinan'] = 'required|mimes:pdf,doc,docx,png,jpg,jpeg'; 
            }
    
            if(isset($request->status_izin_atasan )){
                if($request->status_izin_atasan === '0'){
                    $rules['alasan_ditolak_atasan'] = 'required'; 
                }
            }
    
            if(isset($request->status_izin_ppk )){
                if($request->status_izin_ppk === '0'){
                    $rules['alasan_ditolak_ppk'] = 'required'; 
                }
            }
    
            $request->validate($rules);
            
            $ajuanperizinan = Perizinan::find($id_perizinan);

            if (!$ajuanperizinan) {
                return redirect()->route('ajuanperizinan.index')->with('error', 'Data tidak ditemukan');
            }

            // Dapatkan jenis perizinan sebelumnya pengguna
            $jenisPerizinanSebelumnya = $ajuanperizinan->jenis_perizinan;

            // Update jenis perizinan pada pengguna
            $ajuanperizinan->jenis_perizinan = $request->jenis_perizinan;

            // Hitung perbedaan dalam jatah cuti tahunan
            $perbedaanJatahCuti = 0;

            if ($jenisPerizinanSebelumnya === 'CT' && $request->jenis_perizinan !== 'CT') {
                // Jika sebelumnya cuti tahunan dan sekarang bukan cuti tahunan
                $perbedaanJatahCuti = $ajuanperizinan->hitungJumlahHariPengajuan(
                    $request->tgl_absen_awal,
                    $request->tgl_absen_akhir
                );
            } elseif ($jenisPerizinanSebelumnya !== 'CT' && $request->jenis_perizinan === 'CT') {
                // Jika sebelumnya bukan cuti tahunan dan sekarang cuti tahunan
                $perbedaanJatahCuti = -$ajuanperizinan->hitungJumlahHariPengajuan(
                    $request->tgl_absen_awal,
                    $request->tgl_absen_akhir
                );
            }

            // Perbarui jatah cuti tahunan pengguna
            if ($perbedaanJatahCuti != 0) {
                // Cek apakah pengguna ditemukan berdasarkan kode finger
                $perizinanUser = User::with('cuti')->where('kode_finger', $request->kode_finger)->first();

                if ($perizinanUser) {
                    if (!$perizinanUser->cuti) {
                        return redirect()->back()->with('error', 'Pengguna tidak memiliki cuti tahunan.');
                    }

                    $perizinanUser->cuti->jatah_cuti += $perbedaanJatahCuti;

                    // Pastikan jatah cuti tidak kurang dari 0
                    if ($perizinanUser->cuti->jatah_cuti < 0) {
                        $perizinanUser->cuti->jatah_cuti = 0;
                    }

                    if (!$perizinanUser->cuti->save()) {
                        return redirect()->back()->with('error', 'Gagal mengubah jatah cuti tahunan pengguna.');
                    }
                } else {
                    return redirect()->back()->with('error', 'Pengguna dengan kode finger tersebut tidak ditemukan.');
                }
            }

            $ajuanperizinan->id_atasan = $request->id_atasan;
            $ajuanperizinan->kode_finger = $request->kode_finger;
            $ajuanperizinan->tgl_absen_awal = $request->tgl_absen_awal;
            $ajuanperizinan->tgl_absen_akhir = $request->tgl_absen_akhir;
            $ajuanperizinan->keterangan = $request->keterangan;
    
            if(isset($request->status_izin_atasan )){
                $ajuanperizinan->status_izin_atasan = $request->status_izin_atasan;
            }
            if(isset($request->status_izin_ppk )){
                $ajuanperizinan->status_izin_ppk = $request->status_izin_ppk;
            }
            
            if(isset($request->status_izin_atasan )){
                if($request->status_izin_atasan === '0'){
                    $ajuanperizinan->alasan_ditolak_atasan = $request->alasan_ditolak_atasan;
                }
            }
            if(isset($request->status_izin_ppk )){
                if($request->status_izin_ppk === '0'){
                    $ajuanperizinan->alasan_ditolak_ppk = $request->alasan_ditolak_ppk;
                }
            }
            // dd($request, $ajuanperizinan);
            
            if ($request->hasFile('file_perizinan')) {
                // Menghapus file file_perizinan sebelumnya
                if ($ajuanperizinan->file_perizinan) {
                    Storage::disk('public')->delete('file_perizinan/'.$ajuanperizinan->file_perizinan);
                }
                
                // Upload file file_perizinan baru
                $file_perizinan = $request->file('file_perizinan');
                $namafile_perizinan = time().'.'.$file_perizinan->getClientOriginalExtension();
                Storage::disk('public')->put('file_perizinan/'.$namafile_perizinan, file_get_contents($file_perizinan));
                $ajuanperizinan->file_perizinan = $namafile_perizinan;
            }
            
            $ajuanperizinan->save();
            
        }

        return redirect()->route('ajuanperizinan.index')->with('success_message', 'Data telah tersimpan');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Perizinan $id_perizinan)
    {
        if ($id_perizinan) {
            $id_perizinan->update([
                'is_deleted' => '1',
            ]);
        }
    
        return redirect()->route('ajuanperizinan.index')->with('success_message', 'Data telah terhapus');
    }
    }