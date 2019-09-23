<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use function htmlspecialchars;

final class InstanceNode
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $interface;

    /**
     * @var string
     */
    private $named;

    public function __construct(string $id, string $interface, string $named)
    {
        $this->id = $id;
        $this->interface = htmlspecialchars(str_replace('\\', '\\\\', $interface));
        $this->named = $named ? "<font color=\"#000000\" point-size=\"10\">{$named}<br align=\"left\"/></font>" : '';
    }

    public function __toString()
    {
        return /* @lang html */
            <<< EOT
{$this->id} [style=dashed, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="0" border="0"><tr><td align="left" port="header" bgcolor="#ffffff">{$this->named}<font color="#000000">*instance<br align="left"/></font></td></tr></table>>, shape=box]
EOT;
    }
}
