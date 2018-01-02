<?php

namespace OC\PlatformBundle\Email;

use OC\PlatformBundle\Entity\Advert;

class AdvertMailer{

	/**
	* @var \Swift_Mailer
	*/
	private $mailer;

	public function __construct(\Swift_Mailer $mailer){
		$this->mailer = $mailer;
	}

	public function sendNewNotification(Advert $advert){
		$message = new \Swift_Message(
			'Confirmation de création de votre annonce',
			'Votre annonce à bien été créé.'
		);

		$message->addTo($advert->getAuthor())->addFrom('admin@amazing.com');

		$this->mailer->send($message);
	}

}