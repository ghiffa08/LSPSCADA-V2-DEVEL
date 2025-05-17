<?php

namespace App\Services;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * DataTable Service
 * 
 * A reusable service for handling DataTable server-side processing in CodeIgniter 4
 */
class DataTableService
{
    /**
     * CI4 Request instance
     *
     * @var IncomingRequest
     */
    protected $request;

    /**
     * CI4 Response instance
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->request = service('request');
        $this->response = service('response');
    }

    /**
     * Process DataTable request and return formatted response
     *
     * @param object $model Model instance that has getDataTable method
     * @param array $columns Optional custom column configuration
     * @return ResponseInterface
     */
    public function process($model, array $columns = [])
    {
        // Extract DataTable parameters
        $draw = $this->request->getVar('draw');
        $limit = (int)$this->request->getVar('length');
        $start = (int)$this->request->getVar('start');
        $search = $this->request->getVar('search')['value'] ?? '';

        // Extract ordering information
        $order = $this->request->getVar('order')[0] ?? null;
        $orderColumn = null;
        $orderDir = 'asc';

        if ($order) {
            $columnIndex = $order['column'];
            $orderDir = $order['dir'];

            // If custom columns provided, use them
            if (!empty($columns)) {
                $orderColumn = $columns[$columnIndex] ?? null;
            } else {
                // Otherwise use DataTable's column data
                $orderColumn = $this->request->getVar('columns')[$columnIndex]['data'] ?? null;
            }
        }

        // Call model's getDataTable method
        $result = $model->getDataTable($limit, $start, $search, $orderColumn, $orderDir);

        // Format and return response
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $result['data']
        ]);
    }
}
