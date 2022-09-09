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
        return $this->getTotalTermReplacementsCount() >= $this->getTermReplacementsCount();
    }

    public function getTotalFalsePositivesCount()
    {
        return $this->falsePositives()->count();
    }

    public function getFalsePositivesLimitReached()
    {
        return $this->getTotalFalsePositivesCount() >= $this->getFalsePositivesCount();
    }
}
