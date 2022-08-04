<?php

namespace App\Models;

use App\Events\OrganizationGuidelinesUpdated;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

trait OrganizationGuidelinesUpdateTrait
{
    use HasEvents;

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Fire a custom model event for the given event.
     *
     * @param  string  $event
     * @param  string  $method
     * @return mixed|null
     */
    protected function fireCustomModelEvent($event, $method)
    {
        if (!in_array($event, ['saved', 'restored', 'deleted'])) {
            return;
        }

        $result = static::$dispatcher->$method(new OrganizationGuidelinesUpdated($this->team));
        if (!is_null($result)) {
            return $result;
        }
    }
}
