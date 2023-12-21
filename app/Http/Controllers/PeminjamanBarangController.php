<?php

namespace App\Http\Controllers;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\PeminjamanBarang;
use App\Models\BarangTik;
use Carbon\Carbon;
use App\Models\DetailPeminjamanBarang;
use Illuminate\Http\Request;

class PeminjamanBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       
            $peminjaman = PeminjamanBarang::where('is_deleted', '0')->get();
       


        return view('peminjamanbarang.index', [
            'peminjaman' => $peminjaman,
            'user' => User::where('is_deleted', '0')->orderByRaw("LOWER(nama_pegawai)")->get(),
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
        //Menyimpan Data User Baru
        $request->validate([
            'id_users' => 'required',
            'tgl_peminjaman' => 'required|date',
            'tgl_pengembalian' => 'required|date|after_or_equal:tgl_peminjaman',
            'kegiatan' => 'required',
            'keterangan' => 'required',
        ]);

        $peminjaman = new PeminjamanBarang();

        $peminjaman->id_users = $request->id_users;
        $peminjaman->tgl_peminjaman = $request->tgl_peminjaman;
        $peminjaman->tgl_pengembalian = $request->tgl_pengembalian;
        $peminjaman->kegiatan = $request->kegiatan;
        $peminjaman->keterangan = $request->keterangan;
        $peminjaman->status = 'belum_diajukan';
      
        $peminjaman->save();

        return redirect()->back()->with('success_message', 'Data telah tersimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id_peminjaman)
    {
        $peminjaman = PeminjamanBarang::findOrFail($id_peminjaman);
    
        // Mengambil semua data BarangTik yang tersedia
        $barangTIK = BarangTik::where('status_pinjam', 'Ya')->where('is_deleted', '0')->orderByRaw("LOWER(nama_barang)")->with(['detailPeminjaman'])->get();
    
        // Mengambil seluruh detail peminjaman yang terkait dengan peminjaman ini
        $detailPeminjaman = DetailPeminjamanBarang::with(['barang'])
            ->where('id_peminjaman', $id_peminjaman)
            ->get();
        //  dd($detailPeminjaman);
        
         $peminjamanDetail = DetailPeminjamanBarang::with(['barang'])
            ->where('id_peminjaman', $id_peminjaman)
            ->get();
        
        //   // Kumpulkan detail peminjaman untuk setiap barang
        //      $detailPeminjaman = collect();

            foreach ($barangTIK as $barang) {
                // Ambil semua detail peminjaman terkait dengan barang ini
                $details = DetailPeminjamanBarang::where('id_barang_tik', $barang->id_barang_tik)->get();
        
                // Ambil hanya satu detail peminjaman dengan status 'dipinjam'
                $dipinjamDetail = $details->firstWhere('status', 'dipinjam');
        
                // Jika ada yang dipinjam, masukkan ke dalam koleksi
                if ($dipinjamDetail) {
                    $peminjamanDetail->push($dipinjamDetail);
                }
            }
        
    
        $barangs = BarangTik::where('is_deleted', '0')->where('status_pinjam', 'Ya')->orderByRaw("LOWER(nama_barang)")->pluck('nama_barang', 'id_barang_tik');
    
        return view('peminjamanbarang.show', [
            'peminjaman' => $peminjaman,
            'barangTIK' => $barangTIK,
            'detailPeminjaman' => $detailPeminjaman,
            'barangs' => $barangs,
            'peminjamanDetail' => $peminjamanDetail
           
        ]);
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PeminjamanBarang $peminjamanBarang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id_peminjaman)
    {
        if(auth()->user()->level == 'admin'){
        $request->validate([
            'tgl_peminjaman' => 'required|date',
            'tgl_pengembalian' => 'required|date|after_or_equal:tgl_peminjaman',
            'kegiatan' => 'required',
            'keterangan' => 'required',
            'status' => 'required'
        ]);

        $peminjaman = PeminjamanBarang::find($id_peminjaman);

        $peminjaman->tgl_peminjaman = $request->tgl_peminjaman;
        $peminjaman->tgl_pengembalian = $request->tgl_pengembalian;
        $peminjaman->kegiatan = $request->kegiatan;
        $peminjaman->keterangan = $request->keterangan;
        $peminjaman->status = $request -> status;


        $peminjaman->save();
        if ($peminjaman->status == 'dipinjam') {
        
            // Update status detail peminjaman yang terkait
            $detailPeminjaman = DetailPeminjamanBarang::where('id_peminjaman', $id_peminjaman)->update(['status' => 'dipinjam']);
      
        }elseif ($peminjaman->status == 'belum_diajukan'){
            $peminjaman->status = 'belum_diajukan';
            $peminjaman->save();
        
            // Update status detail peminjaman yang terkait
            $detailPeminjaman = DetailPeminjamanBarang::where('id_peminjaman', $id_peminjaman)->update(['status' => null]);
            
        }
        return redirect()->back()->with('success_message', 'Data telah tersimpan.');
        }else{
              $request->validate([
            'tgl_peminjaman' => 'required|date',
            'tgl_pengembalian' => 'required|date|after_or_equal:tgl_peminjaman',
            'kegiatan' => 'required',
            'keterangan' => 'required',
           
        ]);

        $peminjaman = PeminjamanBarang::find($id_peminjaman);

        $peminjaman->tgl_peminjaman = $request->tgl_peminjaman;
        $peminjaman->tgl_pengembalian = $request->tgl_pengembalian;
        $peminjaman->kegiatan = $request->kegiatan;
        $peminjaman->keterangan = $request->keterangan;
        $peminjaman->save();
      
        
        return redirect()->back()->with('success_message', 'Data telah tersimpan.');

        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_peminjaman)
    {
        $peminjaman = PeminjamanBarang::find($id_peminjaman);
        if ($peminjaman) {
            $peminjaman->update([
                'is_deleted' => '1',
            ]);
        }

        return redirect()->back()->with('success_message', 'Data telah terhapus.');
    }

    public function storeDetailPeminjaman(Request $request)
    {
        // Validasi data yang dikirimkan melalui form
        $request->validate([
            'id_peminjaman' => 'required',
            'id_barang_tik' => 'required',
          
        ]);

        // Simpan data ke dalam tabel tim_kegiatan
        $detailPeminjaman = DetailPeminjamanBarang::create([
            'id_peminjaman' => $request->input('id_peminjaman'),
            'id_barang_tik' => $request->input('id_barang_tik'),
            'keterangan_awal' => $request->input('keterangan_awal'),
        ]);

        // Redirect atau lakukan tindakan lain setelah data berhasil disimpan
        return redirect()->back()->with('success_message', 'Data telah tersimpan');
    }

    public function notifikasi(Request $request, $id_peminjaman)
    {
        
        $peminjaman = PeminjamanBarang::findOrFail($id_peminjaman);

        $peminjaman->status = 'diajukan';
        $peminjaman->save();

        $pengguna = User::where('id_users', $peminjaman->id_users)->first();
        $notifikasi = new Notifikasi();
        $notifikasi->judul = 'Pengajuan Peminjaman Barang TIK';
        $notifikasi->pesan = 'Pengajuan peminjaman anda sudah berhasil dikirimkan.  Kami telah mengirimkan notifikasi untuk memproses pengajuanmu.';
        $notifikasi->is_dibaca = 'tidak_dibaca';
        $notifikasi->label = 'info';
        $notifikasi->send_email = 'yes';
        $notifikasi->link = '/peminjaman';  
        $notifikasi->id_users = $pengguna->id_users;
        $notifikasi->save();

        
         $notifikasiKadiv = User::where('id_jabatan', '8')->get();

        foreach($notifikasiKadiv as $nk){
        $notifikasi = new Notifikasi();
        $notifikasi->judul = 'Pengajuan Peminjaman Barang TIK';
        $notifikasi->pesan =  'Pengajuan peminjaman dari '.$pengguna->nama_pegawai.'. Dimohon untuk segara menyiapkan barang peminjaman.'; 
        $notifikasi->is_dibaca = 'tidak_dibaca';
        $notifikasi->label = 'info';
        $notifikasi->link = '/peminjaman';
        $notifikasi->send_email = 'yes';
        $notifikasi->id_users = $nk->id_users;
        $notifikasi->save();
        }

        $notifikasiAdmin = User::where('level', 'admin')->get();
        
        foreach($notifikasiAdmin as $na){
        $notifikasi = new Notifikasi();
        $notifikasi->judul = 'Pengajuan Peminjaman Barang TIK';
        $notifikasi->pesan =  'Pengajuan peminjaman dari '.$pengguna->nama_pegawai.'. Dimohon untuk segara menyiapkan barang peminjaman.'; 
        $notifikasi->is_dibaca = 'tidak_dibaca';
        $notifikasi->label = 'info';
        $notifikasi->link = '/peminjaman';
        $notifikasi->send_email = 'no';
        $notifikasi->id_users = $na->id_users;
        $notifikasi->save();
        }

        // Redirect atau lakukan tindakan lain setelah data berhasil disimpan
        return redirect()->back()->with('success_message', 'Pengajuan Berhasil Dikirim.');
    }    

    public function updateDetailPeminjaman(Request $request, $id_detail_peminjaman)
    {
    
        $request->validate([
            'id_barang_tik' => 'required',  
          
        ]);
        // Simpan data ke dalam tabel tim_kegiatan
        $detailPeminjaman = DetailPeminjamanBarang::find($id_detail_peminjaman);
        $detailPeminjaman->id_barang_tik = $request->input('id_barang_tik');
        $detailPeminjaman->keterangan_awal = $request->input('keterangan_awal');
        $detailPeminjaman->keterangan_akhir = $request->input('keterangan_akhir');
        $detailPeminjaman->tgl_kembali = now();
        $detailPeminjaman->status = 'dikembalikan';
        $detailPeminjaman->save();

        // Redirect atau lakukan tindakan lain setelah data berhasil disimpan
        return redirect()->back()->with('success_message', 'Data telah tersimpan');
    }

    public function editDetailPeminjaman(Request $request, $id_detail_peminjaman)
    {
    
        $request->validate([
            'keterangan_awal' => 'required',
        ]);
        // Simpan data ke dalam tabel tim_kegiatan
        $detailPeminjaman = DetailPeminjamanBarang::find($id_detail_peminjaman);
        $detailPeminjaman->keterangan_awal = $request->input('keterangan_awal');
        
        $detailPeminjaman->save();

        // Redirect atau lakukan tindakan lain setelah data berhasil disimpan
        return redirect()->back()->with('success_message', 'Data telah tersimpan');
    }

    public function destroyDetail($id_detail_peminjaman)
    {
        
            $detailPeminjaman = DetailPeminjamanBarang::findOrFail($id_detail_peminjaman);
            
            $detailPeminjaman->delete();    

            return redirect()->back()->with('success_message', 'Data telah terhapus');
      
    }
}