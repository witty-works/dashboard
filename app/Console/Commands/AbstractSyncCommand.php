<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

abstract class AbstractSyncCommand extends Command
{
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    protected function filterQueryByIds($query, $optionName = 'ids')
    {
        $idsString = $this->option($optionName);

        if ($idsString !== null) {
            $ids = explode(',', trim($idsString));
            if ($ids !== array_filter($ids, 'is_numeric')) {
                $this->error('Non integer passed as ID: ' . $idsString);
                return false;
            }

            $query->whereIn('id', $ids);
        }

        return $query;
    }
}
