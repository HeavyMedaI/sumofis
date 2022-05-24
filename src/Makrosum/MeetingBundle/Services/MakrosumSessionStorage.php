<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 7.03.2016
 * Time: 16:58
 */

namespace Makrosum\MeetingBundle\Services;


use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class MakrosumSessionStorage extends NativeSessionStorage
{
    public function __construct(Array $options = [], $handler = null, MetadataBag $metaBag = null, RequestStack $requestStack)
    {
        $host = @$requestStack->getCurrentRequest()->getHost();
        $options["cookie_domain"] = substr($host, strpos($host, ".")+1);
        parent::__construct($options, $handler, $metaBag);
    }
}