<?php
declare(strict_types=1);

namespace Pac\ProophPackage\Projection;

use Exception;
use Prooph\EventStore\Projection\ReadModel;

abstract class AbstractReadModelCollection implements ReadModel
{
    /** @var ReadModel[] */
    protected $readModels;

    public function __construct()
    {
        $this->readModels = func_get_args();
    }

    public function init(): void
    {
        foreach ($this->readModels as $readModel) {
            if (! $readModel->isInitialized()) {
                $response = $readModel->init();
            }
        }
    }

    public function isInitialized(): bool
    {
        foreach ($this->readModels as $readModel) {
            if (! $readModel->isInitialized()) {
                return false;
            }
        }

        return true;
    }

    public function reset(): void
    {
        foreach ($this->readModels as $readModel) {
            $readModel->reset();
        }
    }

    public function delete(): void
    {
        foreach ($this->readModels as $readModel) {
            $readModel->delete();
        }
    }

    public function stack(string $operation, ...$args): void
    {
        throw new Exception('stack cannot be called on collection read model');
    }

    public function persist(): void
    {
        foreach ($this->readModels as $readModel) {
            $readModel->persist();
        }
    }
}
