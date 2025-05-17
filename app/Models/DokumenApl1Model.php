<?php

namespace App\Models;

use CodeIgniter\Model;

class DokumenApl1Model extends Model
{
    protected $table            = 'dokumen_apl1';
    protected $primaryKey       = 'id_dokumen';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_apl1',
        'pas_foto',
        'bukti_pendidikan',
        'file_ktp',
        'raport',
        'sertifikat_pkl'
    ];

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    /**
     * Get dokumen by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getDokumenById(int $id)
    {
        return $this->find($id);
    }

    /**
     * Get dokumen by APL1 ID
     *
     * @param string $aplId
     * @return array|null
     */
    public function getDokumenByAplId(string $aplId)
    {
        return $this->where('id_apl1', $aplId)->first();
    }

    /**
     * Update specific document
     *
     * @param string $aplId
     * @param string $documentType
     * @param string $filename
     * @return bool
     */
    public function updateDocument(string $aplId, string $documentType, string $filename)
    {
        $dokumen = $this->where('id_apl1', $aplId)->first();

        if (!$dokumen) {
            return false;
        }

        // Make sure the document type is valid
        $allowedTypes = ['pas_foto', 'bukti_pendidikan', 'file_ktp', 'raport', 'sertifikat_pkl'];
        if (!in_array($documentType, $allowedTypes)) {
            return false;
        }

        return $this->update($dokumen['id_dokumen'], [$documentType => $filename]);
    }

    /**
     * Get all documents for an asesi
     *
     * @param string $asesiId
     * @return array
     */
    public function getDocumentsByAsesiId(string $asesiId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('dokumen_apl1 d');
        $builder->select('d.*');
        $builder->join('pengajuan_asesmen pa', 'pa.id_apl1 = d.id_apl1');
        $builder->where('pa.id_asesi', $asesiId);

        return $builder->get()->getResultArray();
    }

    /**
     * Get combined documents with pengajuan details
     *
     * @param string $aplId
     * @return array|null
     */
    public function getDocumentsWithPengajuanDetails(string $aplId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('dokumen_apl1 d');
        $builder->select('d.*, pa.id_asesi, pa.validasi_apl1, pa.');
        $builder->join('pengajuan_asesmen pa', 'pa.id_apl1 = d.id_apl1');
        $builder->where('d.id_apl1', $aplId);

        return $builder->get()->getRowArray();
    }

    /**
     * Check if a document exists
     *
     * @param string $aplId
     * @param string $documentType
     * @return bool
     */
    public function documentExists(string $aplId, string $documentType)
    {
        $dokumen = $this->where('id_apl1', $aplId)->first();

        if (!$dokumen) {
            return false;
        }

        return !empty($dokumen[$documentType]);
    }

    /**
     * Delete a specific document by updating its field to null
     *
     * @param string $aplId
     * @param string $documentType
     * @return bool
     */
    public function deleteDocument(string $aplId, string $documentType)
    {
        $dokumen = $this->where('id_apl1', $aplId)->first();

        if (!$dokumen) {
            return false;
        }

        // Make sure the document type is valid
        $allowedTypes = ['pas_foto', 'bukti_pendidikan', 'file_ktp', 'raport', 'sertifikat_pkl'];
        if (!in_array($documentType, $allowedTypes)) {
            return false;
        }

        return $this->update($dokumen['id_dokumen'], [$documentType => null]);
    }
}
