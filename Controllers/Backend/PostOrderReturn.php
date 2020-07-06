<?php

use Shopware\Components\CSRFWhitelistAware;
use PostLabelCenter\Models\PostLabelReturnArticle;
use PostLabelCenter\Models\Repository;
use Doctrine\DBAL\Connection;
use Shopware\Components\Model\QueryBuilder;

class Shopware_Controllers_Backend_PostOrderReturn extends Shopware_Controllers_Backend_Analytics implements CSRFWhitelistAware
{
	protected $model = PostLabelReturnArticle::class;
	protected $alias = 'order_return';
	protected $listQuery = null;
	protected $repository = null;

	public function getReturnedArticlesAction()
	{
		$result = $this->getRepository()->getReturnedArticles(
			$this->Request()->getParam('start', 0),
			$this->Request()->getParam('limit', null),
			$this->getFromDate(),
			$this->getToDate()
		);

		$this->send($result->getData(), $result->getTotalCount());
	}

	public function getRepository()
	{
		if (!$this->repository) {
			$this->repository = new Repository(
				$this->get('models')->getConnection(),
				$this->get('events')
			);
		}
		return $this->repository;
	}

	private function getFromDate()
	{
		$fromDate = $this->Request()->getParam('fromDate');
		if (empty($fromDate)) {
			$fromDate = new \DateTime();
			$fromDate = $fromDate->sub(new DateInterval('P1M'));
		} else {
			$fromDate = new \DateTime($fromDate);
		}

		return $fromDate;
	}

	private function getToDate()
	{
		//if a to date passed, format it over the \DateTime object. Otherwise create a new date with today
		$toDate = $this->Request()->getParam('toDate');
		if (empty($toDate)) {
			$toDate = new \DateTime();
		} else {
			$toDate = new \DateTime($toDate);
		}
		//to get the right value cause 2012-02-02 is smaller than 2012-02-02 15:33:12
		$toDate = $toDate->add(new DateInterval('P1D'));
		$toDate = $toDate->sub(new DateInterval('PT1S'));

		return $toDate;
	}

	public function getListQuery()
	{
		$query = parent::getListQuery();
		$query->getQuery();

		return $query;
	}

	public function getWhitelistedCSRFActions()
	{
		return [
			'index',
			'getReturnedArticles',
			'getReturnedArticlesAction'
		];
	}
}
