<?php

namespace PostLabelCenter\Components;
use Doctrine\DBAL\Connection;

class PostLabelReturnService
{
    protected $gf;
	protected $returnReasonList;
	protected $articleRepository;
	protected $mediaRepository;
	protected $hashSalt = 'OWYxNWJmNzBjYjdhNmYyOGFjYWY5ZjY5!!?cv§$%&/[]\}`??)';

	public function __construct(GreenfieldService $gf)
	{
	    try {
	        $this->gf = $gf;
            $this->returnReasonList =  $this->gf->getReturnReasons();
        } catch (\Exception $e)
        {
            // handle eception
        }
		$this->returnReasonList = array_diff(explode(';',  $this->returnReasonList), array(''));
		$this->setArticleRepository(Shopware()->Models()->getRepository('Shopware\Models\Article\Article'));
		$this->setMediaRepository(Shopware()->Models()->getRepository('Shopware\Models\Article\Image'));
	}

	public function getMaxReturnTimeFromOrderTime($date)
	{
		if (!$date) {
			return null;
		}
		try {
            $maxReturnTime =  intval($this->gf->getReturnTimeMax());
        } catch (\Exception $e) {
        }

		$maxReturnDate = new \DateTime($date);
		$maxReturnDate->modify("+$maxReturnTime day");
		return $maxReturnDate->getTimestamp();
	}

	public function getReturnReasonList()
	{
		return $this->returnReasonList;
	}

	public function getOrderReturnArticles($order, $onlyAvailableForReturn = true)
	{
		$allBasketArticles = Shopware()->Models()->toArray($order->getDetails());
		$orderReturnArticles = [];

		foreach ($allBasketArticles as $basketArticle) {
			if ($basketArticle['mode'] == '0') {
				$articleNumber = $basketArticle['articleOrderNumber'];
				$articleId = $basketArticle['articleId'];
				$images = Shopware()->Modules()->Articles()->sGetArticlePictures($articleId, true, null,
					$articleNumber);
				$basketArticle['image'] = $images['src'][0];

				// check already returned articles
				$alreadyReturnedAmount = $this->getReturnedQuantity($basketArticle, true);

				$basketArticle['availableReturn'] = $alreadyReturnedAmount ? ($basketArticle['quantity'] - $alreadyReturnedAmount) : $basketArticle['quantity'];
				if ($onlyAvailableForReturn) {
					if ($basketArticle['availableReturn'] > 0) {
						$orderReturnArticles[] = $basketArticle;
					}
				} else {
					$orderReturnArticles[] = $basketArticle;
				}
			}
		}

		return $orderReturnArticles;
	}

	/**
	 * @return mixed
	 */
	public function getArticleRepository()
	{
		return $this->articleRepository;
	}

	/**
	 * @param mixed $articleRepository
	 */
	public function setArticleRepository($articleRepository)
	{
		$this->articleRepository = $articleRepository;
	}

	/**
	 * @return mixed
	 */
	public function getMediaRepository()
	{
		return $this->mediaRepository;
	}

	/**
	 * @param mixed $mediaRepository
	 */
	public function setMediaRepository($mediaRepository)
	{
		$this->mediaRepository = $mediaRepository;
	}

	protected function getArticleImages($articleId)
	{
		$builder = Shopware()->Models()->createQueryBuilder();

		$builder->select(['images'])
			->from('Shopware\Models\Article\Image', 'images')
			->innerJoin('images.article', 'article')
			->where('article.id = :articleId')
			->orderBy('images.position', 'ASC')
			->andWhere('images.parentId IS NULL')
			->setParameters(['articleId' => $articleId]);

		return $this->getFullResult($builder);
	}

	public function isArticleReturnPossible($detail, $amount)
	{
		$returnedQuantity = $this->getReturnedQuantity($detail);

		if (($returnedQuantity + $amount) <= $detail->getQuantity()) {
			return true;
		}

		return false;
	}

