<?php
namespace PostLabelCenter\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="PostLabelPluginConfigurationRepository")
 * @ORM\Table(name="post_label_plugin_configuration")
 */

class PostLabelPluginConfiguration extends ModelEntity
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
     * @var string
     *
     * @ORM\Column(name="unit_id", type="string", nullable=true)
     */
    private $unitID;

    /**
    *
    * @var string
    *
    * @ORM\Column(name="unit_guid", type="string", nullable=true)
    */
    private $unitGUID;

    /**
     * @var string
     *
     * @ORM\Column(name="license", type="string", nullable=true)
     */
    private $license;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", nullable=true)
     */
    private $identifier;


    /**
     *
     * @var string
     *
     * @ORM\Column(name="info_name", type="string", nullable=true)
     */
    private $infoName;
    /**
     *
     * @var string
     *
     * @ORM\Column(name="info_name_extended", type="string", nullable=true)
     */
    private $infoNameExtended;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="info_phone", type="string", nullable=true)
     */
    private $infoPhone;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="info_street", type="string", nullable=true)
     */
    private $infoStreet;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="info_zip", type="string", nullable=true)
     */
    private $infoZip;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="info_city", type="string", nullable=true)
     */
    private $infoCity;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="info_county", type="string", nullable=true)
     */
    private $infoCountry;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="bank_account_owner", type="string", nullable=true)
     */
    private $bankAccountOwner;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="bank_bic", type="string", nullable=true)
     */
    private $bankBic;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="account_iban", type="string", nullable=true)
     */
    private $accountIban;

    /**
     * @return string
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * @param string $clientid
     */
    public function setClientID($clientid)
    {
        $this->clientID = $clientid;
    }


    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", nullable=true)
     */
    private $clientID;

    /**
     * @var string
     *
    * @ORM\Column(name="api_url", type="string", nullable=true)
    */
    private $apiURL;

    /**
     * @var string
     * @ORM\Column(name="return_time_max", type="integer", nullable=true)
     */
    private $returnTimeMax;


    /**
     * @var string
     * @ORM\Column(name="return_reasons", type="text", nullable=true)
     */
    private $returnReasons;

    /**
     * @var string
     * @ORM\Column(name="contract_numbers", type="text", nullable=true)
     */
    private $contractNumbers;

    /**
     * @var bool
     * @ORM\Column(name="data_import_only", type="boolean", nullable=false)
     */
    private $dataImportOnly;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="paper_layout", type="string", nullable=true)
     */
    private $paperLayout;

    /**
     * @var bool
     * @ORM\Column(name="return_order_allowed", type="boolean", nullable=false)
     */
    private $returnOrderAllowed;

    /**
     * @return string
     */
    public function getUnitID()
    {
        return $this->unitID;
    }

    /**
     * @param string $unitID
     */
    public function setUnitID($unitID)
    {
        $this->unitID = $unitID;
    }

    /**
     * @return string
     */
    public function getUnitGUID()
    {
        return $this->unitGUID;
    }

    /**
     * @param string $unitGUID
     */
    public function setUnitGUID($unitGUID)
    {
        $this->unitGUID = $unitGUID;
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * @return string
     */
    public function getReturnTimeMax()
    {
        return $this->returnTimeMax;
    }

    /**
     * @param string $returnTimeMax
     */
    public function setReturnTimeMax($returnTimeMax)
    {
        $this->returnTimeMax = $returnTimeMax;
    }

    /**
     * @return string
     */
    public function getReturnReasons()
    {
        return $this->returnReasons;
    }

    /**
     * @param string $returnReasons
     */
    public function setReturnReasons($returnReasons)
    {
        $this->returnReasons = $returnReasons;
    }

    /**
     * @return string
     */
    public function getContractNumbers()
    {
        return $this->contractNumbers;
    }

    /**
     * @param string $contractNumbers
     */
    public function setContractNumbers($contractNumbers)
    {
        $this->contractNumbers = $contractNumbers;
    }

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changed", type="datetime", nullable=true)
     */
    private $changed = 'now';


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
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function setChanged($changed)
    {
        $this->changed = $changed;
    }

    /**
    * @return string
    */
    public function getApiUrl()
    {

        return $this->apiURL;
    }

    /**
     * @param string $apiURL
     */

    public function setApiUrl($url)
    {
        return $this->apiURL = $url;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getPaperLayout()
    {
        return $this->paperLayout;
    }

    /**
     * @param string $paperLayout
     */
    public function setPaperLayout($paperLayout)
    {
        $this->paperLayout = $paperLayout;
    }

    /**
     * @return bool
     */
    public function isDataImportOnly()
    {
        return $this->dataImportOnly;
    }

    /**
     * @param bool $dataImportOnly
     */
    public function setDataImportOnly($dataImportOnly)
    {
        $this->dataImportOnly = $dataImportOnly;
    }

    /**
     * @return string
     */
    public function getInfoName()
    {
        return $this->infoName;
    }

    /**
     * @param string $infoName
     */
    public function setInfoName($infoName)
    {
        $this->infoName = $infoName;
    }

    /**
     * @return string
     */
    public function getInfoNameExtended()
    {
        return $this->infoNameExtended;
    }

    /**
     * @param string $infoNameExtended
     */
    public function setInfoNameExtended($infoNameExtended)
    {
        $this->infoNameExtended = $infoNameExtended;
    }

    /**
     * @return string
     */
    public function getInfoPhone()
    {
        return $this->infoPhone;
    }

    /**
     * @param string $infoPhone
     */
    public function setInfoPhone($infoPhone)
    {
        $this->infoPhone = $infoPhone;
    }

    /**
     * @return string
     */
    public function getInfoStreet()
    {
        return $this->infoStreet;
    }

    /**
     * @param string $infoStreet
     */
    public function setInfoStreet($infoStreet)
    {
        $this->infoStreet = $infoStreet;
    }

    /**
     * @return string
     */
    public function getInfoZip()
    {
        return $this->infoZip;
    }

    /**
     * @param string $infoZip
     */
    public function setInfoZip($infoZip)
    {
        $this->infoZip = $infoZip;
    }

    /**
     * @return string
     */
    public function getInfoCity()
    {
        return $this->infoCity;
    }

    /**
     * @param string $infoCity
     */
    public function setInfoCity($infoCity)
    {
        $this->infoCity = $infoCity;
    }

    /**
     * @return string
     */
    public function getInfoCountry()
    {
        return $this->infoCountry;
    }

    /**
     * @param string $infoCountry
     */
    public function setInfoCountry($infoCountry)
    {
        $this->infoCountry = $infoCountry;
    }

    /**
     * @return bool
     */
    public function isReturnOrderAllowed()
    {
        return $this->returnOrderAllowed;
    }

    /**
     * @param bool $returnOrderAllowed
     */
    public function setReturnOrderAllowed($returnOrderAllowed)
    {
        $this->returnOrderAllowed = $returnOrderAllowed;
    }

    /**
     * @return string
     */
    public function getBankAccountOwner()
    {
        return $this->bankAccountOwner;
    }

    /**
     * @param string $bankAccountOwner
     */
    public function setBankAccountOwner($bankAccountOwner)
    {
        $this->bankAccountOwner = $bankAccountOwner;
    }

    /**
     * @return string
     */
    public function getBankBic()
    {
        return $this->bankBic;
    }

    /**
     * @param string $bankBic
     */
    public function setBankBic($bankBic)
    {
        $this->bankBic = $bankBic;
    }

    /**
     * @return string
     */
    public function getAccountIban()
    {
        return $this->accountIban;
    }

    /**
     * @param string $accountIban
     */
    public function setAccountIban($accountIban)
    {
        $this->accountIban = $accountIban;
    }



}
