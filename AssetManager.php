<?php

namespace Aizatto\Bundle\HermesBundle;

class AssetManager {

  private 
    $root_dir,
    $assets,
    $javascripts = array(),
    $stylesheets = array();

  public function __construct($root_dir, $assets) {
    $this->root_dir = $root_dir.'/../';
    $this->assets = $assets;
  }

  public function get($asset) {
    if (!isset($this->assets[$asset])) {
      return null;
    }

    return $this->root_dir.$this->assets[$asset]['path'];
  }

  public function requireJS($asset) {
    if (!isset($this->assets[$asset])) {
      throw new \Exception(sprintf('Unexpected asset: %s', $asset));
    }

    if (isset($this->javascripts[$asset])) {
      return true;
    }

    $this->javascripts[$asset] = true;
  }

  public function requireCSS($asset) {
    $asset = $asset.'-css';
    if (!isset($this->assets[$asset])) {
      throw new \Exception(sprintf('Unexpected asset: %s', $asset));
    }

    if (isset($this->stylesheets[$asset])) {
      return true;
    }

    $this->stylesheets[$asset] = true;
  }

  public function flushJS() {
    $map = $this->resolveResources(array_keys($this->javascripts));
    return array_keys($map);
  }

  public function flushCSS() {
    $map = $this->resolveResources(array_keys($this->stylesheets));
    return array_keys($map);
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
