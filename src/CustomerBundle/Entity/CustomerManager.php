<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\CustomerBundle\Entity;

use Doctrine\ORM\QueryBuilder;

use Sonata\Component\Customer\CustomerManagerInterface;
use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

class CustomerManager extends BaseEntityManager implements CustomerManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        $query = $this->getRepository()
            ->createQueryBuilder('c')
            ->select('c');

        $fields = $this->getEntityManager()->getClassMetadata($this->class)->getFieldNames();
        foreach ($sort as $field => $direction) {
            if (!in_array($field, $fields)) {
                unset($sort[$field]);
            }
        }
        if (count($sort) == 0) {
            $sort = array('lastname' => 'ASC');
        }
        foreach ($sort as $field => $direction) {
            $query->orderBy(sprintf('c.%s', $field), strtoupper($direction));
        }

        $parameters = array();

        if (isset($criteria['is_fake'])) {
            $query->andWhere('c.isFake = :isFake');
            $parameters['isFake'] = $criteria['is_fake'];
        }

        $query->setParameters($parameters);

        $pager = new Pager();
        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }
}
