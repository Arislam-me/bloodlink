<?php

namespace App\Filament\Auth;

use App\Rules\Recaptcha;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Admin login with Google reCAPTCHA. The widget writes its token into
 * `data.recaptcha`, which is verified server-side by the Recaptcha rule.
 */
class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        $components = [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];

        if (Recaptcha::enabled()) {
            $components[] = ViewField::make('recaptcha')
                ->view('filament.recaptcha-field')
                ->hiddenLabel()
                ->rules([new Recaptcha]);
        }

        return $schema->components($components);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            return parent::authenticate();
        } catch (ValidationException $e) {
            // A reCAPTCHA token works only once: make the user tick the box again.
            $this->dispatch('recaptcha-reset');
            throw $e;
        }
    }
}
