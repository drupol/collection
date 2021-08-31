<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\loophp\collection;

use ArrayIterator;
use ArrayObject;
use Closure;
use Doctrine\Common\Collections\Criteria;
use Error;
use Exception;
use Generator;
use InvalidArgumentException;
use Iterator;
use JsonSerializable;
use loophp\collection\Collection;
use loophp\collection\Contract\Collection as CollectionInterface;
use loophp\collection\Contract\Operation;
use loophp\collection\Iterator\ClosureIterator;
use OutOfBoundsException;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Exception\Example\MatcherException;
use PhpSpec\ObjectBehavior;
use stdClass;
use function gettype;
use const INF;
use const PHP_EOL;
use const PHP_VERSION_ID;

class CollectionSpec extends ObjectBehavior
{
    private const PHP_8 = 80_000;

    public function it_can_all(): void
    {
        $this::fromIterable([1, 2, 3])
            ->all()
            ->shouldIterateAs([1, 2, 3]);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->all()
            ->shouldIterateAs(['foo' => 'f', 'bar' => 'b']);

        $duplicateKeyGen = static function (): Generator {
            yield 'a' => 1;

            yield 'b' => 2;

            yield 'a' => 3;
        };

        $this::fromIterable($duplicateKeyGen())
            ->shouldIterateAs($duplicateKeyGen());

        $this::fromIterable($duplicateKeyGen())
            ->all()
            ->shouldIterateAs(['a' => 3, 'b' => 2]);
    }

    public function it_can_append(): void
    {
        $generator = static function (): Generator {
            yield 0 => '1';

            yield 1 => '2';

            yield 2 => '3';

            yield 0 => '4';
        };

        $this::fromIterable(['1', '2', '3'])
            ->append('4')
            ->shouldIterateAs($generator());

        $generator = static function (): Generator {
            yield 0 => '1';

            yield 1 => '2';

            yield 2 => '3';

            yield 0 => '5';

            yield 1 => '6';
        };

        $this::fromIterable(['1', '2', '3'])
            ->append('5', '6')
            ->shouldIterateAs($generator());
    }

    public function it_can_apply(): void
    {
        $input = range('a', 'e');
        $stack = [];

        $this::fromIterable($input)
            ->apply(
                static function ($item) use (&$stack): bool {
                    $stack += [$item => []];
                    $stack[$item][] = 'fn1';

                    return true;
                }
            )
            ->shouldIterateAs($input);

        $expected = [
            'a' => ['fn1'],
            'b' => ['fn1'],
            'c' => ['fn1'],
            'd' => ['fn1'],
            'e' => ['fn1'],
        ];

        if ($stack !== $expected) {
            throw new MatcherException('The expected value does not match.');
        }

        $stack = [];

        $this::fromIterable($input)
            ->apply(
                static function ($item) use (&$stack): bool {
                    $stack += [$item => []];
                    $stack[$item][] = 'fn1';

                    return false;
                }
            )
            ->shouldIterateAs($input);

        $expected = [
            'a' => ['fn1'],
        ];

        if ($stack !== $expected) {
            throw new MatcherException('The expected value does not match.');
        }

        $stack = [];

        $this::fromIterable($input)
            ->apply(
                static function ($item) use (&$stack): bool {
                    $stack += [$item => []];
                    $stack[$item][] = 'fn1';

                    return true;
                },
                static function ($item) use (&$stack): bool {
                    $stack += [$item => []];
                    $stack[$item][] = 'fn2';

                    return true;
                }
            )
            ->shouldIterateAs($input);

        $expected = [
            'a' => ['fn1', 'fn2'],
            'b' => ['fn1', 'fn2'],
            'c' => ['fn1', 'fn2'],
            'd' => ['fn1', 'fn2'],
            'e' => ['fn1', 'fn2'],
        ];

        if ($stack !== $expected) {
            throw new MatcherException('The expected value does not match.');
        }

        $stack = [];

        $this::fromIterable($input)
            ->apply(
                static function ($item) use (&$stack): bool {
                    $stack += [$item => []];
                    $stack[$item][] = 'fn1';

                    if ('c' === $item) {
                        return false;
                    }

                    return true;
                },
                static function ($item) use (&$stack): bool {
                    $stack += [$item => []];
                    $stack[$item][] = 'fn2';

                    if ('b' === $item) {
                        return false;
                    }

                    return true;
                }
            )
            ->shouldIterateAs($input);

        $expected = [
            'a' => ['fn1', 'fn2'],
            'b' => ['fn1', 'fn2'],
            'c' => ['fn1'],
        ];

        if ($stack !== $expected) {
            throw new MatcherException('The expected value does not match.');
        }
    }

    public function it_can_associate(): void
    {
        $input = range(1, 10);

        $this::fromIterable($input)
            ->associate()
            ->shouldIterateAs($input);

        $this::fromIterable($input)
            ->associate(
                static function (int $carry, int $key, int $value): int {
                    return $key * 2;
                },
                static function (int $carry, int $key, int $value): int {
                    return $value * 2;
                }
            )
            ->shouldIterateAs(
                [
                    0 => 2,
                    2 => 4,
                    4 => 6,
                    6 => 8,
                    8 => 10,
                    10 => 12,
                    12 => 14,
                    14 => 16,
                    16 => 18,
                    18 => 20,
                ]
            );
    }

    public function it_can_asyncMap(): void
    {
        $callback = static function (int $v): int {
            sleep($v);

            return $v * 2;
        };

        $this->beConstructedThrough('fromIterable', [['c' => 2, 'b' => 1, 'a' => 0]]);

        $this
            ->asyncMap($callback)
            ->shouldIterateAs(['a' => 0, 'b' => 2, 'c' => 4]);
    }

    public function it_can_asyncMapN(): void
    {
        $callback1 = static function (int $v): int {
            sleep($v);

            return $v;
        };

        $callback2 = static function (int $v): int {
            return $v * 2;
        };

        $this->beConstructedThrough('fromIterable', [['c' => 2, 'b' => 1, 'a' => 0]]);

        $this
            ->asyncMapN($callback1, $callback2)
            ->shouldIterateAs(['a' => 0, 'b' => 2, 'c' => 4]);
    }

    public function it_can_be_constructed_from_a_file(): void
    {
        $this::fromFile(__DIR__ . '/../../fixtures/sample.txt')
            ->shouldIterateAs([
                'a',
                'b',
                'c',
            ]);
    }

    public function it_can_be_constructed_from_a_generator(): void
    {
        $generator = static function () {
            yield 'a';

            yield 'b';

            yield 'c';

            yield 'd';

            yield 'e';
        };

        $subject = $this::fromGenerator($generator());

        $subject
            ->shouldIterateAs(range('a', 'e'));

        $subject
            ->count()
            ->shouldBeEqualTo($subject->count());

        $generator = static function () {
            yield 'a';

            yield 'b';

            yield 'c';

            yield 'd';

            yield 'e';
        };

        $generator = $generator();
        $generator->next();
        $generator->next();

        $subject = $this::fromGenerator($generator);

        $subject
            ->shouldIterateAs([2 => 'c', 3 => 'd', 4 => 'e']);

        $subject
            ->count()
            ->shouldBeEqualTo($subject->count());
    }

    public function it_can_be_constructed_from_a_stream(): void
    {
        $string = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

        $stream = fopen('data://text/plain,' . $string, 'rb');
        $this::fromResource($stream)
            ->count()
            ->shouldReturn(56);

        $stream = fopen('data://text/plain,' . $string, 'rb');
        $this::fromResource($stream)
            ->implode()
            ->shouldIterateAs([55 => $string]);

        $stream = imagecreate(100, 100);

        $this::fromResource($stream)
            ->shouldThrow(InvalidArgumentException::class)
            ->during('all');

        $string = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $stream = fopen('data://text/plain,' . $string, 'rb');

        $this::fromResource($stream)
            ->drop(1)
            ->count()
            ->shouldReturn(55);
    }

    public function it_can_be_constructed_from_a_string(): void
    {
        $this::fromString('izumi')
            ->getIterator()
            ->shouldIterateAs([
                'i',
                'z',
                'u',
                'm',
                'i',
            ]);
    }

    public function it_can_be_constructed_from_an_iterable(): void
    {
        $this
            ->beConstructedThrough('fromIterable', [range('A', 'E')]);

        $this->shouldImplement(Collection::class);

        $this
            ->shouldIterateAs(['A', 'B', 'C', 'D', 'E']);

        $iterable = [
            'a',
            'b',
            'c',
        ];

        $this::fromIterable($iterable)
            ->shouldIterateAs([
                'a',
                'b',
                'c',
            ]);

        $generator = static function () {
            yield 'a';

            yield 'b';

            yield 'c';

            yield 'd';

            yield 'e';
        };

        $generator = $generator();
        $generator->next();
        $generator->next();

        $subject = $this::fromIterable($generator);

        $subject
            ->shouldIterateAs([2 => 'c', 3 => 'd', 4 => 'e']);

        $subject
            ->shouldThrow(Error::class)
            ->during('count');
    }

    public function it_can_be_constructed_from_an_iterator(): void
    {
        $this::fromIterable(new ArrayIterator(range('a', 'e')))
            ->getIterator()
            ->shouldIterateAs(range('a', 'e'));
    }

    public function it_can_be_constructed_from_empty(): void
    {
        $this
            ->beConstructedThrough('empty');

        $this
            ->shouldIterateAs([]);
    }

    public function it_can_be_constructed_with_a_callable(): void
    {
        $test1 = $this::fromCallable(static fn (int $a, int $b): Generator => yield from range($a, $b), [1, 5]);
        $test1->shouldImplement(Collection::class);
        $test1->getIterator()->shouldIterateAs([1, 2, 3, 4, 5]);

        $test2 = $this::fromCallable(static fn (int $a, int $b): array => range($a, $b), [1, 5]);
        $test2->shouldImplement(Collection::class);
        $test2->getIterator()->shouldIterateAs([1, 2, 3, 4, 5]);

        $test3 = $this::fromCallable(static fn (int $a, int $b): ArrayIterator => new ArrayIterator(range($a, $b)), [1, 5]);
        $test3->shouldImplement(Collection::class);
        $test3->getIterator()->shouldIterateAs([1, 2, 3, 4, 5]);

        $classWithMethod = new class() {
            public function getValues(): Generator
            {
                yield from range(1, 5);
            }
        };
        $test4 = $this::fromCallable([$classWithMethod, 'getValues']);
        $test4->shouldImplement(Collection::class);
        $test4->getIterator()->shouldIterateAs([1, 2, 3, 4, 5]);

        $classWithStaticMethod = new class() {
            public static function getValues(): Generator
            {
                yield from range(1, 5);
            }
        };
        $test5 = $this::fromCallable([$classWithStaticMethod, 'getValues']);
        $test5->shouldImplement(Collection::class);
        $test5->getIterator()->shouldIterateAs([1, 2, 3, 4, 5]);

        $invokableClass = new class() {
            public function __invoke(): Generator
            {
                yield from range(1, 5);
            }
        };
        $test6 = $this::fromCallable($invokableClass);
        $test6->shouldImplement(Collection::class);
        $test6->getIterator()->shouldIterateAs([1, 2, 3, 4, 5]);
    }

    public function it_can_be_constructed_with_an_arrayObject(): void
    {
        $this
            ->beConstructedThrough('fromIterable', [new ArrayObject([1, 2, 3])]);

        $this->shouldImplement(Collection::class);
    }

    public function it_can_be_instantiated_with_withClosure(): void
    {
        $fibonacci = static function ($start, $inc) {
            yield $start;

            while (true) {
                $inc = $start + $inc;
                $start = $inc - $start;

                yield $start;
            }
        };

        $this::fromCallable($fibonacci, [0, 1])
            ->limit(10)
            ->shouldIterateAs([0, 1, 1, 2, 3, 5, 8, 13, 21, 34]);
    }

    public function it_can_be_json_encoded()
    {
        $input = ['a' => 'A', 'b' => 'B', 'c' => 'C'];

        $this->beConstructedThrough('fromIterable', [$input]);

        $this
            ->jsonSerialize()
            ->shouldReturn($this->all());

        $this
            ->shouldImplement(JsonSerializable::class);
    }

    public function it_can_be_returned_as_an_array(): void
    {
        $this::fromIterable(new ArrayObject(['1', '2', '3']))
            ->shouldIterateAs(['1', '2', '3']);
    }

