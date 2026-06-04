<?php
namespace App\Controllers;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\LinkedIn;
use App\Services\AuthService;
use App\Config\JwtConfig;

class OAuthController {
    private $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }

    // Google Methods
    public function googleRedirect() {
        // Save the requested role from the frontend
        $_SESSION['oauth_role'] = $_GET['role'] ?? 'student';

        $provider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirectUri'  => $_ENV['GOOGLE_REDIRECT_URI'],
        ]);
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    public function googleCallback() {
        $provider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirectUri'  => $_ENV['GOOGLE_REDIRECT_URI'],
        ]);

        // Security Check: Validate State to prevent CSRF
        if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
            exit('Invalid state');
        }

        try {
            // Step 1: Exchange code for an access token
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Step 2: Fetch user details from Google
            $googleUser = $provider->getResourceOwner($token);
            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();
            $avatar = $googleUser->getAvatar();
            $role = $_SESSION['oauth_role'] ?? 'student';

            // Step 2.5: Domain Validation based on Role
            if (in_array($role, ['student', 'admin'])) {
                if (!str_ends_with($email, '@iiitmanipur.ac.in')) {
                    // Redirect back to login with an error message
                    header('Location: /frontend/login.html?error=domain_invalid');
                    exit;
                }
            }

            // Step 3: Check if user exists, link Google ID, and mint JWT
            $jwt = $this->authService->handleOAuthLogin('google', $googleId, $email, $avatar, $role);

            // Step 4: Redirect back to frontend with the token
            header('Location: /frontend/login.html?token=' . $jwt);
            exit;
            
        } catch (\Exception $e) {
            exit('Authentication failed: ' . $e->getMessage());
        }
    }
    
    // LinkedIn Methods
    public function linkedinRedirect() {
        // Implementation for LinkedIn redirect
        $_SESSION['oauth_role'] = $_GET['role'] ?? 'student';

        $provider = new LinkedIn([
            'clientId'     => $_ENV['LINKEDIN_CLIENT_ID'],
            'clientSecret' => $_ENV['LINKEDIN_CLIENT_SECRET'],
            'redirectUri'  => $_ENV['LINKEDIN_REDIRECT_URI'],
        ]);
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    public function linkedinCallback() {
        $provider = new LinkedIn([
            'clientId'     => $_ENV['LINKEDIN_CLIENT_ID'],
            'clientSecret' => $_ENV['LINKEDIN_CLIENT_SECRET'],
            'redirectUri'  => $_ENV['LINKEDIN_REDIRECT_URI'],
        ]);

        if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
            exit('Invalid state');
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            $linkedinUser = $provider->getResourceOwner($token);
            $email = $linkedinUser->getEmail();
            $linkedinId = $linkedinUser->getId();
            $avatar = $linkedinUser->getImageUrl();
            $role = $_SESSION['oauth_role'] ?? 'student';

            if (in_array($role, ['student', 'admin'])) {
                if (!str_ends_with($email, '@iiitmanipur.ac.in')) {
                    header('Location: /frontend/login.html?error=domain_invalid');
                    exit;
                }
            }

            $jwt = $this->authService->handleOAuthLogin('linkedin', $linkedinId, $email, $avatar, $role);

            header('Location: /frontend/login.html?token=' . $jwt);
            exit;
            
        } catch (\Exception $e) {
            exit('Authentication failed: ' . $e->getMessage());
        }
    }
}
