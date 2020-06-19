<?php

namespace Netbaseteam\Onlinedesign\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface OnlinedesignLoaderInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Netbaseteam\Onlinedesign\Model\Onlinedesign
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