    public function it_can_cache(): void
    {
        $fhandle = fopen(__DIR__ . '/../../fixtures/sample.txt', 'rb');

        $this::fromResource($fhandle)
            ->window(2)
            ->shouldIterateAs([
                [0 => 'a'],
                [0 => 'a', 1 => 'b'],
                [0 => 'a', 1 => 'b', 2 => 'c'],
            ]);

        $fhandle = fopen(__DIR__ . '/../../fixtures/sample.txt', 'rb');

        $this::fromResource($fhandle)
            ->cache()
            ->window(2)
            ->shouldIterateAs([
                [0 => 'a'],
                [0 => 'a', 1 => 'b'],
                [0 => 'a', 1 => 'b', 2 => 'c'],
            ]);

        $fhandle = fopen(__DIR__ . '/../../fixtures/sample.txt', 'rb');

        $this::fromResource($fhandle)
            ->cache()
            ->shouldIterateAs(['a', 'b', 'c']);

        $fhandle = fopen(__DIR__ . '/../../fixtures/sample.txt', 'rb');

        $this::fromResource($fhandle)
            ->cache()
            ->shouldIterateAs(['a', 'b', 'c']);
    }

    public function it_can_chunk(): void
    {
        $this::fromIterable(range('A', 'F'))
            ->chunk(2)
            ->shouldIterateAs([[0 => 'A', 1 => 'B'], [0 => 'C', 1 => 'D'], [0 => 'E', 1 => 'F']]);

        $this::fromIterable(range('A', 'F'))
            ->chunk(0)
            ->shouldIterateAs([]);

        $this::fromIterable(range('A', 'F'))
            ->chunk(1)
            ->shouldIterateAs([[0 => 'A'], [0 => 'B'], [0 => 'C'], [0 => 'D'], [0 => 'E'], [0 => 'F']]);

        $this::fromIterable(range('A', 'F'))
            ->chunk(2, 3)
            ->shouldIterateAs([[0 => 'A', 1 => 'B'], [0 => 'C', 1 => 'D', 2 => 'E'], [0 => 'F']]);
    }

    public function it_can_coalesce(): void
    {
        $input = range('a', 'e');

        $this::fromIterable($input)
            ->coalesce()
            ->shouldIterateAs([
                0 => 'a',
            ]);

        $input = ['', null, 'foo', false, ...range('a', 'e')];

        $this::fromIterable($input)
            ->coalesce()
            ->shouldIterateAs([
                2 => 'foo',
            ]);
    }

    public function it_can_collapse(): void
    {
        $generator = static function () {
            yield 0 => 'A';

            yield 1 => 'B';

            yield 'foo' => 'C';

            yield 0 => 'E';

            yield 1 => 'F';
        };

        $this::fromIterable([
            ['A', 'B', 'foo' => 'C'],
            'D',
            ['E', 'F'],
            'G',
        ])
            ->collapse()
            ->shouldIterateAs($generator());

        $this::fromIterable(range('A', 'E'))
            ->collapse()
            ->shouldIterateAs([]);
    }

    public function it_can_column(): void
    {
        $records = [
            [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ],
        ];

        $this::fromIterable($records)
            ->column('first_name')
            ->shouldIterateAs([0 => 'John', 1 => 'Sally', 2 => 'Jane', 3 => 'Peter']);

        $this::fromIterable($records)
            ->column('middle_name')
            ->shouldIterateAs([]);

        $nonArrayKeyRecords = [
            (static fn () => yield ['id'] => 1234)(),
            (static fn () => yield ['id'] => 4567)(),
        ];

        $this::fromIterable($nonArrayKeyRecords)
            ->column(['id'])
            ->shouldIterateAs([0 => 1234, 1 => 4567]);
    }

    public function it_can_combinate(): void
    {
        $this::fromIterable(range('a', 'c'))
            ->combinate(0)
            ->shouldIterateAs(
                [
                    [
                        0 => 'a',
                        1 => 'b',
                        2 => 'c',
                    ],
                ]
            );

        $this::fromIterable(range('a', 'c'))
            ->combinate(1)
            ->shouldIterateAs(
                [
                    [
                        'a',
                    ],
                    [
                        'b',
                    ],
                    [
                        'c',
                    ],
                ]
            );

        $this::fromIterable(range('a', 'c'))
            ->combinate()
            ->all()
            ->shouldBeEqualTo(
                [
                    0 => [
                        0 => 'a',
                        1 => 'b',
                        2 => 'c',
                    ],
                    1 => [
                        0 => 'a',
                        1 => 'c',
                    ],
                    2 => [
                        0 => 'b',
                        1 => 'c',
                    ],
                ]
            );
    }

    public function it_can_combine(): void
    {
        $this::fromIterable(range('A', 'E'))
            ->combine(...range('e', 'a'))
            ->shouldIterateAs(['e' => 'A', 'd' => 'B', 'c' => 'C', 'b' => 'D', 'a' => 'E']);

        $output = static function () {
            yield 'a' => 'a';

            yield 'b' => 'b';

            yield 'c' => 'c';

            yield null => 'd';

            yield null => 'e';
        };

        $this::fromIterable(range('a', 'e'))
            ->combine(...range('a', 'c'))
            ->shouldIterateAs($output());

        $output = static function () {
            yield 'a' => 'a';

            yield 'b' => 'b';

            yield 'c' => 'c';

            yield 'd' => null;

            yield 'e' => null;
        };

        $this::fromIterable(range('a', 'c'))
            ->combine(...range('a', 'e'))
            ->shouldIterateAs($output());
    }

    public function it_can_compact(): void
    {
        $input = ['a', 1 => 'b', null, false, 0, 'c', ''];

        $this::fromIterable($input)
            ->compact()
            ->shouldIterateAs(['a', 1 => 'b', 5 => 'c']);

        $this::fromIterable($input)
            ->compact(null, 0)
            ->shouldIterateAs(['a', 1 => 'b', 3 => false, 5 => 'c', '']);
    }

    public function it_can_contains(): void
    {
        $this::fromIterable(range('A', 'C'))
            ->contains('A')
            ->shouldBe(true);

        $this::fromIterable(range('A', 'C'))
            ->contains('unknown')
            ->shouldBe(false);

        $this::fromIterable(range('A', 'C'))
            ->contains('C', 'A')
            ->shouldBe(true);

        $this::fromIterable(range('A', 'C'))
            ->contains('C', 'unknown', 'A')
            ->shouldBe(true);

        $this::fromIterable(['a' => 'b', 'c' => 'd'])
            ->contains('d')
            ->shouldBe(true);
    }

    public function it_can_convert_use_a_string_as_parameter(): void
    {
        $this::fromString('foo')
            ->shouldIterateAs([0 => 'f', 1 => 'o', 2 => 'o']);

        $this::fromString('hello, world', ',')
            ->shouldIterateAs([0 => 'hello', 1 => ' world']);
    }

    public function it_can_count_its_items(): void
    {
        $this::fromIterable(range('A', 'C'))
            ->count()
            ->shouldReturn(3);
    }

    public function it_can_current(): void
    {
        $input = array_combine(
            range('a', 'e'),
            range(1, 5)
        );

        $this::fromIterable($input)
            ->current(0)
            ->shouldReturn(1);

        $this::fromIterable($input)
            ->current(4)
            ->shouldReturn(5);

        $this::fromIterable(['a'])
            ->current(0)
            ->shouldReturn('a');

        $this::fromIterable(new ArrayIterator())
            ->current()
            ->shouldBeNull();
    }

    public function it_can_cycle(): void
    {
        $generator = static function (): Generator {
            yield 0 => 1;

            yield 1 => 2;

            yield 2 => 3;

            yield 0 => 1;

            yield 1 => 2;

            yield 2 => 3;

            yield 0 => 1;
        };

        $this::fromIterable(range(1, 3))
            ->cycle()
            ->limit(7)
            ->shouldIterateAs($generator());
    }

    public function it_can_diff(): void
    {
        $this::fromIterable(range(1, 5))
            ->diff(1, 2, 3, 9)
            ->shouldIterateAs([3 => 4, 4 => 5]);

        $this::fromIterable(range(1, 5))
            ->diff()
            ->shouldIterateAs(range(1, 5));

        $this::fromIterable(range(1, 5))
            ->diff(...Collection::fromIterable(range(2, 5)))
            ->shouldIterateAs([0 => 1]);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->diff(...Collection::fromIterable(['f']))
            ->shouldIterateAs(['bar' => 'b']);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->diff('F', 'b')
            ->shouldIterateAs(['foo' => 'f']);

        if (PHP_VERSION_ID >= self::PHP_8) {
            $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
                ->diff(...Collection::fromIterable(['foo' => 'f']))
                ->shouldIterateAs(['bar' => 'b']);

            $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
                ->diff(...['foo' => 'F', 'bar' => 'b'])
                ->shouldIterateAs(['foo' => 'f']);
        }
    }

    public function it_can_diffKeys(): void
    {
        $input = array_combine(range('a', 'e'), range(1, 5));

        $this::fromIterable($input)
            ->diffKeys('b', 'd')
            ->shouldIterateAs(['a' => 1, 'c' => 3, 'e' => 5]);

        $this::fromIterable($input)
            ->diffKeys()
            ->shouldIterateAs($input);
    }

    public function it_can_distinct(): void
    {
        $stdclass = new stdClass();

        $this::fromIterable([1, 1, 2, 2, 3, 3, $stdclass, $stdclass])
            ->distinct()
            ->shouldIterateAs([0 => 1, 2 => 2, 4 => 3, 6 => $stdclass]);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b', 'baz' => 'f'])
            ->distinct()
            ->shouldIterateAs(['foo' => 'f', 'bar' => 'b']);

        $cat = static fn (string $name) => new class($name) {
            private string $name;

            public function __construct(string $name)
            {
                $this->name = $name;
            }

            public function name(): string
            {
                return $this->name;
            }
        };

        $cats = [
            $cat1 = $cat('izumi'),
            $cat2 = $cat('nakano'),
            $cat3 = $cat('booba'),
            $cat3,
        ];

        $this::fromIterable($cats)
            ->distinct()
            ->shouldIterateAs([$cat1, $cat2, $cat3]);

        $this::fromIterable($cats)
            ->distinct(
                static fn (object $left) => static fn (object $right) => $left->name() === $right->name()
            )
            ->shouldIterateAs([$cat1, $cat2, $cat3]);

        $this::fromIterable($cats)
            ->distinct(
                static fn (string $left) => static fn (string $right) => $left === $right,
                static fn (object $cat): string => $cat->name()
            )
            ->shouldIterateAs([$cat1, $cat2, $cat3]);

        $this::fromIterable($cats)
            ->distinct(
                null,
                static fn (object $cat): string => $cat->name()
            )
            ->shouldIterateAs([$cat1, $cat2, $cat3]);
    }

    public function it_can_drop(): void
    {
        $this::fromIterable(range('A', 'F'))
            ->drop(3)
            ->shouldIterateAs([3 => 'D', 4 => 'E', 5 => 'F']);

        $this::fromIterable(range('A', 'F'))
            ->drop(6)
            ->shouldIterateAs([]);
    }

    public function it_can_dropWhile(): void
    {
        $isSmallerThanThree = static function (int $value): bool {
            return 3 > $value;
        };

        $isSmallerThanFive = static function (int $value): bool {
            return 5 > $value;
        };

        $input = [1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3];

        $this::fromIterable($input)
            ->dropWhile($isSmallerThanFive, $isSmallerThanThree)
            ->shouldIterateAs([
                4 => 5,
                5 => 6,
                6 => 7,
                7 => 8,
                8 => 9,
                9 => 1,
                10 => 2,
                11 => 3,
            ]);

        $this::fromIterable($input)
            ->dropWhile($isSmallerThanFive)
            ->dropWhile($isSmallerThanThree)
            ->shouldIterateAs([
                4 => 5,
                5 => 6,
                6 => 7,
                7 => 8,
                8 => 9,
                9 => 1,
                10 => 2,
                11 => 3,
            ]);
    }

    public function it_can_dump(): void
    {
        $count = 0;
        $input = range('a', 'e');

        $callback = static function (string $name, $key, $value) use (&$count) {
            ++$count;
        };

        $this::fromIterable($input)
            ->dump('here', 0, $callback)
            ->shouldIterateAs($input);

        if (5 !== $count) {
            throw new FailureException('Invalid count1');
        }

        $count = 0;

        $callback = static function (string $name, $key, $value) use (&$count) {
            ++$count;
        };

        $this::fromIterable($input)
            ->dump('here', -1, $callback)
            ->shouldIterateAs($input);

        if (0 !== $count) {
            throw new FailureException('Invalid count2');
        }

        $callback = static function (string $name, $key, $value) use (&$count) {
            ++$count;
        };

        $this::fromIterable($input)
            ->dump('here', 2, $callback)
            ->shouldIterateAs($input);

        if (2 !== $count) {
            throw new FailureException('Invalid count3');
        }

        ob_start();
        $this::fromIterable($input)
            ->dump('debug', 2)
            ->shouldIterateAs($input);
        $output = ob_get_contents();
        ob_end_clean();

        $expectedOutput = <<<'EOF'
            array(3) {
              ["name"]=>
              string(5) "debug"
              ["key"]=>
              int(0)
              ["value"]=>
              string(1) "a"
            }
            array(3) {
              ["name"]=>
              string(5) "debug"
              ["key"]=>
              int(1)
              ["value"]=>
              string(1) "b"
            }

            EOF;

        var_dump('------');
        print_r($expectedOutput);
        var_dump('------');
        print_r($output);
        var_dump('------');

        if ($expectedOutput !== $output) {
            throw new MatcherException('Invalid output');
        }
    }

