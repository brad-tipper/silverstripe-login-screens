<?php

namespace BradTipper\LoginScreens;

use SilverStripe\Core\Extension;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * Adds the configured colours to the login screens and exposes the logo URLs to the AppBrand template.
 *
 * @extends Extension<Security>
 */
class LoginScreenExtension extends Extension
{
    protected function onAfterInit(): void
    {
        $css = LoginScreen::css();
        if ($css !== '') {
            Requirements::customCSS($css, 'login-screens');
        }
    }

    public function getLoginLogoUrl(): string
    {
        return LoginScreen::logoUrl();
    }

    public function getLoginLogoDarkUrl(): string
    {
        return LoginScreen::logoUrl(true);
    }
}
