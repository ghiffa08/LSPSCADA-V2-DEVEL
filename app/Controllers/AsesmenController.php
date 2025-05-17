<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AsesmenController extends BaseController
{
    public function index()
    {
        $data = [
            'siteTitle' => 'Kelola Asesmen',
            'listSkema' => $this->skemaModel->getActiveSchemes(),
            'listAsesmen' => $this->asesmenModel->getAllAsesmen(),
            'listSettanggal' => $this->settanggalModel->findAll(),
            'listTUK' => $this->tukModel->findAll(),
        ];

        return view('admin/kelola_asesmen', $data);
    }

    public function getJadwal()
    {
        $id_skema = $this->request->getPost('id_skema');
        $tuk = $this->asesmenModel->getTuk($id_skema);
        $jadwal = $this->asesmenModel->getJadwal($id_skema);

        $options_tanggal = '';
        $options_tuk = '';
        $id_asesmen = '';

        if (!empty($jadwal)) {
            $options_tanggal .= '<option value="">--Pilih Jadwal Sertifikasi--</option>';
            foreach ($jadwal as $key => $row) {
                $options_tanggal .= '<option value="' . $row['id_tanggal'] . '">' . $row['keterangan'] . ' - ' . format_tanggal_indonesia($row['tanggal']) . ' - TUK: ' . $row['nama_tuk'] . '</option>';
                $id_asesmen = $row['id_asesmen'];
            }
        }

        $response = [
            'options_tanggal' => $options_tanggal,
            '' => $id_asesmen
        ];

        echo json_encode($response);
    }
}
