<?php

namespace App\Livewire;

use App\Http\Controllers\OAuthController;
use Livewire\Component;
use Jfcherng\Diff\DiffHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Prompt extends Component
{
    public $prompt;
    public $orignal_response;
    public $witty_response;
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
        $this->resetErrorBag();

        $user = Auth::user();
        if (!$user->currentTeam->llm_alternatives || !$user->hasRole('Superadmin')) {
            $message = __('content.prompt_error');
            throw ValidationException::withMessages(['prompt' => $message]);
        }

        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['urls'])) {
            return;
        }

        $url = reset($endpoint['urls']) . '/v1.0/prompt';

        $data = [
            'text' => $this->prompt,
        ];

        try {
            $token = $user->getTokenFor(OAuthController::AZURE_AD_B2C_PROVIDER);
            if ($token === null) {
                $message = __('content.prompt_error');
                throw ValidationException::withMessages(['prompt' => $message]);
            }
            $response = Http::withToken($token)->post($url, $data);
        } catch (RequestException $e) {
            return;
        }

        if (!$response || $response->failed()) {
            $message = $response->status() === 403 ? __('content.prompt_auth_error') : __('content.prompt_error');
            throw ValidationException::withMessages(['prompt' => $message]);
        }

        $response = $response->json();

        $this->orignal_response = $response['inititial_response'] ?? '';
        $this->witty_response = $response['reviewed_response'] ?? $this->orignal_response;

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
}