    public function it_can_duplicate(): void
    {
        $this::fromIterable(['a', 'b', 'c', 'a', 'c'])
            ->duplicate()
            ->shouldIterateAs([3 => 'a', 4 => 'c']);

        $cat = static fn (string $name) => new class($name) {
            private string $name;

            public function __construct(string $name)
            {
                $this->name = $name;
            }

            public function name(): string
            {
                return $this->name;
            }
        };

        $cats = [
            $cat1 = $cat('booba'),
            $cat2 = $cat('lola'),
            $cat3 = $cat('lalee'),
            $cat3,
        ];

        $this::fromIterable($cats)
            ->duplicate()
            ->shouldIterateAs([3 => $cat3]);

        $this::fromIterable($cats)
            ->duplicate(
                static fn (object $left) => static fn (object $right) => $left->name() === $right->name()
            )
            ->shouldIterateAs([3 => $cat3]);

        $this::fromIterable($cats)
            ->duplicate(
                static fn (string $left) => static fn (string $right) => $left === $right,
                static fn (object $cat): string => $cat->name()
            )
            ->shouldIterateAs([3 => $cat3]);

        $this::fromIterable($cats)
            ->duplicate(
                null,
                static fn (object $cat): string => $cat->name()
            )
            ->shouldIterateAs([3 => $cat3]);
    }

    public function it_can_equals(): void
    {
        $a = (object) ['id' => 'a'];
        $a2 = (object) ['id' => 'a'];
        $b = (object) ['id' => 'b'];

        // empty variations
        $this::empty()
            ->equals(Collection::empty())
            ->shouldBe(true);

        $this::empty()
            ->equals(Collection::fromIterable([1]))
            ->shouldBe(false);

        $this::fromIterable([1])
            ->equals(Collection::empty())
            ->shouldBe(false);

        // same elements, same order
        $this::fromIterable([1, 2, 3])
            ->equals(Collection::fromIterable([1, 2, 3]))
            ->shouldBe(true);

        $this::fromIterable([$a, $b])
            ->equals(Collection::fromIterable([$a, $b]))
            ->shouldBe(true);

        // same elements, different order
        $this::fromIterable([1, 2, 3])
            ->equals(Collection::fromIterable([3, 1, 2]))
            ->shouldBe(true);

        $this::fromIterable([$a, $b])
            ->equals(Collection::fromIterable([$b, $a]))
            ->shouldBe(true);

        // same lengths, with one element different
        $this::fromIterable([1, 2, 3])
            ->equals(Collection::fromIterable([1, 2, 4]))
            ->shouldBe(false);

        // different lengths, extra elements in first
        $this::fromIterable([1, 2, 3, 4])
            ->equals(Collection::fromIterable([1, 2, 3]))
            ->shouldBe(false);

        // different lengths, extra elements in second
        $this::fromIterable([1, 2, 3])
            ->equals(Collection::fromIterable([1, 2, 3, 4]))
            ->shouldBe(false);

        // objects, different instances and contents
        $this::fromIterable([$a])
            ->equals(Collection::fromIterable([$b]))
            ->shouldBe(false);

        // objects, different instances but same contents
        $this::fromIterable([$a])
            ->equals(Collection::fromIterable([$a2]))
            ->shouldBe(false);

        // "maps" with string keys and values
        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->equals(Collection::fromIterable(['foo' => 'f', 'bar' => 'b']))
            ->shouldBe(true);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->equals(Collection::fromIterable(['bar' => 'b', 'foo' => 'f']))
            ->shouldBe(true);

        $this::fromIterable(['foo' => 'f'])
            ->equals(Collection::fromIterable(['bar' => 'f']))
            ->shouldBe(true);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->equals(Collection::fromIterable(['bar' => 'b']))
            ->shouldBe(false);

        $this::fromIterable(['foo' => 'f'])
            ->equals(Collection::fromIterable(['bar' => 'b']))
            ->shouldBe(false);

        $this::fromIterable(['foo' => 'f'])
            ->equals(Collection::fromIterable(['foo' => 'f', 'bar' => 'b']))
            ->shouldBe(false);
    }

    public function it_can_every(): void
    {
        $input = range(0, 10);
        $callback = static fn ($value): bool => 20 > $value;

        $this::fromIterable($input)
            ->every($callback)
            ->shouldBe(true);

        $this::empty()
            ->every($callback)
            ->shouldBe(true);

        $this::fromIterable($input)
            ->every(static fn ($value, $key): bool => is_numeric($key))
            ->shouldBe(true);

        $this::fromIterable($input)
            ->every(static fn ($value, $key, Iterator $iterator): bool => $iterator instanceof ClosureIterator)
            ->shouldBe(true);

        $callback1 = static fn ($value, $key): bool => 20 > $value;
        $this::fromIterable($input)
            ->every($callback1)
            ->shouldBe(true);

        $callback2 = static fn ($value, $key): bool => 50 < $value;
        $this::fromIterable($input)
            ->every($callback2)
            ->shouldBe(false);

        $this::fromIterable($input)
            ->every($callback2, $callback1)
            ->shouldBe(true);

        // Validate a date
        $this::fromString('2021-04-09xxx', '-')
            ->every(static fn (string $value): bool => is_numeric($value))
            ->shouldBe(false);
    }

    public function it_can_explode(): void
    {
        $string = 'I am just a random piece of text.';

        $this::fromString($string)
            ->explode('o')
            ->shouldIterateAs(
                [
                    0 => [
                        0 => 'I',
                        1 => ' ',
                        2 => 'a',
                        3 => 'm',
                        4 => ' ',
                        5 => 'j',
                        6 => 'u',
                        7 => 's',
                        8 => 't',
                        9 => ' ',
                        10 => 'a',
                        11 => ' ',
                        12 => 'r',
                        13 => 'a',
                        14 => 'n',
                        15 => 'd',
                    ],
                    1 => [
                        0 => 'm',
                        1 => ' ',
                        2 => 'p',
                        3 => 'i',
                        4 => 'e',
                        5 => 'c',
                        6 => 'e',
                        7 => ' ',
                    ],
                    2 => [
                        0 => 'f',
                        1 => ' ',
                        2 => 't',
                        3 => 'e',
                        4 => 'x',
                        5 => 't',
                        6 => '.',
                    ],
                ]
            );
    }

    public function it_can_falsy(): void
    {
        $this::fromIterable([false, false, false])
            ->falsy()
            ->shouldBe(true);

        $this::fromIterable([false, true, false])
            ->falsy()
            ->shouldBe(false);

        $this::fromIterable([1, 2, null])
            ->falsy()
            ->shouldBe(false);

        $this::fromIterable([0, [], ''])
            ->falsy()
            ->shouldBe(true);
    }

    public function it_can_filter(): void
    {
        $input = array_merge([0, false], range(1, 10));

        $callable = static function ($value) {
            return $value % 2;
        };

        $callableWithKey = static fn (int $value, int $key): bool => $value % 2 === 0 && 4 < $key;

        $this::fromIterable($input)
            ->filter($callable)
            ->count()
            ->shouldReturn(5);

        $this::fromIterable($input)
            ->filter($callable)
            ->normalize()
            ->shouldIterateAs([1, 3, 5, 7, 9]);

        $this::fromIterable(range(0, 10))
            ->filter($callableWithKey)
            ->shouldIterateAs([6 => 6, 8 => 8, 10 => 10]);

        $this::fromIterable(['a', 'b', 'c', 'd'])
            ->filter(
                static fn (string $value): bool => 'a' === $value,
                static fn (string $value): bool => 'd' === $value
            )
            ->shouldIterateAs([0 => 'a', 3 => 'd']);

        $this::fromIterable(range(0, 10))
            ->filter(static fn (int $value): bool => $value % 2 === 0)
            ->filter(static fn (int $value): bool => $value % 3 === 0)
            ->shouldIterateAs([0 => 0, 6 => 6]);

        $this::fromIterable([true, false, 0, '', null])
            ->filter()
            ->shouldIterateAs([true]);
    }

    public function it_can_flatMap(): void
    {
        $this::fromIterable([1, 2, 3])
            ->flatMap(static fn (int $item, int $key): array => [$key => $item * $item])
            ->shouldIterateAs([1, 4, 9]);

        $gen = static function (): Generator {
            yield 0 => 1;

            yield 0 => 4;

            yield 0 => 9;
        };

        $this::fromIterable([1, 2, 3])
            ->flatMap(static fn (int $item): array => [$item * $item])
            ->shouldIterateAs($gen());

        $this::fromIterable([1, 2, 3])
            ->flatMap(static fn (int $item): iterable => new ArrayIterator([$item * $item]))
            ->shouldIterateAs($gen());

        $this::fromIterable([1, 2, 3])
            ->flatMap(static fn (int $item): Collection => Collection::fromIterable([$item + $item, $item * $item]))
            ->normalize()
            ->shouldIterateAs([2, 1, 4, 4, 6, 9]);

        $this::fromIterable([1, 2, 3])
            ->flatMap(static fn (int $item): array => [[$item + $item], [$item * $item]])
            ->normalize()
            ->shouldIterateAs([[2], [1], [4], [4], [6], [9]]);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->flatMap(static fn (string $item, string $key): array => [$item => $key])
            ->shouldIterateAs(['f' => 'foo', 'b' => 'bar']);

        $barGen = static function (): Generator {
            yield 0 => 'fbar';

            yield 0 => 'bbar';
        };

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->flatMap(static fn (string $item): array => [$item . 'bar'])
            ->shouldIterateAs($barGen());

        $gen = static function (): Generator {
            yield 0 => ['f' => 'foo'];

            yield 1 => ['FOO' => 'F'];

            yield 0 => ['b' => 'bar'];

            yield 1 => ['BAR' => 'B'];
        };

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->flatMap(static fn (string $item, string $key): array => [[$item => $key], [mb_strtoupper($key) => mb_strtoupper($item)]])
            ->shouldIterateAs($gen());
    }

    public function it_can_flatten(): void
    {
        $input = [
            ['a', 'b', 'c'],
            'd',
            ['d', ['e', 'f']],
        ];

        $output = static function (): Generator {
            yield 0 => 'a';

            yield 1 => 'b';

            yield 2 => 'c';

            yield 1 => 'd';

            yield 0 => 'd';

            yield 0 => 'e';

            yield 1 => 'f';
        };

        $this::fromIterable($input)
            ->flatten()
            ->shouldIterateAs($output());

        $output = static function (): Generator {
            yield 0 => 'a';

            yield 1 => 'b';

            yield 2 => 'c';

            yield 1 => 'd';

            yield 0 => 'd';

            yield 1 => ['e', 'f'];
        };

        $this::fromIterable($input)
            ->flatten(1)
            ->shouldIterateAs($output());

        $output = static function (): Generator {
            yield 0 => 1;

            yield 0 => 2;

            yield 1 => 3;

            yield 2 => 4;
        };

        $this::fromIterable([1, new ArrayIterator([2, 3]), 4])
            ->flatten()
            ->shouldIterateAs($output());
    }

