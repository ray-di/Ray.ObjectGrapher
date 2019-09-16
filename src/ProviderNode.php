<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

final class ProviderNode
{
    /**
     * @var string
     */
    private $provider;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
    }

    public function __toString()
    {
        return /** @lang html */
            <<< EOT;
i_18fba087 [style=solid, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="1" border="0"><tr><td align="left" port="header" bgcolor="#aaaaaa"><font color="#ffffff"><br align="left"/></font></td></tr><tr><td align="left" port="m_65f6d5d3">&lt;construct&gt;</td></tr></table>>, shape=box
EOT;
    }
}