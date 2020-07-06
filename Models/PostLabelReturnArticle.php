<?php
namespace PostLabelCenter\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Shopware Model Log
 *
 * This is the model for the postlabelreturnarticle, which contains a single row from post_label_return_articles.
 *
 * @ORM\Entity
 * @ORM\Table(name="post_label_return_articles")
 */
class PostLabelReturnArticle extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     *
     * @var integer $orderId
     *
     * @ORM\Column(name="orderID", type="integer", nullable=false)
     */
    private $orderId;

    /**
     *
     * @var integer $detailId
     *
     * @ORM\Column(name="detailID", type="string", nullable=false)
     */
    private $detailId;

    /**
     *
     * @var string $articleId
     *
     * @ORM\Column(name="articleId", type="string", length=255, nullable=false)
     */
    private $articleId;

    /**
     *
     * @var string $articleOrderNumber
     *
     * @ORM\Column(name="articleOrderNumber", type="string", length=255, nullable=false)
     */
    private $articleOrderNumber;

    /**
     * @var \DateTime $returnTime
     *
     * @ORM\Column(name="returnTime", type="datetime", nullable=false)
     */
    private $returnTime;

    /**
     *
     * @var integer $amount
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;


    /**
     *
     * @var string $returnReason
     *
     * @ORM\Column(name="returnReason", type="string", length=255, nullable=false)
     */

    private $returnReason;

    public function __construct()
    {
        $this->returnTime = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDetailId()
    {
        return $this->detailId;
    }

    /**
     * @param mixed $detailId
     */
    public function setDetailId($detailId)
    {
        $this->detailId = $detailId;
    }

    /**
     * @return string
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @param string $articleId
     */
    public function setArticleId(string $articleId)
    {
        $this->articleId = $articleId;
    }

    /**
     * @return string
     */
    public function getArticleOrderNumber()
    {
        return $this->articleOrderNumber;
    }

    /**
     * @param string $articleOrderNumber
     */
    public function setArticleOrderNumber(string $articleOrderNumber)
    {
        $this->articleOrderNumber = $articleOrderNumber;
    }


    /**
     * @return mixed
     */
    public function getReturnTime()
    {
        return $this->returnTime;
    }


    /**
     * @param \DateTime|string $returnTime
     * @return PostLabelReturnArticle
     */

    public function setReturnTime($returnTime)
    {
        if (!$returnTime instanceof \DateTime && is_string($returnTime)) {
            $returnTime = new \DateTime($returnTime);
        }
        $this->returnTime = $returnTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getReturnReason()
    {
        return $this->returnReason;
    }

    /**
     * @param mixed $returnReason
     */
    public function setReturnReason($returnReason)
    {
        $this->returnReason = $returnReason;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }



}