    public function it_can_flip(): void
    {
        $this::fromIterable(range('A', 'E'))
            ->flip()
            ->shouldIterateAs(['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4]);

        $this::fromIterable(['a', 'b', 'c', 'd', 'a'])
            ->flip()
            ->flip()
            ->all()
            ->shouldIterateAs(['a', 'b', 'c', 'd', 'a']);
    }

    public function it_can_fold_from_the_left(): void
    {
        $this::empty()
            ->foldLeft(static fn (string $carry, string $string): string => sprintf('%s%s', $carry, $string), 'foo')
            ->shouldIterateAs(['foo']);

        $this::fromIterable(range('A', 'C'))
            ->foldLeft(
                static function (string $carry, string $item): string {
                    $carry .= $item;

                    return $carry;
                },
                ''
            )
            ->shouldIterateAs([2 => 'ABC']);
    }

    public function it_can_fold_from_the_right(): void
    {
        $this::fromIterable(range('A', 'C'))
            ->foldRight(
                static function (string $carry, string $item): string {
                    $carry .= $item;

                    return $carry;
                },
                ''
            )
            ->shouldIterateAs([0 => 'CBA']);
    }

    public function it_can_foldleft1(): void
    {
        $callback = static function ($carry, $value) {
            return $carry / $value;
        };

        $this::fromIterable([64, 4, 2, 8])
            ->foldLeft1($callback)
            ->shouldIterateAs([3 => 1]);

        $this::fromIterable([12])
            ->foldLeft1($callback)
            ->shouldIterateAs([0 => 12]);
    }

    public function it_can_foldright1(): void
    {
        $callback = static function ($carry, $value) {
            return $value / $carry;
        };

        $this::fromIterable([8, 12, 24, 4])
            ->foldRight1($callback)
            ->shouldIterateAs([0 => 4]);

        $this::fromIterable([12])
            ->foldRight1($callback)
            ->shouldIterateAs([0 => 12]);
    }

    public function it_can_forget(): void
    {
        $this::fromIterable(range('A', 'E'))
            ->forget(0, 4)
            ->shouldIterateAs([1 => 'B', 2 => 'C', 3 => 'D']);
    }

    public function it_can_frequency(): void
    {
        $object = new StdClass();

        $input = ['1', '2', '3', null, '4', '2', null, '6', $object, $object];

        $iterateAs = static function () use ($object): Generator {
            yield 1 => '1';

            yield 2 => '2';

            yield 1 => '3';

            yield 2 => null;

            yield 1 => '4';

            yield 1 => '6';

            yield 2 => $object;
        };

        $this::fromIterable($input)
            ->frequency()
            ->shouldIterateAs($iterateAs());
    }

    public function it_can_get(): void
    {
        $this::fromIterable(range('A', 'E'))
            ->get(4)
            ->shouldIterateAs([4 => 'E']);

        $this::fromIterable(range('A', 'E'))
            ->get('unexistent key', 'default')
            ->shouldIterateAs(['default']);
    }

    public function it_can_get_an_iterator(): void
    {
        $collection = Collection::fromIterable(range(1, 5));

        $this::fromIterable($collection)
            ->getIterator()
            ->shouldImplement(Iterator::class);
    }

    public function it_can_get_current()
    {
        $input = array_combine(range('A', 'E'), range('A', 'E'));

        $this::fromIterable($input)
            ->current()
            ->shouldReturn('A');

        $this::fromIterable($input)
            ->current(1)
            ->shouldReturn('B');

        $this::fromIterable($input)
            ->current(10)
            ->shouldReturn(null);
    }

    public function it_can_get_key()
    {
        $input = array_combine(range('A', 'E'), range('A', 'E'));

        $this::fromIterable($input)
            ->key()
            ->shouldReturn('A');

        $this::fromIterable($input)
            ->key(1)
            ->shouldReturn('B');

        $this::fromIterable($input)
            ->key(10)
            ->shouldReturn(null);
    }

    public function it_can_get_the_first_item(): void
    {
        $this::fromIterable(range(1, 10))
            ->first()
            ->shouldIterateAs([0 => 1]);

        $this::fromIterable([])
            ->first()
            ->shouldIterateAs([]);

        $this::fromIterable(['foo' => 'bar', 'baz' => 'bar'])
            ->first()
            ->shouldIterateAs(['foo' => 'bar']);
    }

    public function it_can_get_the_last_item(): void
    {
        $this::fromIterable(range('A', 'F'))
            ->last()
            ->shouldIterateAs([5 => 'F']);

        $this::fromIterable(['A'])
            ->last()
            ->shouldIterateAs([0 => 'A']);

        $this::fromIterable([])
            ->last()
            ->shouldIterateAs([]);

        $this::fromIterable(['foo' => 'bar', 'baz' => 'bar'])
            ->last()
            ->shouldIterateAs(['baz' => 'bar']);

        $input = [
            ['a'],
            ['b', 'a'],
            ['c', 'b', 'a'],
            ['d', 'c', 'b', 'a'],
        ];

        $this::fromIterable($input)
            ->last()
            ->shouldIterateAs([
                3 => ['d', 'c', 'b', 'a'],
            ]);
    }

    public function it_can_group(): void
    {
        $this::fromString('Mississippi')
            ->group()
            ->shouldIterateAs([
                0 => [0 => 'M'],
                1 => [0 => 'i'],
                2 => [0 => 's', 1 => 's'],
                3 => [0 => 'i'],
                4 => [0 => 's', 1 => 's'],
                5 => [0 => 'i'],
                6 => [0 => 'p', 1 => 'p'],
                7 => [0 => 'i'],
            ]);

        $this::fromString('aabbcc')
            ->group()
            ->shouldIterateAs([
                0 => [0 => 'a', 1 => 'a'],
                1 => [0 => 'b', 1 => 'b'],
                2 => [0 => 'c', 1 => 'c'],
            ]);

        $this::empty()
            ->group()
            ->shouldIterateAs([]);
    }

    public function it_can_groupBy(): void
    {
        $callback = static function () {
            yield 1 => 'a';

            yield 1 => 'b';

            yield 1 => 'c';

            yield 2 => 'd';

            yield 2 => 'e';

            yield 3 => 'f';

            yield 4 => 'g';

            yield 10 => 'h';
        };

        $this::fromCallable($callback)
            ->groupBy()
            ->shouldIterateAs([
                1 => [
                    'a',
                    'b',
                    'c',
                ],
                2 => [
                    'd',
                    'e',
                ],
                3 => ['f'],
                4 => ['g'],
                10 => ['h'],
            ]);

        $callback = static function (int $value, int $key) {
            return 0 === ($value % 2) ? 'even' : 'odd';
        };

        $this::fromIterable(range(0, 20))
            ->groupBy($callback)
            ->shouldIterateAs([
                'even' => [
                    0,
                    2,
                    4,
                    6,
                    8,
                    10,
                    12,
                    14,
                    16,
                    18,
                    20,
                ],
                'odd' => [
                    1,
                    3,
                    5,
                    7,
                    9,
                    11,
                    13,
                    15,
                    17,
                    19,
                ],
            ]);

        $input = range(0, 20);
        $this::fromIterable($input)
            ->groupBy(static function () {return null; })
            ->shouldIterateAs($input);
    }

    public function it_can_has(): void
    {
        $input = range('A', 'C');

        $this::fromIterable($input)
            ->has(static fn () => 'A')
            ->shouldBe(true);

        $this::fromIterable($input)
            ->has(static fn () => 'Z')
            ->shouldBe(false);

        $input = ['b', 1, 'foo', 'bar'];

        $this::fromIterable($input)
            ->has(static fn () => 'foo')
            ->shouldBe(true);

        $this::fromIterable($input)
            ->has(static fn () => 'unknown')
            ->shouldBe(false);

        $this::empty()
            ->has(static fn ($value) => $value)
            ->shouldBe(false);

        $this::fromIterable($input)
            ->has(static fn () => 1, static fn () => 'bar')
            ->shouldBe(true);

        $this::fromIterable($input)
            ->has(static fn () => 'coin', static fn () => 'bar')
            ->shouldBe(true);

        $this::fromIterable($input)
            ->has(static fn ($value, $key) => 5 < $key ? 'bar' : 'coin')
            ->shouldBe(false);
    }

    public function it_can_head(): void
    {
        $this::fromIterable(range(1, 10))
            ->head()
            ->shouldIterateAs([0 => 1]);

        $this::fromIterable([])
            ->head()
            ->shouldIterateAs([]);

        $this::fromIterable(['foo' => 'bar', 'baz' => 'bar'])
            ->head()
            ->shouldIterateAs(['foo' => 'bar']);
    }

    public function it_can_if_then_else(): void
    {
        $input = range(1, 5);

        $condition = static function ($value) {
            return 0 === $value % 2;
        };

        $then = static function ($value) {
            return $value * $value;
        };

        $else = static function ($value) {
            return $value + 2;
        };

        $this::fromIterable($input)
            ->ifThenElse($condition, $then)
            ->shouldIterateAs([
                1, 4, 3, 16, 5,
            ]);

        $this::fromIterable($input)
            ->ifThenElse($condition, $then, $else)
            ->shouldIterateAs([
                3, 4, 5, 16, 7,
            ]);
    }

    public function it_can_implode(): void
    {
        $this::fromIterable(range('A', 'C'))
            ->implode('-')
            ->shouldIterateAs([2 => 'A-B-C']);

        $this::fromIterable(range('A', 'C'))
            ->implode()
            ->shouldIterateAs([2 => 'ABC']);
    }

    public function it_can_init(): void
    {
        $this::fromIterable(range(0, 4))
            ->init()
            ->shouldIterateAs([
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
            ]);

        $input = [
            ['a'],
            ['b', 'a'],
            ['c', 'b', 'a'],
            ['d', 'c', 'b', 'a'],
        ];

        $this::fromIterable($input)
            ->init()
            ->shouldIterateAs([
                ['a'],
                ['b', 'a'],
                ['c', 'b', 'a'],
            ]);
    }

    public function it_can_inits(): void
    {
        $this::fromIterable(range('a', 'c'))
            ->inits()
            ->shouldIterateAs([
                [],
                ['a'],
                ['a', 'b'],
                ['a', 'b', 'c'],
            ]);
    }

    public function it_can_intersect(): void
    {
        $this::fromIterable(range(1, 5))
            ->intersect(1, 2, 3, 9)
            ->shouldIterateAs([0 => 1, 1 => 2, 2 => 3]);

        $this::fromIterable(range(1, 5))
            ->intersect()
            ->shouldIterateAs([]);
    }

    public function it_can_intersectKeys(): void
    {
        $input = array_combine(range('a', 'e'), range(1, 5));

        $this::fromIterable($input)
            ->intersectKeys('b', 'd')
            ->shouldIterateAs(['b' => 2, 'd' => 4]);

        $this::fromIterable($input)
            ->intersectKeys()
            ->shouldIterateAs([]);

        $this::fromIterable(range('A', 'E'))
            ->intersectKeys(0, 1, 3)
            ->shouldIterateAs([0 => 'A', 1 => 'B', 3 => 'D']);

        $this::fromIterable(range('A', 'E'))
            ->intersectKeys()
            ->shouldIterateAs([]);
    }

    public function it_can_intersperse(): void
    {
        $generator = static function () {
            yield 0 => 'foo';

            yield 0 => 'A';

            yield 1 => 'foo';

            yield 1 => 'B';

            yield 2 => 'foo';

            yield 2 => 'C';

            yield 3 => 'foo';

            yield 3 => 'D';

            yield 4 => 'foo';

            yield 4 => 'E';

            yield 5 => 'foo';

            yield 5 => 'F';
        };

        $this::fromIterable(range('A', 'F'))
            ->intersperse('foo')
            ->shouldIterateAs($generator());

        $generator = static function () {
            yield 0 => 'foo';

            yield 0 => 'A';

            yield 1 => 'B';

            yield 2 => 'foo';

            yield 2 => 'C';

            yield 3 => 'D';

            yield 4 => 'foo';

            yield 4 => 'E';

            yield 5 => 'F';
        };

        $this::fromIterable(range('A', 'F'))
            ->intersperse('foo', 2, 0)
            ->shouldIterateAs($generator());

        $generator = static function () {
            yield 0 => 'A';

            yield 1 => 'foo';

            yield 1 => 'B';

            yield 2 => 'C';

            yield 3 => 'foo';

            yield 3 => 'D';

            yield 4 => 'E';

            yield 5 => 'foo';

            yield 5 => 'F';
        };

        $this::fromIterable(range('A', 'F'))
            ->intersperse('foo', 2, 1)
            ->shouldIterateAs($generator());

        $generator = static function () {
            yield 0 => 'foo';

            yield 0 => 'A';

            yield 1 => 'B';

            yield 2 => 'foo';

            yield 2 => 'C';

            yield 3 => 'D';

            yield 4 => 'foo';

            yield 4 => 'E';

            yield 5 => 'F';
        };

        $this::fromIterable(range('A', 'F'))
            ->intersperse('foo', 2, 2)
            ->shouldIterateAs($generator());

        $this::fromIterable(range('A', 'F'))
            ->intersperse('foo', -1, 1)
            ->shouldThrow(Exception::class)
            ->during('all');

        $this::fromIterable(range('A', 'F'))
            ->intersperse('foo', 1, -1)
            ->shouldThrow(Exception::class)
            ->during('all');
    }

    public function it_can_isEmpty(): void
    {
        $gen = static fn (): Generator => yield from [];

        $this::fromIterable([])->isEmpty()->shouldBe(true);
        $this::fromIterable($gen())->isEmpty()->shouldBe(true);
        $this::empty()->isEmpty()->shouldBe(true);

        $this::fromIterable([null])->isEmpty()->shouldBe(false);
        $this::fromIterable([[]])->isEmpty()->shouldBe(false);
        $this::fromIterable([1, 2, 3])->isEmpty()->shouldBe(false);

        $withValues = $this::fromIterable([1, 2, 3]);

        foreach ($withValues as $value) {
            // iterating once through it
        }
        $withValues->isEmpty()->shouldBe(false);

        $withoutValues = $this::fromIterable([]);

        foreach ($withoutValues as $value) {
            // iterating once through it
        }
        $withoutValues->isEmpty()->shouldBe(true);
    }

    public function it_can_key(): void
    {
        $input = array_combine(
            range('a', 'e'),
            range(1, 5)
        );

        $this::fromIterable($input)
            ->key(0)
            ->shouldReturn('a');

        $this::fromIterable($input)
            ->key()
            ->shouldReturn('a');

        $this::fromIterable($input)
            ->key(9)
            ->shouldReturn(null);

        $this::fromIterable($input)
            ->key(4)
            ->shouldReturn('e');
    }

    public function it_can_keys(): void
    {
        $this::fromIterable(range('A', 'E'))
            ->keys()
            ->shouldIterateAs(range(0, 4));
    }

    public function it_can_limit(): void
    {
        $input = range('A', 'E');
        $this::fromIterable($input)
            ->limit(3)
            ->shouldHaveCount(3);

        $this::fromIterable($input)
            ->limit(3)
            ->shouldIterateAs(['A', 'B', 'C']);

        $this::fromIterable($input)
            ->limit(0)
            ->shouldThrow(OutOfBoundsException::class)
            ->during('all');
    }

    public function it_can_lines(): void
    {
        $string = <<<'EOF'
            The quick brow fox jumps over the lazy dog.

            This is another sentence.
            EOF;

        $lines = [
            'The quick brow fox jumps over the lazy dog.',
            '',
            'This is another sentence.',
        ];

        $this::fromString($string)
            ->lines()
            ->shouldIterateAs($lines);
    }

    public function it_can_map(): void
    {
        $input = array_combine(range('A', 'E'), range('A', 'E'));

        $this::fromIterable($input)
            ->map(static fn (string $item): string => $item . $item)
            ->shouldIterateAs(['A' => 'AA', 'B' => 'BB', 'C' => 'CC', 'D' => 'DD', 'E' => 'EE']);

        $square = static fn (int $a): int => $a ** 2;
        $toString = static fn (int $a): string => (string) $a;
        $appendBar = static fn (string $a): string => $a . 'bar';

        $this::fromIterable(range(1, 3))
            ->map($square)
            ->map($toString)
            ->map($appendBar)
            ->shouldIterateAs(['1bar', '4bar', '9bar']);

        $nonStandardInput = static function (): Generator {
            yield ['a'] => 1;

            yield ['b'] => 2;

            yield ['a'] => 3;
        };

        $expected = static function (): Generator {
            yield ['a'] => 1;

            yield ['b'] => 4;

            yield ['a'] => 9;
        };

        $this::fromIterable($nonStandardInput())
            ->map(static fn (int $value): int => $value ** 2)
            ->shouldIterateAs($expected());
    }

    public function it_can_mapN(): void
    {
        $input = array_combine(range('A', 'E'), range('A', 'E'));

        $this::fromIterable($input)
            ->mapN(static fn (string $item): string => $item . $item)
            ->shouldIterateAs(['A' => 'AA', 'B' => 'BB', 'C' => 'CC', 'D' => 'DD', 'E' => 'EE']);

        $square = static fn (int $a): int => $a ** 2;
        $toString = static fn (int $a): string => (string) $a;

        $this::fromIterable(range(1, 3))
            ->mapN($square, $toString)
            ->shouldIterateAs(['1', '4', '9']);

        $this::fromIterable(range(1, 3))
            ->mapN(static fn (int $item): int => $item ** 2)
            ->mapN(static fn (int $item): string => (string) $item)
            ->mapN(static fn (string $item): string => $item . 'bar')
            ->shouldIterateAs(['1bar', '4bar', '9bar']);
    }

    public function it_can_match(): void
    {
        $input = range(1, 10);

        $this::fromIterable($input)
            ->match(static fn (int $value): bool => 7 === $value)
            ->shouldBe(true);

        $this::fromIterable($input)
            ->match(static fn (int $value): bool => 17 === $value)
            ->shouldBe(false);

        $this::fromIterable($input)
            ->match(static fn (int $value): bool => 5 !== $value, static fn (): bool => false)
            ->shouldBe(true);
    }

    public function it_can_matching(): void
    {
        $users = [
            [
                'name' => 'Pol',
                'age' => 39,
                'is_admin' => true,
            ],
            [
                'name' => 'Sandra',
                'age' => 38,
                'is_admin' => false,
            ],
            [
                'name' => 'Izumi',
                'age' => 7,
                'is_admin' => true,
            ],
            [
                'name' => 'Nakano',
                'age' => 4,
                'is_admin' => false,
            ],
        ];

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('is_admin', true))
            ->orderBy(['age' => 'ASC'])
            ->setMaxResults(1);

        $this::fromIterable($users)
            ->matching($criteria)
            ->shouldIterateAs(
                [
                    2 => [
                        'name' => 'Izumi',
                        'age' => 7,
                        'is_admin' => true,
                    ],
                ]
            );
    }

    public function it_can_merge(): void
    {
        $collection = Collection::fromCallable(static function () {
            yield from range('F', 'J');
        });

        $generator = static function (): Generator {
            yield 0 => 'A';

            yield 1 => 'B';

            yield 2 => 'C';

            yield 3 => 'D';

            yield 4 => 'E';

            yield 0 => 'F';

            yield 1 => 'G';

            yield 2 => 'H';

            yield 3 => 'I';

            yield 4 => 'J';
        };

        $this::fromIterable(range('A', 'E'))
            ->merge($collection->all())
            ->shouldIterateAs($generator());
    }

    public function it_can_normalize(): void
    {
        $this::fromIterable(['a' => 10, 'b' => 100, 'c' => 1000])
            ->normalize()
            ->shouldIterateAs([0 => 10, 1 => 100, 2 => 1000]);

        $generator = static function (): Generator {
            yield 1 => 'a';

            yield 2 => 'b';

            yield 1 => 'c';

            yield 3 => 'd';
        };

        $this::fromIterable($generator())
            ->normalize()
            ->shouldIterateAs([0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd']);

        $this::fromIterable(range(1, 5))
            ->filter(static fn (int $val): bool => $val % 2 === 0)
            ->normalize()
            ->shouldIterateAs([0 => 2, 1 => 4]);
    }

    public function it_can_nth(): void
    {
        $this::fromIterable(range(0, 70))
            ->nth(7)
            ->shouldIterateAs([0 => 0, 7 => 7, 14 => 14, 21 => 21, 28 => 28, 35 => 35, 42 => 42, 49 => 49, 56 => 56, 63 => 63, 70 => 70]);

        $this::fromIterable(range(0, 70))
            ->nth(7, 3)
            ->shouldIterateAs([3 => 3, 10 => 10, 17 => 17, 24 => 24, 31 => 31, 38 => 38, 45 => 45, 52 => 52, 59 => 59, 66 => 66]);
    }

    public function it_can_nullsy(): void
    {
        $this::fromIterable([null, null, null])
            ->nullsy()
            ->shouldBe(true);

        $this::fromIterable([null, 0, null])
            ->nullsy()
            ->shouldBe(true);

        $this::fromIterable([null, [], 0, false, ''])
            ->nullsy()
            ->shouldBe(true);

        $this::fromIterable([null, [], 0, false, '', 'foo'])
            ->nullsy()
            ->shouldBe(false);
    }

    public function it_can_pack(): void
    {
        $input = array_combine(range('A', 'C'), range('a', 'c'));

        $this::fromIterable($input)
            ->pack()
            ->shouldIterateAs(
                [
                    ['A', 'a'],
                    ['B', 'b'],
                    ['C', 'c'],
                ]
            );
    }

    public function it_can_pad(): void
    {
        $input = array_combine(range('A', 'E'), range('A', 'E'));

        $this::fromIterable($input)
            ->pad(10, 'foo')
            ->shouldIterateAs(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 0 => 'foo', 1 => 'foo', 2 => 'foo', 3 => 'foo', 4 => 'foo']);
    }

    public function it_can_pair(): void
    {
        $input = [
            [
                'key' => 'k1',
                'value' => 'v1',
            ],
            [
                'key' => 'k2',
                'value' => 'v2',
            ],
            [
                'key' => 'k3',
                'value' => 'v3',
            ],
            [
                'key' => 'k4',
                'value' => 'v4',
            ],
            [
                'key' => 'k4',
                'value' => 'v5',
            ],
        ];

        $gen = static function () {
            yield 'k1' => 'v1';

            yield 'k2' => 'v2';

            yield 'k3' => 'v3';

            yield 'k4' => 'v4';

            yield 'k4' => 'v5';
        };

        $this::fromIterable($input)
            ->unwrap()
            ->pair()
            ->shouldIterateAs($gen());

        $input = ['a', 'b', 'c'];

        $gen = static function () {
            yield 'a' => 'b';

            yield 'c' => null;
        };

        $this::fromIterable($input)
            ->pair()
            ->shouldIterateAs($gen());
    }

    public function it_can_partition(): void
    {
        $isGreaterThan = static fn (int $left): Closure => static fn (int $right): bool => $left < $right;

        $input = array_combine(range('a', 'l'), [1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3]);

        // Using `first` and `last`, single callback

        $subject = $this::fromIterable($input)->partition($isGreaterThan(5));
        $subject->shouldHaveCount(2);

        $first = $subject->first()->current();
        $last = $subject->last()->current();

        $first->shouldBeAnInstanceOf(CollectionInterface::class);
        $last->shouldBeAnInstanceOf(CollectionInterface::class);

        $first->shouldHaveCount(4);
        $last->shouldHaveCount(8);

        $first->shouldIterateAs(['f' => 6, 'g' => 7, 'h' => 8, 'i' => 9]);
        $last->shouldIterateAs(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'j' => 1, 'k' => 2, 'l' => 3]);

        // Using `all` and array destructuring, single callback

        [$passed, $rejected] = $this::fromIterable($input)->partition($isGreaterThan(5))->all();
        $passed->shouldBeAnInstanceOf(CollectionInterface::class);
        $rejected->shouldBeAnInstanceOf(CollectionInterface::class);

        $passed->shouldHaveCount(4);
        $rejected->shouldHaveCount(8);

        $passed->shouldIterateAs(['f' => 6, 'g' => 7, 'h' => 8, 'i' => 9]);
        $rejected->shouldIterateAs(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'j' => 1, 'k' => 2, 'l' => 3]);

        // Using multiple callbacks

        $this::fromIterable($input)
            ->partition($isGreaterThan(5), $isGreaterThan(3))
            ->first()
            ->current()
            ->shouldIterateAs(['d' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8, 'i' => 9]);

        $this::fromIterable($input)
            ->partition($isGreaterThan(5), $isGreaterThan(3))
            ->last()
            ->current()
            ->shouldIterateAs(['a' => 1, 'b' => 2, 'c' => 3, 'j' => 1, 'k' => 2, 'l' => 3]);
    }

