<?php

namespace App\Models;


trait GuidelinesTrait
{
    public function getTotalTermReplacementsCount()
    {
        return $this->termReplacements()->count();
    }

    public function getTermReplacementsLimitReached()
    {
        if ($this->getTermReplacementsCount() === LanguageGuidelines::UNLIMITED) {
            return false;
        }

        return $this->getTotalTermReplacementsCount() >= $this->getTermReplacementsCount();
    }

    public function getTotalFalsePositivesCount()
    {
        return $this->falsePositives()->count();
    }

    public function getFalsePositivesLimitReached()
    {
        if ($this->getFalsePositivesCount() === LanguageGuidelines::UNLIMITED) {
            return false;
        }

        return $this->getTotalFalsePositivesCount() >= $this->getFalsePositivesCount();
    }
}
