<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 29.06.2015
 * Time: 12:51
 */

namespace Ibw\JobeetBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity()
 */
abstract class Users extends BaseUser implements TwoFactorInterface, TrustedComputerInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255)
     */
    protected $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(name="auth_code", type="integer", nullable=true)
     */
    private $authCode;

    /**
     * @ORM\Column(name="trusted", type="json_array", nullable=true)
     */
    private $trusted;

    public function __construct(){
        parent::__construct();
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /*
     * Implement the TwoFactorInterface
     */

    public function isEmailAuthEnabled() {
        return true; // This can also be a persisted field but it is enabled by default for now
    }

    public function getEmailAuthCode() {
        return $this->authCode;
    }

    public function setEmailAuthCode($authCode) {
        $this->authCode = $authCode;
    }

    /*
     * Implement the TrustedComputerInterface
     */

    public function addTrustedComputer($token, \DateTime $validUntil)
    {
        $this->trusted[$token] = $validUntil->format("r");
    }

    public function isTrustedComputer($token)
    {
        if (isset($this->trusted[$token])) {
            $now = new \DateTime();
            $validUntil = new \DateTime($this->trusted[$token]);
            return $now < $validUntil;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
}