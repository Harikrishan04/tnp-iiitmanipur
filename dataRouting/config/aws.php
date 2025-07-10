
  <?php
  require_once __DIR__ . '/../../vendor/autoload.php';
  use Dotenv\Dotenv;

  $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
  $dotenv->load();

  return [
      'ses' => [
          'region' => $_ENV['AWS_SES_REGION'],
          'credentials' => [
              'key' => $_ENV['AWS_ACCESS_KEY_ID'],
              'secret' => $_ENV['AWS_SECRET_ACCESS_KEY']
          ],
          'sender_email' => 'no-reply-tnp@iiitmanipur.ac.in'
      ]
  ];
  ?>
