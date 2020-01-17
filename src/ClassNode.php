<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class ClassNode implements NodeInterface
{
    /**
     * @var array<string>
     */
    public static $ids = [];

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
    private $class;

    /**
     * @var string
     */
    private $settersTable = '';

    /**
     * @var bool
     */
    private $invalid = false;

    /**
     * @param string        $id      ID
     * @param string        $class   class
     * @param array<string> $setters
     *
     * @throws \ReflectionException
     */
    public function __construct(string $id, string $class, array $setters)
    {
        if (in_array($id, self::$ids, true)) {
            $this->invalid = true;

            return;
        }
        self::$ids[] = $id;
        $this->id = $id;
        assert(class_exists($class));
        $ref = new \ReflectionClass($class);
        $this->namespace = str_replace('\\', '\\\\', $ref->getNamespaceName());
        $this->class = $ref->getShortName();
        foreach ($setters as $setterMethod => $port) {
            $this->settersTable .= "<tr><td align=\"left\" port=\"{$port}\">&lt;{$setterMethod}&gt;</td></tr>";
        }
    }

    public function __toString()
    {
        $html = $this->invalid ? '' : /* @lang html */  <<< EOT
{$this->id} [style=solid, margin=0.02, label=
<<table cellspacing="0" cellpadding="5" cellborder="1" border="0">
    <tr>
        <td align="left" port="header" bgcolor="#000000"><font color="grey" point-size="12">{$this->namespace}<br align="left"/></font><font color="#ffffff">{$this->class}<br align="left"/></font></td>
    </tr>
{$this->settersTable}
</table>>, shape=box]
EOT;

        return str_replace(PHP_EOL, '', $html);
    }
}
