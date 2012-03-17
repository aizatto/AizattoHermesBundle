<?php

namespace Aizatto\Bundle\HermesBundle;

class AssetManagerContainer {

  protected
    $root_dir,
    $assets,
    $packages,
    $symbols,
    $rendered;

  public function __construct($root_dir, $assets, $packages) {
    $this->root_dir = $root_dir;
    $this->assets = $assets;
    $this->symbols = array();
    $this->rendered = array();
  }

  public function get($asset) {
    if (!isset($this->assets[$asset])) {
      return null;
    }

    return $this->root_dir.$this->assets[$asset]['path'];
  }

  public function requireAsset($asset) {
    if (!isset($this->assets[$asset])) {
      throw new \Exception(sprintf('Unexpected asset: %s', $asset));
    }

    if (isset($this->symbols[$asset])) {
      return true;
    }

    $this->symbols[$asset] = true;
    return true;
  }

  public function flush() {
    $map = $this->resolveResources(array_keys($this->symbols));
    return $map;
  }

  public function resolveResources(array $resources) {
    $map = array();
    foreach ($resources as $resource) {
      if (!empty($map[$resource])) {
        continue;
      }
      $this->resolveResource($map, $resource);
    }
    return $map;
  }

  public function resolveResource(array &$map, $resource) {
    if (!isset($this->assets[$resource])) {
      throw new \Exception(sprintf('Unexpected asset: %s', $resource));
    }

    if (isset($this->rendered[$resource]) &&
        $this->rendered[$resource]) {
      return;
    }

    $this->rendered[$resource] = true;
    
    $data = $this->assets[$resource];
    foreach ($data['requires'] as $require) {
      if (!empty($map[$require])) {
        continue;
      }
      $this->resolveResource($map, $require);
    }
    $map[$resource] = $data;
  }

}
