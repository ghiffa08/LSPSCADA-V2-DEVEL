<?php

namespace App\Controllers\Api;

use App\Controllers\DataTableController;
use App\Models\RekamanAsesmenModel;
use CodeIgniter\API\ResponseTrait;

class RekamanAsesmenAdmin extends DataTableController
{
    use ResponseTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = new RekamanAsesmenModel();

        // Peta kolom ini digunakan untuk sorting
        $this->columnMap = [
            0 => null, // No
            1 => 'apl1.nama_siswa',
            2 => null, // Checkbox
            3 => 'asesor_user.nama_lengkap',
            4 => 'skema.nama_skema',
            5 => 'rekaman_asesmen.tanggal_rekaman',
            6 => 'rekaman_asesmen.rekomendasi',
            7 => null, // Aksi
        ];
    }

    /**
     * Menyediakan data untuk Server-Side DataTables.
     * DIADAPTASI AGAR SAMA DENGAN KODE PMO YANG SUDAH BERHASIL
     */
    public function list(): \CodeIgniter\HTTP\ResponseInterface
    {
        $request = service('request');
        $postData = $request->getPost();

        // Ambil parameter dari request DataTables
        $limit = (int) ($postData['length'] ?? 10);
        $start = (int) ($postData['start'] ?? 0);
        $search = $postData['search']['value'] ?? '';

        // Logika untuk ordering
        $orderColumnIndex = $postData['order'][0]['column'] ?? null;
        $orderDir = $postData['order'][0]['dir'] ?? 'asc';

        // Gunakan $this->columnMap yang sudah didefinisikan di constructor
        $orderColumn = $this->columnMap[$orderColumnIndex] ?? null;

        // Ambil filter kustom
        // CATATAN PENTING: DataTableTrait Anda saat ini belum bisa menangani filter ini.
        // Jika filter ingin berfungsi, DataTableTrait Anda perlu di-upgrade.
        // Untuk sekarang, ini tidak akan menyebabkan error.
        $filters = [
            'rekaman_asesmen.id_asesor'         => $request->getPost('id_asesor'),
            'skema.id_skema'                    => $request->getPost('id_skema'),
            'rekaman_asesmen.tanggal_rekaman >=' => $request->getPost('tanggal_dari'),
            'rekaman_asesmen.tanggal_rekaman <=' => $request->getPost('tanggal_sampai'),
        ];

        // Panggil method dari trait dengan parameter individual yang benar
        $result = $this->model->getDataTable($limit, $start, $search, $orderColumn, $orderDir);

        // Ubah format output agar sesuai dengan yang diharapkan DataTables
        $output = [
            "draw"            => (int) ($postData['draw'] ?? 0),
            "recordsTotal"    => $result['total'],
            "recordsFiltered" => $result['filtered'],
            "data"            => $result['data'],
            "csrf_token"      => csrf_hash()
        ];

        return $this->respond($output);
    }


    /**
     * Menghapus (soft delete) data rekaman asesmen.
     */
    public function delete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$id || !is_numeric($id)) {
            return $this->fail('ID tidak valid.', 400);
        }

        // Model sudah dikonfigurasi untuk soft delete ($useSoftDeletes = true)
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['status' => true, 'message' => 'Data rekaman berhasil diarsipkan.']);
        }

        return $this->fail('Gagal mengarsipkan data rekaman.', 500);
    }
}
