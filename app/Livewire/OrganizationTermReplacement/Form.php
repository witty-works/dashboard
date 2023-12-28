<?php

namespace App\Livewire\OrganizationTermReplacement;

use App\Models\TermReplacement;
use Http\Client\Exception\RequestException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $term;
    public $term_replacement_id;
    public $replacement;
    public $explanation;
    public $url;
    public $emoji;
    public $language_code;
    public $matching_type;
    public $word_type;
    public $show_word_type;

    protected $listeners = ['edit'];

    protected $rules = [
        'term_replacement_id' => 'int|nullable',
        'term' => 'required|min:1|max:250',
        'replacement' => 'required|min:1|max:250|different:term',
        'explanation' => 'required_with:url,emoji|max:100',
        'url' => 'nullable|url|max:250',
        'emoji' => 'nullable',
        'language_code' => 'nullable|string|in:en,de,',
        'matching_type' => 'nullable|string|in:case_insensitive,case_sensitive,lemmatize',
        'word_type' => 'nullable|string|in:a,v,s',
    ];

    /**
     * The model instance.
     *
     * @var mixed
     */
    public $model;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount($model)
    {
        $this->model = $model;

        $this->resetForm();
    }

    protected function getLanguageCodes()
    {
        $languageCodes = TermReplacement::LANGUAGE_CODES;
        if ($this->matching_type === 'lemmatize') {
            $this->show_word_type = true;
            unset($languageCodes['']);
        } else {
            $this->show_word_type = false;
            $this->word_type = '';
        }

        return $languageCodes;
    }

    protected function cleanValues($subscribed)
    {
        $this->term = trim($this->term);
        $this->replacement = trim($this->replacement);
        if (!$subscribed) {
            $this->matching_type = 'case_insensitive';
        }
    }

    public function render()
    {
        $this->cleanValues($this->model->subscribed());

        $params = ['language_codes' => $this->getLanguageCodes()];
        return view('livewire.organization-term-replacement.form', $params);
    }

    public function showHideWordType()
    {
        return $this->render();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->term_replacement_id = '';
        $this->term = '';
        $this->replacement = '';
        $this->explanation = '';
        $this->url = '';
        $this->emoji = '';
        $this->language_code = null;
        $this->matching_type = '';
        $this->word_type = '';
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    public function edit(TermReplacement $termReplacement)
    {
        $this->term_replacement_id = $termReplacement->id;
        $this->term = $termReplacement->term;
        $this->replacement = $termReplacement->replacement;
        $this->explanation = $termReplacement->explanation;
        $this->url = $termReplacement->url;
        $this->emoji = $termReplacement->emoji;
        $this->language_code = $termReplacement->language_code;
        $this->matching_type = $termReplacement->matching_type;
        $this->word_type = $termReplacement->word_type;

        return $this->render();
    }

    public function storeTermReplacement()
    {
        $this->validate();
        $this->cleanValues($this->model->subscribed());

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $this->term = trim($this->term);
        $this->replacement = trim($this->replacement);

        $query = TermReplacement::query()
            ->where('team_id', $this->model->id)
            ->where('term', $this->term);

        if ($this->language_code) {
            $query->where(function ($q) {
                $q->whereNull('language_code')
                    ->orWhere('language_code', '')
                    ->orWhere('language_code', $this->language_code);
            });
        }

        if ($this->term_replacement_id) {
            $query->whereNot('id', $this->term_replacement_id);
            $termReplacement = TermReplacement::find($this->term_replacement_id);
        }

        if ($query->exists()) {
            $message = __('guidelines.term_already_exists');
            throw ValidationException::withMessages(['term' => $message]);
        }

        if (!empty($termReplacement)) {
            if ($this->model->id !== $termReplacement->team_id) {
                $message = __('guidelines.term_replacement_error');
                throw ValidationException::withMessages(['term' => $message]);
            }
        } else {
            if ($this->model->getTermReplacementsLimitReached()) {
                $message = __(
                    'guidelines.term_replacement_limit_reached_error',
                    ['max_count' => $this->model->getTermReplacementsCount()]
                );
                throw ValidationException::withMessages(['term' => $message]);
            }

            $termReplacement = new TermReplacement();
        }

        $this->emoji = TermReplacement::validateEmoji($this->emoji);

        $this->handleMatchingType();

        $termReplacement->term = $this->term;
        $termReplacement->replacement = $this->replacement;
        $termReplacement->explanation = $this->explanation;
        $termReplacement->url = $this->url;
        $termReplacement->emoji = $this->emoji;
        $termReplacement->language_code = $this->language_code;
        $termReplacement->word_type = $this->word_type;
        $termReplacement->team_id = $this->model->id;

        $termReplacement->save();
        $termReplacement->dispatchEventToPosthog();

        $this->dispatch('saved');
        $this->resetForm();
    }

    protected function handleMatchingType()
    {
        switch ($this->matching_type) {
            case 'lemmatize':
                if (preg_match('/[\t\n\r\f\v ]/', $this->term)) {
                    $message = __('guidelines.lemmatize_requires_single_word');
                    throw ValidationException::withMessages(['term' => $message]);
                }

                if (empty($this->language_code)) {
                    $languageCodes = $this->getLanguageCodes();
                    $this->language_code = key($languageCodes);
                }

                if (empty($this->word_type)) {
                    $wordTypes = TermReplacement::WORD_TYPES;
                    $this->word_type = key($wordTypes);
                }

                $result = $this->getLemma($this->term, $this->language_code);
                if ($result === null) {
                    $message = __('guidelines.lemmatization_error');
                    throw ValidationException::withMessages(['matching_type' => $message]);
                }

                $this->term = $result;

                $result = $this->getLemma($this->replacement, $this->language_code);
                if ($result !== null) {
                    $this->replacement = $result;
                }
                break;
            case 'case_sensitive':
                $this->word_type = '=';
                break;
            case 'case_insensitive':
            default:
                $this->word_type = '-';
                break;
        }
    }

    protected function getLemma($text, $lang)
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['urls'])) {
            return null;
        }

        $url = reset($endpoint['urls']) . '/lemmatize';

        $data = [
            'text' => $text,
            'lang' => $lang,
        ];

        try {
            if (empty($endpoint['user'])) {
                $response = Http::get($url, $data);
            } else {
                $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                    ->get($url, $data);
            }
        } catch (RequestException $e) {
            $response = false;
        }

        if (!$response || ($response->failed() && $response->status() !== 404)) {
            $message = __('guidelines.lemmatization_error');
            throw ValidationException::withMessages(['matching_type' => $message]);
        }

        return $response->json();
    }
}
