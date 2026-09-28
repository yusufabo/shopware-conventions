<?php declare(strict_types=1);

namespace Conventions\Tests\Unit\Core\Content\ProductLabel;

use Conventions\Core\Content\ProductLabel\ProductLabelEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProductLabelEntityTest extends TestCase
{
    #[DataProvider('colorProvider')]
    public function testHasLightColor(string $color, bool $expected): void
    {
        $label = new ProductLabelEntity();
        $label->setColor($color);

        static::assertSame($expected, $label->hasLightColor());
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function colorProvider(): iterable
    {
        yield 'white' => ['#FFFFFF', true];
        yield 'yellow' => ['#FFD500', true];
        yield 'red' => ['#E52427', false];
        yield 'black' => ['#000000', false];
        yield 'invalid' => ['red', false];
    }
}
