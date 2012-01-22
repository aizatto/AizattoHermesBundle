<?php

namespace Aizatto\Bundle\HermesBundle\Graph;

use AbstractDirectedGraph;

phutil_require_module('phutil', 'utils/abstractgraph');

final class ResourceGraph extends AbstractDirectedGraph {

  public function loadEdges(array $nodes) {
    $edges = array();
    foreach ($nodes as $node) {
      $edges[$node] = idx($this->knownNodes, $node, array());
    }
    return $edges;
  }

}
