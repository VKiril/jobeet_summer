<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 29.06.2015
 * Time: 12:30
 */

namespace Ibw\JobeetBundle\Services;



use Ibw\JobeetBundle\Entity\User;
use Ibw\JobeetBundle\Entity\Users;
use Jhg\NexmoBundle\Managers\SmsManager;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Swift_Mailer;
use Swift_Message;

class SmsMailer implements AuthCodeMailerInterface
{
    private $smsSender;
    private $senderMail;
    private $mailer;
    private $isSmsDisabled;
    private $deliveryPhoneNumber;
    private $senderAddress;



    public function __construct(SmsManager $smsSender, Swift_Mailer $mailer, $isSmsDisabled, $deliveryPhoneNumber, $senderAddress)
    {
        $this->smsSender = $smsSender;
        $this->mailer = $mailer;
        $this->isSmsDisabled = $isSmsDisabled;
        $this->deliveryPhoneNumber = $deliveryPhoneNumber;
        $this->senderAddress = $senderAddress;
    }

    /**
     * Send the auth code to the user via email
     *
     * @param \Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface $user
     */
    public function sendAuthCode(TwoFactorInterface $user)
    {
        $msg = "Your validation code is ";// . $user->getEmailAuthCode();

        $fromName = "SMSAuthTest";

        $this->sendSMS($user, $msg, $fromName);
    }

    public function sendSMS(Users $user, $msg, $fromName)
    {
        // Fallback to mail if isSmsDisabled
        if ($this->isSmsDisabled) {
            $this->sendMail($user->getEmail(), $msg, $fromName);
        } else {

            if ($this->deliveryPhoneNumber !== null) {
                $number = $this->deliveryPhoneNumber;
            } else {
                $number = $user->getPhoneNumber();
            }

            $this->smsSender->sendText($number, $msg, $fromName);
        }
    }

    public function sendMail($deliveryAddress, $msg, $fromName)
    {
        $message = Swift_Message::newInstance()
            ->setSubject("[SMS - ".$fromName."]")
            ->setFrom($this->senderAddress)
            ->setTo($deliveryAddress);
        $message->setBody($msg, 'text/html');

        return $this->mailer->send($message);
    }
}