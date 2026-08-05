<?php

namespace App\Models;


trait GuidelinesTrait
{
    public function getTotalTermReplacementsCount()
    {
        return $this->termReplacements()->count();
    }

    public function getTotalFalsePositivesCount()
    {
        return $this->falsePositives()->count();
    }

}
