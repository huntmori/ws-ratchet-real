<?php

namespace Tests\Repository;

use App\Enum\JoinType;
use App\Enum\OpenType;
use App\Enum\RoomState;
use App\Model\Room;
use App\Repository\RoomRepository;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RoomRepositoryTest extends TestCase
{
    private RoomRepository $repository;
    private Medoo $medoo;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->medoo = $this->createMock(Medoo::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->repository = new RoomRepository($this->medoo, $this->logger);
    }

    public function testSaveWithNewRoom(): void
    {
        $room = Room::builder()
            ->roomName('Test Room')
            ->maximumUsers(10)
            ->joinType(JoinType::PUBLIC)
            ->openType(OpenType::PUBLIC)
            ->roomState(RoomState::OPEN)
            ->build();

        $this->medoo->expects($this->once())
            ->method('insert')
            ->with('room', $this->callback(function ($data) {
                return isset($data['room_name'])
                    && $data['room_name'] === 'Test Room'
                    && $data['maximum_users'] === 10;
            }));

        $this->medoo->expects($this->once())
            ->method('id')
            ->willReturn('1');

        $this->medoo->expects($this->once())
            ->method('get')
            ->with('room', '*', ['idx' => 1])
            ->willReturn([
                'idx' => 1,
                'uuid' => 'test-uuid-123',
                'room_name' => 'Test Room',
                'maximum_users' => 10,
                'join_type' => 'PUBLIC',
                'open_type' => 'PUBLIC',
                'join_password' => null,
                'created_datetime' => '2025-01-01 00:00:00',
                'updated_datetime' => '2025-01-01 00:00:00',
                'is_deleted' => false,
                'deleted_datetime' => null,
                'room_state' => 'OPEN',
            ]);

        $result = $this->repository->save($room);

        $this->assertInstanceOf(Room::class, $result);
        $this->assertEquals(1, $result->idx);
        $this->assertEquals('Test Room', $result->roomName);
    }

    public function testSaveWithExistingRoom(): void
    {
        $room = Room::builder()
            ->idx(1)
            ->roomName('Existing Room')
            ->maximumUsers(5)
            ->joinType(JoinType::PUBLIC)
            ->openType(OpenType::PUBLIC)
            ->roomState(RoomState::OPEN)
            ->build();

        $result = $this->repository->save($room);

        $this->assertNull($result);
    }

    public function testInsert(): void
    {
        $room = Room::builder()
            ->roomName('New Room')
            ->maximumUsers(20)
            ->joinType(JoinType::PASSWORD)
            ->openType(OpenType::PRIVATE)
            ->joinPassword('secret123')
            ->roomState(RoomState::OPEN)
            ->build();

        $this->medoo->expects($this->once())
            ->method('insert')
            ->with('room', $this->callback(function ($data) {
                return $data['room_name'] === 'New Room'
                    && $data['maximum_users'] === 20
                    && $data['join_type'] === 'PASSWORD'
                    && $data['open_type'] === 'PRIVATE';
            }));

        $this->medoo->expects($this->once())
            ->method('id')
            ->willReturn('2');

        $this->medoo->expects($this->once())
            ->method('get')
            ->with('room', '*', ['idx' => 2])
            ->willReturn([
                'idx' => 2,
                'uuid' => 'test-uuid-456',
                'room_name' => 'New Room',
                'maximum_users' => 20,
                'join_type' => 'PASSWORD',
                'open_type' => 'PRIVATE',
                'join_password' => 'secret123',
                'created_datetime' => '2025-01-01 00:00:00',
                'updated_datetime' => '2025-01-01 00:00:00',
                'is_deleted' => false,
                'deleted_datetime' => null,
                'room_state' => 'OPEN',
            ]);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->repository->insert($room);

        $this->assertInstanceOf(Room::class, $result);
        $this->assertEquals(2, $result->idx);
        $this->assertEquals('New Room', $result->roomName);
        $this->assertEquals(20, $result->maximumUsers);
    }

    public function testGetOneByIdx(): void
    {
        $this->medoo->expects($this->once())
            ->method('get')
            ->with('room', '*', ['idx' => 1])
            ->willReturn([
                'idx' => 1,
                'uuid' => 'test-uuid-789',
                'room_name' => 'Existing Room',
                'maximum_users' => 15,
                'join_type' => 'PUBLIC',
                'open_type' => 'PUBLIC',
                'join_password' => null,
                'created_datetime' => '2025-01-01 00:00:00',
                'updated_datetime' => '2025-01-01 00:00:00',
                'is_deleted' => false,
                'deleted_datetime' => null,
                'room_state' => 'OPEN',
            ]);

        $result = $this->repository->getOneByIdx(1);

        $this->assertInstanceOf(Room::class, $result);
        $this->assertEquals(1, $result->idx);
        $this->assertEquals('Existing Room', $result->roomName);
        $this->assertEquals(15, $result->maximumUsers);
    }

    public function testInsertWithoutUuid(): void
    {
        $room = Room::builder()
            ->roomName('Room Without UUID')
            ->maximumUsers(8)
            ->joinType(JoinType::PUBLIC)
            ->openType(OpenType::PUBLIC)
            ->roomState(RoomState::OPEN)
            ->build();

        $this->medoo->expects($this->once())
            ->method('insert')
            ->with('room', $this->callback(function ($data) {
                return isset($data['uuid']) && $data['uuid'] instanceof \Medoo\Raw;
            }));

        $this->medoo->expects($this->once())
            ->method('id')
            ->willReturn('3');

        $this->medoo->expects($this->once())
            ->method('get')
            ->willReturn([
                'idx' => 3,
                'uuid' => 'auto-generated-uuid',
                'room_name' => 'Room Without UUID',
                'maximum_users' => 8,
                'join_type' => 'PUBLIC',
                'open_type' => 'PUBLIC',
                'join_password' => null,
                'created_datetime' => '2025-01-01 00:00:00',
                'updated_datetime' => '2025-01-01 00:00:00',
                'is_deleted' => false,
                'deleted_datetime' => null,
                'room_state' => 'OPEN',
            ]);

        $this->logger->expects($this->once())
            ->method('info');

        $result = $this->repository->insert($room);

        $this->assertInstanceOf(Room::class, $result);
        $this->assertEquals('auto-generated-uuid', $result->uuid);
    }
}
