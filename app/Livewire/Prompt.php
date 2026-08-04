<?php

namespace App\Livewire;

use Livewire\Component;
use Jfcherng\Diff\DiffHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Prompt extends Component
{
    public $prompt;
    public $orignal_response;
    public $witty_response;
    public $results;
    public $diff;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount()
    {
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();
    }

    public function sendPrompt()
    {
        $this->orignal_response = '';
        $this->witty_response = '';
        $this->results = '';
        $this->resetErrorBag();

        // The /prompt route is not registered when LLM support is switched off
        // for this installation, but the component is still reachable over the
        // Livewire endpoint, so the check is repeated here.
        if (!config('app.llm_enabled')) {
            $message = __('content.prompt_error');
            throw ValidationException::withMessages(['prompt' => $message]);
        }

        $user = Auth::user();
        if (!$user->planId() || !$user->currentTeam->llm_alternatives) {
            $message = __('content.prompt_error');
            throw ValidationException::withMessages(['prompt' => $message]);
        }

        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['urls'])) {
            $message = __('content.prompt_error');
            throw ValidationException::withMessages(['prompt' => $message]);
        }

        $url = reset($endpoint['urls']) . '/v1.0/prompt?user_email=' . $user->email;

        $data = [
            'text' => $this->prompt,
            'client' => 'dasboard',
        ];

        try {
            if (empty($endpoint['user'])) {
                $response = Http::post($url, $data);
            } else {
                $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                    ->post($url, $data);
            }
        } catch (\Exception $e) {
            return;
        }

        if (!$response || $response->failed()) {
            $message = $response->status() === 403 ? __('content.prompt_auth_error') : __('content.prompt_error');
            throw ValidationException::withMessages(['prompt' => $message]);
        }

        $response = $response->json();

        $this->orignal_response = $response['inititial_response'] ?? '';
        $this->witty_response = $response['reviewed_response'] ?? $this->orignal_response;
        $this->results = $response['check_results'] ?? [];

        $rendererName = 'Combined';
        $this->diff = DiffHelper::calculate(
            $this->orignal_response,
            $this->witty_response,
            $rendererName,
            ['ignoreWhitespace' => false, 'ignoreLineEnding' => false],
            ['showHeader' => false, 'detailLevel' => 'word']
        );
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.prompt');
    }

    static public function getColor($result)
    {
        if ($result['subcategory'] === 'corporate_rules') {
            return '#6f9FED';
        }
        # inclusive green
        if (empty($result['gravity'])) {
            return '#BCD485';
        }
        # openly discriminating red
        if ($result['gravity'] < 1.5) {
            return '#E6635A';
        }
        # advanced
        if ($result['gravity'] > 2.5) {
            return '#F6EC6B';
        }
        #basic
        return '#EB9F46';
    }

    static public function getSubcategoryLabel($result)
    {
        $words = explode(':', $result['label']);
        $word = end($words);
        return trim($word);
    }
}