    public function it_can_permutate(): void
    {
        $this::fromIterable(range('a', 'c'))
            ->permutate()
            ->shouldIterateAs(
                [
                    [
                        0 => 'a',
                        1 => 'b',
                        2 => 'c',
                    ],
                    [
                        0 => 'a',
                        1 => 'c',
                        2 => 'b',
                    ],
                    [
                        0 => 'b',
                        1 => 'a',
                        2 => 'c',
                    ],
                    [
                        0 => 'b',
                        1 => 'c',
                        2 => 'a',
                    ],
                    [
                        0 => 'c',
                        1 => 'a',
                        2 => 'b',
                    ],
                    [
                        0 => 'c',
                        1 => 'b',
                        2 => 'a',
                    ],
                ]
            );
    }

    public function it_can_pipe(Operation $operation): void
    {
        $square = new class() implements Operation {
            public function __invoke(): Closure
            {
                return static function ($collection): Generator {
                    foreach ($collection as $item) {
                        yield $item ** 2;
                    }
                };
            }
        };

        $sqrt = new class() implements Operation {
            public function __invoke(): Closure
            {
                return static function ($collection) {
                    foreach ($collection as $item) {
                        yield $item ** .5;
                    }
                };
            }
        };

        $castToInt = new class() implements Operation {
            public function __invoke(): Closure
            {
                return static function ($collection) {
                    foreach ($collection as $item) {
                        yield (int) $item;
                    }
                };
            }
        };

        $this::fromIterable(range(1, 5))
            ->pipe($square(), $sqrt(), $castToInt())
            ->shouldIterateAs(range(1, 5));
    }

