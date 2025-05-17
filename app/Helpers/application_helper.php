<?php

/**
 * Application Helper Functions
 * 
 * Collection of helper functions for the assessment application system
 */

if (!function_exists('generate_application_id')) {
    /**
     * Generate a daily incremental application ID in CodeIgniter 4.
     *
     * Format: [TYPE]-[YYYYMMDD]-[INCREMENT]
     * Example: APL01-20250430-0001
     *
     * @param string $type     Prefix of the ID (e.g., 'APL01', 'APL02')
     * @param string $table    Database table name
     * @param string $column   Column name where the ID is stored
     * @return string
     */
    function generate_application_id(
        string $type = 'APL01',
        string $table = 'applications',
        string $column = 'application_id'
    ): string {
        $db = \Config\Database::connect();
        $date = date('Ymd');
        $prefix = "{$type}-{$date}-";

        // Ambil ID terakhir yang sesuai prefix
        $builder = $db->table($table);
        $builder->select($column);
        $builder->like($column, $prefix, 'after');
        $builder->orderBy($column, 'DESC');
        $builder->limit(1);

        $query = $builder->get();
        $lastId = $query->getRow() ? $query->getRow()->$column : null;

        $nextIncrement = '0001';

        if ($lastId) {
            $lastNumber = (int) substr($lastId, -4);
            $nextIncrement = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . $nextIncrement;
    }
}


if (!function_exists('format_tanggal_indonesia')) {
    /**
     * Format date to Indonesian format
     *
     * @param string|null $date
     * @return string
     */
    function format_tanggal_indonesia(?string $date): string
    {
        if (empty($date)) {
            return '';
        }

        // Ambil hanya bagian tanggal jika ada waktu
        $dateOnly = explode(' ', $date)[0];

        // Jika format dd/mm/yyyy
        if (strpos($dateOnly, '/') !== false) {
            $split = explode('/', $dateOnly);
            if (count($split) === 3) {
                $day = ltrim($split[0], '0');
                $month = (int)$split[1];
                $year = $split[2];
            } else {
                return $date;
            }
        }
        // Jika format yyyy-mm-dd
        elseif (strpos($dateOnly, '-') !== false) {
            $split = explode('-', $dateOnly);
            if (count($split) === 3) {
                $day = ltrim($split[2], '0');
                $month = (int)$split[1];
                $year = $split[0];
            } else {
                return $date;
            }
        } else {
            return $date;
        }

        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return "$day {$bulan[$month]} $year";
    }
}

if (!function_exists('get_file_extension')) {
    /**
     * Get file extension from mime type
     *
     * @param string $mime_type
     * @return string
     */
    function get_file_extension(string $mime_type): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        return $extensions[$mime_type] ?? 'unknown';
    }
}

if (!function_exists('status_badge')) {
    /**
     * Generate HTML badge for status
     *
     * @param string $status
     * @return string
     */
    function status_badge(string $status): string
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Menunggu</span>',
            'approved' => '<span class="badge bg-success">Disetujui</span>',
            'rejected' => '<span class="badge bg-danger">Ditolak</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename to be safe for storage
     *
     * @param string $filename
     * @return string
     */
    function sanitize_filename(string $filename): string
    {
        // Remove any character that is not alphanumeric, dot, hyphen or underscore
        $filename = preg_replace('/[^\w\.-]/', '_', $filename);

        // Ensure filename doesn't start with a dot (hidden file)
        $filename = ltrim($filename, '.');

        return $filename;
    }
}


// Helper function to set form value
function setFormValue($field, $applicantData = null)
{
    // If there's old input (from failed validation), use that first
    if (old($field) !== null) {
        return old($field);
    }

    // If we have existing data from database, use that
    if ($applicantData && isset($applicantData[$field])) {
        return $applicantData[$field];
    }

    // Otherwise return empty
    return '';
}

// Helper function to check if an option should be selected

if (!function_exists('isSelected')) {
    /**
     * Check if an option should be selected in dropdown
     * 
     * @param mixed $value The option value
     * @param string $field The field name
     * @param array|object|null $existingData Data from database (for edit mode)
     * @return string 'selected' if the option should be selected, empty string otherwise
     */
    function isSelected($value, $field, $existingData = null)
    {
        // If there's old input (from failed validation)
        if (old($field) !== null) {
            return old($field) == $value ? 'selected' : '';
        }

        // If we have existing data from database
        if ($existingData) {
            if (is_object($existingData) && isset($existingData->$field)) {
                return $existingData->$field == $value ? 'selected' : '';
            } elseif (is_array($existingData) && isset($existingData[$field])) {
                return $existingData[$field] == $value ? 'selected' : '';
            }
        }

        return '';
    }
}

if (!function_exists('renderSelectOptions')) {
    /**
     * Render select options for dropdown
     * 
     * @param array $data Array of data
     * @param string $defaultText Default option text
     * @param mixed $selectedValue Currently selected value
     * @param string $valueField Field to use as value (default: 'id')
     * @param string $textField Field to use as text (default: 'nama')
     * @return string HTML options for select element
     */
    function renderSelectOptions($data, $defaultText = '-- Please Select --', $selectedValue = null, $valueField = 'id', $textField = 'nama')
    {
        $options = '<option value="">' . $defaultText . '</option>';

        foreach ($data as $item) {
            $value = is_array($item) ? $item[$valueField] : $item->$valueField;
            $text = is_array($item) ? $item[$textField] : $item->$textField;
            $selected = ($selectedValue == $value) ? 'selected' : '';

            $options .= '<option value="' . $value . '" ' . $selected . '>' . $text . '</option>';
        }

        return $options;
    }
}

if (!function_exists('getDropdownValue')) {
    /**
     * Get dropdown value considering both old input and existing data
     * 
     * @param string $field Field name
     * @param array|object|null $existingData Data from database (for edit mode)
     * @return mixed The value to use
     */
    function getDropdownValue($field, $existingData = null)
    {
        // If there's old input (from failed validation)
        if (old($field) !== null) {
            return old($field);
        }

        // If we have existing data from database
        if ($existingData) {
            if (is_object($existingData) && isset($existingData->$field)) {
                return $existingData->$field;
            } elseif (is_array($existingData) && isset($existingData[$field])) {
                return $existingData[$field];
            }
        }

        return null;
    }
}
