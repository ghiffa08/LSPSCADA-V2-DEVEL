<?php

namespace App\Controllers;

use App\Traits\BaseConfigTrait;
use App\Services\DataTableService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Base DataTable Controller
 * 
 * Extend this controller for resources that need DataTable functionality
 */
abstract class DataTableController extends ResourceController
{
    use BaseConfigTrait;

    /**
     * DataTable Service instance
     *
     * @var DataTableService
     */
    protected $dataTableService;

    /**
     * Model instance to use with DataTable
     * 
     * @var object
     */
    protected $model;

    /**
     * Custom column mapping for ordering
     * If empty, will use DataTable's column data
     * 
     * @var array
     */
    protected $columnMap = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initBaseConfig();

        $this->dataTableService = new DataTableService();
    }

    /**
     * Process DataTable AJAX request
     * 
     * @return ResponseInterface
     */
    public function getDataTable()
    {
        // Verify AJAX request - uncomment for production
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized('Akses ditolak');
        }

        // Ensure model is set
        if (!$this->model) {
            return $this->fail('Model not configured for DataTable', 500);
        }

        // Process datatable request using the service
        return $this->dataTableService->process($this->model, $this->columnMap);
    }
}
