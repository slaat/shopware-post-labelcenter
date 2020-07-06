<?php
namespace PostLabelCenter\Models;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

class ShippingRepository extends ModelRepository
{
    public function getSavedConfigurations()
    {
        $configurationsBuilder = $this->getConfigurationsBuilder();

        if (!$configurationsBuilder) {
            //todo - send initial request to webservice
            die('no builder');
        }
        return $configurationsBuilder->getQuery();
    }

    public function getConfigurationsBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('*')
            ->from($this->getEntityName(), 'post_config_set');
////            ->where('config_set.s_premium_dispatch_id = :s_premium_dispatch_id')
////            ->setParameter('s_premium_dispatch_id', $premiumDispatchId);
        return $builder;
    }
    /**
     * @param bool $premiumDispatchId
     */
    public function getDistinctContractNumbers($premiumDispatchId = false)
    {
        //todo : sql
        $contractNumbers = $this->getDistinctContractNumbersBuilder($premiumDispatchId);
    }

    /**
     * @param $premiumDispatchId
     * @return array
     */
    public function getDistinctContractNumbersBuilder($premiumDispatchId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select('DISTINCT contract_number')
            ->from($this->getEntityName(), 'config_set')
            ->where('config_set.s_premium_dispatch_id = :s_premium_dispatch_id')
            ->setParameter('s_premium_dispatch_id', $premiumDispatchId);

        return null;
    }
}
