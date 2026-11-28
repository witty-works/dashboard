<?php

namespace App\Http\Controllers;

use App\Helpers\CategoryDataHelper;
use App\Jobs\SyncUserToNlpApi;
use App\Models\Domain;
use App\Models\FalsePositive;
use App\Models\LanguageGuidelines;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserGuidelinesApiController extends Controller
{
    public function deleteDomain(Request $request)
    {
        $user = $this->getUser($request);
        $domain = Domain::validateDomain($request->get('domain'));

        $result = Domain::where('domain', $domain)
            ->where('user_id', $user->id)->delete();

        if ($result) {
            dispatch(new SyncUserToNlpApi($user, 'high'));
        }

        return response()->noContent();
    }

    public function putDomain(Request $request)
    {
        $user = $this->getUser($request);
        $domain = Domain::validateDomain($request->get('domain'));

        if (strlen($domain) > 250) {
            return response()->json(['message' => "'domain' query parameter is too long"], 400);
        }

        $result = Domain::upsert(
            [
                ['user_id' => $user->id, 'domain' => $domain],
            ],
            ['user_id', 'domain']
        );

        if ($result === 1) {
            dispatch(new SyncUserToNlpApi($user, 'high'));

            $domain = Domain::where('domain', $domain)
                ->where('user_id', $user->id)->first();

            if ($domain instanceof Domain) {
                $domain->dispatchEventToPosthog(['from_extension' => true]);
            }
        }

        return response()->noContent();
    }

    public function deleteFalsePositive(Request $request)
    {
        $user = $this->getUser($request);
        $false_positive = $request->get('false_positive', '');
        if (empty($false_positive)) {
            return response()->json(['message' => "'false_positive' query parameter is empty"], 400);
        }

        $result = FalsePositive::where('false_positive', $false_positive)
            ->where('user_id', $user->id)->delete();

        if ($result) {
            dispatch(new SyncUserToNlpApi($user, 'high'));
        }

        return response()->noContent();
    }

    public function putFalsePositive(Request $request)
    {
        $user = $this->getUser($request);
        $false_positive = $request->get('false_positive', '');
        if (empty($false_positive)) {
            return response()->json(['message' => "'false_positive' query parameter is empty"], 400);
        }

        if (strlen($false_positive) > 250) {
            return response()->json(['message' => "'false_positive' query parameter is too long"], 400);
        }

        $result = FalsePositive::upsert(
            [
                ['user_id' => $user->id, 'false_positive' => $false_positive],
            ],
            ['user_id', 'false_positive']
        );

        if ($result === 1) {
            dispatch(new SyncUserToNlpApi($user, 'high'));

            $false_positive = FalsePositive::where('false_positive', $false_positive)
                ->where('user_id', $user->id)->first();

            if ($false_positive instanceof FalsePositive) {
                $false_positive->dispatchEventToPosthog(['from_extension' => true]);
            }
        }

        return response()->noContent();
    }

    public function adjustDDDLevel(Request $request)
    {
        $user = $this->getUser($request);
        $diversity_dimension = $request->get('diversity_dimension');
        if (empty($diversity_dimension)) {
            return response()->json(['error' => "'diversity_dimension' query parameter is empty"], 400);
        }

        // NLP API has '_advanced' as a suffix and not 'advanced_' as a prefix
        if (str_ends_with($diversity_dimension, '_advanced')) {
            $diversity_dimension = str_replace('_advanced', '', $diversity_dimension);
            $is_advanced = true;
        } else {
            $is_advanced = false;
        }

        $diversityDimensionDrivers = CategoryDataHelper::loadTableData('diversity_dimension_drivers');
        $ddds = $diversityDimensionDrivers->toArray();
        if (!array_key_exists($diversity_dimension, $ddds)) {
            return response()->json(['error' => "Invalid parameter for 'diversity_dimension': '$diversity_dimension'"], 400);
        }

        $direction = $request->get('direction', 'down');
        if ($direction !== 'down' && $is_advanced && LanguageGuidelines::isBasicOnly($ddds[$diversity_dimension]['proficiency_level'])) {
            return response()->json(['error' => "'$diversity_dimension' is basic only"], 400);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($user);
        $disabledCategories = (array) $languageGuidelines->disabled_categories;

        $ddd = $diversity_dimension;
        if ($is_advanced) {
            $ddd = 'advanced_' . $ddd;

            // check if advanced is enabled
            $currentLevel = in_array($ddd, $disabledCategories)
                ? LanguageGuidelines::DISABLED
                : LanguageGuidelines::ADVANCED_ENABLED;
        } else {
            $currentLevel = LanguageGuidelines::DISABLED;
        }

        if ($currentLevel === LanguageGuidelines::DISABLED) {
            // check if basic is enabled
            $currentLevel = in_array($diversity_dimension, $disabledCategories)
                ? LanguageGuidelines::DISABLED
                : LanguageGuidelines::BASIC_ENABLED;
        }

        if ($direction === 'down') {
            $targetLevel = $is_advanced
                ? LanguageGuidelines::BASIC_ENABLED
                : LanguageGuidelines::DISABLED;
        } elseif ($direction === 'off') {
            $targetLevel = LanguageGuidelines::DISABLED;
        } else {
            $targetLevel = $is_advanced
                ? LanguageGuidelines::ADVANCED_ENABLED
                : LanguageGuidelines::BASIC_ENABLED;
        }

        if ($targetLevel !== $currentLevel) {
            $languageGuidelines->adjustLevel($diversity_dimension, $targetLevel);

            dispatch(new SyncUserToNlpApi($user, 'high'));

            $languageGuidelines->dispatchEventToPosthog(['from_extension' => true]);
        }

        return response()->noContent();
    }

    /**
     * The routes carry `auth:extension`, so the bearer token — a Passport access
     * token from the browser extension, or a Microsoft id_token from the Office
     * add-in — has already been validated by App\Auth\ExtensionUserResolver and
     * an unauthenticated request never reaches the controller.
     *
     * @return \App\Models\User
     */
    protected function getUser(Request $request)
    {
        return $request->user();
    }
}
