<?php

namespace App\Repositories\Contracts;

/**
 * Observasi Repository Interface
 * 
 * @package App\Repositories\Contracts
 */
interface ObservasiRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findByIdAndAsesor(int $id, int $asesorId): ?array;
    public function findByAsesor(int $asesorId, array $filters = []): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getWithDetails(int $id): ?array;
}
