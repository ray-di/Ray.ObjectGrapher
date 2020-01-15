<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class ToClass implements ArrowInterface
{
    /**
     * @var array<bool>
     */
    public static $index;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    private $interface;

    /**
     * @var bool
     */
    private $noArrow;

    public function __construct(string $interfaceId, string $classId)
    {
        $this->class = $classId;
        $this->interface = $interfaceId;
        $indexId = $interfaceId . $classId;
        $this->noArrow = isset(self::$index[$indexId]);
        self::$index[$indexId] = true;
    }

    public function __toString()
    {
        return $this->noArrow ? '' : <<<EOT
{$this->interface} -> {$this->class} [style=dashed, arrowtail=none, arrowhead=onormal]
EOT;
    }
}
