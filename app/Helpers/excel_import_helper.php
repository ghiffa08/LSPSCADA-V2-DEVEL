<?php

/**
 * Excel Import Helper Functions
 *
 * Helper functions for Excel Import operations
 */

if (!function_exists('prepare_excel_import_config')) {
    /**
     * Prepare Excel import configuration
     *
     * @param string $modelClass The model class to use for importing
     * @param array $columnMapping Column mapping configuration
     * @param array $options Additional options
     * @return array Import configuration
     */
    function prepare_excel_import_config(string $modelClass, array $columnMapping, array $options = []): array
    {
        $defaultOptions = [
            'has_header' => true,
            'commit_on_error' => true,
            'additional_data' => null
        ];

        $options = array_merge($defaultOptions, $options);

        return [
            'model' => $modelClass,
            'has_header' => $options['has_header'],
            'commit_on_error' => $options['commit_on_error'],
            'columns' => $columnMapping,
            'additional_data' => $options['additional_data']
        ];
    }
}

if (!function_exists('build_column_config')) {
    /**
     * Build column configuration for Excel import
     *
     * @param int $index Column index (0-based)
     * @param array $options Additional column options
     * @return array Column configuration
     */
    function build_column_config(int $index, array $options = []): array
    {
        $config = [
            'index' => $index
        ];

        // Add optional flag if specified
        if (isset($options['optional'])) {
            $config['optional'] = $options['optional'];
        }

        // Add transformation function if specified
        if (isset($options['transform']) && is_callable($options['transform'])) {
            $config['transform'] = $options['transform'];
        }

        // Add foreign key resolver if specified
        if (isset($options['resolver'])) {
            $config['resolver'] = $options['resolver'];
        }

        return $config;
    }
}

if (!function_exists('foreign_key_resolver')) {
    /**
     * Create a foreign key resolver configuration
     *
     * @param string $table Foreign table name
     * @param string $displayField Display field in foreign table
     * @param string $idField ID field in foreign table
     * @param array $options Additional resolver options
     * @return array Resolver configuration
     */
    function foreign_key_resolver(string $table, string $displayField, string $idField = 'id', array $options = []): array
    {
        $resolver = [
            'table' => $table,
            'display_field' => $displayField,
            'id_field' => $idField
        ];

        // Add create_if_not_exists if specified
        if (isset($options['create_if_not_exists'])) {
            $resolver['create_if_not_exists'] = $options['create_if_not_exists'];
        }

        // Add additional where conditions if specified
        if (isset($options['where']) && is_array($options['where'])) {
            $resolver['where'] = $options['where'];
        }

        // Add additional fields callback if specified
        if (isset($options['additional_fields']) && is_callable($options['additional_fields'])) {
            $resolver['additional_fields'] = $options['additional_fields'];
        }

        return $resolver;
    }
}

if (!function_exists('get_import_result_message')) {
    /**
     * Generate import result message
     *
     * @param array $result Import result
     * @return string Formatted message
     */
    function get_import_result_message(array $result): string
    {
        if ($result['status'] === 'success') {
            return $result['message'];
        } else if ($result['status'] === 'partial') {
            $failedCount = count($result['failed_rows'] ?? []);
            return "Berhasil mengimpor {$result['success_count']} data, gagal {$failedCount} data.";
        } else {
            return $result['message'] ?? 'Terjadi kesalahan saat import data.';
        }
    }
}
