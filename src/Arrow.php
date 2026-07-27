<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use Ray\Aop\ReflectionClass;
use Ray\Di\ProviderInterface;

use function class_exists;

final class Arrow implements ArrowInterface
{
    /** @var array<bool> */
    public static $history;

    /** @var string */
    public $class;

    /** @var string */
    private $interface;

    /** @var string */
    private $arrowHead;

    /** @var bool */
    private $isInvalid;

    public function __construct(string $fromId, string $toId, string $toClass)
    {
        $this->class = $toId;
        $this->interface = $fromId;
        $isProvider = class_exists($toClass) && (new ReflectionClass($toClass))->implementsInterface(ProviderInterface::class);
        $this->arrowHead = $isProvider ? 'arrowhead=onormalonormal' : 'arrowhead=onormal';
        $index = $fromId . $toId . $toClass;
        // log duplicated (mostly @depracated) bindings (
        // error_log(sprintf('from:%s to:%s class:%s index:%s ', $fromId, $toClass, $toClass, $index));

        $this->isInvalid = isset(self::$history[$index]);
        self::$history[$index] = true;
    }

    public function __toString(): string
    {
        return $this->isInvalid ? '' : <<<EOT
{$this->interface} -> {$this->class} [style=solid, arrowtail=none, {$this->arrowHead}]
EOT;
    }
}
