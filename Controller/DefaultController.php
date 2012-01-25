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
  public function showAction($id, $type)
  {
    switch ($type) {
      case 'js':
        $type = 'application/x-javascript; charset=utf-8';
        $file = $this->get('hermes')->scripts->get($id);
        break;

      case 'css':
        $type = 'text/css';
        $file = $this->get('hermes')->stylesheets->get($id);
        break;
    }

    if (!$file) {
      throw $this->createNotFoundException(sprintf(
        'Unknown resource: %s', $id));
    }

    $expires = 60*60*24*365;
    $expires = 60*60;

    $response = new Response();
    $response->setPublic();
    $response->setMaxAge($expires);
    $response->setExpires(id(new \DateTime())->setTimestamp(time() + $expires));
    $response->headers->set('Content-Type', $type);

    $etag = md5(filemtime($file));
    $response->setEtag($etag);

    if ($response->isNotModified($this->getRequest())) {
      return $response;
    }

    $content = file_get_contents($file);
    $response->setContent($content);

    return $response;
  }

}
