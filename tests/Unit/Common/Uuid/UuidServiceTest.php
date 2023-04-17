<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Uuid;

use App\Common\Uuid\Exception\UuidInvalidException;
use App\Common\Uuid\UuidService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV4;

class UuidServiceTest extends TestCase
{
    private const UUID = 'd58c70a2-0567-4144-ad74-82fd77abe451';

    private readonly UuidService $unit;

    protected function setUp(): void
    {
        $this->unit = new UuidService();
    }

    public function testCheckStringThrowsExceptionOnInvalidUuid(): void
    {
        self::expectException(UuidInvalidException::class);

        $this->unit->checkString('aaa');
    }

    public function testCheckStringDoesNotThrowsExceptionOnValidUuid(): void
    {
        $this->unit->checkString(self::UUID);
        self::assertTrue(true); // Fake asserting since we can not simply assert that nothing happened
    }

    public function testToUuidThrowsExceptionOnInvalidUuid(): void
    {
        self::expectException(UuidInvalidException::class);

        $this->unit->toUuid('aaa');
    }

    public function testToUuidReturnsProperUuid(): void
    {
        $uuid = $this->unit->toUuid(self::UUID);

        self::assertInstanceOf(UuidV4::class, $uuid);
        self::assertSame(self::UUID, (string) $uuid);
    }
}
