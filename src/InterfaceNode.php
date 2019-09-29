<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class InterfaceNode implements NodeInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $interface;

    /**
     * @var string
     */
    private $named;

    /**
     * @var string
     */
    private $bgColor;

    public function __construct(string $id, string $interface, string $named)
    {
        $this->id = $id;
        $interface ? $this->setNamespace($interface) : $this->setInstance();
        $this->named = $named ? "<font color=\"#000000\" point-size=\"10\">@{$named}<br align=\"left\"/></font>" : '';
        $this->bgColor = $interface ? 'ffffff' : 'aaaaaa';
    }

    public function __toString()
    {
        return /* @lang html */
            <<< EOT
{$this->id} [style=dashed, margin=0.02, label=
<<table cellspacing="0" cellpadding="5" cellborder="0" border="0">
<tr>
<td align="left" port="header" bgcolor="#{$this->bgColor}">{$this->named}
<font point-size="11" color="#333333">{$this->namespace}<br align="left"/></font>
<font color="#000000">{$this->interface}<br align="left"/></font>
</td>
</tr>
</table>>, shape=box]
EOT;
    }

    public function setInstance() : void
    {
        $this->interface = 'instance';
    }

    private function setNamespace(string $interface) : void
    {
        $ref = new \ReflectionClass($interface);
        $this->namespace = str_replace('\\', '\\\\', $ref->getNamespaceName());
        $this->interface = $ref->getShortName();
    }
}
