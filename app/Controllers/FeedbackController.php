<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class FeedbackController extends BaseController
{
    public function index()
    {
        return view('dashboard/feedback', [
            'siteTitle' => 'Umpan Balik',
            'listFeedback' => $this->feedback->findAll()
        ]);
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->feedback->delete($id);
        session()->setFlashdata('pesan', 'Umpan Balik berhasil dihapus!');
        return redirect()->to('/umpan-balik');
    }
}
