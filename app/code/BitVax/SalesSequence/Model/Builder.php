<?php
/**
 * Override class of Magento\SalesSequence\Model\Builder
 * 
 * @category BitVax_SalesSequence
 * @package  BitVax
 * @author   BitVax <info@kutinko.com>
 * @license  MIT License, http://www.opensource.org/licenses/MIT
 * @link     http://url.com
 */
namespace Bitvax\SalesSequence\Model;

use Magento\SalesSequence\Model\Builder as SourceBuilder;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Ddl\Sequence as DdlSequence;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceMetadata;
use Psr\Log\LoggerInterface as Logger;
use Magento\SalesSequence\Model\MetaFactory;
use Magento\SalesSequence\Model\ProfileFactory;

class Builder extends SourceBuilder
{

    /**
     * Initialization
     * 
     * @param ResourceMetadata $resourceMetadata
     * @param MetaFactory $metaFactory
     * @param ProfileFactory $profileFactory
     * @param AppResource $appResource
     * @param DdlSequence $ddlSequence
     * @param Logger $logger
     */
    public function __construct(
        ResourceMetadata $resourceMetadata,
        MetaFactory $metaFactory,
        ProfileFactory $profileFactory,
        AppResource $appResource,
        DdlSequence $ddlSequence,
        Logger $logger
    ) {
        parent::__construct(
            $resourceMetadata,
            $metaFactory,
            $profileFactory,
            $appResource,
            $ddlSequence,
            $logger
        );
    }
    /**
     * Returns sequence table name
     *
     * @return string
     */
    protected function getSequenceName()
    {
        //Modified to return always the same table associated to store id 0 --> Admin
        return $this->appResource->getTableName(
            sprintf(
                'sequence_%s_%s',
                $this->data['entity_type'],
                0
            )
        );
    }
}