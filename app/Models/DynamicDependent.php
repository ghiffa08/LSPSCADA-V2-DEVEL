<?php

namespace App\Models;

use CodeIgniter\Model;

class DynamicDependent extends Model
{
    /**
     * Mengambil semua data provinsi.
     * Nama fungsi ini sudah sesuai dengan yang dipanggil di controller.
     */
    public function AllProvinsi()
    {
        return $this->db->table('wilayah_provinsi')->get()->getResultArray();
    }

    /**
     * Mengambil data kabupaten berdasarkan ID provinsi.
     * Nama fungsi dan nama kolom 'provinsi_id' telah disesuaikan.
     */
    public function getKabupaten($id_provinsi)
    {
        return $this->db->table('wilayah_kabupaten')
            ->where('provinsi_id', $id_provinsi)
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil data kecamatan berdasarkan ID kabupaten.
     * Nama fungsi dan nama kolom 'kabupaten_id' telah disesuaikan.
     */
    public function getKecamatan($id_kabupaten)
    {
        return $this->db->table('wilayah_kecamatan')
            ->where('kabupaten_id', $id_kabupaten)
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil data desa/kelurahan berdasarkan ID kecamatan.
     * Nama fungsi dan nama kolom 'kecamatan_id' telah disesuaikan.
     */
    public function getKelurahan($id_kecamatan)
    {
        // Controller memanggil getKelurahan, jadi kita sesuaikan namanya dari AllDesa
        return $this->db->table('wilayah_desa')
            ->where('kecamatan_id', $id_kecamatan)
            ->get()
            ->getResultArray();
    }

    /**
     * FUNGSI KRUSIAL YANG HILANG: Mengambil nama wilayah berdasarkan ID.
     * Fungsi ini dibutuhkan oleh controller untuk menampilkan data profil yang sudah tersimpan.
     */
    public function getNamaWilayah(string $jenis, string $id)
    {
        if (empty($id)) {
            return '';
        }

        // Mapping dari input 'jenis' ke nama tabel yang sebenarnya
        $tabelMapping = [
            'provinsi'  => 'wilayah_provinsi',
            'kabupaten' => 'wilayah_kabupaten',
            'kecamatan' => 'wilayah_kecamatan',
            'kelurahan' => 'wilayah_desa'
        ];

        // Jika jenis tidak valid, kembalikan string kosong
        if (!isset($tabelMapping[$jenis])) {
            return '';
        }

        $tabel = $tabelMapping[$jenis];

        $result = $this->db->table($tabel)
            ->select('nama')
            ->where('id', $id) // Asumsi kolom primary key di semua tabel wilayah adalah 'id'
            ->get()
            ->getRow();

        return $result ? $result->nama : '';
    }
}
