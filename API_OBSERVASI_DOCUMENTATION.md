# OBSERVASI API DOCUMENTATION

## Overview
Optimized RESTful API for managing observasi (observation) data in the LSP SCADA system. This API provides efficient endpoints for loading, saving, and managing KUK (Kriteria Unjuk Kerja) observation data with enhanced performance, security, and validation.

## Base URL
```
https://your-domain.com/api/observasi
```

## Authentication
All API endpoints require valid session authentication. Include CSRF token in requests.

### Headers
```
Content-Type: application/json
X-Requested-With: XMLHttpRequest
X-CSRF-TOKEN: {csrf_token}
```

---

## Endpoints

### 1. Load Observasi Data

**Endpoint:** `GET /api/observasi/load`

**Description:** Retrieve observasi data with existing observation records for a specific assessment and participant.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id_skema | integer | Yes | Schema ID |
| id_asesmen | integer | Yes | Assessment ID |
| id_asesi | integer | Yes | Participant ID |

**Example Request:**
```bash
GET /api/observasi/load?id_skema=1&id_asesmen=123&id_asesi=456
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Data berhasil dimuat",
  "data": {
    "observasi": [
      {
        "id_kelompok": 1,
        "nama_kelompok": "Kelompok A",
        "id_unit": 1,
        "kode_unit": "TIK.J02",
        "nama_unit": "Mengoperasikan PC dalam jaringan",
        "id_elemen": 1,
        "nama_elemen": "Mengidentifikasi kebutuhan jaringan",
        "id_kuk": 1,
        "kriteria_unjuk_kerja": "Kebutuhan aplikasi diidentifikasi"
      }
    ],
    "existing_data": {
      "1": {
        "kompeten": "Y",
        "keterangan": "Sudah kompeten melakukan identifikasi"
      }
    },
    "totalKUK": 25,
    "completedKUK": 12
  },
  "csrfHash": "new_csrf_token"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Parameter tidak valid",
  "errors": {
    "id_skema": "ID Skema wajib diisi"
  }
}
```

---

### 2. Save Batch Observasi

**Endpoint:** `POST /api/observasi/batch`

**Description:** Save multiple KUK observations in a single request for optimal performance.

**Request Body:**
```json
{
  "id_asesmen": 123,
  "id_skema": 1,
  "id_asesi": 456,
  "tanggal_observasi": "2024-12-19",
  "items": {
    "1": {
      "kompeten": "Y",
      "keterangan": "Kompeten melakukan identifikasi"
    },
    "2": {
      "kompeten": "N",
      "keterangan": "Perlu perbaikan"
    }
  }
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Batch data berhasil disimpan",
  "data": {
    "saved_count": 2,
    "updated_count": 1,
    "inserted_count": 1
  },
  "csrfHash": "new_csrf_token"
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "items[1].kompeten": "Status kompeten harus Y atau N",
    "items[2].keterangan": "Keterangan maksimal 500 karakter"
  }
}
```

---

### 3. Save Single KUK

**Endpoint:** `POST /api/observasi/single`

**Description:** Save individual KUK observation with real-time updates.

**Request Body:**
```json
{
  "id_asesmen": 123,
  "id_skema": 1,
  "id_asesi": 456,
  "id_kuk": 1,
  "kompeten": "Y",
  "keterangan": "Kompeten melakukan identifikasi",
  "tanggal_observasi": "2024-12-19"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Data KUK berhasil disimpan",
  "data": {
    "id": 789,
    "updated": true
  },
  "csrfHash": "new_csrf_token"
}
```

---

### 4. Get Progress Report

**Endpoint:** `GET /api/observasi/progress`

**Description:** Get detailed progress report for observations.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id_asesmen | integer | Yes | Assessment ID |
| id_asesi | integer | No | Specific participant (optional) |

**Example Request:**
```bash
GET /api/observasi/progress?id_asesmen=123&id_asesi=456
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "overview": {
      "total_kuk": 25,
      "completed_kuk": 18,
      "kompeten_count": 15,
      "belum_kompeten_count": 3,
      "progress_percentage": 72.0
    },
    "by_unit": [
      {
        "id_unit": 1,
        "nama_unit": "Mengoperasikan PC dalam jaringan",
        "total_kuk": 8,
        "completed_kuk": 6,
        "progress_percentage": 75.0
      }
    ],
    "recent_activity": [
      {
        "id_kuk": 1,
        "kriteria_unjuk_kerja": "Kebutuhan aplikasi diidentifikasi",
        "kompeten": "Y",
        "updated_at": "2024-12-19 10:30:00"
      }
    ]
  }
}
```

