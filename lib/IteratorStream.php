<?php

namespace Amp\ByteStream;

use Amp\Iterator;
use function Amp\await;

final class IteratorStream implements InputStream
{
    /** @var Iterator<string> */
    private Iterator $iterator;

    private ?\Throwable $exception = null;

    private bool $pending = false;

    /**
     * @psam-param Iterator<string> $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /** @inheritdoc */
    public function read(): ?string
    {
        if ($this->exception) {
            throw $this->exception;
        }

        if ($this->pending) {
            throw new PendingReadError;
        }

        $this->pending = true;

        try {
            $hasNextElement = await($this->iterator->advance());

            if (!$hasNextElement) {
                return null;
            }

            $chunk = $this->iterator->getCurrent();

            if (!\is_string($chunk)) {
                throw new StreamException(\sprintf(
                    "Unexpected iterator value of type '%s', expected string",
                    \is_object($chunk) ? \get_class($chunk) : \gettype($chunk)
                ));
            }

            return $chunk;
        } catch (\Throwable $exception) {
            $this->exception = $exception instanceof StreamException
                ? $exception
                : new StreamException("Iterator threw an exception", 0, $exception);
            throw $exception;
        } finally {
            $this->pending = false;
        }
    }}
