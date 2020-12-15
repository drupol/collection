<?php

declare(strict_types=1);

namespace loophp\collection\Iterator;

use Generator;
use IteratorIterator;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T of string
 *
 * @extends ProxyIterator<TKey, T>
 */
final class StringIterator extends ProxyIterator
{
    public function __construct(string $data, string $delimiter = '')
    {
        $callback = static function (string $input, string $delimiter): Generator {
            $offset = 0;

            while (
                mb_strlen($input) > $offset
                && false !== $nextOffset = '' !== $delimiter ? mb_strpos($input, $delimiter, $offset) : 1 + $offset
            ) {
                yield (string) mb_substr($input, $offset, $nextOffset - $offset);

                $offset = $nextOffset + mb_strlen($delimiter);
            }

            if ('' !== $delimiter) {
                yield (string) mb_substr($input, $offset);
            }
        };

        $this->iterator = new IteratorIterator($callback($data, $delimiter));

        $this->rewind();
    }
}