    public function it_can_pluck(): void
    {
        $six = new class() {
            public $foo = [
                'bar' => 5,
            ];
        };

        $input = [
            [
                0 => 'A',
                'foo' => [
                    'bar' => 0,
                ],
            ],
            [
                0 => 'B',
                'foo' => [
                    'bar' => 1,
                ],
            ],
            [
                0 => 'C',
                'foo' => [
                    'bar' => 2,
                ],
            ],
            Collection::fromIterable(
                [
                    'foo' => [
                        'bar' => 3,
                    ],
                ]
            ),
            new ArrayObject([
                'foo' => [
                    'bar' => 4,
                ],
            ]),
            new class() {
                public $foo = [
                    'bar' => 5,
                ];
            },
            [
                0 => 'D',
                'foo' => [
                    'bar' => $six,
                ],
            ],
        ];

        $this::fromIterable($input)
            ->pluck('foo')
            ->shouldIterateAs([0 => ['bar' => 0], 1 => ['bar' => 1], 2 => ['bar' => 2], 3 => ['bar' => 3], 4 => ['bar' => 4], 5 => ['bar' => 5], 6 => ['bar' => $six]]);

        $this::fromIterable($input)
            ->pluck('foo.*')
            ->shouldIterateAs([0 => [0 => 0], 1 => [0 => 1], 2 => [0 => 2], 3 => [0 => 3], 4 => [0 => 4], 5 => [0 => 5], 6 => [0 => $six]]);

        $this::fromIterable($input)
            ->pluck('.foo.bar.')
            ->shouldIterateAs([0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => $six]);

        $this::fromIterable($input)
            ->pluck('foo.bar.*', 'taz')
            ->shouldIterateAs([0 => 'taz', 1 => 'taz', 2 => 'taz', 3 => 'taz', 4 => 'taz', 5 => 'taz', 6 => 'taz']);

        $this::fromIterable($input)
            ->pluck('azerty', 'taz')
            ->shouldIterateAs([0 => 'taz', 1 => 'taz', 2 => 'taz', 3 => 'taz', 4 => 'taz', 5 => 'taz', 6 => 'taz']);

        $this::fromIterable($input)
            ->pluck(0)
            ->shouldIterateAs([0 => 'A', 1 => 'B', 2 => 'C', null, null, null, 6 => 'D']);
    }

    public function it_can_prepend(): void
    {
        $generator = static function (): Generator {
            yield 0 => 'A';

            yield 1 => 'B';

            yield 2 => 'C';

            yield 0 => 'D';

            yield 1 => 'E';

            yield 2 => 'F';
        };

        $this::fromIterable(range('D', 'F'))
            ->prepend('A', 'B', 'C')
            ->shouldIterateAs($generator());
    }

    public function it_can_product(): void
    {
        $this::fromIterable(range('A', 'C'))
            ->product()
            ->shouldIterateAs([0 => ['A'], 1 => ['B'], 2 => ['C']]);

        $this::fromIterable(range('A', 'C'))
            ->product([1, 2], [3, 4])
            ->shouldIterateAs([
                ['A', 1, 3],
                ['A', 1, 4],
                ['A', 2, 3],
                ['A', 2, 4],
                ['B', 1, 3],
                ['B', 1, 4],
                ['B', 2, 3],
                ['B', 2, 4],
                ['C', 1, 3],
                ['C', 1, 4],
                ['C', 2, 3],
                ['C', 2, 4],
            ]);
    }

    public function it_can_random(): void
    {
        $input = range('a', 'z');

        $generator = static function (array $input): Generator {
            yield from $input;
        };

        $this::fromIterable($input)
            ->random()
            ->count()
            ->shouldBeEqualTo(1);

        $this::fromIterable($input)
            ->random(100)
            ->count()
            ->shouldBeEqualTo(26);

        $this::fromIterable($input)
            ->random(26)
            ->shouldNotIterateAs($generator($input));

        $input = range('a', 'z');

        $generator = static function (array $input): Generator {
            yield from $input;
        };

        $this::fromIterable(['a'])
            ->random()
            ->shouldIterateAs(['a']);

        $this::fromIterable($input)
            ->random(0)
            ->shouldThrow(OutOfBoundsException::class)
            ->during('all');
    }

    public function it_can_reduce(): void
    {
        $this::empty()
            ->reduce(static fn (string $carry, string $string): string => sprintf('%s%s', $carry, $string), 'foo')
            ->shouldIterateAs([]);

        $this::fromIterable(range(1, 5))
            ->reduce(
                static fn (int $carry, int $item): int => $carry + $item,
                0
            )
            ->shouldIterateAs([4 => 15]);

        $this::fromIterable(array_combine(range('x', 'z'), range('a', 'c')))
            ->reduce(
                static fn (string $carry, string $letter, string $index): string => sprintf('%s[%s:%s]', $carry, $index, $letter),
                '=> '
            )
            ->shouldIterateAs([
                'z' => '=> [x:a][y:b][z:c]',
            ]);
    }

    public function it_can_reduction(): void
    {
        $this::fromIterable(range(1, 5))
            ->reduction(
                static function ($carry, $item) {
                    return $carry + $item;
                },
                0
            )
            ->shouldIterateAs([1, 3, 6, 10, 15]);
    }

    public function it_can_reject(): void
    {
        $input = array_merge([0, false], range(1, 10));

        $callable = static function ($value) {
            return $value % 2;
        };

        $callableWithKey = static fn (int $value, int $key): bool => $value % 2 === 0 && 4 < $key;

        $this::fromIterable($input)
            ->reject($callable)
            ->count()
            ->shouldReturn(7);

        $this::fromIterable($input)
            ->reject($callable)
            ->normalize()
            ->shouldIterateAs([0, false, 2, 4, 6, 8, 10]);

        $this::fromIterable(range(0, 10))
            ->reject($callableWithKey)
            ->shouldIterateAs([0, 1, 2, 3, 4, 5, 7 => 7, 9 => 9]);

        $this::fromIterable(['a', 'b', 'c', 'd'])
            ->reject(
                static fn (string $value): bool => 'a' === $value,
                static fn (string $value): bool => 'd' === $value
            )
            ->shouldIterateAs([1 => 'b', 2 => 'c']);

        $this::fromIterable(range(0, 10))
            ->reject(static fn (int $value): bool => $value % 2 === 0)
            ->reject(static fn (int $value): bool => $value % 3 === 0)
            ->shouldIterateAs([1 => 1, 5 => 5, 7 => 7]);

        $this::fromIterable([true, false, 0, '', null])
            ->reject()
            ->shouldIterateAs([1 => false, 2 => 0, 3 => '', 4 => null]);
    }

    public function it_can_reverse(): void
    {
        $this::empty()
            ->reverse()
            ->shouldIterateAs([]);

        $this::fromIterable(range('A', 'F'))
            ->reverse()
            ->shouldIterateAs([5 => 'F', 4 => 'E', 3 => 'D', 2 => 'C', 1 => 'B', 0 => 'A']);
    }

    public function it_can_rsample(): void
    {
        $this::fromIterable(range(1, 10))
            ->rsample(1)
            ->shouldHaveCount(10);

        $this::fromIterable(range(1, 10))
            ->rsample(.5)
            ->shouldNotHaveCount(10);
    }

    public function it_can_same(): void
    {
        $a = (object) ['id' => 'a'];
        $a2 = (object) ['id' => 'a'];
        $b = (object) ['id' => 'b'];

        // empty variations
        $this::empty()
            ->same(Collection::empty())
            ->shouldBe(true);

        $this::empty()
            ->same(Collection::fromIterable([1]))
            ->shouldBe(false);

        $this::fromIterable([1])
            ->same(Collection::empty())
            ->shouldBe(false);

        // same elements, same order (same keys)
        $this::fromIterable([1, 2, 3])
            ->same(Collection::fromIterable([1, 2, 3]))
            ->shouldBe(true);

        $this::fromIterable([$a, $b])
            ->same(Collection::fromIterable([$a, $b]))
            ->shouldBe(true);

        // same elements, different order (different keys)
        $this::fromIterable([1, 2, 3])
            ->same(Collection::fromIterable([3, 1, 2]))
            ->shouldBe(false);

        $this::fromIterable([$a, $b])
            ->same(Collection::fromIterable([$b, $a]))
            ->shouldBe(false);

        // same lengths, with one element different
        $this::fromIterable([1, 2, 3])
            ->same(Collection::fromIterable([1, 2, 4]))
            ->shouldBe(false);

        // different lengths, extra elements in first
        $this::fromIterable([1, 2, 3, 4])
            ->same(Collection::fromIterable([1, 2, 3]))
            ->shouldBe(false);

        // different lengths, extra elements in second
        $this::fromIterable([1, 2, 3])
            ->same(Collection::fromIterable([1, 2, 3, 4]))
            ->shouldBe(false);

        // objects, different instances and contents
        $this::fromIterable([$a])
            ->same(Collection::fromIterable([$b]))
            ->shouldBe(false);

        // objects, different instances but same contents
        $this::fromIterable([$a])
            ->same(Collection::fromIterable([$a2]))
            ->shouldBe(false);

        // "maps" with string keys and values
        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->same(Collection::fromIterable(['foo' => 'f', 'bar' => 'b']))
            ->shouldBe(true);

        $this::fromIterable(['foo' => 'f'])
            ->same(Collection::fromIterable(['bar' => 'f']))
            ->shouldBe(false);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->same(Collection::fromIterable(['bar' => 'b', 'foo' => 'f']))
            ->shouldBe(false);

        $this::fromIterable(['foo' => 'f', 'bar' => 'b'])
            ->same(Collection::fromIterable(['bar' => 'b']))
            ->shouldBe(false);

        $this::fromIterable(['foo' => 'f'])
            ->same(Collection::fromIterable(['bar' => 'b']))
            ->shouldBe(false);

        $this::fromIterable(['FOO' => 'f'])
            ->same(Collection::fromIterable(['foo' => 'f']))
            ->shouldBe(false);

        $this::fromIterable(['foo' => 'f'])
            ->same(Collection::fromIterable(['foo' => 'f', 'bar' => 'b']))
            ->shouldBe(false);

        // custom comparators
        $comparator = static fn ($left) => static fn ($right): bool => (int) $left === (int) $right;
        $this::fromIterable([1, 2, 3])
            ->same(Collection::fromIterable(['1', '2', '3']), $comparator)
            ->shouldBe(true);

        $comparator = static fn ($left) => static fn ($right): bool => $left === $right;
        $this::fromIterable(['foo' => 'f'])
            ->same(Collection::fromIterable(['bar' => 'f']), $comparator)
            ->shouldBe(true);

        $comparator = static fn ($left, $leftKey) => static fn ($right, $rightKey): bool => $left === $right
            && mb_strtolower($leftKey) === mb_strtolower($rightKey);
        $this::fromIterable(['foo' => 'f'])
            ->same(Collection::fromIterable(['FOO' => 'f']), $comparator)
            ->shouldBe(true);

        $comparator = static fn (stdClass $left) => static fn (stdClass $right): bool => $left->id === $right->id;
        $this::fromIterable([$a])
            ->same(Collection::fromIterable([$a2]), $comparator)
            ->shouldBe(true);
    }

    public function it_can_scale(): void
    {
        $input = [0, 2, 4, 6, 8, 10];

        $this::fromIterable($input)
            ->scale(0, 10)
            ->shouldIterateAs([0.0, 0.2, 0.4, 0.6, 0.8, 1.0]);

        $this::fromIterable($input)
            ->scale(0, 10, 5, 15, 3)
            ->map(static function ($value) {
                return (float) round($value, 2);
            })
            ->shouldIterateAs([5.0, 8.01, 11.02, 12.78, 14.03, 15.0]);
    }

    public function it_can_scanLeft(): void
    {
        $callback = static function ($carry, $value) {
            return $carry / $value;
        };

        $result = static function () {
            yield 0 => 64;

            yield 0 => 16;

            yield 1 => 8;

            yield 2 => 2;
        };

        $this::fromIterable([4, 2, 4])
            ->scanLeft($callback, 64)
            ->shouldIterateAs($result());

        $this::fromIterable([])
            ->scanLeft($callback, 3)
            ->shouldIterateAs([0 => 3]);
    }

    public function it_can_scanLeft1(): void
    {
        $callback = static function ($carry, $value) {
            return $carry / $value;
        };

        $this::fromIterable([64, 4, 2, 8])
            ->scanLeft1($callback)
            ->shouldIterateAs([64, 16, 8, 1]);

        $this::fromIterable([12])
            ->scanLeft1($callback)
            ->shouldIterateAs([12]);
    }

    public function it_can_scanRight(): void
    {
        $callback = static function ($carry, $value) {
            return $value / $carry;
        };

        $result = static function () {
            yield 0 => 8;

            yield 1 => 1;

            yield 2 => 12;

            yield 3 => 2;

            yield 0 => 2;
        };

        $this::fromIterable([8, 12, 24, 4])
            ->scanRight($callback, 2)
            ->shouldIterateAs($result());

        $this::fromIterable([])
            ->scanRight($callback, 3)
            ->shouldIterateAs([3]);
    }

