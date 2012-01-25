<?php

namespace Aizatto\Bundle\HermesBundle\Graph;

use AbstractDirectedGraph;

phutil_require_module('phutil', 'utils/abstractgraph');

final class ResourceGraph extends AbstractDirectedGraph {

  protected $resourceGraph;

  public function loadEdges(array $nodes) {
    $edges = array();
    foreach ($nodes as $node) {
      $edges[$node] = idx($this->resourceGraph, $node, array());
    }
    return $edges;
  }

  final public function setResourceGraph(array $graph) {
    $this->resourceGraph = $graph;
  }

}
