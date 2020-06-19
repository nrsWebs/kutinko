<?php

namespace Netbaseteam\Onlinedesign\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class OnlinedesignLoader implements OnlinedesignLoaderInterface
{
    /**
     * @var \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory
     */
    protected $onlinedesignFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory $onlinedesignFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Netbaseteam\Onlinedesign\Model\OnlinedesignFactory $onlinedesignFactory,
        Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->onlinedesignFactory = $onlinedesignFactory;
        $this->registry = $registry;
        $this->url = $url;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    public function load(RequestInterface $request, ResponseInterface $response)
    {
        $id = (int)$request->getParam('id');
        if (!$id) {
            $request->initForward();
            $request->setActionName('noroute');
            $request->setDispatched(false);
            return false;
        }

        $onlinedesign = $this->onlinedesignFactory->create()->load($id);
        $this->registry->register('current_onlinedesign', $onlinedesign);
        return true;
    }
}
