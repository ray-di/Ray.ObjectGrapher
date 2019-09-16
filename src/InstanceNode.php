<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

final class InterfaceNode
{
    /**
     * @var string
     */
    private $interface;

    /**
     * @var string
     */
    private $named;

    public function __construct(string $interface, string $named)
    {
        $this->interface = $interface;
        $this->named = $named;
    }

    public function __toString()
    {
        return /** @lang html */
            <<< EOT;
[style=dashed, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="0" border="0"><tr><td align="left" port="header" bgcolor="#ffffff"><font color="#000000" point-size="10">{$this->name}<br align="left"/></font><font color="#000000">{$this->interface}<br align="left"/></font></td></tr></table>>, shape=box]
EOT;
    }
}