    public function it_can_scanright1(): void
    {
        $callback = static function ($carry, $value) {
            return $value / $carry;
        };

        $result = static function () {
            yield 0 => 8;

            yield 1 => 1;

            yield 2 => 12;

            yield 0 => 2;
        };

        $this::fromIterable([8, 12, 24, 2])
            ->scanRight1($callback)
            ->shouldIterateAs($result());

        $this::fromIterable([12])
            ->scanRight1($callback)
            ->shouldIterateAs([12]);
    }

    public function it_can_shuffle(): void
    {
        $input = range('A', 'Z');

        $this::fromIterable($input)
            ->shuffle()
            ->shouldNotIterateAs($input);

        $this::fromIterable($input)
            ->shuffle()
            ->shouldNotIterateAs([]);

        $this::fromIterable($input)
            ->shuffle(123)
            ->shouldIterateAs([
                2 => 'C',
                20 => 'U',
                8 => 'I',
                3 => 'D',
                7 => 'H',
                9 => 'J',
                0 => 'A',
                21 => 'V',
                12 => 'M',
                15 => 'P',
                13 => 'N',
                4 => 'E',
                19 => 'T',
                10 => 'K',
                22 => 'W',
                11 => 'L',
                1 => 'B',
                5 => 'F',
                18 => 'S',
                23 => 'X',
                17 => 'R',
                24 => 'Y',
                16 => 'Q',
                25 => 'Z',
                14 => 'O',
                6 => 'G',
            ]);
    }

    public function it_can_since(): void
    {
        $input = range('a', 'z');

        $this::fromIterable($input)
            ->since(
                static function ($letter) {
                    return 'x' === $letter;
                }
            )
            ->shouldIterateAs([23 => 'x', 24 => 'y', 25 => 'z']);

        $this::fromIterable($input)
            ->since(
                static function ($letter) {
                    return 'x' === $letter;
                },
                static function ($letter) {
                    return 1 === mb_strlen($letter);
                }
            )
            ->shouldIterateAs($input);

        $this::fromIterable($input)
            ->since(
                static function ($letter) {
                    return 'foo' === $letter;
                },
                static function ($letter) {
                    return 'x' === $letter;
                }
            )
            ->shouldIterateAs([23 => 'x', 24 => 'y', 25 => 'z']);

        $isGreaterThanThree = static function (int $value): bool {
            return 3 < $value;
        };

        $isGreaterThanFive = static function (int $value): bool {
            return 5 < $value;
        };

        $input = [1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3];

        $this::fromIterable($input)
            ->since($isGreaterThanThree, $isGreaterThanFive)
            ->shouldIterateAs([
                3 => 4,
                4 => 5,
                5 => 6,
                6 => 7,
                7 => 8,
                8 => 9,
                9 => 1,
                10 => 2,
                11 => 3,
            ]);

        $this::fromIterable($input)
            ->since($isGreaterThanThree)
            ->since($isGreaterThanFive)
            ->shouldIterateAs([
                5 => 6,
                6 => 7,
                7 => 8,
                8 => 9,
                9 => 1,
                10 => 2,
                11 => 3,
            ]);
    }

    public function it_can_slice(): void
    {
        $this::fromIterable(range(0, 10))
            ->slice(5)
            ->shouldIterateAs([5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10]);

        $this::fromIterable(range(0, 10))
            ->slice(5, 2)
            ->shouldIterateAs([5 => 5, 6 => 6]);

        $this::fromIterable(range(0, 10))
            ->slice(5, 1000)
            ->shouldIterateAs([5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10]);
    }

    public function it_can_sort(): void
    {
        $input = array_combine(range('A', 'E'), range('E', 'A'));

        $this::fromIterable($input)
            ->sort(3)
            ->shouldThrow(Exception::class)
            ->during('all');

        $this::fromIterable($input)
            ->sort()
            ->shouldIterateAs(array_combine(range('E', 'A'), range('A', 'E')));

        $this::fromIterable($input)
            ->sort(Operation\Sortable::BY_VALUES)
            ->shouldIterateAs(array_combine(range('E', 'A'), range('A', 'E')));

        $this::fromIterable($input)
            ->sort(Operation\Sortable::BY_KEYS)
            ->shouldIterateAs(array_combine(range('A', 'E'), range('E', 'A')));

        $this::fromIterable($input)
            ->sort(
                Operation\Sortable::BY_VALUES,
                static function ($left, $right): int {
                    return $right <=> $left;
                }
            )
            ->shouldIterateAs(array_combine(range('A', 'E'), range('E', 'A')));

        $this::fromIterable($input)
            ->sort(Operation\Sortable::BY_KEYS)
            ->shouldIterateAs(array_combine(range('A', 'E'), range('E', 'A')));

        $inputGen = static function () {
            yield 'k1' => 'v1';

            yield 'k2' => 'v2';

            yield 'k3' => 'v3';

            yield 'k4' => 'v4';

            yield 'k1' => 'v1';

            yield 'k2' => 'v2';

            yield 'k3' => 'v3';

            yield 'k4' => 'v4';

            yield 'a' => 'z';
        };

        $output = static function () {
            yield 'a' => 'z';

            yield 'k1' => 'v1';

            yield 'k1' => 'v1';

            yield 'k2' => 'v2';

            yield 'k2' => 'v2';

            yield 'k3' => 'v3';

            yield 'k3' => 'v3';

            yield 'k4' => 'v4';

            yield 'k4' => 'v4';
        };

        $this::fromIterable($inputGen())
            ->sort(Operation\Sortable::BY_KEYS)
            ->shouldIterateAs($output());

        $this::fromIterable($inputGen())
            ->flip()
            ->sort(Operation\Sortable::BY_VALUES)
            ->flip()
            ->shouldIterateAs($output());
    }

    public function it_can_span(): void
    {
        $input = range(1, 10);

        $subject = $this::fromIterable($input)->span(static fn (int $x): bool => 4 > $x);
        $subject->shouldHaveCount(2);
        $subject->first()->shouldBeAnInstanceOf(CollectionInterface::class);
        $subject->last()->shouldBeAnInstanceOf(CollectionInterface::class);
        $subject->first()->current()->shouldIterateAs([1, 2, 3]);
        $subject->last()->current()->shouldIterateAs([3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10]);

        $subject = $this::fromIterable($input)->span(static fn (int $x): bool => 4 > $x, static fn (int $x): bool => $x % 2 === 0);
        $subject->shouldHaveCount(2);
        $subject->first()->shouldBeAnInstanceOf(CollectionInterface::class);
        $subject->last()->shouldBeAnInstanceOf(CollectionInterface::class);
        $subject->first()->current()->shouldIterateAs([1, 2, 3, 4]);
        $subject->last()->current()->shouldIterateAs([4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10]);
    }

    public function it_can_split(): void
    {
        $splitter = static function ($value): bool {
            return 0 === $value % 3;
        };

        $this::fromIterable(range(0, 10))
            ->split(
                Operation\Splitable::BEFORE,
                $splitter
            )
            ->shouldIterateAs([[0, 1, 2], [3, 4, 5], [6, 7, 8], [9, 10]]);

        $this::fromIterable(range(0, 10))
            ->split(
                Operation\Splitable::REMOVE,
                $splitter
            )
            ->shouldIterateAs([[], [1, 2], [4, 5], [7, 8], [10]]);

        $this::fromIterable(range(0, 10))
            ->split(
                Operation\Splitable::AFTER,
                $splitter
            )
            ->shouldIterateAs([[0], [1, 2, 3], [4, 5, 6], [7, 8, 9], [10]]);
    }

    public function it_can_squash(): void
    {
        $this::fromIterable([16, 4, -9, 9])
            ->map(
                static function (int $value): int {
                    if (0 > $value) {
                        throw new Exception('This should error');
                    }

                    return (int) sqrt($value);
                }
            )
            ->shouldThrow(Exception::class)
            ->during('squash');

        $this::fromIterable([16, 4, 9, 9])
            ->map(
                static function (int $value): int {
                    if (-100 > $value) {
                        throw new Exception('This should not error');
                    }

                    return (int) sqrt($value);
                }
            )
            ->shouldNotThrow(Exception::class)
            ->during('squash');
    }

    public function it_can_strict_allow(): void
    {
        $this->beConstructedThrough('fromIterable', [range('A', 'C')]);

        $this->strict()->shouldIterateAs(['A', 'B', 'C']);
    }

    public function it_can_strict_allow_custom_callback(): void
    {
        $obj1 = new stdClass();
        $obj2 = new class() {
            public function count(): int
            {
                return 0;
            }
        };

        $this->beConstructedThrough('fromIterable', [[$obj1, $obj2]]);

        $callback = static fn ($value): string => gettype($value);

        $this->strict($callback)->shouldNotThrow(InvalidArgumentException::class)->during('all');
    }

    public function it_can_strict_throw(): void
    {
        $this->beConstructedThrough('fromIterable', [[1, 'foo', 2]]);

        $this->strict()->shouldThrow(InvalidArgumentException::class)->during('all');
    }

