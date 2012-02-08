<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Testing\FixtureTestCase;
use Claroline\CoreBundle\Security\PlatformRoles;

class UserManagerTest extends FixtureTestCase
{
    /** @var Claroline\CoreBundle\Manager\UserManager */
    private $manager;

    /** @var Doctrine\ORM\EntityRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->manager = $this->client->getContainer()->get('claroline.user.manager');
        $this->repository = $this->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\User');
    }

    public function testCreateThenDeleteAnUser()
    {
        $user = $this->buildTestUser();

        $this->manager->create($user);

        $users = $this->repository->findByUsername($user->getUsername());
        $this->assertEquals(1, count($users));
        $this->assertEquals($user, $users[0]);
        $this->assertTrue($users[0]->hasRole(PlatformRoles::USER));

        $this->manager->delete($user);

        $users = $this->repository->findByUsername($user->getUsername());
        $this->assertEquals(0, count($users));
    }

    public function testCreateAnUserWithExistingUsernameThrowsAnException()
    {
        $this->setExpectedException('Claroline\CoreBundle\Exception\ClarolineException');

        $jdoe = $this->buildTestUser();
        $copiedJdoe = $this->buildTestUser();

        $this->manager->create($jdoe);
        $this->manager->create($copiedJdoe);
    }

    private function buildTestUser()
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUsername('jdoe');
        $user->setPlainPassword('123');

        return $user;
    }
}