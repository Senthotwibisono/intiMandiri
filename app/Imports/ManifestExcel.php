<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Container as Cont;
use App\Models\Joborder as Job;
use App\Models\Manifest;
use App\Models\TempManifest;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Packing;
use Auth;
use Carbon\Carbon;
use Str;

class ManifestExcel implements ToCollection, WithHeadingRow
{
    // protected $container_id;

    // public function __construct($container_id)
    // {
    //     $this->container_id = $container_id;
    // }

    public function collection(Collection $rows)
    {
        // $sheetTitles = $import->getDelegate()->getActiveSheet()->getTitle();
        // dd($sheetTitles);
        // $headers = $rows->first()->keys()->toArray();
        // dd($headers); // This will output the column headers

      
        foreach ($rows as $row) {

            $detil_id = trim($row['id_detil']);
            $nohbl = trim($row['no_host_blawb']);
            $tgl_hbl =  Carbon::createFromFormat('d-m-Y', trim($row['tgl_host_blawb']))->format('Y-m-d');
            $npwp_consignee = trim($row['npwp_consignee']);
            $nama_consignee = trim($row['nama_consignee']);
            $almt_consignee = trim($row['almt_consignee']);
            $neg_consignee = trim($row['neg_consignee']);
            $npwp_shipper = trim($row['npwp_shipper']);
            $nama_shipper = trim($row['nama_shipper']);
            $almt_shipper = trim($row['almt_shipper']);
            $neg_shipper = trim($row['neg_shipper']);
            $nama_notify = trim($row['nama_notify']);
            $almt_notify = trim($row['almt_notify']);
            $neg_notify = trim($row['neg_notify']);
            $quantity = trim($row['jumlah_kemasan']);
            $jenis_kemasan = trim($row['jenis_kemasan']);
            $merk_kemasan = trim($row['merk_kemasan']);
            $weight = trim($row['bruto']);
            $meas = trim($row['volume']);
            // Hanya buat entri baru jika nama tidak kosong

            $manifest = TempManifest::create([
                'detil_id' => $detil_id,
                'nohbl' => $nohbl,
                'tgl_hbl' => $tgl_hbl,
                'shipper_id' => Customer::where('name', $nama_shipper)->where('npwp', $npwp_shipper)->first()->id ?? null,
                'customer_id' => Customer::where('name', $nama_consignee)->where('npwp', $npwp_consignee)->first()->id ?? null,
                'notifyparty_id' =>  Customer::where('name', $nama_notify)->first()->id ?? Customer::where('name', $nama_consignee)->where('npwp', $npwp_consignee)->first()->id ?? null,
                'marking' => $merk_kemasan,
                'quantity' => $quantity,
                'packing_id' => Packing::where('name', $merk_kemasan)->where('code', $jenis_kemasan)->first()->id ?? null,
                'weight' => $weight,
                'meas' => $meas,
            ]);
            // if (!empty($no_host_blawb)) {
            //     // Cek apakah entri sudah ada
            //     $manifest = Manifest::where('nohbl', $no_host_blawb)->first();
                
            //     // Jika entri belum ada, buat entri baru
            //     $cont = Cont::where('id', $contId)->first();
            //     $job = Job::where('id', $cont->joborder_id)->first();

            //     $lastTally = Manifest::where('joborder_id', $job->id)
            //                   ->orderBy('id', 'desc')
            //                   ->first();
            //     if ($lastTally) {
            //         $lastTallyNumber = intval(substr($lastTally->notally, 12, 3));
            //         $newTallyNumber = str_pad($lastTallyNumber + 1, 3, '0', STR_PAD_LEFT);
            //     } else {
            //         $newTallyNumber = '001';
            //     }
            //     $noTally = $job->nojoborder . '-' . $newTallyNumber;
            //     do {
            //         $uniqueBarcode = Str::random(19);
            //     } while (Manifest::where('barcode', $uniqueBarcode)->exists());

            //     if (!$manifest) {
            //         $manifest = new Manifest();
            //         $manifest->notally = $noTally;
            //         $manifest->validasi = 'N';
            //         $manifest->barcode = $uniqueBarcode;
            //         $manifest->nohbl = $no_host_blawb;
            //         $manifest->container_id = $contId;
            //         $manifest->joborder_id = $cont->joborder_id;
            //         $manifest->tgl_hbl = $tgl_host_blawb;
            //         $manifest->shipper_id = Customer::where('name', $nama_shipper)->where('npwp', $npwp_shipper)->first()->id ?? null;
            //         $manifest->customer_id = Customer::where('name', $nama_consignee)->where('npwp', $npwp_consignee)->first()->id ?? null;
            //         $manifest->notifyparty_id = Customer::where('name', $nama_notify)->first()->id ?? null;
            //         $manifest->marking = $noTally;
            //         $manifest->quantity = $jumlah_kemasan;
            //         $manifest->packing_id = Packing::where('name', $merk_kemasan)->where('code', $jenis_kemasan)->first()->id ?? null;
            //         $manifest->weight = $bruto;
            //         $manifest->meas = $volume;
            //         $manifest->packing_tally = $jumlah_kemasan;
            //         $manifest->uid = Auth::user()->id;
            //         $manifest->save();
            //     }

            //     for ($i = 1; $i <= $manifest->quantity; $i++) {
            //         $item= Item::create([
            //             'manifest_id' => $manifest->id,
            //             'barcode' => $manifest->barcode . $i,
            //             'nomor' => $i,
            //             'stripping' => 'N',
            //             'uid'=> Auth::user()->id,
            //         ]);
            //     }
            // }
        }
    }
}

