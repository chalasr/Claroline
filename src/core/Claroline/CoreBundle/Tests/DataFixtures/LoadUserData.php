<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * Loads five users with the following roles :
     * 
     * Jane Doe  : ROLE_USER
     * Bob Doe   : ROLE_USER
     * Bill Doe   : ROLE_USER
     * Henry Doe : ROLE_WS_CREATOR (i.e. ROLE_USER -> ROLE_WS_CREATOR)
     * John Doe  : ROLE_ADMIN (i.e. ROLE_USER -> ROLE_WS_CREATOR -> ROLE_ADMIN)
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setFirstName('Jane');
        $user->setLastName('Doe');
        $user->setUserName('user');
        $user->setPlainPassword('123');
        
        $secondUser = new User();
        $secondUser->setFirstName('Bob');
        $secondUser->setLastName('Doe');
        $secondUser->setUserName('user_2');
        $secondUser->setPlainPassword('123');

        $thirdUser = new User();
        $thirdUser->setFirstName('Bill');
        $thirdUser->setLastName('Doe');
        $thirdUser->setUserName('user_3');
        $thirdUser->setPlainPassword('123');
        
        $wsCreator = new User();
        $wsCreator->setFirstName('Henry');
        $wsCreator->setLastName('Doe');
        $wsCreator->setUserName('ws_creator');
        $wsCreator->setPlainPassword('123');
        $wsCreator->addRole($this->getReference('role/ws_creator'));
        
        $admin = new User();
        $admin->setFirstName('John');
        $admin->setLastName('Doe');
        $admin->setUserName('admin');
        $admin->setPlainPassword('123');
        $admin->addRole($this->getReference('role/admin'));
        
        $userManager = $this->container->get('claroline.user.manager');
        $userManager->create($user);
        $userManager->create($secondUser);
        $userManager->create($thirdUser);
        $userManager->create($wsCreator);
        $userManager->create($admin);

        $this->addReference('user/user', $user);
        $this->addReference('user/user_2', $secondUser);
        $this->addReference('user/user_3', $thirdUser);
        $this->addReference('user/ws_creator', $wsCreator);
        $this->addReference('user/admin', $admin);
    }
}