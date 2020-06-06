<?php
/**
 * Override class of Magento\SalesSequence\Observer\SequenceCreatorObserver
 * 
 * @category BitVax_SalesSequence
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace BitVax\SalesSequence\Observer;

use Magento\SalesSequence\Observer\SequenceCreatorObserver as SourceSequenceCreatorObserver;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\EntityPool;
use Magento\SalesSequence\Model\Config;

class SequenceCreatorObserver extends SourceSequenceCreatorObserver
{

    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var EntityPool
     */
    private $entityPool;

    /**
     * @var Config
     */
    private $sequenceConfig;

    /**
     * Initialization
     *
     * @param Builder $sequenceBuilder
     * @param EntityPool $entityPool
     * @param Config $sequenceConfig
     */
    public function __construct(
        Builder $sequenceBuilder,
        EntityPool $entityPool,
        Config $sequenceConfig
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->entityPool = $entityPool;
        $this->sequenceConfig = $sequenceConfig;
    }

    /**
     * Observer triggered during adding new store
     *
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute(EventObserver $observer)
    {
        //Modified to always set NULL in prefix
        $storeId = $observer->getData('store')->getId();
        foreach ($this->entityPool->getEntities() as $entityType) {
            $this->sequenceBuilder->setPrefix(null)
                ->setSuffix($this->sequenceConfig->get('suffix'))
                ->setStartValue($this->sequenceConfig->get('startValue'))
                ->setStoreId($storeId)
                ->setStep($this->sequenceConfig->get('step'))
                ->setWarningValue($this->sequenceConfig->get('warningValue'))
                ->setMaxValue($this->sequenceConfig->get('maxValue'))
                ->setEntityType($entityType)
                ->create();
        }
        return $this;
    }
}