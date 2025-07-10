
     <?php
     require_once __DIR__ . '/../../vendor/autoload.php';
     use Dotenv\Dotenv;

     $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
     $dotenv->load();

     return [
         'google' => [
             'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
             'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
             'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI']
         ],
         'linkedin' => [
             'client_id' => $_ENV['LINKEDIN_CLIENT_ID'],
             'client_secret' => $_ENV['LINKEDIN_CLIENT_SECRET'],
             'redirect_uri' => $_ENV['LINKEDIN_REDIRECT_URI']
         ]
     ];
     ?>
