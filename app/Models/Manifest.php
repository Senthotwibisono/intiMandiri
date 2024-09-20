<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Manifest extends Model
{
    use HasFactory;
    protected $table = 'tmanifest';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'notally',
        'nohbl',
        'marking',
        'descofgoods',
        'weight',
        'meas',
        'barcode',
        'customer_id',
        'shipper_id',
        'notifyparty_id',
        'joborder_id',
        'container_id',
        'uid',
        'uidrelease',
        'invoice_id',
        'sfoto',
        'jmlfoto',
        'nopos',
        'no_bc11',
        'no_pos_bc11',
        'nosegel',
        'tgl_hbl',
        'kode_kemas',
        'tgl_bc11',
        'quantity',
        'namapacking',
        'packing_id',
        'validasi',
        'validasiBc',
        'dg_surcharge',
        'weight_surcharge',
        'racking',
        'partoff',
        'quantity_val',
        'namapacking_val',
        'uraian_val',
        'total',
        'keterangan',
        'val_date',
        'val_user',
        'kerani',
        'kerani_by',
        'kerani_date',
        'kerani_time',
        'koordinator',
        'koordinator_by',
        'koordinator_date',
        'koordinator_time',
        'supervisor',
        'supervisor_by',
        'supervisor_date',
        'supervisor_time',
        'check_date',
        'check_time',
        'check_by',
        'stock_audit',
        'blhostjict_flag',
        'blhostjict_response',
        'blhostjict_datetime',
        'tglentry',
        'jamentry',
        'tglmasuk',
        'jammasuk',
        'tglstripping',
        'jamstripping',
        'tglbehandle',
        'jambehandle',
        'tglfiat',
        'jamfiat',
        'tglrelease',
        'jamrelease',
        'behandle',
        'ref_number',
        'tglbuangmty',
        'jambuangmty',
        'nojoborder',
        'nocontainer',
        'size',
        'no_plp',
        'tgl_plp',
        'consolidator_id',
        'namaconsolidator',
        'lokasisandar_id',
        'kd_tps_asal',
        'eta',
        'etd',
        'vessel',
        'voy',
        'call_sign',
        'pelabuhan_id',
        'namapelabuhan',
        'pel_muat',
        'pel_bongkar',
        'pel_transit',
        'kd_tps_tujuan',
        'kode_dokumen',
        'kd_dok_inout',
        'no_dok',
        'tgl_dok',
        'id_consignee',
        'consignee',
        'npwp_consignee',
        'no_spjm',
        'tgl_spjm',
        'no_sppb',
        'tgl_sppb',
        'nama_imp',
        'npwp_imp',
        'alamat_imp',
        'namaemkl',
        'telpemkl',
        'nopol',
        'tglcetakwo',
        'jamcetakwo',
        'tglsuratjalan',
        'jamsuratjalan',
        'uidsuratjalan',
        'nombl',
        'tgl_master_bl',
        'nopol_masuk',
        'nopol_mty',
        'nopol_release',
        'ref_number_out',
        'lokasi_gudang',
        'shipper',
        'notifyparty',
        'id_consolidator',
        'startstripping',
        'endstripping',
        'penagihan',
        'invoice',
        'esealcode',
        'tgl_respon',
        'jam_respon',
        'snm',
        'sna',
        'cnm',
        'cna',
        'nnm',
        'nna',
        'hscode',
        'eta_jam',
        'smr',
        'des',
        'no_kuitansi',
        'lokasi_tujuan',
        'flag_bc',
        'no_flag_bc',
        'description_flag_bc',
        'alasan_segel',
        'alasan_lepas_segel',
        'status_bc',
        'release_bc',
        'release_bc_date',
        'release_bc_uid',
        'sor_update',
        'perubahan_hbl',
        'alasan_perubahan',
        'bcf_consignee',
        'photo_stripping',
        'photo_release',
        'photo_release_in',
        'photo_release_out',
        'no_pabean',
        'tgl_pabean',
        'description_unflag_bc',
        'no_unflag_bc',
        'photo_lock',
        'photo_unlock',
        'status_behandle',
        'date_ready_behandle',
        'date_check_behandle',
        'date_finish_behandle',
        'desc_check_behandle',
        'desc_finish_behandle',
        'location_id',
        'location_name',
        'final_qty',
        'hasil_tally',
        'packing_tally',
        'telp_ppjk',
        'photo_behandle',
        'location_behandle',
        'keterangan_release',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }
    public function release()
    {
        return $this->belongsTo(User::class, 'release_bc_uid', 'id');
    }

    public function cont()
    {
        return $this->belongsTo(Container::class, 'container_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'joborder_id', 'id');
    }

    public function consolidator()
    {
        return $this->belongsTo(Consolidator::class, 'consolidator_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function shipperM()
    {
        return $this->belongsTo(Customer::class, 'shipper_id', 'id');
    }

    public function packing()
    {
        return $this->belongsTo(Packing::class, 'packing_id', 'id');
    }

    public function packingTally()
    {
        return $this->belongsTo(Packing::class, 'packing_tally', 'id');
    }

    public function dokumen()
    {
        return $this->belongsTo(KodeDok::class, 'kd_dok_inout', 'kode');
    }
    
    public function Rack()
    {
        return $this->belongsTo(PlacementManifest::class, 'racking', 'id');
    }

    public function BehandleLocation()
    {
        return $this->belongsTo(PlacementManifest::class, 'location_behandle', 'id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'manifest_id', 'id');
    }

    public function mostItemsLocation()
    {
        return $this->items()
                    ->select('lokasi_id', \DB::raw('COUNT(*) as total'))
                    ->groupBy('lokasi_id')
                    ->orderByDesc('total')
                    ->first();
    }
    
    public function lamaTimbun()
    {
        $tglStripping = Carbon::parse($this->tglstripping);
        $tglBuangMty = $this->tglbuangmty ? Carbon::parse($this->tglbuangmty) : Carbon::now();

        return $tglBuangMty->diffInDays($tglStripping);
    }

}