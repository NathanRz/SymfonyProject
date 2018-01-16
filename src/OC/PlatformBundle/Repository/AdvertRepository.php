<?php

namespace OC\PlatformBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * AdvertRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdvertRepository extends \Doctrine\ORM\EntityRepository
{
	public function whereCurrentYear(QueryBuilder $qb){
		$qb
		->andWhere('a.date BETWEEN :start AND :end')
		->setParameter('start', new \DateTime(date('Y').'-01-01'))
		->setParameter('end', new \DateTime(date('Y').'-12-31'));
	}

	public function findByAuthor($author){
		$qb = $this->createQueryBuilder('a');

		$qb
		->where('a.author = :author')
		->setParameter('author', $author);

		$this->whereCurrentYear($qb);

		$qb->orderBy('a.date', 'DESC');

		return $qb->getQuery()->getResult();
	}

	public function getAdvertWithComments($id){
		$qb = $this->createQueryBuilder('a')->leftJoin('a.comments', 'com')->addSelect('com');

		$qb->andWhere('a.id = :id')->setParameter('id', $id);

		return $qb->getQuery()->getSingleResult();
	}

	public function getAdvertWithCategories(array $catNames){
		$qb = $this
			->createQueryBuilder('a')
			->innerJoin('a.categories', 'c')
			->addSelect('c');

		$qb->where($qb->expr()->in('c.name', $catNames));

		return $qb
			->getQuery()
			->getResult();
	}

	public function getAdverts($page, $nbPerPage){
		$query = $this->createQueryBuilder('a')->orderBy('a.date', 'DESC')->leftJoin('a.image', 'i')->addSelect('i')->leftJoin('a.categories', 'c')->addSelect('c')->getQuery();

		$query->setFirstResult(($page-1)*$nbPerPage)->setMaxResults($nbPerPage);

		return new Paginator($query, true);
	}

	public function getLastXAdverts($nb){
		$qb = $this
			->createQueryBuilder('a')
			->orderBy('a.date', 'DESC')
			->setMaxResults($nb);

		return $qb->getQuery()
				->getResult();
	}
}
