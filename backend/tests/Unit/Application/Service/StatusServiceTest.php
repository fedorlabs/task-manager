<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Dto\Query\StatusQuery;
use App\Application\Dto\Status\CreateStatusRequest;
use App\Application\Dto\Status\UpdateStatusRequest;
use App\Application\Service\StatusService;
use App\Domain\Entity\Status;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\StatusRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class StatusServiceTest extends TestCase
{
    private StatusRepositoryInterface&MockObject $statusRepository;
    private StatusService $service;

    protected function setUp(): void
    {
        $this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
        $this->service = new StatusService($this->statusRepository);
    }

    public function testGetStatusByIdReturnsStatus(): void
    {
        $status = new Status();
        $status->setName('Active');

        $this->statusRepository->method('findById')->with(1)->willReturn($status);

        $result = $this->service->getStatusById(1);
        $this->assertSame('Active', $result->getName());
    }

    public function testGetStatusByIdThrowsNotFound(): void
    {
        $this->statusRepository->method('findById')->with(999)->willReturn(null);
        $this->expectException(NotFoundException::class);

        $this->service->getStatusById(999);
    }

    public function testGetStatuses(): void
    {
        $status = new Status();
        $status->setName('Active');

        $this->statusRepository->expects($this->once())
            ->method('findPaginated')
            ->willReturn([$status]);

        $query = new StatusQuery();
        $result = $this->service->getStatuses($query);

        $this->assertCount(1, $result);
    }

    public function testCreateStatus(): void
    {
        $this->statusRepository->expects($this->once())->method('save');

        $dto = CreateStatusRequest::fromArray(['name' => 'New']);
        $status = $this->service->createStatus($dto);

        $this->assertSame('New', $status->getName());
    }

    public function testUpdateStatus(): void
    {
        $status = new Status();
        $status->setName('Old');

        $this->statusRepository->method('findById')->with(1)->willReturn($status);
        $this->statusRepository->expects($this->once())->method('save');

        $dto = UpdateStatusRequest::fromArray(['name' => 'New']);
        $updated = $this->service->updateStatus(1, $dto);

        $this->assertSame('New', $updated->getName());
    }
}