	public function orderReturnByQuantityAllowed($details)
	{
		$detailId = $details[0]['id'];

		foreach ($details as $detail) {
			$articleNumbers[] = $detail['articleordernumber'];
		}

		$connection = Shopware()->Container()->get('dbal_connection');
		$queryBuilder = $connection->createQueryBuilder();

		$queryBuilder->select([
			'retArticles.articleOrderNumber as articleordernumber',
			'SUM(retArticles.amount) as returnquantitysum'
		])
			->from('post_label_return_articles', 'retArticles')
			->where('retArticles.detailID = :detailID')
			->andWhere('retArticles.articleOrderNumber IN (:articleNumbers)')
			->groupBy('retArticles.articleOrderNumber')
			->setParameter(':detailID', $detailId)
			->setParameter(':articleNumbers', $articleNumbers, Connection::PARAM_STR_ARRAY);

		$statement = $queryBuilder->execute();
		$dbArticles = $statement->fetchAll(\PDO::FETCH_ASSOC);

		// ESD articles are not valid!!!!
		foreach ($details as $idx => $detail) {

			if ($detail['esdarticle'] != '1' && $detail['modus'] == '0' && $dbArticles) {
				foreach ($dbArticles as $dbArticle) {
					if ($detail['articleordernumber'] == $dbArticle['articleordernumber']) {
						if ($detail['quantity'] - $dbArticle['returnquantitysum'] > 0) {
							return true;
						}
					}
				}
			} else if ($detail['esdarticle'] != '1' && $detail['modus'] == '0' && !$dbArticles) {
				return true;
			}
		}

		return false;
	}

	public function getReturnedQuantity($detail, $isArray = false)
	{
		if ($isArray) {
			$detailSqlArray = [
				'detailId' => $detail['id'],
				'articleId' => $detail['articleId'],
				'articleNumber' => $detail['articleNumber']
			];
		} else {
			$detailSqlArray = [
				'detailId' => $detail->getId(),
				'articleId' => $detail->getArticleId(),
				'articleNumber' => $detail->getArticleNumber()
			];
		}

		$sql = "SELECT SUM(amount) AS returnsum FROM post_label_return_articles WHERE detailID = :detailId AND articleId = :articleId AND articleOrderNumber = :articleNumber";
		$returnSum = Shopware()->Container()->get('dbal_connection')->executeQuery(
			$sql,
			$detailSqlArray
		);

		$result = $returnSum->fetch();
		$returnAmount = $result['returnsum'] ? $result['returnsum'] : 0;

		return $returnAmount;
	}

	public function getGeneratedHash($value)
	{
		if (!$value) {
			return false;
		}

		return $this->getEncryptedParam($value);
	}

	public function getEncryptedParam($value)
	{
		return hash('sha256', $value . $this->hashSalt);
	}

	public function getOrderIdByNumber($number)
	{
		if (!$number) {
			return false;
		}
		$sql = "SELECT id FROM s_order WHERE ordernumber = :orderNumber";
		$query = Shopware()->Container()->get('dbal_connection')->executeQuery(
			$sql,
			['orderNumber' => $number]
		);

		$result = $query->fetch();
		$orderId = $result['id'] ? $result['id'] : false;

		return $orderId;
	}

	public function updateOrderHashIdAttribute($orderId, $orderIdHash)
	{
		if ($orderId) {
			$sql = "UPDATE s_order_attributes SET orderidhash = :orderIdHash WHERE orderID = :orderID LIMIT 1";
			Shopware()->Container()->get('dbal_connection')->executeQuery(
				$sql,
				[
					'orderIdHash' => $orderIdHash,
					'orderID' => $orderId
				]
			);
		}
	}

	public function getOrderIdFromHash($orderIdHash)
	{
		if (!$orderIdHash) {
			return false;
		}

		$sql = "SELECT orderID FROM s_order_attributes WHERE orderidhash = :orderIdHash";
		$query = Shopware()->Container()->get('dbal_connection')->executeQuery(
			$sql,
			[
				'orderIdHash' => $orderIdHash
			]
		);

		$result = $query->fetch();
		$orderId = $result['orderID'] ? $result['orderID'] : false;

		return $orderId;
	}
}
