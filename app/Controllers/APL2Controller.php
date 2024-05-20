<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use TCPDF;

class APL2Controller extends BaseController
{
    public function index()
    {
        $dataAPL1 = $this->apl1->getAllAPL1(user()->id);
        $data = [
            'siteTitle' => "Asesmen Mandiri",
            'dataAPL1' => $dataAPL1,
            // 'listSkema' => $this->skema->AllSkema(),
            'listUnit' => $this->unit->getUnit($dataAPL1['id_skema']),
            'listElemen' => $this->elemen->AllElemen(),
            'listSubelemen' => $this->subelemen->getbySkema($dataAPL1['id_skema']),
            // 'listPertanyaan' => $this->subelemen->getbyUnit($dataAPL1['id_unit']),

        ];

        return view('dashboard/apl2', $data);
        // dd($data);
    }

    public function asesmen($name)
    {
        $dataAPL1 = $this->apl1->getAPL1Name($name);
        $data = [
            'siteTitle' => "Asesmen Mandiri",
            'dataAPL1' => $dataAPL1,
            'listUnit' => $this->unit->getUnit($dataAPL1['id_skema']),
            'listElemen' => $this->elemen->AllElemen(),
            'listSubelemen' => $this->subelemen->getbySkema($dataAPL1['id_skema']),

        ];

        return view('dashboard/apl2', $data);
        // dd($data);
    }



