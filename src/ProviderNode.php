<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class ProviderNode implements NodeInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $provider;

    /**
     * @var string
     */
    private $settersTable = '';

    /**
     * @param string        $id       ID
     * @param string        $provider Provider
     * @param array<string> $setters
     */
    public function __construct(string $id, string $provider, array $setters)
    {
        $this->id = $id;
        $this->provider = str_replace('\\', '\\\\', $provider);
        foreach ($setters as $setterMethod => $port) {
            $this->settersTable .= "<tr><td align=\"left\" port=\"{$port}\">&lt;{$setterMethod}&gt;</td></tr>";
        }
    }

    public function __toString()
    {
        $html = /* @lang html */ <<< EOT
{$this->id} [style=solid, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="1" border="0"><tr><td align="left" port="header" bgcolor="#aaaaaa"><font color="#ffffff">{$this->provider}<br align="left"/></font></td></tr>
{$this->settersTable}
</table>>, shape=box]
EOT;

        return str_replace(PHP_EOL, '', $html);
    }
}
