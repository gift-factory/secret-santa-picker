<?php

declare(strict_types=1);

use GiftFactory\SecretSanta\Draw;
use GiftFactory\SecretSanta\Player;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Draw::class)]
final class DrawTest extends TestCase
{
    public function testPick(): void
    {
        $expected = [
            'result' => [
                [
                    [
                        'userName'   => 'Chuck',
                        'exclusions' => [
                            'Bob',
                            'Dane',
                        ],
                    ],
                    [
                        'userName'   => 'Fiona',
                        'exclusions' => [
                            'Anna',
                        ],
                    ],
                ],
                [
                    [
                        'userName' => 'Anna',
                    ],
                    [
                        'userName' => 'Bob',
                    ],
                ],
                [
                    [
                        'userName' => 'Bob',
                    ],
                    [
                        'userName' => 'Dane',
                    ],
                ],
                [
                    [
                        'userName'   => 'Fiona',
                        'exclusions' => [
                            'Anna',
                        ],
                    ],
                    [
                        'userName'   => 'Chuck',
                        'exclusions' => [
                            'Bob',
                            'Dane',
                        ],
                    ],
                ],
                [
                    [
                        'userName' => 'Dane',
                    ],
                    [
                        'userName' => 'Anna',
                    ],
                ],
            ],
        ];
        $list = new Draw([
            [new Player('Chuck', exclusions: ['Bob', 'Dane']), new Player('Fiona', exclusions: ['Anna'])],
            [new Player('Anna'), new Player('Bob')],
            [new Player('Bob'), new Player('Dane')],
            [new Player('Fiona', exclusions: ['Anna']), new Player('Chuck', exclusions: ['Bob', 'Dane'])],
            [new Player('Dane'), new Player('Anna')],
        ]);
        $data = json_decode(json_encode($list), true);

        self::assertSame($expected, $data);

        $data = json_decode(json_encode(Draw::fromArray($data)), true);

        self::assertSame($expected, $data);
    }
}
