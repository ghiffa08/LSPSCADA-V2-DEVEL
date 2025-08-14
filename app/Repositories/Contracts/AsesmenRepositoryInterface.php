<?php

namespace App\Repositories\Contracts;

/**
 * Asesmen Repository Interface
 * 
 * @package App\Repositories\Contracts
 */
interface AsesmenRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findBySkemaId(int $skemaId): array;
    public function findAll(): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
