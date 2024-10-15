<?php

namespace App\Http\Controllers\lcl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use App\Models\Container as Cont;
use App\Models\JobOrder as Job;
use App\Models\Manifest;
use App\Models\Eseal;
use App\Models\User;
use App\Models\Photo;
use App\Models\BarcodeGate as Barcode;
use App\Models\YardDetil as RowTier;

use GuzzleHttp\Client;
class GateInController extends Controller
{
    protected $client;
    protected $url;
    protected $token;

    public function __construct()
    {
        $this->middleware('auth');

        $this->url = 'https://vtsapi.easygo-gps.co.id/api/eseal/newDoPLP';
        $this->token = '73612D582EF54F119F8E41845405B8D6';
        $this->client = new Client(); // Inisialisasi Guzzle Client
    }

    public function index()
    {
        $data['title'] = "Import LCL - Gate In";
        $data['conts'] = Cont::where('type', '=', 'lcl')->where('tglkeluar', null )->get();
        $data['user'] = Auth::user()->name;
        $data['seals'] = Eseal::get();
        
        return view('lcl.realisasi.gateIn.index', $data);
    }

    public function edit($id)
    {
        $gate = Cont::where('id', $id)->first();
        if ($gate) {
            $job = Job::where('id', $gate->joborder_id)->first();
            $user = Auth::user()->name;
            $userId = Auth::user()->id;
            $uid = User::where('id', $gate->uidmasuk)->first();
            return response()->json([
                'success' => true,
                'data' => $gate,
                'job' =>$job,
                'user' => $user,
                'userId' => $userId,
                'uid' => $uid,
            ]);
        }
    }