---

### 5. Get Statistics

**Endpoint:** `GET /api/observasi/statistics`

**Description:** Get comprehensive statistics for observasi data.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id_asesmen | integer | No | Filter by assessment |
| id_skema | integer | No | Filter by schema |
| date_from | date | No | Start date (Y-m-d) |
| date_to | date | No | End date (Y-m-d) |

**Example Request:**
```bash
GET /api/observasi/statistics?id_skema=1&date_from=2024-12-01&date_to=2024-12-31
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_observations": 150,
      "total_participants": 25,
      "average_completion": 78.5,
      "competency_rate": 85.2
    },
    "trends": [
      {
        "date": "2024-12-19",
        "observations": 12,
        "completion_rate": 80.0
      }
    ],
    "by_schema": [
      {
        "id_skema": 1,
        "nama_skema": "Teknisi Komputer",
        "participants": 15,
        "avg_completion": 82.3
      }
    ]
  }
}
```

---

### 6. Delete Observasi

**Endpoint:** `DELETE /api/observasi/delete`

**Description:** Delete observasi data for specific assessment and participant.

**Request Body:**
```json
{
  "id_asesmen": 123,
  "id_asesi": 456,
  "confirm": true
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Data observasi berhasil dihapus",
  "data": {
    "deleted_count": 25
  }
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not exists |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |

## Rate Limiting

API requests are limited to:
- 100 requests per hour per user
- 10 concurrent requests per user
- Bulk operations limited to 50 items per request

## Caching

- GET requests are cached for 5 minutes
- Cache is automatically invalidated on data updates
- ETags supported for conditional requests

## Validation Rules

### Common Validations
- `id_*` fields: Required, integer, greater than 0
- `tanggal_observasi`: Required, valid date (Y-m-d format), not in future
- `kompeten`: Required, must be 'Y' or 'N'
- `keterangan`: Optional, string, max 500 characters

### Batch Validations
- `items`: Required, object/array, not empty
- Maximum 50 items per batch
- Each item must contain valid `kompeten` value

## Performance Optimization

### Response Times
- Simple GET requests: < 200ms
- Batch operations: < 500ms
- Complex statistics: < 1000ms

### Database Optimization
- Optimized indexes on frequently queried columns
- Stored procedures for complex operations
- Connection pooling for concurrent requests

## Security Features

### Input Sanitization
- All string inputs sanitized against XSS
- SQL injection protection via prepared statements
- File upload restrictions (if applicable)

### CSRF Protection
- CSRF tokens required for all POST/PUT/DELETE requests
- Token rotation on successful requests
- Double-submit cookie pattern

### Access Control
- Session-based authentication
- Role-based permissions
- User action logging

## Sample Client Code

### JavaScript (Fetch API)
```javascript
// Load observasi data
async function loadObservasi(id_skema, id_asesmen, id_asesi) {
  try {
    const response = await fetch(`/api/observasi/load?id_skema=${id_skema}&id_asesmen=${id_asesmen}&id_asesi=${id_asesi}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error loading observasi:', error);
    throw error;
  }
}

// Save batch data
async function saveBatch(batchData) {
  try {
    const response = await fetch('/api/observasi/batch', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(batchData)
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Update CSRF token
      document.querySelector('meta[name="csrf-token"]').content = data.csrfHash;
      return data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error saving batch:', error);
    throw error;
  }
}
```

### PHP (cURL)
```php
// Save single KUK
function saveSingleKUK($id_asesmen, $id_skema, $id_asesi, $id_kuk, $kompeten, $keterangan) {
    $data = [
        'id_asesmen' => $id_asesmen,
        'id_skema' => $id_skema,
        'id_asesi' => $id_asesi,
        'id_kuk' => $id_kuk,
        'kompeten' => $kompeten,
        'keterangan' => $keterangan,
        'tanggal_observasi' => date('Y-m-d')
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://your-domain.com/api/observasi/single');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Requested-With: XMLHttpRequest',
        'X-CSRF-TOKEN: ' . csrf_hash()
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}
```

## Changelog

### Version 2.0.0 (2024-12-19)
- Complete API refactoring for performance
- Added batch operations
- Enhanced validation and security
- Implemented caching mechanisms
- Added comprehensive error handling
- Introduced rate limiting
- Added progress tracking
- Optimized database queries

### Version 1.0.0 (Previous)
- Basic CRUD operations
- Simple validation
- Limited error handling
