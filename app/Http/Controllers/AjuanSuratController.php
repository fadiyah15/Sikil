<?php

namespace App\Http\Controllers;

use App\Models\KodeSurat;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Http\Request;

class AjuanSuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $surat = Surat::where('is_deleted', '0')
            ->get();

        return view('ajuansurat.index', [
            'surat' => $surat,
            'users' => User::where('is_deleted', '0')->get(),
            'kodesurat' => KodeSurat::all(),
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

        $rules = [
            'id_users' => 'required',
            'jenis_surat' => 'required',
            'id_kode_surat' => 'required',
            'keterangan' => 'required',
            'tgl_surat' => 'required|date',
            'bulan_kegiatan' => 'required',
        ];

        $request->validate($rules);

        $surat = new Surat();

        $surat->tgl_surat = $request->tgl_surat;
        $surat->id_users = $request->id_users;
        $surat->jenis_surat = $request->jenis_surat;
        $surat->id_kode_surat = $request->id_kode_surat;
        $surat->keterangan = $request->keterangan;
        $surat->bulan_kegiatan = $request->bulan_kegiatan;
        $surat->status = '1';

        $surat->urutan = 33 + Surat::where('jenis_surat', $request->jenis_surat)->where('is_deleted', '0')->count() + 1;

        $kode_surat = KodeSurat::find($request->id_kode_surat);

        if($request->jenis_surat == 'nota_dinas'){
            $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/ND/' . date('Y', strtotime($surat->tgl_surat));
        }else if($request->jenis_surat == 'notula_rapat'){
            $surat->no_surat = $surat->urutan .  '/NR/' . date('Y', strtotime($surat->tgl_surat));
        }else if($request->jenis_surat == 'sertifikat_kegiatan'){
            $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/II/' . date('Y', strtotime($surat->tgl_surat));
        }else if($request->jenis_surat == 'sertifikat_magang'){
            $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/I/' . date('Y', strtotime($surat->tgl_surat));
        }else if($request->jenis_surat == 'surat_keluar'){
            $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . date('Y', strtotime($surat->tgl_surat));
        }

        $surat->save();

        return redirect()->back()->with('success_message', 'Data telah tersimpan.');

        $this->repair();

    }

    /**
     * Display the specified resource.
     */
    public function show(Surat $surat)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Surat $surat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id_surat)
    {
        $rules = [
            'id_users' => 'required',
            'jenis_surat' => 'required',
            'id_kode_surat' => 'required',
            'keterangan' => 'required',
            'tgl_surat' => 'required|date',
            'bulan_kegiatan' => 'required',
            'status' => 'required',
        ];

        $surat = Surat::where('id_surat',$id_surat)->get()[0];

        $request->validate($rules);

        if($request->jenis_surat === $surat->jenis_surat && $request->id_kode_surat == $surat->id_kode_surat){

            $surat->tgl_surat = $request->tgl_surat;
            $surat->id_users = $request->id_users;
            $surat->jenis_surat = $request->jenis_surat;
            $surat->id_kode_surat = $request->id_kode_surat;
            $surat->keterangan = $request->keterangan;
            $surat->bulan_kegiatan = $request->bulan_kegiatan;
            $surat->status = $request->status;
            $surat->save();
        }else{
            $surat->tgl_surat = $request->tgl_surat;
            $surat->id_users = $request->id_users;
            $surat->jenis_surat = $request->jenis_surat;
            $surat->id_kode_surat = $request->id_kode_surat;
            $surat->keterangan = $request->keterangan;
            $surat->bulan_kegiatan = $request->bulan_kegiatan;
            $surat->status = $request->status;
            $surat->urutan = 33 + Surat::where('jenis_surat', $request->jenis_surat)->where('is_deleted', '0')->count() + 1;

            $kode_surat = KodeSurat::find($request->id_kode_surat);

            if($request->jenis_surat == 'nota_dinas'){
                $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/ND/' . date('Y', strtotime($surat->tgl_surat));
            }else if($request->jenis_surat == 'notula_rapat'){
                $surat->no_surat = $surat->urutan .  '/NR/' . date('Y', strtotime($surat->tgl_surat));
            }else if($request->jenis_surat == 'sertifikat_kegiatan'){
                $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/II/' . date('Y', strtotime($surat->tgl_surat));
            }else if($request->jenis_surat == 'sertifikat_magang'){
                $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/I/' . date('Y', strtotime($surat->tgl_surat));
            }else if($request->jenis_surat == 'surat_keluar'){
                $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . date('Y', strtotime($surat->tgl_surat));
            }

            $surat->save();

            $surats = Surat::where('is_deleted', '0')->where('jenis_surat', '!=', $request->jenis_surat)->get();
            $this->repair($surats);
        }

        return redirect()->back()->with('success_message', 'Data telah tersimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_surat)
    {
        $surat = Surat::find($id_surat);
        if ($surat) {
            $surat->update([
                'is_deleted' => '1',
            ]);
        }

        $surats = Surat::where('is_deleted', '0')->get();
        $this->repair($surats);

        return redirect()->back()->with('success_message', 'Data telah terhapus');

    }

    public function repair($surats){
        // repair urutan surat so when there is something missing in the middle of the data, it will be repaired

        foreach ($surats as $key => $surat) {

            // set urutan to 0
            $surat->urutan = 33;

            // get all surat with same jenis_surat
            $surats2 = Surat::where('jenis_surat', $surat->jenis_surat)->where('is_deleted', '0')->get();
            foreach ($surats2 as $key => $surat2) {

                // and where id_surat not higher than current id_surat
                if ($surat2->id_surat <= $surat->id_surat) {
                    $surat->urutan++;
                }

                $kode_surat = KodeSurat::find($surat->id_kode_surat);

                if($surat->jenis_surat == 'nota_dinas'){
                    $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/ND/' . date('Y', strtotime($surat->tgl_surat));
                }else if($surat->jenis_surat == 'notula_rapat'){
                    $surat->no_surat = $surat->urutan .  '/NR/' . date('Y', strtotime($surat->tgl_surat));
                }else if($surat->jenis_surat == 'sertifikat_kegiatan'){
                    $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/II/' . date('Y', strtotime($surat->tgl_surat));
                }else if($surat->jenis_surat == 'sertifikat_magang'){
                    $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . '/I/' . date('Y', strtotime($surat->tgl_surat));
                }else if($surat->jenis_surat == 'surat_keluar'){
                    $surat->no_surat = $surat->urutan . '/' . $kode_surat->kode_surat . date('Y', strtotime($surat->tgl_surat));
                }
            }

            $surat->save();
        }
    }
}