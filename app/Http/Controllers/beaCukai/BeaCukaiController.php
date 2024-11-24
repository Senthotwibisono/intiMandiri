<?php

namespace App\Http\Controllers\beaCukai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;

use App\Models\Container as Cont;
use App\Models\JobOrder as Job;
use App\Models\BarcodeGate as Barcode;
use App\Models\Manifest;
use App\Models\Photo;
use App\Models\PlacementManifest as PM;
use App\Models\Item;

use DataTables;

class BeaCukaiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:bc');
    }

    public function home()
    {
        $data['title'] = "Dashboard Bea Cukai";
        $data['contRemaining'] = Cont::whereNotNull('endstripping')->where('status_bc', null)->count();
        $data['behandle'] = Manifest::whereNotNull('status_behandle')->whereNot('status_behandle', 'release')->count();
        $data['GateOut'] = Manifest::whereNotNull('status_bc')->whereNot('status_bc', 'release')->count();

        return view('bc.dashboard', $data);
    }
    
    public function buangMt()
    {
        $data['title'] = "LCL Import || Realisasi - Buang Empty";
        $data['conts'] = Cont::whereNotNull('endstripping')->where('status_bc', null)->get();
        // dd($data['conts']);
        $data['user'] = Auth::user()->name;
        return view('bc.lcl.mty', $data);
    }

    public function buangMtPost($id){
        $cont = Cont::where('id', $id)->first();
        if ($cont) {
            $cont->update([
                'status_bc'=>'release',
            ]);
            $barcode = Barcode::where('ref_id', $cont->id)->where('ref_type', '=', 'LCL')->where('ref_action', 'hold')->first();
            $barcode->update([
                'ref_action' => 'release',
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Container diperbolehkan keluar',
            ]);
        }else {
            return response()->json([
                'success' => false,
                'message' => 'Something wrong !!',
            ]);
        }
    }

    public function behandle()
    {
        $data['title'] = "LCL Import || Delivery - Behandle";
        $data['manifest'] = Manifest::whereNotNull('status_behandle')->whereNot('status_behandle', 'release')->get();

        return view('bc.lcl.behandle', $data);
    }

    public function behandleUpdate(Request $request)
    {
        $manifest = Manifest::where('id', $request->id)->first();
        try {
            if ($request->status_behandle == 3 || $request->status_behandle == 4) {
                $item = Item::where('manifest_id', $manifest->id)->get();
                $oldLokasi = Item::where('manifest_id', $manifest->id)->pluck('lokasi_id')->unique();
                if ($oldLokasi) {
                    foreach ($oldLokasi as $lokasiId) {
                        $itemCount = $item->where('lokasi_id', $lokasiId)->count();
                        $lokasiLama = PM::where('id', $lokasiId)->first();
                        if ($lokasiLama) {
                            $newJumlah = $lokasiLama->jumlah_barang - $itemCount;
                            $lokasiLama->update([
                                'jumlah_barang' => $newJumlah,
                            ]);
                            foreach ($item as $barang) {
                                $barang->update([
                                    'lokasi_id' => null,
                                ]);
                            }
                        }
                    }
                }
            }
            $manifest->update([
                'date_check_behandle' => $request->date_check_behandle,
                'desc_check_behandle' => $request->desc_check_behandle,
                'status_behandle' => $request->status_behandle,
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $fileName = $photo->getClientOriginalName();
                    $photo->storeAs('imagesInt', $fileName, 'public'); 
                    $newPhoto = Photo::create([
                        'master_id' => $manifest->id,
                        'type' => 'manifest',
                        'action' => 'behandle',
                        'photo' => $fileName,
                    ]);
                }
            }

            return redirect()->back()->with('status', ['type'=>'success', 'message'=>'Data Berhasil di Update']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('status', ['type'=>'error', 'message'=>'Ooppss, Something Wrong'. $e->getMessage()]);
        }
    }

    public function approveBehandle($id){
        $manifest = Manifest::where('id', $id)->first();
        if ($manifest) {
                $item = Item::where('manifest_id', $manifest->id)->get();
                $oldLokasi = Item::where('manifest_id', $manifest->id)->pluck('lokasi_id')->unique();
                if ($oldLokasi) {
                    foreach ($oldLokasi as $lokasiId) {
                        $itemCount = $item->where('lokasi_id', $lokasiId)->count();
                        $lokasiLama = PM::where('id', $lokasiId)->first();
                        if ($lokasiLama) {
                            $newJumlah = $lokasiLama->jumlah_barang - $itemCount;
                            $lokasiLama->update([
                                'jumlah_barang' => $newJumlah,
                            ]);
                        }
                    }
                }
            $manifest->update([
                'status_behandle'=>'release',
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Behandle Selesai',
            ]);
        }else {
            return response()->json([
                'success' => false,
                'message' => 'Something wrong !!',
            ]);
        }
    }
    
    public function gateOut()
    {
        $data['manifest'] = Manifest::whereNotNull('status_bc')->whereNot('status_bc', 'release')->get();
        $data['title'] = "LCL Import || Delivery - Gate Out";
        
        return view('bc.lcl.gateOut', $data);
    }

    public function approveGateOut($id){
        $manifest = Manifest::where('id', $id)->first();
        if ($manifest->status_bc == 'HOLDP2') {
            return response()->json([
                'success' => false,
                'message' => 'Harap Hubungi P2 !!',
            ]);
        }
        if ($manifest) {
            $barcode = Barcode::where('ref_id', $manifest->id)->where('ref_type', '=', 'Manifest')->where('ref_action', 'hold')->first();
            if ($barcode) {
                $manifest->update([
                    'status_bc'=>'release',
                    'release_bc_date' => Carbon::now(),
                    'release_bc_uid' => Auth::user()->id,
                ]);
                $barcode->update([
                    'ref_action' => 'release',
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Manifest Persilahkan Keluar',
                ]);
            }else {
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode belum di cetak !!',
                ]);
            }
            
        }else {
            return response()->json([
                'success' => false,
                'message' => 'Something wrong !!',
            ]);
        }
    }

    // stripping
    public function strippingIndex()
    {
        $data['title'] = "Stripping Approve";
        $data['user'] = Auth::user()->name;

        return view('bc.lcl.stripping', $data);
    }

    public function strippingIndexData(Request $request)
    {
        $cont = Cont::with(['job', 'user'])->where('type', '=', 'lcl')->whereNot('tglmasuk', null)->where('tglkeluar', null )->orderBy('endstripping', 'asc')->get();
        
        return DataTables::of($cont)
        ->addColumn('check', function($cont){
            if ($cont->status_ijin =='Y') {
                return '<input type="checkbox" class="form-check-input form-check-glow select-cont" value="' . $cont->id . '" disabled>';
            }else {
                return '<input type="checkbox" class="form-check-input form-check-glow select-cont" value="' . $cont->id . '">';
            }
        })
        ->addColumn('action', function($cont){
            return '<a href="/lcl/realisasi/stripping/proses-' . $cont->id . '" class="btn btn-warning"><i class="fa fa-pen"></i></a>';
        })
        ->addColumn('detil', function($cont){
            if ($cont->status_ijin == 'Y') {
                return '<span class="badge bg-light-success">Approved</span>';
            } else {
                return '<span class="badge bg-light-danger">Unapprove</span>';
            }
        })
        ->addColumn('status', function($cont){
            if ($cont->endstripping != null) {
                return '<span class="badge bg-light-danger">Finished</span>';
            }else {
                return '<span class="badge bg-light-success">On Proggress</span>';
            }
        })
        ->addColumn('kapal', function($cont){
            return $cont->job->Kapal->name ?? '-';
        })
        ->addColumn('no_plp', function($cont){
            return $cont->job->PLP->no_plp ?? '-';
        })
        ->addColumn('tgl_plp', function($cont){
            return $cont->job->PLP->tgl_plp ?? '-';
        })
        ->addColumn('kd_kantor', function($cont){
            return $cont->job->PLP->kd_kantor ?? '-';
        })
        ->addColumn('kd_tps', function($cont){
            return $cont->job->PLP->kd_tps ?? '-';
        })
        ->addColumn('kd_tps_asal', function($cont){
            return $cont->job->PLP->kd_tps_asal ?? '-';
        })
        ->addColumn('kd_tps_tujuan', function($cont){
            return $cont->job->PLP->kd_tps_tujuan ?? '-';
        })
        ->addColumn('nm_angkut', function($cont){
            return $cont->job->PLP->nm_angkut ?? '-';
        })
        ->addColumn('no_voy_flight', function($cont){
            return $cont->job->PLP->no_voy_flight ?? '-';
        })
        ->addColumn('no_surat', function($cont){
            return $cont->job->PLP->no_surat ?? '-';
        })
        ->addColumn('no_bc11', function($cont){
            return $cont->job->PLP->no_bc11 ?? '-';
        })
        ->addColumn('tgl_bc11', function($cont){
            return $cont->job->PLP->tgl_bc11 ?? '-';
        })
        ->rawColumns(['check', 'action', 'detil', 'status'])
        ->make(true);
    }

    public function strippingApproveCont(Request $request)
    {
        $ids = $request->input('ids');
        // var_dump($ids);
        // die;
        try {
            $conts = Cont::whereIn('id', $ids)->get();
            foreach ($conts as $cont) {
                if ($cont->status_ijin != 'Y') {
                    $cont->update([
                        'status_ijin' => 'Y',
                        'tgl_ijin_stripping' => Carbon::now()->format('Y-m-d'),
                        'jam_ijin_stripping' => Carbon::now()->format('H:i:s'),
                        'ijin_stripping_by' => Auth::user()->id,
                    ]);
                }
            }
            return response()->json([
                 'success' => true,
                 'message' => 'Data success updated',
            ]);
        } catch (\Throwable $th) {
           return response()->json([
                'success' => false,
                'message' => 'Something Wrong' . $th->getMessage(),
           ]);
        }
    }

    public function strippingDetail($id)
    {
        $cont = Cont::where('id', $id)->first();
        $data['title'] = "Stripping Proccess Container || " . $cont->nocontainer;
        $data['manifest'] = Manifest::where('container_id', $id)->get();
        $data['cont'] = $cont;
        $data['validateManifest'] = $data['manifest']->where('validasi', '=', 'Y')->count();

        return view('lcl.realisasi.stripping.proses', $data);
    }

    public function strippingApprove($id)
    {
        $manifest = Manifest::where('id', $id)->first();
        if ($manifest) {
            $manifest->update([
                'validasiBc' => 'Y',
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Manifest Approved!!',
            ]);
        }else {
            return response()->json([
                'success' => false,
                'message' => 'Something wrong !!',
            ]);
        }
    }

    public function strippingApproveAll()
    {
        $manifest = Manifest::where('validasi', '=', 'Y')->where('validasiBc', '=', null)->get();
        // dd($manifest);
        foreach ($manifest as $mans) {
            $mans->update([
                'validasiBc' => 'Y',
            ]);
        }
        return redirect()->back()->with('status', ['type'=>'success', 'message'=>'Data Berhasil di Update']);
    }
}