    public function store()
    {

        $dataAPL1 = $this->apl1->getAllAPL1(user()->id);
        $listSubelemen = $this->subelemen->getbySkema($dataAPL1['id_skema']);

        $rules = [];
        foreach ($listSubelemen as $subelemen) {
            $rules['bk_' . $subelemen['id_subelemen']] = [
                'label' => $subelemen['pertanyaan'],
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ];
            if ($this->request->getPost('bk_' . $subelemen['id_subelemen']) == "K") {
                $rules['bukti_pendukung_' . $subelemen['id_subelemen']] = [
                    'label' => 'Bukti Pendukung',
                    'rules' => 'uploaded[bukti_pendukung_' . $subelemen['id_subelemen'] . ']',
                    'errors' => [
                        'uploaded' => 'Harus upload {field} *',
                    ],
                ];
            }
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [];

        foreach ($listSubelemen as $subelemen) {

            $data['tk'] = [
                'tk' => $this->request->getPost('bk_' . $subelemen['id_subelemen']),
            ];
            $data['id_skema'] = [
                'id_skema' => $this->request->getPost('id_skema_' . $subelemen['id_subelemen']),
            ];
            $data['id_unit'] = [
                'id_unit' => $this->request->getPost('id_unit_' . $subelemen['id_subelemen']),
            ];
            $data['id_elemen'] = [
                'id_elemen' => $this->request->getPost('id_elemen_' . $subelemen['id_subelemen']),
            ];
            $data['id_subelemen'] = [
                'id_subelemen' => $this->request->getPost('id_subelemen_' . $subelemen['id_subelemen']),
            ];
            $data['id_user'] = [
                'id_user' => $this->request->getPost('id_user_' . $subelemen['id_subelemen']),
            ];

            if ($this->request->getPost('bk_' . $subelemen['id_subelemen']) == "K") {
                $file = $this->request->getFile('bukti_pendukung_' . $subelemen['id_subelemen']);
                $nama_file = $file->getRandomName();
                $file->move('upload/bukti pendukung', $nama_file);

                $data['bukti_pendukung'] = [
                    'bukti_pendukung' => $nama_file,
                ];
            } else {
                unset($data['bukti_pendukung']);
            }

            $this->apl2->save($data);
        }



        session()->setFlashdata('pesan', 'Subelemen berhasil ditambahkan!');
        return redirect()->to('/asesmen-mandiri');
    }

    public function pdf($id_apl1)
    {
        $dataAPL1 = $this->apl1->getAPL1byid($id_apl1);
        $listUnit = $this->unit->getUnit($dataAPL1['id_skema']);
        $listElemen = $this->elemen->AllElemen();
        $listAsesi = $this->apl2->getbySkema($dataAPL1['id_skema']);


        // Create a new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

        // Set title based on user's full name
        $pdf->SetTitle('FR.APL.02. ' . $dataAPL1['nama_siswa']);

        // Set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'LEMBAGA SERTIFIKASI PROFESI - P1 SMK NEGERI 2 KUNINGAN', PDF_HEADER_STRING);

        // Set header and footer fonts
        $pdf->SetHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->SetFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        // $pdf->SetAutoPageBreak(true, 5);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Add a page
        $pdf->AddPage();

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
              <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
              }

            </style>
            <body>
            ';

        $html .= '
        <h4>FR.APL.02. ASESMEN MANDIRI</h4>

        <table>
        <tr>
            <td rowspan="2" style="width: 25%;">Skema Sertifikasi
            (KKNI/Okupasi/Klaster)</td>  
            <td style="width: 10%;">Judul </td>
            <td style="width: 5%;">:</td>
            <td style="width: 60%;">' . $dataAPL1['nama_skema'] . '</td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td>:</td>
        </tr>

        </table>

        <h4></h4>

        <table>
        <tr>
            <td>PANDUAN ASESMEN MANDIRI</td>  
        </tr>
         <tr>
            <td>Instruksi:</td>
        </tr>
        <tr>
            <td>
            <ul>
            <li>Baca setiap pertanyaan di kolom sebelah kiri.</li>
            <li>Beri tanda centang pada kotak jika anda yakin dapat melakukan tugas yang dijelaskan.</li>
            <li>Isi kolom di sebelah kanan dengan mendaftar bukti yang Anda miliki untuk menunukan bahwa anda melakukan tugas-tugas ini.</li>
            </ul>
            </td>
        </tr>
        

        </table>
        
            ';
        foreach ($listUnit as $unit) {
            $html .= '
        <h4></h4>
        <table>
        <tr>
            <td rowspan="2" style="width: 25%;">Unit Kompetensi</td>  
            <td style="width: 10%;">Kode Unit</td>
            <td style="width: 5%;">:</td>
            <td style="width: 60%;">' . $unit['kode_unit'] . '</td>
        </tr>
        <tr>
            <td>Judul Unit</td>
            <td>:</td>
            <td>' . $unit['nama_unit'] .
                '</td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td style="width: 60%;">Dapatkah Saya ...?</td>
            <td style="width:5%;">K</td>
            <td style="width: 5%;">BK</td>
            <td style="width: 30%;">Bukti Yang Relevan</td>
        </tr>
         ';
            $noElemen = 0;
            foreach ($listElemen as $elemen) {
                if ($elemen['id_unit'] == $unit['id_unit']) {
                    $noElemen++;

                    $html .= '
            <tr>
                <td colspan="4">' . $noElemen . '. Elemen: ' . $elemen['nama_elemen'] . '</td>
            </tr>
         ';
                    $no = 0;
                    foreach ($listAsesi as $asesi) {
                        if ($asesi['id_apl1'] == $dataAPL1['id_apl1']) {
                            if ($asesi['id_elemen'] == $elemen['id_elemen'] && $unit['id_unit']) {
                                $no++;

                                $html .=
                                    '
            <tr>
                <td>' . $noElemen . '.' . $no . '. ' . $asesi['pertanyaan'] . '</td>
                <td colspan="2" style="text-align: center;">' . $asesi['tk'] . '</td>
                <td>' . $asesi['bukti_pendukung'] . '</td>
            </tr>
         ';
                            }
                        }
                    }
                }
            }
            $html .=
                '
        </table>
         ';
        }
        $html .= '
            </body>
            </html>';


        $pdf->lastPage();

        // Output the HTML content to the PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output the PDF as attachment to browser
        $this->response->setContentType('application/pdf');
        $pdf->Output('FR.APL.02. ' . $dataAPL1['nama_siswa'] . '.pdf', 'I');
    }
}
