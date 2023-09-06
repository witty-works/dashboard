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
        if ($this->getTermReplacementsCount() === config('stripe.plans.witty_enterprise.features.organization_term_replacements.count')) {
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
        if ($this->getFalsePositivesCount() === config('stripe.plans.witty_enterprise.features.organization_false_positives.count')) {
            return false;
        }

        return $this->getTotalFalsePositivesCount() >= $this->getFalsePositivesCount();
    }
}
