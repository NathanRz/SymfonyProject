<?php

namespace OC\PlatformBundle\DoctrineListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use OC\PlatformBundle\Email\AdvertMailer;
use OC\PlatformBundle\Entity\Advert;

class AdvertCreationListener{

	/**
	* @var AdvertMailer
	*/
	private $advertMailer;

	public function __construct(AdvertMailer $advMailer){
		$this->advertMailer = $advMailer;
	}

	public function postPersist(LifecycleEventArgs $args){
		$entity = $args->getObject();

		if(!$entity instanceof Advert){
			return;
		}

		//$this->advertMailer->sendNewNotification($entity);
	}

}