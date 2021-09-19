<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection;

use loophp\collection\Collection;
use loophp\collection\Contract\Collection as ContractCollection;

use function is_array;

/**
 * @template TKey
 * @template T
 *
 * @mixin Collection<TKey, T>
 *
 * phpcs:disable PSR12.Classes.AnonClassDeclaration.SpaceAfterKeyword
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class HigherOrderCollectionProxy
{
    /**
     * @var ContractCollection<TKey, T>
     */
    private ContractCollection $collection;

    private string $method;

    /**
     * @param ContractCollection<TKey, T> $collection
     */
    public function __construct(ContractCollection $collection, string $method)
    {
        $this->collection = $collection;
        $this->method = $method;
    }

    public function __call(string $method, array $parameters = [])
    {
        switch ($this->method) {
            case 'when':
                return new class($this->collection, $method, $parameters) {
                    private ContractCollection $collection;

                    private string $method;

                    private array $parameters;

                    public function __construct(ContractCollection $collection, string $method, array $parameters = [])
                    {
                        $this->collection = $collection;
                        $this->method = $method;
                        $this->parameters = $parameters;
                    }

                    public function __call(string $name, array $arguments = []): ContractCollection
                    {
                        return $this->collection->{$this->method}(...$this->parameters)->current()
                                ? $this->collection->{$name}(...$arguments)
                                : $this->collection;
                    }
                };

                break;
        }

        return $this
            ->collection
            ->{$this->method}(
                static fn (iterable $collection): ContractCollection => Collection::fromIterable($collection)->{$method}(...$parameters)
            );
    }

    public function __get(string $key): ContractCollection
    {
        return $this
            ->collection
            ->{$this->method}(
                static fn ($value) => is_array($value) ? $value[$key] : $value->{$key}
            );
    }
}
