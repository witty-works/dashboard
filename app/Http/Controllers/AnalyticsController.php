<?php

namespace App\Http\Controllers;

use App\Console\Commands\SyncToHubspotCategoriesCommand;
use App\Models\User;
use App\Helpers\PosthogHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use RuntimeException;

class AnalyticsController extends Controller
{
    protected $refresh;
    protected $categories;
    protected $subcategories;

    public function __construct(Request $request)
    {
        $this->refresh = $request->get('refresh', false);
        $this->categories = SyncToHubspotCategoriesCommand::loadTableData('categories');
        $this->subcategories = SyncToHubspotCategoriesCommand::loadTableData('diversity_dimension_drivers', true);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            abort(403);
        }

        return view('analytics', [
            'user' => $user,
            'categories' => $this->categories,
            'default_event' => PosthogHelper::getDefaultEvent(),
            'is_premium_user' => $user->isPremium(),
        ]);
    }

    protected function teamAnalyticsAllowed(User $user = null)
    {
        if (
            empty($user)
            || empty($user->currentTeam)
            || (!$user->hasTeamPermission($user->currentTeam, 'edit_guidelines')
                && !$user->currentTeam->user_access_to_team_analytics
            )
        ) {
            abort(403);
        }
    }

    public function organization(Request $request)
    {
        $user = $request->user();

        $this->teamAnalyticsAllowed($user);

        return view('analytics', [
            'team' => $user->currentTeam,
            'team_edit' => $user->hasTeamPermission($user->currentTeam, 'edit_guidelines'),
            'categories' => $this->categories,
            'default_event' => PosthogHelper::getDefaultEvent(),
            'is_premium_user' => $user->currentTeam->isPremium(),
        ]);
    }

    public function userApi(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            abort(403);
        }

        $properties = PosthogHelper::getUserFilter($user);

        try {
            return $this->buildJson($request, $properties, $user, $this->refresh, $this->categories, $this->subcategories);
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Analytics data temporarily unavailable'], 503);
        }
    }

    public function organizationApi(Request $request)
    {
        $user = $request->user();
        $this->teamAnalyticsAllowed($user);

        $properties = PosthogHelper::getOrganizationFilter($user->currentTeam);

        try {
            return $this->buildJson($request, $properties, $user->currentTeam, $this->refresh, $this->categories, $this->subcategories);
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Analytics data temporarily unavailable'], 503);
        }
    }

    protected function buildJson(Request $request, $properties, $model, $refresh, $categories, $subcategories)
    {
        $rules = [
            'chart' => 'required|in:dau,total,topSubcategories,topWords',
            'interval' => 'in:day,week,month',
            'from' => 'required',
            'to' => 'nullable',
            'lang' => 'nullable|in:en,de',
            'events' => 'nullable|array|in:check_highlights,popover_open,alternative,ignore,learning_bites',
            'categories' => 'nullable|array|in:' . implode(',', $this->categories->keys()->toArray()),
            'subcategories' => 'nullable|array|in:' . implode(',', $this->subcategories->keys()->toArray()),
            'group_subcategories' => 'nullable:bool',
            'inclusive' => 'nullable|in:inclusive,non_inclusive,both',
        ];

        $validated = $request->validate($rules);

        $data = PosthogHelper::getData($validated, $properties, $model, $refresh, $categories, $subcategories);
        $code = empty($data['error']) ? 200 : $data['error'];

        return response()->json($data, $code);
    }
}
