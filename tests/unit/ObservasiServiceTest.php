<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\ObservasiService;
use App\Models\ObservasiModel;
use App\Requests\ObservasiRequest;

class ObservasiServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $seed = 'ObservasiTestSeeder';

    protected $service;
    protected $model;
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ObservasiService();
        $this->model = new ObservasiModel();
        $this->request = new ObservasiRequest();
    }

    protected function tearDown(): void
    {
        // Clear cache after each test
        cache()->clean();
        parent::tearDown();
    }

    // Test load observasi functionality
    public function testLoadObservasiWithValidData()
    {
        $params = [
            'id_skema' => 1,
            'id_asesmen' => 1,
            'id_asesi' => 1
        ];

        $result = $this->service->loadObservasi($params);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('observasi', $result);
        $this->assertArrayHasKey('existing_data', $result);
        $this->assertArrayHasKey('totalKUK', $result);
        $this->assertIsArray($result['observasi']);
    }

    public function testLoadObservasiWithInvalidData()
    {
        $params = [
            'id_skema' => -1,
            'id_asesmen' => 'invalid',
            'id_asesi' => null
        ];

        $result = $this->service->loadObservasi($params);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
    }

    // Test batch save functionality
    public function testBatchSaveWithValidData()
    {
        $data = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'tanggal_observasi' => date('Y-m-d'),
            'items' => [
                '1' => [
                    'kompeten' => 'Y',
                    'keterangan' => 'Test keterangan 1'
                ],
                '2' => [
                    'kompeten' => 'N',
                    'keterangan' => 'Test keterangan 2'
                ]
            ]
        ];

        $result = $this->service->saveBatch($data);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('saved_count', $result);
        $this->assertEquals(2, $result['saved_count']);
    }

    public function testBatchSaveWithEmptyItems()
    {
        $data = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'tanggal_observasi' => date('Y-m-d'),
            'items' => []
        ];

        $result = $this->service->saveBatch($data);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Items tidak boleh kosong', $result['message']);
    }

    public function testBatchSaveWithInvalidKompeten()
    {
        $data = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'tanggal_observasi' => date('Y-m-d'),
            'items' => [
                '1' => [
                    'kompeten' => 'INVALID',
                    'keterangan' => 'Test keterangan'
                ]
            ]
        ];

        $result = $this->service->saveBatch($data);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
    }

    // Test single KUK save functionality
    public function testSaveSingleKukWithValidData()
    {
        $data = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'id_kuk' => 1,
            'kompeten' => 'Y',
            'keterangan' => 'Single KUK test',
            'tanggal_observasi' => date('Y-m-d')
        ];

        $result = $this->service->saveSingle($data);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result);
    }

    public function testSaveSingleKukWithLongKeterangan()
    {
        $longKeterangan = str_repeat('a', 501); // Exceeds 500 character limit

        $data = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'id_kuk' => 1,
            'kompeten' => 'Y',
            'keterangan' => $longKeterangan,
            'tanggal_observasi' => date('Y-m-d')
        ];

        $result = $this->service->saveSingle($data);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('maksimal 500 karakter', $result['message']);
    }

    // Test delete functionality
    public function testDeleteObservasiWithValidData()
    {
        // First save some data
        $saveData = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'id_kuk' => 1,
            'kompeten' => 'Y',
            'keterangan' => 'To be deleted',
            'tanggal_observasi' => date('Y-m-d')
        ];
        $this->service->saveSingle($saveData);

        // Then delete
        $deleteData = [
            'id_asesmen' => 1,
            'id_asesi' => 1
        ];

        $result = $this->service->deleteObservasi($deleteData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('deleted_count', $result);
    }

    // Test progress tracking
    public function testGetProgressReport()
    {
        // Save some test data first
        $data1 = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'id_kuk' => 1,
            'kompeten' => 'Y',
            'keterangan' => 'Kompeten',
            'tanggal_observasi' => date('Y-m-d')
        ];
        $this->service->saveSingle($data1);

        $data2 = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'id_kuk' => 2,
            'kompeten' => 'N',
            'keterangan' => 'Belum kompeten',
            'tanggal_observasi' => date('Y-m-d')
        ];
        $this->service->saveSingle($data2);

        $params = [
            'id_asesmen' => 1,
            'id_asesi' => 1
        ];

        $result = $this->service->getProgressReport($params);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('overview', $result);
        $this->assertArrayHasKey('total_kuk', $result['overview']);
        $this->assertArrayHasKey('completed_kuk', $result['overview']);
        $this->assertArrayHasKey('progress_percentage', $result['overview']);
    }

    // Test statistics functionality
    public function testGetStatistics()
    {
        // Save some test data first
        $testData = [
            [
                'id_asesmen' => 1,
                'id_skema' => 1,
                'id_asesi' => 1,
                'id_kuk' => 1,
                'kompeten' => 'Y',
                'keterangan' => 'Test 1',
                'tanggal_observasi' => date('Y-m-d')
            ],
            [
                'id_asesmen' => 1,
                'id_skema' => 1,
                'id_asesi' => 2,
                'id_kuk' => 1,
                'kompeten' => 'N',
                'keterangan' => 'Test 2',
                'tanggal_observasi' => date('Y-m-d')
            ]
        ];

        foreach ($testData as $data) {
            $this->service->saveSingle($data);
        }

        $params = [
            'id_skema' => 1,
            'date_from' => date('Y-m-d'),
            'date_to' => date('Y-m-d')
        ];

        $result = $this->service->getStatistics($params);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('total_observations', $result['summary']);
    }

    // Test caching functionality
    public function testCacheImplementation()
    {
        $params = [
            'id_skema' => 1,
            'id_asesmen' => 1,
            'id_asesi' => 1
        ];

        // First call - should hit database
        $result1 = $this->service->loadObservasi($params);

        // Second call - should hit cache
        $result2 = $this->service->loadObservasi($params);

        $this->assertEquals($result1, $result2);
        $this->assertTrue($result1['success']);
        $this->assertTrue($result2['success']);
    }

    public function testCacheClearAfterSave()
    {
        $loadParams = [
            'id_skema' => 1,
            'id_asesmen' => 1,
            'id_asesi' => 1
        ];

        // Load data to cache it
        $this->service->loadObservasi($loadParams);

        // Save new data (should clear cache)
        $saveData = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'id_kuk' => 1,
            'kompeten' => 'Y',
            'keterangan' => 'New data',
            'tanggal_observasi' => date('Y-m-d')
        ];

        $saveResult = $this->service->saveSingle($saveData);
        $this->assertTrue($saveResult['success']);

        // Load again - should reflect new data
        $result = $this->service->loadObservasi($loadParams);
        $this->assertTrue($result['success']);
    }

    // Test validation methods
    public function testValidateLoadData()
    {
        $validData = [
            'id_skema' => 1,
            'id_asesmen' => 1,
            'id_asesi' => 1
        ];

        $validation = $this->request->validateLoad($validData);
        $this->assertTrue($validation['valid']);

        $invalidData = [
            'id_skema' => 'invalid',
            'id_asesmen' => -1
            // missing id_asesi
        ];

        $validation = $this->request->validateLoad($invalidData);
        $this->assertFalse($validation['valid']);
        $this->assertArrayHasKey('errors', $validation);
    }

    public function testValidateBatchData()
    {
        $validData = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'tanggal_observasi' => date('Y-m-d'),
            'items' => [
                '1' => [
                    'kompeten' => 'Y',
                    'keterangan' => 'Valid keterangan'
                ]
            ]
        ];

        $validation = $this->request->validateBatch($validData);
        $this->assertTrue($validation['valid']);

        $invalidData = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'tanggal_observasi' => 'invalid-date',
            'items' => [
                '1' => [
                    'kompeten' => 'INVALID'
                ]
            ]
        ];

        $validation = $this->request->validateBatch($invalidData);
        $this->assertFalse($validation['valid']);
    }

    // Test data sanitization
    public function testDataSanitization()
    {
        $dirtyData = [
            'keterangan' => '<script>alert("xss")</script>Normal text',
            'id_kuk' => '123',
            'kompeten' => 'y'
        ];

        $sanitized = $this->request->sanitize($dirtyData);

        $this->assertEquals('alert("xss")Normal text', $sanitized['keterangan']);
        $this->assertEquals('123', $sanitized['id_kuk']);
        $this->assertEquals('y', $sanitized['kompeten']);
    }

    // Test rate limiting
    public function testRateLimiting()
    {
        $identifier = 'test_user_123';

        // Should allow first request
        $allowed1 = $this->request->checkRateLimit($identifier, 2, 3600);
        $this->assertTrue($allowed1);

        // Should allow second request
        $allowed2 = $this->request->checkRateLimit($identifier, 2, 3600);
        $this->assertTrue($allowed2);

        // Should block third request (limit is 2)
        $blocked = $this->request->checkRateLimit($identifier, 2, 3600);
        $this->assertFalse($blocked);
    }

    // Test performance with large datasets
    public function testBatchSavePerformance()
    {
        $startTime = microtime(true);

        // Create batch with 50 items
        $items = [];
        for ($i = 1; $i <= 50; $i++) {
            $items[$i] = [
                'kompeten' => $i % 2 === 0 ? 'Y' : 'N',
                'keterangan' => "Performance test item {$i}"
            ];
        }

        $data = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'tanggal_observasi' => date('Y-m-d'),
            'items' => $items
        ];

        $result = $this->service->saveBatch($data);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertTrue($result['success']);
        $this->assertEquals(50, $result['saved_count']);
        $this->assertLessThan(1000, $executionTime, 'Batch save should complete within 1 second');
    }

    // Test error handling
    public function testDatabaseErrorHandling()
    {
        // Simulate database error by providing invalid foreign key
        $data = [
            'id_asesmen' => 99999, // Non-existent assessment
            'id_skema' => 99999,   // Non-existent schema
            'id_asesi' => 99999,   // Non-existent participant
            'id_kuk' => 1,
            'kompeten' => 'Y',
            'keterangan' => 'Test with invalid foreign keys',
            'tanggal_observasi' => date('Y-m-d')
        ];

        $result = $this->service->saveSingle($data);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    // Test concurrent access
    public function testConcurrentSaveOperations()
    {
        $results = [];

        // Simulate concurrent saves to same KUK
        for ($i = 0; $i < 3; $i++) {
            $data = [
                'id_asesmen' => 1,
                'id_skema' => 1,
                'id_asesi' => 1,
                'id_kuk' => 1,
                'kompeten' => 'Y',
                'keterangan' => "Concurrent save {$i}",
                'tanggal_observasi' => date('Y-m-d')
            ];

            $results[] = $this->service->saveSingle($data);
        }

        // All operations should succeed
        foreach ($results as $result) {
            $this->assertTrue($result['success']);
        }

        // Last save should be the final state
        $loadParams = [
            'id_skema' => 1,
            'id_asesmen' => 1,
            'id_asesi' => 1
        ];

        $loadResult = $this->service->loadObservasi($loadParams);
        $this->assertTrue($loadResult['success']);
        $this->assertStringContains('Concurrent save 2', $loadResult['existing_data']['1']['keterangan']);
    }

    // Test date validation
    public function testDateValidation()
    {
        // Future date should be rejected
        $futureDate = date('Y-m-d', strtotime('+1 day'));

        $data = [
            'id_asesmen' => 1,
            'id_skema' => 1,
            'id_asesi' => 1,
            'id_kuk' => 1,
            'kompeten' => 'Y',
            'keterangan' => 'Future date test',
            'tanggal_observasi' => $futureDate
        ];

        $result = $this->service->saveSingle($data);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('masa depan', $result['message']);

        // Today's date should be accepted
        $data['tanggal_observasi'] = date('Y-m-d');
        $result = $this->service->saveSingle($data);
        $this->assertTrue($result['success']);
    }
}
