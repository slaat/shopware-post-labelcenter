<?php
namespace PostLabelCenter\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="ShippingRepository")
 * @ORM\Table(name="post_shipping_sets")
 */

class ShippingSet extends ModelEntity
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="contract_number", type="integer", nullable=true)
     */
    private $contractNumber;

    /**
    * s_premium_dispatch foreign key
    *
    * @var int
    *
    * @ORM\Column(name="s_premium_dispatch_id", type="integer", nullable=true)
    */
    private $sPremiumDispatchID;

    /**
     * @var string
     *
     * @ORM\Column(name="product_id", type="string", nullable=true)
     */
    private $productID;


    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


    /**
     * @var string
     * @ORM\Column(name="features", type="text", nullable=true)
     */
    private $features;

    /**
     * @return string
     */
    public function getProductID()
    {
        return $this->productID;
    }

    /**
     * @param string $productID
     */
    public function setProductID(string $productID)
    {
        $this->productID = $productID;
    }

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;


    /**
     * @var \DateTime
     *
     * @Assert\DateTime()
     *
     * @ORM\Column(name="imported_date", type="date", nullable=true)
     */
    private $importedDate;

    /**
     * saved form state
     *
     * @var bool
     * @ORM\Column(name="checked", type="boolean")
     */
    private $checked = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changed", type="datetime", nullable=true)
     */
    private $changed = 'now';


    /**
     * @return string
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @param string $features
     */
    public function setFeatures(string $features)
    {
        $this->features = $features;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param int $sorting
     */
    public function setSorting(int $sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="sorting", type="integer", nullable=true)
     */
    private $sorting;


    /**
     * @return int
     */
    public function getContractNumber()
    {
        return $this->contractNumber;
    }

    /**
     * @param int $contractNumber
     */
    public function setContractNumber(int $contractNumber)
    {
        $this->contractNumber = $contractNumber;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     */
    public function setChecked(bool $checked)
    {
        $this->checked = $checked;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getImportedDate()
    {
        return $this->importedDate;
    }

    /**
     * @param \DateTime $importedDate
     */
    public function setImportedDate(\DateTime $importedDate)
    {
        $this->importedDate = $importedDate;
    }

    /**
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * @param \DateTime $changed
     */
    public function setChanged(\DateTime $changed)
    {
        $this->changed = $changed;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getSPremiumDispatchID()
    {
        return $this->sPremiumDispatchID;
    }

    /**
     * @param int $sPremiumDispatchID
     */
    public function setSPremiumDispatchID(int $sPremiumDispatchID)
    {
        $this->sPremiumDispatchID = $sPremiumDispatchID;
    }

}
