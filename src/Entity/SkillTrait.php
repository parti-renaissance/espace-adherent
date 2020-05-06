<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait SkillTrait
{
    public function addSkill(BaseSkill $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }
    }

    public function replaceSkill(BaseSkill $actual, BaseSkill $new): void
    {
        $this->removeSkill($actual);
        $this->addSkill($new);
    }

    public function removeSkill(BaseSkill $skill): void
    {
        if ($this->skills->contains($skill)) {
            $this->skills->removeElement($skill);
        }
    }

    /**
     * @return Skill[]|Collection|iterable
     */
    public function getSkills(): iterable
    {
        return $this->skills;
    }

    public function setSkills(ArrayCollection $skills): void
    {
        $this->skills = $skills;
    }
}
