<?php

namespace Aizatto\Bundle\HermesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
  
  /**
   * TODO cache headers
   * TODO mimetype
   */
  public function showAction($id)
  {
    $file = $this->get('hermes')->get($id);
    if (!$file) {
      throw $this->createNotFoundException(sprintf(
        'Unknown resource: %s', $id));
    }

    $type = 'js';
    if (preg_match('/\.css$/', $file)) {
      $type = 'css';
    }

    switch ($type) {
      case 'js':
        $type = 'application/x-javascript; charset=utf-8';
        break;

      case 'css':
        $type = 'text/css';
        break;
    }

    $content = file_get_contents($file);
    $response = new Response($content);
    $response->setPublic();
    $response->headers->set('Content-Type', $type);
    return $response;
  }

}
