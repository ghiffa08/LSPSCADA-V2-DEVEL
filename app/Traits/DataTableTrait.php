<?php

namespace App\Traits;

/**
 * DataTable Trait
 * 
 * A reusable trait for models to handle DataTable server-side processing
 */
trait DataTableTrait
{
    /**
     * Get data for DataTable with pagination, search, and ordering
     *
     * @param int $limit Limit per page
     * @param int $start Offset
     * @param string $search Search keyword
     * @param string $orderColumn Column to order by
     * @param string $orderDir Order direction (asc/desc)
     * @param array $searchFields Fields to search in (defaults to $this->dataTableSearchFields)
     * @return array
     */
    public function getDataTable($limit = 10, $start = 0, $search = '', $orderColumn = null, $orderDir = 'asc', array $searchFields = [])
    {
        // Use provided search fields or default to model property if defined
        $fieldsToSearch = !empty($searchFields) ? $searchFields : ($this->dataTableSearchFields ?? []);

        // Start building the query
        $builder = $this;

        // Apply joins if defined in the model
        if (method_exists($this, 'applyDataTableJoins')) {
            $builder = $this->applyDataTableJoins($builder);
        }

        // Apply custom selects if defined in the model
        if (method_exists($this, 'applyDataTableSelects')) {
            $builder = $this->applyDataTableSelects($builder);
        }

        // Get total count before filtering
        $total = $builder->countAllResults(false);

        // Apply search if provided and search fields are defined
        if ($search !== '' && !empty($fieldsToSearch)) {
            $builder->groupStart();
            foreach ($fieldsToSearch as $index => $field) {
                if ($index === 0) {
                    $builder->like($field, $search);
                } else {
                    $builder->orLike($field, $search);
                }
            }
            $builder->groupEnd();
        }

        // Get filtered count
        $filtered = $builder->countAllResults(false);

        // Apply sorting
        if ($orderColumn) {
            $builder->orderBy($orderColumn, $orderDir);
        }

        // Apply limit and offset for pagination
        $data = $builder->limit($limit, $start)->get()->getResultArray();

        // Apply custom transformations if defined in the model
        if (method_exists($this, 'transformDataTableResults')) {
            $data = $this->transformDataTableResults($data);
        }

        return [
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data
        ];
    }
}