    public function update(Request $request)
    {
        $cont = Cont::where('id', $request->id)->first();
        if ($cont) {
            $cont->update([
                'tglmasuk'=>$request->tglmasuk,
                'jammasuk'=>$request->jammasuk,
                'uidmasuk'=>$request->uidmasuk,
                'nopol'=>$request->nopol,
                'no_seal'=> $request->no_seal,
            ]);
            $manifest = Manifest::where('container_id', $cont->id)->get();
            foreach ($manifest as $mans) {
                $mans->update([
                    'tglmasuk'=>$request->tglmasuk,
                    'jammasuk'=>$request->jammasuk,
                ]);
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $fileName = $photo->getClientOriginalName();
                    $photo->storeAs('imagesInt', $fileName, 'public'); 
                    $newPhoto = Photo::create([
                        'master_id' => $cont->id,
                        'type' => 'lcl',
                        'action' => 'gate_in',
                        'photo' => $fileName,
                    ]);
                }
            }
            return redirect()->back()->with('status', ['type'=>'success', 'message'=>'Data berhasil di update']);
        }
    }

    public function detail($id)
    {
        $cont = Cont::where('id', $id)->first();
        $data['title'] = "Photo Gate In Container - " . $cont->nocontainer;
        $data['item'] = $cont;
        $data['photos'] = Photo::where('master_id', $id)->where('action', '=', 'gate_in')->get();
        // dd($data['photos']);
        return view('photo.index', $data);
    }

    public function detailDelete(Request $request)
    {
        $photo = Photo::where('id', $request->id)->first();
        if ($photo) {
            $filePath = public_path('storage/imagesInt/' . $photo->photo);

            // Hapus file foto jika ada
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $photo->delete();
            return redirect()->back()->with('success', 'Data berhasil di Hapus');
        }else {
            return redirect()->back()->with('error', 'Oopss, Something Wrong!!');
        }
    }

    // Seal
    public function indexSeal()
    {
        $data['title'] = "Import LCL - Dispatche E-Seal";
        $data['conts'] = Cont::where('type', '=', 'lcl')->where('tglkeluar', null )->get();
        $data['user'] = Auth::user()->name;
        $data['seals'] = Eseal::get();
        $data['now'] = Carbon::now();
        
        return view('lcl.realisasi.gateIn.seal', $data);
    }

    public function updateSeal(Request $request)
    {
        try {
            $cont = Cont::where('id', $request->id)->first();
            if ($cont) {
                $cont->update([
                    'nopol' => $request->nopol,
                    'no_seal' => $request->no_seal,
                ]);
                return redirect()->back()->with('status', ['type' => 'success', 'message' => 'Data Berhasil di Update']);
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('status', ['type' => 'error', 'message' => 'Something Wrong!! : ' . $th->getMessage()]);
        }
    }


    public function indexMT()
    {
        $data['title'] = "LCL Import || Realisasi - Buang Empty";
        $data['conts'] = Cont::whereNotNull('endstripping')->whereNotNull('tglmasuk')->get();
        $data['user'] = Auth::user()->name;
        $data['seals'] = Eseal::get();

        return view('lcl.realisasi.gateIn.mty', $data);
    }

    public function detailMt($id)
    {
        $cont = Cont::where('id', $id)->first();
        $data['title'] = "Photo Gate Out Container - " . $cont->nocontainer;
        $data['item'] = $cont;
        $data['photos'] = Photo::where('master_id', $id)->where('type', '=', 'lcl')->where('action', '=', 'gate_out')->get();
        // dd($data['photos']);
        return view('photo.index', $data);
    }

    public function updateMt(Request $request)
    {
        $cont = Cont::where('id', $request->id)->first();
        if ($cont) {
            if ($cont->status_bc != 'release') {
                return redirect()->back()->with('status', ['type'=>'error', 'message'=>'Status belum release']);
            }
            $cont->update([
                'tglkeluar'=>$request->tglkeluar,
                'jamkeluar'=>$request->jamkeluar,
                'uidmty'=>$request->uidmty,
                'nopol_mty'=>$request->nopol_mty,
                'no_seal'=> $request->no_seal,
            ]);

            $oldYard = RowTier::where('cont_id', $cont->id)->get();
            if ($oldYard) {
                foreach ($oldYard as $old) {
                    $old->update([
                        'cont_id' => null,
                        'active' => 'N',
                    ]);
                }
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $fileName = $photo->getClientOriginalName();
                    $photo->storeAs('imagesInt', $fileName, 'public'); 
                    $newPhoto = Photo::create([
                        'master_id' => $cont->id,
                        'type' => 'lcl',
                        'action' => 'gate_out',
                        'photo' => $fileName,
                    ]);
                }
            }
            return redirect()->back()->with('status', ['type'=>'success', 'message'=>'Data berhasil di update']);
        }
    }   

    public function createBarcode(Request $request)
    {
        $cont = Cont::where('id', $request->id)->first();
        if ($cont->status_bc != 'release') {
            $action = 'hold';
        }else {
            $action = 'release';
        }
        $barcode = Barcode::where('ref_id', $cont->id)->where('ref_type', '=', 'LCL')->where('ref_action', $action)->first();
        if ($barcode) {
                $now = Carbon::now();
                if ($barcode->status == 'inactive' || $barcode->expired <= $now) {
                    do {
                        $uniqueBarcode = Str::random(20);
                    } while (Barcode::where('barcode', $uniqueBarcode)->exists());
                    $barcode->update([
                        'barcode'=> $uniqueBarcode,
                        'status'=>'active',
                        'expired'=> Carbon::now()->addDays(3),
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => 'updated successfully!',
                        'data'    => $barcode,
                    ]);
                }else {
                    return response()->json([
                        'success' => true,
                        'message' => 'updated successfully!',
                        'data'    => $barcode,
                    ]);
                }
        }else {
            do {
                $uniqueBarcode = Str::random(20);
            } while (Barcode::where('barcode', $uniqueBarcode)->exists());    
            $newBarcode = Barcode::create([
                'ref_id'=>$cont->id,
                'ref_type'=>'LCL',
                'ref_action'=> $action,
                'ref_number'=>$cont->nocontainer,
                'barcode'=> $uniqueBarcode,
                'status'=>'active',
                'expired'=> Carbon::now()->addDays(3),
                'uid'=> Auth::user()->id,
                'created_at'=> Carbon::now(),
            ]);
            return response()->json([
                'success' => true,
                'message' => 'updated successfully!',
                'data'    => $newBarcode,
            ]);
        }
    }

    public function easyGoSend(Request $request)
    {
        $cont = Cont::find($request->id);
        // var_dump($cont);
        // die;
        $data = [
            "no_plp" => $cont->job->PLP->no_plp,
            "no_sj" => $request->no_sj ?? "",  // Use an empty string if null
            "no_eseal" => $cont->seal->code,
            "tgl_plp"=> Carbon::parse($cont->job->PLP->tgl_plp)->format('Y/m/d'),
            "nopol"=> "",
            "maxtime_delivery"=> 0,
            "maxtime_checking"=> 0,
            "alert_telegram"=> [],
            "alert_email"=> [],
            "alert_dur_idle"=> 0,
            "alert_dur_notUpdate"=> 0,
            "alert_dur_terlarang"=> 0,
            "alert_tujuan_lain"=> 0,
            "alert_dur_parking"=> 0,
            "opsi_complete"=> 0,
            "dur_valid_geofence"=> 0,
            "flag"=> "",
            "user_login"=> "",
            "client_code"=> "",
            "project_code"=> "",
            "allow_multiple_do"=> 0,
            "driver_code"=> "",
            "asal" => [
                [
                    "geo_code"=> $cont->job->PLP->kd_tps_asal,
                    "plan_loading_time"=> "",
                    "description"=> "",
                    "lon"=> null,
                    "lat"=> null,
                    "radius"=> null
                ]
            ],
            "tujuan" => [
                [
                    "geo_code"=> $cont->job->PLP->gudang_tujuan,
                    "no_sj"=> "",
                    "description"=> "",
                    "cust_alert_telegram"=> [],
                    "cust_alert_email"=> [],
                    "lon"=> null,
                    "lat"=> null,
                    "radius"=> null,
                    "plan_unloading_time"=> "",
                    "std_minute_delivery"=> 0,
                    "std_km_delivery"=> 0
                ]
            ],
            "shipment" => [
               "jns_muatan"=> "",
                "vol_sj"=> null,
                "vol_timbangan"=> null,
                "vol_kosong"=> null,
                "vol_tujuan"=> null,
                "cpty_muatan"=> null,
                "no_tiket_timbangan"=> "",
                "note_shipment"=> "",
                "down_payment"=> null,
                "truck_id"=> "",
                "no_container"=> "",
                "jns_cont_id"=> "",
                "size_cont_id"=> "",
                "driver_name_2"=> "",
                "driver_phone_2"=> "",
                "consignee"=> "",
                "tarif_angkut"=> null,
                "uang_jalan"=> null,
                "add_uang_jalan"=> null,
                "shipping_line"=> "",
                "shipper"=> "",
                "transporter"=> "",
                "do_expired"=> "",
                "tipe"=> ""
            ]
        ];
        
    
        try {
            // Mengirim permintaan ke API eksternal menggunakan Guzzle
            $response = $this->client->post($this->url, [
                'headers' => [
                    'Token' => $this->token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data, // Mengirim data dalam format JSON
            ]);
        
            if ($response->getStatusCode() == 200) {
                $responseData = json_decode($response->getBody(), true);
                // var_dump($responseData, $this->token, $responseData['Data']['do_id']);
                // die;
                if ($responseData['ResponseCode'] == 1) {
                    $cont->update([
                        'do_id' => $responseData['Data']['do_id'],
                        'status_dispatche' => 'Y',
                        'tgl_dispatche' => Carbon::now(),
                        'jam_dispatche' => Carbon::now(),
                        'response_dispatche' => '1',
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => 'dispatche successfully!',
                    ]);
                } else {
                    // return redirect()->back()->with('status', ['type' => 'error', 'message' => 'Gagal mengirim ke API: ' . $responseData['ResponseMessage']]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengirim ke API: ' . $responseData['ResponseMessage'],
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Oops, Something Wrong!!!',
                ]);
            }
        } catch (\Exception $e) {
            // return redirect()->back()->with('status', ['type' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Oops, Something Wrong!!!' . $e->getMessage(),
            ]);
        } 
    }

    public function closeDO(Request $request)
    {
        // Find the container record
        $cont = Cont::find($request->id);

        // Prepare the data to send
        $data = [
            "tgl_pod" => $request->tgl_pod,
            "tgl_closed" => $request->tgl_closed,
            "ket_close" => $request->ket_close,
            "photo_pod" => "", // Assume you will replace with real base64 data
            "signature_pod" => "", // Optional base64 signature
            "scanner_pod" => "", // Optional base64 scanner image
            "remark_pod" => "" // Optional remark
        ];

        try {
            // Define the API endpoint URL
            $url = 'https://vtsapi.easygo-gps.co.id/api/do/closeDOV1/' . $cont->do_id;

            // Make the API request
            $response = $this->client->post($url, [
                'headers' => [
                    'Token' => $this->token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data, // Send the data as JSON
            ]);

            // Decode the response body
            $responseData = json_decode($response->getBody(), true);
            // dd($responseData);
            if ($responseData['ResponseCode'] == 1) {
                $cont->update([
                    'do_id' => null,
                    'status_dispatche' => 'n',
                    'tgl_dispatche' => null,
                    'jam_dispatche' => null,
                    'response_dispatche' => null,
                ]);
                return redirect()->back()->with('status', ['type' => 'success', 'message' => $responseData['ResponseMessage']]);
            }else {
                if ($responseData['ResponseMessage'] == "DO already closed !! " || $responseData['ResponseMessage'] == "Close DO Successfully !" ) {
                    $cont->update([
                        'do_id' => null,
                        'status_dispatche' => 'n',
                        'tgl_dispatche' => null,
                        'jam_dispatche' => null,
                        'response_dispatche' => null,
                    ]);
                    return redirect()->back()->with('status', ['type' => 'success', 'message' => $responseData['ResponseMessage']]);
                }else {
                    return redirect()->back()->with('status', ['type' => 'error', 'message' => $responseData['ResponseMessage']]);
                }
            }
                
                

        } catch (\Exception $e) {
            return redirect()->back()->with('status', ['type' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

}
