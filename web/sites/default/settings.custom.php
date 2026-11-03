<?php

$config['symfony_mailer_lite.symfony_mailer_lite_transport.smtp']['configuration']['user'] = getenv('smtp__user');
$config['symfony_mailer_lite.symfony_mailer_lite_transport.smtp']['configuration']['pass'] = getenv('smtp__pass');

// Disable Lagoon logs if not on Amazee.
if (!getenv('LAGOON')) {
  $config['lagoon_logs.settings']['disable'] = 1;
}
