<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class ClassNode
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $class;

    /**
     * @var array
     */
    private $settersTable = '';

    public function __construct(string $id, string $class, array $setters)
    {
        $this->id = $id;
        $this->class = str_replace('\\', '\\\\', $class);
        foreach ($setters as $setterMethod => $port) {
            $this->settersTable .= "<tr><td align=\"left\" port=\"{$port}\">&lt;{$setterMethod}&gt;</td></tr>";
        }
    }

    public function __toString()
    {
        return /* @lang html */
            <<< EOT
{$this->id} [style=solid, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="1" border="0"><tr><td align="left" port="header" bgcolor="#000000"><font color="#ffffff">{$this->class}<br align="left"/></font></td></tr>
{$this->settersTable}
</table>>, shape=box]
EOT;
    }
}
