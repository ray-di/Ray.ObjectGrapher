# Ray.ObjectGrapher

_Using ObjectGrapher to visualize Ray.Di applications_

![fake](https://user-images.githubusercontent.com/529021/72650686-866ec100-39c4-11ea-8b49-2d86d991dc6d.png)

## Grapher
When you've written a sophisticated application, [Ray.Di](https://github.com/ray-di/Ray.Di)'s rich introspection API can describe the object graph in detail. This grapher exposes this data as an easily understandable visualization. It can show the bindings and dependencies from several classes in a complex application in a unified diagram.

## Installation
You can install the ObjectGrapher with composer:

```
composer --dev require ray/object-visual-grapher
```

### Generating a .dot file
Ray.Di's grapher leans heavily on [GraphViz](http://www.graphviz.org/), an open source graph visualization package. It cleanly separates graph specification from visualization and layout. To produce a graph `.dot` file for an `Injector`, you can use the following code:

```php
use Ray\ObjectGrapher\ObjectGrapher;

$dot = (new ObjectGrapher)(new FooModule);
file_put_contents('path/to/file', $dot);
```

### The .dot file
Executing the code above produces a `.dot` file that specifies a graph. Each entry in the file represents either a node or an edge in the graph. Here's a sample `.dot` file:

```dot
digraph injector {
graph [rankdir=TB];
dependency_BEAR_Resource_ResourceInterface_ [style=dashed, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="0" border="0"><tr><td align="left" port="header" bgcolor="#ffffff"><font color="#000000">BEAR\\Resource\\ResourceInterface<br align="left"/></font></td></tr></table>>, shape=box]
dependency_BEAR_Resource_FactoryInterface_ [style=dashed, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="0" border="0"><tr><td align="left" port="header" bgcolor="#ffffff"><font color="#000000">BEAR\\Resource\\FactoryInterface<br align="left"/></font></td></tr></table>>, shape=box]
dependency_BEAR_Resource_ResourceInterface_ -> class_BEAR_Resource_Resource [style=dashed, arrowtail=none, arrowhead=onormal]
dependency_BEAR_Resource_FactoryInterface_ -> class_BEAR_Resource_Factory [style=dashed, arrowtail=none, arrowhead=onormal]
```

### Rendering the .dot file
Download a [Graphviz viewer](http://www.graphviz.org/) for your platform, and use it to render the `.dot` file.

On Linux, you can use the command-line `dot` tool to convert `.dot` files into images.
```shell
  dot -T png my_injector.dot > my_injector.png
```

#### Graph display

Edges:
   * **Solid edges** represent dependencies from implementations to the types they depend on.
   * **Dashed edges** represent bindings from types to their implementations.
   * **Double arrows** indicate that the binding or dependency is to a `Provider`.

Nodes:
   * Implementation types are given *black backgrounds*.
   * Implementation instances have *gray backgrounds*.

---
*This document is mostly taken from https://github.com/google/guice/wiki/Grapher.*
