<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectComment;
use AppBundle\Entity\PostAddress;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CitizenProjectCommentTest extends TestCase
{
    public function testConstructValidComment(): void
    {
        $citizenProject = $this->createCitizenProject();
        $adherent = $this->createAdherent($citizenProject);
        $commment = $this->createComment($citizenProject, $adherent);

        self::assertNull($commment->getId());
        self::assertInstanceOf(UuidInterface::class, $commment->getUuid());
        self::assertSame($adherent, $commment->getAuthor());
        self::assertSame($citizenProject, $commment->getCitizenProject());
        self::assertSame('Awesome', $commment->getContent());
        self::assertSame('Jean DUPONT', $commment->getAuthorFullName());
        self::assertInstanceOf(\DateTimeImmutable::class, $commment->getCreatedAt());
        self::assertLessThanOrEqual(2, time() - $commment->getCreatedAt()->getTimestamp());
    }

    public function testCommentCanBecomeAnonymous(): void
    {
        $comment = $this->createComment();

        self::assertNotNull($comment->getAuthor());
        self::assertSame('Jean DUPONT', $comment->getAuthorFullName());

        $comment->makeAnonymous();
        self::assertNull($comment->getAuthor());
        self::assertSame('Anonyme', $comment->getAuthorFullName());
    }

    public function testCommentCannotBeAssociatedToUnapprovedCitizenProject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The citizen project is not approved yet');

        $citizenProject = $this->createCitizenProject(false);
        $adherent = $this->createAdherent($citizenProject);

        $this->createComment($citizenProject, $adherent);
    }

    public function testCommentContentCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Comment content cannot be empty');

        $this->createComment(null, null, '');
    }

    public function testAdherentMustBeAMemberOfTheCitizenProject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only members of the CitizenProject can comment');

        $citizenProject = $this->createCitizenProject();
        $adherent = $this->createAdherent();

        $this->createComment($citizenProject, $adherent);
    }

    private function createComment(CitizenProject $citizenProject = null, Adherent $adherent = null, ?string $content = 'Awesome'): CitizenProjectComment
    {
        $citizenProject = $citizenProject ?: $this->createCitizenProject();
        $adherent = $adherent ?: $this->createAdherent($citizenProject);

        return new CitizenProjectComment(null, $citizenProject, $adherent, $content);
    }

    private function createCitizenProject(bool $approved = true): CitizenProject
    {
        $citizenProject = new CitizenProject(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'Project Name',
            'Subtitle',
            $this->createMock(CitizenProjectCategory::class)
        );

        if ($approved) {
            $citizenProject->approved();
        }

        return $citizenProject;
    }

    private function createAdherent(CitizenProject $citizenProject = null): Adherent
    {
        $adherent = new Adherent(
            Uuid::uuid4(),
            'toto@mail.com',
            'password',
            'male',
            'Jean',
            'DUPONT',
            new \DateTime('1991-02-09'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );

        if ($citizenProject) {
            $adherent->followCitizenProject($citizenProject);
        }

        return $adherent;
    }
}
