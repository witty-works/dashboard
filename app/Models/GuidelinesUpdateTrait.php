<?php

namespace App\Models;

use App\Events\UserGuidelinesUpdated;
use App\Events\OrganizationGuidelinesUpdated;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Laravel\Jetstream\Jetstream;

trait GuidelinesUpdateTrait
{
    use HasEvents;

    public function team()
    {
        return $this->belongsTo(Jetstream::teamModel(), 'team_id');
    }

    public function user()
    {
        return $this->belongsTo(Jetstream::userModel(), 'user_id');
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

        if ($this->user) {
            $result = static::$dispatcher->$method(new UserGuidelinesUpdated($this->user));
            if (!is_null($result)) {
                return $result;
            }
        } elseif ($this->team) {
            $result = static::$dispatcher->$method(new OrganizationGuidelinesUpdated($this->team));
            if (!is_null($result)) {
                return $result;
            }
        }
    }
}
