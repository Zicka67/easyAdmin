<?php 

namespace App\EventSubscriber;

use DateTimeImmutable;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister;



class AdminSubscriber implements EventSubscriberInterface 

{
    public static function getSubscribedEvents() {

        return [
            BeforeEntityPersistedEvent::class => ['setCreatedAt'],
            BeforeEntityUpdatedEvent::class => ['setUpdatedAt']
        ];
    }

    public function setCreatedAt(BeforeEntityPersistedEvent $event) 
    {
       
        $entityInstance = $event->getEntityInstance();

        if(!$entityInstance instanceof Product && !$entityInstance instanceof Category) return;

        $entityInstance->getCreatedAt(new DateTimeImmutable());

    }

    public function setUpdatedAt(BeforeEntityPersistedEvent $event) 
    {
       
        $entityInstance = $event->getEntityInstance();

        if(!$entityInstance instanceof Product && !$entityInstance instanceof Category) return;

        $entityInstance->setUpdatedAt(new DateTimeImmutable());

    }

}