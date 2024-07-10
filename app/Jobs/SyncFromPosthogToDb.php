<?php

namespace App\Jobs;

use App\Console\Commands\SyncToHubspotCategoriesCommand;
use App\Helpers\PosthogHelper;
use PostHog\PostHog;
use App\Models\Kpi;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;

class SyncFromPosthogToDb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 50;

    protected $id;
    protected $model;
    protected $params = [];
    protected $categories = [];
    protected $subcategories = [];
    protected $dateFrom;
    protected $dateTo;
    protected $event = 'check_highlights';
    protected $interval;
    protected $charts = [
        'topSubcategories',
        'topWords',
    ];

    public function __construct($model, $dateFrom, $interval)
    {
        $this->id = $model->id;
        $this->model = $model instanceof Team ? 'team' : 'user';
        $this->dateFrom = $dateFrom;
        // 'month' or 'week'
        $this->interval = $interval;

        switch ($this->interval) {
            case 'week':
                $this->dateTo = date('Y-m-d', strtotime("{$this->dateFrom} +6 day"));
                break;
            case 'month':
                $this->dateTo = date('Y-m-t', strtotime("{$this->dateFrom}"));
                break;
            default:
                throw new InvalidArgumentException("The interval '{$this->interval} does not exist, use 'week' or 'month'.");
        }
    }

    public function handle()
    {
        if (!config('posthog.enabled')) {
            Log::debug("Posthog not enabled, otherwise update model {$this->model}, id {$this->id})");

            return true;
        }

        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
        );

        $this->params = [
            'interval' => $this->interval,
            'from' => $this->dateFrom,
            'to' => $this->dateTo,
            'lang' => null,
            'events' => [$this->event],
            'categories' => null,
            'subcategories' => null,
            'group_subcategories' => true,
            'inclusive' => 'non_inclusive',
        ];
        $this->categories = SyncToHubspotCategoriesCommand::loadTableData('categories');
        $this->subcategories = SyncToHubspotCategoriesCommand::loadTableData('diversity_dimension_drivers', true);

        switch ($this->model) {
            case 'team':
                $result = $this->handleTeam();
                break;
            case 'user':
                $result = $this->handleUser();
                break;
            default:
                throw new InvalidArgumentException("Invalid model type {$this->model} (must be either 'team' or 'user').");
        }

        if (!$result) {
            throw new InvalidArgumentException("{$this->model}, id {$this->id} could not be synced to Posthog.");
        }

        return $result;
    }

    public function handleUser()
    {
        return 'user analytics collection not implemented due to rate limit concerns';
    }

    public function handleTeam()
    {
        $team = Team::find($this->id);
        if (!$team instanceof Team) {
            throw new InvalidArgumentException("Team id '{$this->id} does not exist.");
        }

        $properties = PosthogHelper::getOrganizationFilter($team);
        $params = $this->params;

        $kpi = "{$this->event}-{$this->interval}";
        $dateRange = "{$this->dateFrom} - {$this->dateTo}";
        print_r($dateRange);

        $result = [];
        foreach ($this->charts as $chart) {
            $params['chart'] = $chart;
            $data = PosthogHelper::getData($params, $properties, $team, true, $this->categories, $this->subcategories);
            $result["$chart $dateRange"] = $this->storeKpis($team, $data['events']['check_highlights'], $kpi . $params['chart']);
        }

        return $result;
    }


    protected function storeKpis($model, $data, $kpi)
    {
        if (!empty($data['error'])) {
            return $data['message'];
        }

        foreach ($data as $name => $values) {
            if (is_array($values)) {
                foreach ($values['counts'] as $date => $count) {
                    Kpi::storeKpi($model, $kpi, $count, $date, $name);
                }
            } else {
                Kpi::storeKpi($model, $kpi, $values, $this->dateFrom, $name);
            }
        }

        return 'ok';
    }
}
