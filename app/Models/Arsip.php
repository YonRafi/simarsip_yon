<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    protected $table = 'arsip';
    protected $primaryKey = 'ID_ARSIP';
    public $timestamps = true;

    protected $fillable = [
        'ID_DOKUMEN',
        'NO_DOK_PENGANGKATAN',
        'NO_DOK_SURAT_PINDAH',
        'NO_DOK_PERCERAIAN',
        'NO_DOK_PENGESAHAN',
        'ID_OPERATOR',
        'ID_HISTORY',
        'NO_DOK_KEMATIAN',
        'NO_DOK_KELAHIRAN',
        'NO_DOK_PENGAKUAN',
        'NO_DOK_PERKAWINAN',
        'NO_DOK_KK',
        'NO_DOK_SKOT',
        'NO_DOK_SKTT',
        'NO_DOK_KTP',
        'JUMLAH_BERKAS',
        'NO_BUKU',
        'NO_RAK',
        'NO_BARIS',
        'NO_BOKS',
        'LOK_SIMPAN',
        'TANGGAL_PINDAI',
        'KETERANGAN'
    ];

    public function operator()
    {
        return $this->belongsTo(Operator::class, 'ID_OPERATOR');
    }

    public function historyPelayanan()
    {
        return $this->belongsTo(HistoryPelayanan::class, 'ID_HISTORY');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, 'ID_DOKUMEN');
    }

    public function infoArsipPengangkatan()
    {
        return $this->belongsTo(InfoArsipPengangkatan::class, 'NO_DOK_PENGANGKATAN');
    }

    public function infoArsipSuratPindah()
    {
        return $this->belongsTo(InfoArsipSuratPindah::class, 'NO_DOK_SURAT_PINDAH');
    }

    public function infoArsipPerceraian()
    {
        return $this->belongsTo(InfoArsipPerceraian::class, 'NO_DOK_PERCERAIAN');
    }

    public function infoArsipPengesahan()
    {
        return $this->belongsTo(InfoArsipPengesahan::class, 'NO_DOK_PENGESAHAN');
    }

    public function infoArsipKematian()
    {
        return $this->belongsTo(InfoArsipKematian::class, 'NO_DOK_KEMATIAN');
    }

    public function infoArsipKelahiran()
    {
        return $this->belongsTo(InfoArsipKelahiran::class, 'NO_DOK_KELAHIRAN');
    }

    public function infoArsipPengakuan()
    {
        return $this->belongsTo(InfoArsipPengakuan::class, 'NO_DOK_PENGAKUAN');
    }

    public function infoArsipPerkawinan()
    {
        return $this->belongsTo(InfoArsipPerkawinan::class, 'NO_DOK_PERKAWINAN');
    }

    public function infoArsipKk()
    {
        return $this->belongsTo(InfoArsipKk::class, 'NO_DOK_KK');
    }

    public function infoArsipSkot()
    {
        return $this->belongsTo(InfoArsipSkot::class, 'NO_DOK_SKOT');
    }

    public function infoArsipSktt()
    {
        return $this->belongsTo(InfoArsipSktt::class, 'NO_DOK_SKTT');
    }

    public function infoArsipKtp()
    {
        return $this->belongsTo(InfoArsipKtp::class, 'NO_DOK_KTP');
    }
}
