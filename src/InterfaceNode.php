<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class InterfaceNode
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

    private $bgColor;

    public function __construct(string $id, string $interface, string $named)
    {
        $this->id = $id;
        $this->interface = $interface ? str_replace('\\', '\\\\', $interface) : '{scalar}';
        $this->named = $named ? "<font color=\"#000000\" point-size=\"10\">@{$named}<br align=\"left\"/></font>" : '';
        $this->bgColor = $interface ? 'ffffff' : 'aaaaaa';
    }

    public function __toString()
    {
        return /* @lang html */
            <<< EOT
{$this->id} [style=dashed, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="0" border="0"><tr><td align="left" port="header" bgcolor="#{$this->bgColor}">{$this->named}<font color="#000000">{$this->interface}<br align="left"/></font></td></tr></table>>, shape=box]
EOT;
    }
}
