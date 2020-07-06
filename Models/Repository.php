<?php
namespace PostLabelCenter\Models;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Shopware\Components\Model\DBAL\Result;
use Shopware\Models\Shop\Shop;

/**
 * Class Repository
 * @category  Shopware
 * @package PostLabelCenter\Models
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Repository
{
	/**
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @var \Enlight_Event_EventManager
	 */
	protected $eventManager;

	/**
	 * Class constructor which allows to inject all dependencies of this class.
	 *
	 * @param Connection $connection
	 * @param \Enlight_Event_EventManager $eventManager
	 */
	public function __construct(Connection $connection, \Enlight_Event_EventManager $eventManager)
	{
		$this->connection = $connection;
		$this->eventManager = $eventManager;
	}

	public function getReturnedArticles($offset, $limit, \DateTime $from = null, \DateTime $to = null)
	{
		$builder = $this->createReturnedArticlesBuilder($from, $to);

		$this->addPagination($builder, $offset, $limit);

		$builder = $this->eventManager->filter('Post_Label_Order_Return_ReturnedArticles', $builder, array(
			'subject' => $this
		));

		return new Result($builder);
	}

	/**
	 * Returns a query which selects the sell count of each product.
	 * @param \DateTime $from
	 * @param \DateTime $to
	 * @return DBALQueryBuilder
	 */
	protected function createReturnedArticlesBuilder(\DateTime $from = null, \DateTime $to = null)
	{
		$builder = $builder = $this->connection->createQueryBuilder();
		$builder->select(array(
			'orderdetails.ordernumber AS orderNumber',
			'postlabelreturnarticles.articleOrderNumber',
			'orderdetails.name AS articleName',
			'postlabelreturnarticles.returnReason',
			'postlabelreturnarticles.amount',
			'postlabelreturnarticles.returnTime'
		))
			->from('post_label_return_articles', 'postlabelreturnarticles')
			->innerJoin('postlabelreturnarticles', 's_order_details', 'orderdetails', 'orderdetails.id = postlabelreturnarticles.detailID')
			->orderBy('postlabelreturnarticles.returnTime', 'DESC');

		$this->addDateRangeCondition($builder, $from, $to, 'postlabelreturnarticles.returnTime');

		return $builder;
	}
/*
	public function innerJoin($fromAlias, $join, $alias, $condition = null)
	{
		return $this->add('join', array(
			$fromAlias => array(
				'joinType'      => 'inner',
				'joinTable'     => $join,
				'joinAlias'     => $alias,
				'joinCondition' => $condition
			)
		), true);
	}
*/
//	protected function createReturnedArticlesBuilder(\DateTime $from = null, \DateTime $to = null)
//	{
//		$builder = $builder = $this->connection->createQueryBuilder();
//		$builder->select(array(
//			'SUM(details.quantity) AS sales',
//			'articles.name',
//			'details.articleordernumber as ordernumber'
//		))
//			->from('s_order_details', 'details')
//			->innerJoin('details', 's_articles', 'articles', 'articles.id = details.articleID')
//			->innerJoin('details', 's_order', 'orders', 'orders.id = details.orderID')
//			->andWhere('orders.status NOT IN (-1, 4)')
//			->groupBy('articles.id')
//			->orderBy('sales', 'DESC');
//
//		$this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');
//
//		return $builder;
//	}



	private function addDateRangeCondition(DBALQueryBuilder $builder, \DateTime $from = null, \DateTime $to = null, $column = null)
	{
		if ($from instanceof \DateTime) {
			$builder->andWhere($column . ' >= :fromDate')
				->setParameter('fromDate', $from->format("Y-m-d H:i:s"));
		}
		if ($to instanceof \DateTime) {
			$builder->andWhere($column . ' <= :toDate')
				->setParameter('toDate', $to->format("Y-m-d H:i:s"));
		}

		return $this;
	}

	/**
	 * Helper function which iterates all sort arrays and at them as order by condition.
	 * @param DBALQueryBuilder $builder
	 * @param $sort
	 * @return $this
	 */
	private function addSort(DBALQueryBuilder $builder, $sort)
	{
		if (empty($sort)) {
			return $this;
		}

		foreach ($sort as $condition) {
			$builder->addOrderBy(
				$condition['property'],
				$condition['direction']
			);
		}
		return $this;
	}

	/**
	 * Small helper function which adds the first and max result to the query builder.
	 *
	 * @param DBALQueryBuilder $builder
	 * @param $offset
	 * @param $limit
	 * @return $this
	 */
	private function addPagination(DBALQueryBuilder $builder, $offset, $limit)
	{
		$builder->setFirstResult($offset)
			->setMaxResults($limit);

		return $this;
	}
}
