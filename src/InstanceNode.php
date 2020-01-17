<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use function htmlspecialchars;
use Ray\Di\Instance;

final class InstanceNode implements NodeInterface
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
    private $type;

    /**
     * @var string
     */
    private $named;

    public function __construct(string $id, string $interface, string $named, Instance $instance)
    {
        $this->id = $id;
        $this->interface = addslashes($interface);
        $this->named = $named ? /* @lang html */ "@<font color=\"#000000\" point-size=\"10\">{$named}<br align=\"left\"/></font>" : '';
        $this->type = getType($instance->value);
    }

    public function __toString()
    {
        $html = /* @lang html */ <<< EOT
{$this->id} [style=dashed, margin=0.02, label=
<<table cellspacing="0" cellpadding="5" cellborder="0" border="0">
<tr>
<td align="left" port="header" bgcolor="#aaaaaa">{$this->named}
<font point-size="11" color="#333333">{$this->interface}<br align="left"/></font>
<font color="#000000">instance ($this->type)<br align="left"/></font>
</td>
</tr>
</table>>, shape=box]
EOT;

        return str_replace(PHP_EOL, '', $html);
    }
}