    public function it_can_tail(): void
    {
        $this::fromIterable(range('A', 'F'))
            ->tail()
            ->shouldIterateAs([1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F']);
    }

    public function it_can_tails(): void
    {
        $this::fromIterable(range('A', 'E'))
            ->tails()
            ->shouldIterateAs([
                ['A', 'B', 'C', 'D', 'E'],
                [0 => 'B', 1 => 'C', 2 => 'D', 3 => 'E'],
                [0 => 'C', 1 => 'D', 2 => 'E'],
                [0 => 'D', 1 => 'E'],
                [0 => 'E'],
                [],
            ]);
    }

    public function it_can_takeWhile(): void
    {
        $isSmallerThan = static fn (int $bound) => static fn ($value): bool => $bound > $value;

        $this::fromIterable([1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3])
            ->takeWhile($isSmallerThan(5))
            ->shouldIterateAs([
                0 => 1,
                1 => 2,
                2 => 3,
                3 => 4,
            ]);

        $this::fromIterable([1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3])
            ->takeWhile(
                $isSmallerThan(3),
                $isSmallerThan(5)
            )
            ->shouldIterateAs([
                0 => 1,
                1 => 2,
                2 => 3,
                3 => 4,
            ]);

        $this::fromIterable([1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3])
            ->takeWhile(
                $isSmallerThan(5),
                $isSmallerThan(3),
            )
            ->shouldIterateAs([
                0 => 1,
                1 => 2,
                2 => 3,
                3 => 4,
            ]);
    }

    public function it_can_transpose(): void
    {
        $records = [
            [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ],
        ];

        $this::fromIterable($records)
            ->transpose()
            ->shouldIterateAs(
                [
                    'id' => [
                        0 => 2135,
                        1 => 3245,
                        2 => 5342,
                        3 => 5623,
                    ],
                    'first_name' => [
                        0 => 'John',
                        1 => 'Sally',
                        2 => 'Jane',
                        3 => 'Peter',
                    ],
                    'last_name' => [
                        0 => 'Doe',
                        1 => 'Smith',
                        2 => 'Jones',
                        3 => 'Doe',
                    ],
                ]
            );
    }

    public function it_can_truthy(): void
    {
        $this::fromIterable([true, true, true])
            ->truthy()
            ->shouldBe(true);

        $this::fromIterable([true, false, true])
            ->truthy()
            ->shouldBe(false);

        $this::fromIterable([1, 2, 3])
            ->truthy()
            ->shouldBe(true);

        $this::fromIterable([1, 2, 3, 0])
            ->truthy()
            ->shouldBe(false);
    }

    public function it_can_unfold(): void
    {
        $this::unfold(static function (int $n): int {return $n + 1; }, 1)
            ->limit(10)
            ->shouldIterateAs([
                0 => 2,
                1 => 3,
                2 => 4,
                3 => 5,
                4 => 6,
                5 => 7,
                6 => 8,
                7 => 9,
                8 => 10,
                9 => 11,
            ]);

        $fibonacci = static function ($value1, $value2) {
            return [$value2, $value1 + $value2];
        };

        $this::unfold($fibonacci, 0, 1)
            ->limit(10)
            ->shouldIterateAs([[1, 1], [1, 2], [2, 3], [3, 5], [5, 8], [8, 13], [13, 21], [21, 34], [34, 55], [55, 89]]);

        $plusOne = static function ($value) {
            return $value + 1;
        };

        $this::unfold($plusOne, 0)
            ->limit(10)
            ->shouldIterateAs([
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
            ]);
    }

    public function it_can_unlines(): void
    {
        $lines = [
            'The quick brow fox jumps over the lazy dog.',
            'This is another sentence.',
        ];

        $string = sprintf(
            '%s%s%s',
            'The quick brow fox jumps over the lazy dog.',
            PHP_EOL,
            'This is another sentence.'
        );

        $this::fromIterable($lines)
            ->unlines()
            ->shouldIterateAs([
                1 => $string,
            ]);
    }

    public function it_can_unpack(): void
    {
        $input = [
            ['a', 'a'],
            ['b', 'b'],
            ['c', 'c'],
            ['d', 'd'],
            ['e', 'e'],
        ];

        $this::fromIterable($input)
            ->unpack()
            ->shouldIterateAs([
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
                'd' => 'd',
                'e' => 'e',
            ]);

        $input = [
            ['a', 'b', 'c' => 'c', 'd' => 'd'],
            ['e', 'f', 'g' => 'g', 'h' => 'h'],
            ['i', 'j'],
        ];

        $this::fromIterable($input)
            ->unpack()
            ->shouldIterateAs([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
                'g' => 'h',
                'i' => 'j',
            ]);
    }

    public function it_can_unpair(): void
    {
        $input = [
            'k1' => 'v1',
            'k2' => 'v2',
            'k3' => 'v3',
            'k4' => 'v4',
        ];

        $this::fromIterable($input)
            ->unpair()
            ->shouldIterateAs([
                'k1', 'v1',
                'k2', 'v2',
                'k3', 'v3',
                'k4', 'v4',
            ]);
    }

    public function it_can_until(): void
    {
        $collatz = static function (int $initial = 1): int {
            return 0 === $initial % 2 ?
                $initial / 2 :
                $initial * 3 + 1;
        };

        $this::unfold($collatz, 10)
            ->until(static fn (int $number): bool => 1 === $number)
            ->shouldIterateAs([
                0 => 5,
                1 => 16,
                2 => 8,
                3 => 4,
                4 => 2,
                5 => 1,
            ]);

        $input = range(1, 10);

        $callback1 = static fn (int $number): bool => 8 < $number;
        $callback2 = static fn (int $number): bool => 3 < $number;

        $this::fromIterable($input)
            ->until(
                $callback2,
                $callback1
            )
            ->shouldIterateAs([
                0 => 1,
                1 => 2,
                2 => 3,
                3 => 4,
            ]);
    }

    public function it_can_unwindow(): void
    {
        $this::fromIterable([
            0 => [
                0 => 'a',
            ],
            1 => [
                0 => 'a',
                1 => 'b',
            ],
            2 => [
                0 => 'a',
                1 => 'b',
                2 => 'c',
            ],
            3 => [
                0 => 'b',
                1 => 'c',
                2 => 'd',
            ],
            4 => [
                0 => 'c',
                1 => 'd',
                2 => 'e',
            ],
            5 => [
                0 => 'd',
                1 => 'e',
                2 => 'f',
            ],
            6 => [
                0 => 'e',
                1 => 'f',
                2 => 'g',
            ],
            7 => [
                0 => 'f',
                1 => 'g',
                2 => 'h',
            ],
            8 => [
                0 => 'g',
                1 => 'h',
                2 => 'i',
            ],
            9 => [
                0 => 'h',
                1 => 'i',
                2 => 'j',
            ],
            10 => [
                0 => 'i',
                1 => 'j',
                2 => 'k',
            ],
            11 => [
                0 => 'j',
                1 => 'k',
                2 => 'l',
            ],
            12 => [
                0 => 'k',
                1 => 'l',
                2 => 'm',
            ],
            13 => [
                0 => 'l',
                1 => 'm',
                2 => 'n',
            ],
            14 => [
                0 => 'm',
                1 => 'n',
                2 => 'o',
            ],
            15 => [
                0 => 'n',
                1 => 'o',
                2 => 'p',
            ],
            16 => [
                0 => 'o',
                1 => 'p',
                2 => 'q',
            ],
            17 => [
                0 => 'p',
                1 => 'q',
                2 => 'r',
            ],
            18 => [
                0 => 'q',
                1 => 'r',
                2 => 's',
            ],
            19 => [
                0 => 'r',
                1 => 's',
                2 => 't',
            ],
            20 => [
                0 => 's',
                1 => 't',
                2 => 'u',
            ],
            21 => [
                0 => 't',
                1 => 'u',
                2 => 'v',
            ],
            22 => [
                0 => 'u',
                1 => 'v',
                2 => 'w',
            ],
            23 => [
                0 => 'v',
                1 => 'w',
                2 => 'x',
            ],
            24 => [
                0 => 'w',
                1 => 'x',
                2 => 'y',
            ],
            25 => [
                0 => 'x',
                1 => 'y',
                2 => 'z',
            ],
        ])
            ->unwindow()
            ->shouldIterateAs(range('a', 'z'));
    }

    public function it_can_unwords(): void
    {
        $string = <<<'EOF'
            The quick brow fox jumps over the lazy dog.

            This is another sentence.
            EOF;

        $words = [
            'The',
            'quick',
            'brow',
            'fox',
            'jumps',
            'over',
            'the',
            'lazy',
            "dog.\n\nThis",
            'is',
            'another',
            'sentence.',
        ];

        $this::fromIterable($words)
            ->unwords()
            ->shouldIterateAs([11 => $string]);
    }

    public function it_can_unwrap()
    {
        $this::fromIterable([['a' => 'A'], ['b' => 'B'], ['c' => 'C']])
            ->unwrap()
            ->shouldIterateAs([
                'a' => 'A',
                'b' => 'B',
                'c' => 'C',
            ]);

        $this::fromIterable(['foo' => ['a' => 'A'], 'bar' => ['b' => 'B'], 'foobar' => ['c' => 'C', 'd' => 'D']])
            ->unwrap()
            ->shouldIterateAs([
                'a' => 'A',
                'b' => 'B',
                'c' => 'C',
                'd' => 'D',
            ]);

        $inner = static fn (): Generator => yield from [2, 3];

        $this::fromIterable([1, $inner(), 4, 5])
            ->unwrap()
            ->normalize()
            ->shouldIterateAs([1, 2, 3, 4, 5]);
    }

    public function it_can_unzip(): void
    {
        $this::fromIterable(range('A', 'C'))
            ->zip(['D', 'E', 'F', 'G'], [1, 2, 3, 4, 5])
            ->unzip()
            ->shouldIterateAs([
                [
                    'A', 'B', 'C', null, null,
                ],
                [
                    'D', 'E', 'F', 'G', null,
                ],
                [
                    1, 2, 3, 4, 5,
                ],
            ]);
    }

    public function it_can_use_range(): void
    {
        $this::range(0, 5)
            ->shouldIterateAs([(float) 0, (float) 1, (float) 2, (float) 3, (float) 4]);

        $this::range(1, 10, 2)
            ->shouldIterateAs([(float) 1, (float) 3, (float) 5, (float) 7, (float) 9]);

        $this::range(-5, 5, 2)
            ->shouldIterateAs([0 => (float) -5, 1 => (float) -3, 2 => (float) -1, 3 => (float) 1, 4 => (float) 3]);

        $this::range()
            ->limit(10)
            ->shouldIterateAs([0 => (float) 0, 1 => (float) 1, 2 => (float) 2, 3 => (float) 3, 4 => (float) 4, 5 => (float) 5, 6 => (float) 6, 7 => (float) 7, 8 => (float) 8, 9 => (float) 9]);

        $this::range(0, INF, 0)
            ->limit(10)
            ->shouldIterateAs([
                (float) 0,
                (float) 0,
                (float) 0,
                (float) 0,
                (float) 0,
                (float) 0,
                (float) 0,
                (float) 0,
                (float) 0,
                (float) 0,
            ]);

        $this::range(1, 5)
            ->shouldIterateAs([
                0 => (float) 1,
                1 => (float) 2,
                2 => (float) 3,
                3 => (float) 4,
            ]);

        $this::range(1, 5, 0)
            ->limit(5)
            ->shouldIterateAs([
                0 => (float) 1,
                1 => (float) 1,
                2 => (float) 1,
                3 => (float) 1,
                4 => (float) 1,
            ]);
    }

    public function it_can_use_range_with_value_1(): void
    {
        $this::range(0, 1)
            ->shouldIterateAs([(float) 0]);

        $this::range()
            ->limit(5)
            ->shouldIterateAs([(float) 0, (float) 1, (float) 2, (float) 3, (float) 4]);
    }

    public function it_can_use_times_with_a_callback(): void
    {
        $a = [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5]];

        $this::times(2, static function () {
            return range(1, 5);
        })
            ->shouldIterateAs($a);

        $this::times(-1, 'count')
            ->shouldIterateAs([]);
    }

    public function it_can_use_times_without_a_callback(): void
    {
        $this::times(10)
            ->shouldIterateAs(range(1, 10));

        $this::times(-5)
            ->shouldIterateAs([]);

        $this::times(1)
            ->shouldIterateAs([1]);

        $this::times(0)
            ->shouldIterateAs([]);

        $this::times()
            ->shouldIterateAs([]);
    }

    public function it_can_when(): void
    {
        $this::fromIterable(range('a', 'c'))
            ->when(
                static fn () => true,
                static fn (Iterator $iterator) => new ArrayIterator(range('c', 'a'))
            )
            ->shouldIterateAs([0 => 'c', 1 => 'b', 2 => 'a']);

        $this::fromIterable(range('a', 'c'))
            ->when(
                static fn () => false,
                static fn (Iterator $iterator) => new ArrayIterator(range('c', 'a'))
            )
            ->shouldIterateAs([0 => 'a', 1 => 'b', 2 => 'c']);
    }

    public function it_can_window(): void
    {
        $this::fromIterable(['a' => 'A', 'b' => 'B', 'c' => 'C'])
            ->window(0)
            ->shouldIterateAs([
                'a' => ['A'],
                'b' => ['B'],
                'c' => ['C'],
            ]);

        $this::fromIterable(range('a', 'e'))
            ->window(2)
            ->shouldIterateAs([
                0 => [
                    0 => 'a',
                ],
                1 => [
                    0 => 'a',
                    1 => 'b',
                ],
                2 => [
                    0 => 'a',
                    1 => 'b',
                    2 => 'c',
                ],
                3 => [
                    0 => 'b',
                    1 => 'c',
                    2 => 'd',
                ],
                4 => [
                    0 => 'c',
                    1 => 'd',
                    2 => 'e',
                ],
            ]);

        $this::fromIterable(range('a', 'e'))
            ->window(-1)
            ->shouldIterateAs([
                ['a'], ['a', 'b'], ['a', 'b', 'c'], ['a', 'b', 'c', 'd'], ['a', 'b', 'c', 'd', 'e'],
            ]);

        // Unsupported - but tested.
        $this::fromIterable(range('a', 'e'))
            ->window(-2)
            ->shouldIterateAs([
                [], [], [], [], [],
            ]);
    }

    public function it_can_words(): void
    {
        $string = <<<'EOF'
            The quick brow fox jumps over the lazy dog.

            This is another sentence.
            EOF;

        $words = [
            0 => 'The',
            1 => 'quick',
            2 => 'brow',
            3 => 'fox',
            4 => 'jumps',
            5 => 'over',
            6 => 'the',
            7 => 'lazy',
            8 => 'dog.',
            10 => 'This',
            11 => 'is',
            12 => 'another',
            13 => 'sentence.',
        ];

        $this::fromString($string)
            ->words()
            ->shouldIterateAs($words);
    }

    public function it_can_wrap()
    {
        $this::fromIterable(['a' => 'A', 'b' => 'B', 'c' => 'C'])
            ->wrap()
            ->shouldIterateAs([
                ['a' => 'A'],
                ['b' => 'B'],
                ['c' => 'C'],
            ]);

        $this::fromIterable(range('a', 'e'))
            ->wrap()
            ->shouldIterateAs([[0 => 'a'], [1 => 'b'], [2 => 'c'], [3 => 'd'], [4 => 'e']]);
    }

    public function it_can_zip(): void
    {
        $output = static function () {
            yield [0, 0] => ['A', 'D'];

            yield [1, 1] => ['B', 'E'];

            yield [2, 2] => ['C', 'F'];
        };

        $this::fromIterable(range('A', 'C'))
            ->zip(range('D', 'F'))
            ->shouldIterateAs($output());

        $output = static function () {
            yield [0, 0] => ['A', 'D'];

            yield [1, 1] => ['B', 'E'];

            yield [2, 2] => ['C', 'F'];

            yield [null, 3] => [null, 'G'];
        };

        $this::fromIterable(range('A', 'C'))
            ->zip(range('D', 'G'))
            ->shouldIterateAs($output());
    }

    /**
     * @see https://github.com/loophp/collection/issues/57
     */
    public function it_fix_bug_57()
    {
        $input = array_combine(range(1, 26), range('a', 'z'));

        $collection = $this::fromIterable($input);

        $collection
            ->key()
            ->shouldReturn(1);

        $collection
            ->current()
            ->shouldReturn('a');

        $last = $collection->last();

        $last
            ->key()
            ->shouldReturn(26);

        $last
            ->current()
            ->shouldReturn('z');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Collection::class);
    }

    public function let(): void
    {
        $this->beConstructedThrough('empty');
    }
}
