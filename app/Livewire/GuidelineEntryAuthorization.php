<?php

namespace App\Livewire;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * For the guideline lists' Livewire actions, which take an entry by an id the
 * browser sends: the id is the client's to choose, so the entry has to belong
 * to the user or team the component shows, besides the user being allowed to
 * edit that user's or team's guidelines.
 */
trait GuidelineEntryAuthorization
{
    /**
     * An entry of the list the component shows: the team's or the user's.
     * For the forms, which the user lists inherit from the team lists.
     */
    protected function authorizeEntry(Model $entry): void
    {
        if ($this->model instanceof Team) {
            $this->authorizeTeamEntry($entry);
        } else {
            $this->authorizeUserEntry($entry);
        }
    }

    /** An entry of the signed-in user's own list. */
    protected function authorizeUserEntry(Model $entry): void
    {
        abort_unless(
            (int) Auth::id() === (int) $this->model->id
                && (int) $entry->user_id === (int) $this->model->id,
            403
        );
    }

    /** An entry of the team's list, for a user who may edit its guidelines. */
    protected function authorizeTeamEntry(Model $entry): void
    {
        abort_unless(
            Auth::user()?->hasTeamPermission($this->model, 'edit_guidelines')
                && (int) $entry->team_id === (int) $this->model->id,
            403
        );
    }
}
