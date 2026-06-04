<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\StatusService;
use App\Domain\Entity\Status;
use App\Domain\Repository\StatusRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class StatusServiceTest extends TestCase
{
    private StatusRepositoryInterface&MockObject $statusRepository;
    private StatusService $service;

    protected function setUp(): void
    {
        $this->statusRepository = $this->createMock(StatusRepositoryInterface::class);

        $this->service = new StatusService($this->statusRepository);
    }

    public function testGetAllStatuses(): void
    {
        $s1 = new Status();
        $s1->setName('Active');
        $s2 = new Status();
        $s2->setName('Closed');

        $this->statusRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$s1, $s2]);

        $result = $this->service->getAllStatuses();

        $this->assertCount(2, $result);
        $this->assertSame('Active', $result[0]->getName());
        $this->assertSame('Closed', $result[1]->getName());
    }

    public function testGetStatusByIdReturnsStatus(): void
    {
        $status = new Status();
        $status->setName('In Progress');

        $this->statusRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($status);

        $result = $this->service->getStatusById(1);

        $this->assertSame('In Progress', $result->getName());
    }

    public function testGetStatusByIdThrowsNotFound(): void
    {
        $this->statusRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Status not found');

        $this->service->getStatusById(999);
    }

    public function testGetStatusCount(): void
    {
        $this->statusRepository->expects($this->once())
            ->method('count')
            ->willReturn(3);

        $this->assertSame(3, $this->service->getStatusCount());
    }

    public function testCreateStatus(): void
    {
        $this->statusRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Status::class));

        $status = $this->service->createStatus('New Status');

        $this->assertSame('New Status', $status->getName());
    }

    public function testUpdateStatus(): void
    {
        $status = new Status();
        $status->setName('Old Name');

        $this->statusRepository->method('findById')->with(1)->willReturn($status);
        $this->statusRepository->expects($this->once())->method('save');

        $updated = $this->service->updateStatus(1, ['name' => 'Updated Name']);

        $this->assertSame('Updated Name', $updated->getName());
    }

    public function testUpdateStatusWithoutNameKeepsOldValue(): void
    {
        $status = new Status();
        $status->setName('Original');

        $this->statusRepository->method('findById')->with(1)->willReturn($status);
        $this->statusRepository->expects($this->once())->method('save');

        $updated = $this->service->updateStatus(1, []);

        $this->assertSame('Original', $updated->getName());
    }

    public function testDeleteStatus(): void
    {
        $status = new Status();
        $status->setName('To delete');

        $this->statusRepository->method('findById')->with(1)->willReturn($status);
        $this->statusRepository->expects($this->once())
            ->method('remove')
            ->with($status);

        $this->service->deleteStatus(1);
    }

    public function testDeleteStatusNotFoundThrows(): void
    {
        $this->statusRepository->method('findById')->with(999)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->service->deleteStatus(999);
    }
}
